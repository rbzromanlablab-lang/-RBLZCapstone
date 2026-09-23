@php
    $user = auth()->user();
    $roleLabel = match ($user?->role) {
        'teacher' => 'End-User',
        'admin', 'staff' => 'Supply Office',
        default => 'Guest',
    };
@endphp

<nav class="px-3 px-lg-4 pt-3 pt-lg-4">
    <div class="topbar-card px-3 px-lg-4 py-3">
        <button type="button" class="btn btn-outline-light d-none d-lg-inline-flex align-items-center gap-2 mb-3"
            data-desktop-navigation aria-controls="appNavigation" aria-expanded="true" aria-label="Collapse sidebar">
            <span class="hamburger-icon is-open" aria-hidden="true"><span></span><span></span><span></span></span>
            <span>Menu</span>
        </button>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <p class="page-section-title mb-2">@yield('section_label', 'Property Accountability Records and Disposal System')</p>
                <h1 class="h3 mb-1">@yield('page_title', 'Dashboard')</h1>
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
                    <div class="header-email small">{{ $user?->email ?? 'school@pards.local' }}</div>
                </div>

                <span class="account-role badge rounded-pill px-3 py-2">{{ $roleLabel }}</span>
            </div>
        </div>
    </div>
    @include('property-requests.notification')
</nav>
