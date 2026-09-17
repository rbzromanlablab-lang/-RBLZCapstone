@php
    $user = auth()->user();
    $roleLabel = match ($user?->role) {
        'teacher' => 'End-User',
        'admin', 'staff' => 'Supply Office',
        default => 'Guest',
    };
    $roleDescription = match ($user?->role) {
        'teacher' => 'View your accountability records and assigned properties in one place.',
        'admin' => 'Manage users, property records, assignments, and disposal approvals for the supply office.',
        'staff' => 'Confirm teacher requests, view property records, assign property, and submit disposal requests.',
        default => 'Manage accountability records, disposals, and asset visibility across school offices.',
    };
@endphp

<nav class="px-3 px-lg-4 pt-3 pt-lg-4">
    <div class="topbar-card px-3 px-lg-4 py-3">
        <button type="button" class="btn btn-outline-primary d-none d-lg-inline-flex align-items-center gap-2 mb-3"
            data-desktop-navigation aria-controls="appNavigation" aria-expanded="true" aria-label="Collapse sidebar">
            <span class="hamburger-icon is-open" aria-hidden="true"><span></span><span></span><span></span></span>
            <span>Menu</span>
        </button>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <p class="page-section-title mb-2">@yield('section_label', 'Property Accountability Records and Disposal System')</p>
                <h1 class="h3 mb-1">@yield('page_title', 'Dashboard')</h1>
                <p class="text-muted mb-0">{{ $roleDescription }}</p>
            </div>

            <div class="account-summary d-flex align-items-center gap-3">
                <a href="{{ route('profile.edit') }}" class="profile-avatar d-inline-flex align-items-center justify-content-center text-decoration-none" aria-label="Edit profile picture">
                    @if ($user?->profilePhoto()->exists())
                        <img src="{{ route('profile.photo') }}" alt="Your profile picture" class="profile-avatar">
                    @else
                        <i class="bi bi-person-circle fs-3" aria-hidden="true"></i>
                    @endif
                </a>
                <div class="account-details text-md-end">
                    <div class="fw-semibold">{{ $user?->name ?? 'PARDS User' }}</div>
                    <div class="text-muted small">{{ $user?->email ?? 'school@pards.local' }}</div>
                </div>

                <span class="account-role badge rounded-pill text-bg-warning px-3 py-2">{{ $roleLabel }}</span>
            </div>
        </div>
    </div>
</nav>
