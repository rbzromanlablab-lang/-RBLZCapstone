<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisposalRequest;
use App\Http\Requests\UpdateDisposalStatusRequest;
use App\Models\Assignment;
use App\Models\Disposal;
use App\Models\DisposalMethod;
use App\Models\Property;
use App\Models\PropertyHistory;
use App\Models\PropertyUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisposalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $method = trim($request->string('method')->toString());
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $disposals = Disposal::query()
            ->with(['property', 'disposedBy', 'processedBy'])
            ->when($user?->isStaff(), fn ($query) => $query->where('disposed_by', $user->id))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->whereHas('property', function ($propertyQuery) use ($search): void {
                            $propertyQuery
                                ->where('property_name', 'like', "%{$search}%")
                                ->orWhere('property_code', 'like', "%{$search}%")
                                ->orWhere('serial_number', 'like', "%{$search}%")
                                ->orWhere('office', 'like', "%{$search}%")
                                ->orWhere('location', 'like', "%{$search}%");
                        })
                        ->orWhereHas('disposedBy', function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('processedBy', function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhere('disposal_reason', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($method !== '', fn ($query) => $query->where('disposal_method', $method))
            ->when($dateFrom, fn ($query) => $query->whereDate('disposal_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('disposal_date', '<=', $dateTo))
            ->latest('disposal_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('disposals.index', [
            'disposals' => $disposals,
            'statuses' => Disposal::statuses(),
            'methods' => Disposal::methods(),
            'isAdmin' => $user?->isAdmin() ?? false,
            'isStaff' => $user?->isStaff() ?? false,
        ]);
    }

    public function create(Request $request): View
    {
        $assignment = null;

        if ($request->filled('assignment')) {
            $assignment = Assignment::query()
                ->with(['property', 'assignee'])
                ->findOrFail($request->integer('assignment'));
        }

        $properties = Property::query()
            ->where('status', '!=', Property::STATUS_DISPOSED)
            ->orderBy('property_name')
            ->get()
            ->filter(function (Property $property) use ($assignment) {
                return $property->available_quantity > 0 || $property->id === $assignment?->property_id;
            })
            ->map(function (Property $property) use ($assignment) {
                $property->setAttribute(
                    'disposal_limit',
                    $property->id === $assignment?->property_id
                        ? (int) $assignment->quantity_assigned
                        : $property->available_quantity
                );

                return $property;
            })
            ->values();

        return view('disposals.create', [
            'assignment' => $assignment,
            'disposal' => new Disposal(),
            'properties' => $properties,
            'isAdmin' => $request->user()?->isAdmin() ?? false,
            'isStaff' => $request->user()?->isStaff() ?? false,
        ]);
    }

    public function store(StoreDisposalRequest $request): RedirectResponse
    {
        $isAdmin = $request->user()?->isAdmin() ?? false;

        $disposal = DB::transaction(function () use ($request) {
            $assignment = null;

            if ($request->filled('assignment_id')) {
                $assignment = Assignment::query()
                    ->lockForUpdate()
                    ->findOrFail($request->integer('assignment_id'));
            }

            $property = Property::query()
                ->lockForUpdate()
                ->findOrFail($request->integer('property_id'));

            if ($assignment && $assignment->property_id !== $property->id) {
                throw ValidationException::withMessages([
                    'property_id' => 'The selected property does not match the assignment.',
                ]);
            }

            if ($assignment && $assignment->status !== Assignment::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'Only active assignments can be moved to disposal.',
                ]);
            }

            $quantityDisposed = $request->integer('quantity_disposed');
            $this->ensureDisposalAvailability($property, $assignment, $quantityDisposed);
            $isAdmin = $request->user()?->isAdmin() ?? false;

            $disposal = Disposal::create([
                'property_id' => $property->id,
                'assignment_id' => $assignment?->id,
                'disposed_by' => $request->user()->id,
                'admin_id' => $isAdmin ? $request->user()?->adminProfile?->id : null,
                'processed_by' => $isAdmin ? $request->user()?->id : null,
                'quantity_disposed' => $quantityDisposed,
                'disposal_date' => $request->date('disposal_date'),
                'disposal_reason' => $request->string('disposal_reason')->toString(),
                'disposal_method' => $request->string('disposal_method')->toString(),
                'disposal_method_id' => DisposalMethod::resolveId($request->string('disposal_method')->toString()),
                'remarks' => $request->input('remarks'),
                'response_notes' => null,
                'processed_at' => $isAdmin ? now() : null,
                'status' => $isAdmin ? Disposal::STATUS_COMPLETED : Disposal::STATUS_PENDING,
            ]);

            if ($isAdmin) {
                $this->finalizeDisposal($disposal, $property, $assignment);
            }

            return $disposal;
        });

        return redirect()
            ->route('disposals.show', $disposal)
            ->with('success', $isAdmin
                ? 'Disposal recorded successfully.'
                : 'Disposal request submitted for admin review.');
    }

    public function show(Disposal $disposal): View
    {
        $disposal->load(['property', 'assignment', 'disposedBy', 'processedBy']);
        abort_if(request()->user()?->isStaff() && (int) $disposal->disposed_by !== (int) request()->user()?->id, 403);

        return view('disposals.show', [
            'disposal' => $disposal,
            'canReviewDisposal' => request()->user()?->isAdmin() && $disposal->status === Disposal::STATUS_PENDING,
        ]);
    }

    public function updateStatus(
        UpdateDisposalStatusRequest $request,
        Disposal $disposal
    ): RedirectResponse {
        $nextStatus = $request->string('status')->toString();

        DB::transaction(function () use ($request, $disposal, $nextStatus) {
            $disposal = Disposal::query()
                ->lockForUpdate()
                ->findOrFail($disposal->id);

            if ($disposal->status !== Disposal::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Only pending disposal requests can be reviewed.',
                ]);
            }

            $property = Property::query()
                ->lockForUpdate()
                ->findOrFail($disposal->property_id);

            $assignment = $disposal->assignment_id
                ? Assignment::query()->lockForUpdate()->find($disposal->assignment_id)
                : null;

            $reviewPayload = [
                'admin_id' => $request->user()?->adminProfile?->id,
                'processed_by' => $request->user()?->id,
                'processed_at' => now(),
                'response_notes' => $this->normalizeText($request->input('response_notes')),
            ];

            if ($nextStatus === Disposal::STATUS_APPROVED) {
                $this->ensureDisposalAvailability($property, $assignment, (int) $disposal->quantity_disposed);
                $disposal->forceFill($reviewPayload + [
                    'status' => Disposal::STATUS_APPROVED,
                ])->save();

                $this->finalizeDisposal($disposal, $property, $assignment);

                return;
            }

            $disposal->forceFill($reviewPayload + [
                'status' => Disposal::STATUS_CANCELLED,
            ])->save();
        });

        return redirect()
            ->route('disposals.show', $disposal)
            ->with('success', $nextStatus === Disposal::STATUS_APPROVED
                ? 'Disposal request approved successfully.'
                : 'Disposal request disapproved successfully.');
    }

    private function ensureDisposalAvailability(
        Property $property,
        ?Assignment $assignment,
        int $quantityDisposed
    ): void {
        if ($assignment) {
            if ($assignment->property_id !== $property->id) {
                throw ValidationException::withMessages([
                    'property_id' => 'The selected property does not match the assignment.',
                ]);
            }

            if ($assignment->status !== Assignment::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'Only active assignments can be moved to disposal.',
                ]);
            }

            if ($quantityDisposed > (int) $assignment->quantity_assigned) {
                throw ValidationException::withMessages([
                    'quantity_disposed' => 'Only '.$assignment->quantity_assigned.' item'.((int) $assignment->quantity_assigned === 1 ? ' is' : 's are').' assigned and available for disposal.',
                ]);
            }

            return;
        }

        if ($property->status === Property::STATUS_DISPOSED) {
            throw ValidationException::withMessages([
                'property_id' => 'This property is already marked as disposed.',
            ]);
        }

        if ($quantityDisposed > (int) $property->quantity) {
            throw ValidationException::withMessages([
                'quantity_disposed' => 'Only '.$property->quantity.' item'.((int) $property->quantity === 1 ? ' is' : 's are').' available for disposal.',
            ]);
        }
    }

    private function finalizeDisposal(
        Disposal $disposal,
        Property $property,
        ?Assignment $assignment
    ): void {
        $quantityDisposed = (int) $disposal->quantity_disposed;

        if ($assignment) {
            $this->disposeAssignedPropertyUnits($assignment, $quantityDisposed);
            $remainingAssignedQuantity = max(0, (int) $assignment->quantity_assigned - $quantityDisposed);
            $updatedRemarks = trim(implode("\n", array_filter([
                $assignment->remarks,
                'Disposed '.$quantityDisposed.' item'.($quantityDisposed === 1 ? '' : 's').' on '.(optional($disposal->disposal_date)->format('F d, Y') ?? now()->format('F d, Y')).'.',
            ])));

            $assignment->update([
                'quantity_assigned' => $remainingAssignedQuantity,
                'remarks' => $updatedRemarks !== '' ? $updatedRemarks : null,
                'status' => $remainingAssignedQuantity > 0 ? Assignment::STATUS_ACTIVE : Assignment::STATUS_DISPOSED,
            ]);
        } else {
            $this->disposeAvailablePropertyUnits($property, $quantityDisposed);
            $property->decrement('quantity', $quantityDisposed);
        }

        $property->refresh();
        $property->syncInventoryStatus();
        PropertyHistory::create([
            'property_id' => $property->id,
            'action_type' => 'disposed',
            'reference' => 'disposal:'.$disposal->id,
            'action_date' => $disposal->disposal_date?->toDateString() ?? now()->toDateString(),
            'remarks' => $this->buildHistoryRemarks($disposal, $assignment),
        ]);
    }

    private function buildHistoryRemarks(Disposal $disposal, ?Assignment $assignment): string
    {
        if ($assignment) {
            return $disposal->response_notes
                ?: 'Disposed from assignment #'.$assignment->id.'. '.$disposal->disposal_reason;
        }

        return $disposal->response_notes ?: ($disposal->remarks ?: $disposal->disposal_reason);
    }

    private function normalizeText(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function disposeAssignedPropertyUnits(Assignment $assignment, int $quantityDisposed): void
    {
        $unitIds = $assignment->propertyUnits()
            ->lockForUpdate()
            ->limit($quantityDisposed)
            ->pluck('id');

        if ($unitIds->isEmpty()) {
            return;
        }

        PropertyUnit::query()
            ->whereIn('id', $unitIds)
            ->update([
                'assignment_id' => null,
                'status' => PropertyUnit::STATUS_DISPOSED,
                'updated_at' => now(),
            ]);
    }

    private function disposeAvailablePropertyUnits(Property $property, int $quantityDisposed): void
    {
        $this->ensureAvailablePropertyUnits($property);

        $unitIds = $property->availableUnits()
            ->lockForUpdate()
            ->limit($quantityDisposed)
            ->pluck('id');

        if ($unitIds->isEmpty()) {
            return;
        }

        PropertyUnit::query()
            ->whereIn('id', $unitIds)
            ->update([
                'status' => PropertyUnit::STATUS_DISPOSED,
                'updated_at' => now(),
            ]);
    }

    private function ensureAvailablePropertyUnits(Property $property): void
    {
        $missingUnits = max(0, (int) $property->quantity - $property->availableUnits()->count());

        for ($i = 0; $i < $missingUnits; $i++) {
            $property->units()->create([
                'serial_number' => Property::generateSerialNumber(),
                'status' => PropertyUnit::STATUS_AVAILABLE,
            ]);
        }
    }
}
