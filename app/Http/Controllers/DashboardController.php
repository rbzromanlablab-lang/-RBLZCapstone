<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Disposal;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $user = request()->user();

        if ($user) {
            return match ($user->role) {
                User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
                User::ROLE_STAFF => redirect()->route('staff.dashboard'),
                User::ROLE_TEACHER => redirect()->route('teacher.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return view('dashboard.index');
    }

    public function admin(): View
    {
        return $this->buildOperationsDashboard(
            role: User::ROLE_ADMIN,
            pageTitle: 'Supply Office Dashboard',
            sectionLabel: 'Supply Office Workspace',
            welcomeTag: 'Supply office access enabled',
            roleName: 'Supply Office Admin'
        );
    }

    public function staff(): View
    {
        return $this->buildOperationsDashboard(
            role: User::ROLE_STAFF,
            pageTitle: 'Supply Office Dashboard',
            sectionLabel: 'Supply Office Workspace',
            welcomeTag: 'Supply office operations ready',
            roleName: 'Supply Office Staff'
        );
    }

    private function buildOperationsDashboard(
        string $role,
        string $pageTitle,
        string $sectionLabel,
        string $welcomeTag,
        string $roleName
    ): View {
        return view('dashboard.index', [
            'pageTitle' => $pageTitle,
            'sectionLabel' => $sectionLabel,
            'welcomeTag' => $welcomeTag,
            'roleName' => $roleName,
            'roleActions' => $this->roleActions($role),
            'stats' => [
                'properties' => Property::count(),
                'activeAssignments' => Assignment::query()->where('status', Assignment::STATUS_ACTIVE)->count(),
                'teachers' => User::query()->where('role', User::ROLE_TEACHER)->count(),
                'staffUsers' => User::query()->whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->count(),
                'disposed' => Disposal::query()->whereIn('status', Disposal::finalizedStatuses())->count(),
            ],
            'recentProperties' => Property::query()
                ->latest('updated_at')
                ->take(5)
                ->get(),
        ]);
    }

    private function roleActions(string $role): array
    {
        return match ($role) {
            User::ROLE_ADMIN => [
                [
                    'label' => 'Approve Property Requests',
                    'description' => 'Review requests forwarded by staff and approve available properties.',
                    'icon' => 'bi-clipboard-check',
                    'url' => route('property-requests.index'),
                ],
                [
                    'label' => 'Manage Users',
                    'description' => 'Create, update, activate, or deactivate accounts.',
                    'icon' => 'bi-people-fill',
                    'url' => route('users.index'),
                ],
                [
                    'label' => 'Manage Property',
                    'description' => 'Monitor and update all property records.',
                    'icon' => 'bi-box-seam-fill',
                    'url' => route('properties.index'),
                ],
                [
                    'label' => 'View Assign Property',
                    'description' => 'Review teacher and staff assignment records.',
                    'icon' => 'bi-journal-check',
                    'url' => route('assignments.index'),
                ],
                [
                    'label' => 'View Disposal Request',
                    'description' => 'Approve or disapprove staff disposal requests.',
                    'icon' => 'bi-archive-fill',
                    'url' => route('disposals.index'),
                ],
            ],
            User::ROLE_STAFF => [
                [
                    'label' => 'Confirm Teacher Requests',
                    'description' => 'Review property requests submitted by teachers.',
                    'icon' => 'bi-clipboard-plus-fill',
                    'url' => route('property-requests.index'),
                ],
                [
                    'label' => 'View Property List',
                    'description' => 'Open the current property inventory list.',
                    'icon' => 'bi-box-seam-fill',
                    'url' => route('properties.index'),
                ],
                [
                    'label' => 'Assign Property',
                    'description' => 'Create new accountability records for end-users.',
                    'icon' => 'bi-journal-check',
                    'url' => route('assignments.create'),
                ],
                [
                    'label' => 'Request Disposal',
                    'description' => 'Submit disposal requests for admin approval.',
                    'icon' => 'bi-archive-fill',
                    'url' => route('disposals.create'),
                ],
            ],
            default => [],
        };
    }
}
