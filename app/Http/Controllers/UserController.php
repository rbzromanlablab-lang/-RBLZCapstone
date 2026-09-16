<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Assignment;
use App\Models\Property;
use App\Models\PropertyHistory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->latest()
            ->paginate(10);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(),
            'roles' => User::roles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $userData = Arr::except($validated, ['employee_number', 'department', 'subject_area']);

        $user = User::create($userData);
        $user->syncRoleProfile($validated);

        return redirect()
            ->route('users.index')
            ->with('success', "{$user->name} added successfully.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => User::roles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $userData = Arr::except($validated, ['employee_number', 'department', 'subject_area']);

        if (blank($userData['password'] ?? null)) {
            unset($userData['password']);
        }

        $user->update($userData);
        $user->syncRoleProfile($validated);

        return redirect()
            ->route('users.index')
            ->with('success', "{$user->name} updated successfully.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if (request()->user()?->is($user)) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot deactivate your own account while logged in.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $statusLabel = $user->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('users.index')
            ->with('success', "{$user->name} {$statusLabel} successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if (request()->user()?->is($user)) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account while logged in.');
        }

        $name = $user->name;

        DB::transaction(function () use ($user) {
            $activeAssignments = $user->teacherAssignments()
                ->where('status', Assignment::STATUS_ACTIVE)
                ->get(['id', 'property_id', 'quantity_assigned']);

            $affectedPropertyIds = [];

            foreach ($activeAssignments as $assignment) {
                $property = Property::query()
                    ->lockForUpdate()
                    ->find($assignment->property_id);

                if (! $property) {
                    continue;
                }

                $property->increment('quantity', (int) $assignment->quantity_assigned);
                $affectedPropertyIds[] = $property->id;

                PropertyHistory::create([
                    'property_id' => $property->id,
                    'action_type' => 'assignment_removed',
                    'reference' => 'user:'.$user->id,
                    'action_date' => now()->toDateString(),
                    'remarks' => 'Active assignment cleared because the assigned user account was deleted.',
                ]);
            }

            $user->teacherAssignments()->delete();
            $user->delete();

            Property::query()
                ->whereIn('id', array_values(array_unique($affectedPropertyIds)))
                ->get()
                ->each
                ->syncInventoryStatus();
        });

        return redirect()
            ->route('users.index')
            ->with('success', "{$name} deleted successfully.");
    }
}
