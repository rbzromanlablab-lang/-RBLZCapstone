<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PARDS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --pards-navy: #183153;
            --pards-sky: #d9e8f5;
            --pards-gold: #d9a441;
            --pards-ink: #1f2937;
            --pards-panel: #ffffff;
            --pards-bg: #eef3f8;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(217, 164, 65, 0.16), transparent 24%),
                linear-gradient(180deg, #f8fbfd 0%, var(--pards-bg) 100%);
            color: var(--pards-ink);
        }

        .app-shell {
            min-height: 100vh;
        }

        .sidebar-shell {
            width: 260px;
            flex-shrink: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #10253f 0%, var(--pards-navy) 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-shell .nav-link {
            color: #dbe7f3;
            border-radius: 0.9rem;
            padding: 0.8rem 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .sidebar-shell .nav-link.active {
            color: #fff3d6;
            background: linear-gradient(90deg, rgba(217, 164, 65, 0.2), rgba(217, 164, 65, 0.06));
            box-shadow: inset 3px 0 0 var(--pards-gold);
        }

        .sidebar-shell .nav-link.active i {
            color: var(--pards-gold);
        }

        @keyframes sidebar-item-enter {
            from { opacity: 0; transform: translateX(-12px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (min-width: 992px) and (prefers-reduced-motion: no-preference) {
            .sidebar-shell .nav-item {
                animation: sidebar-item-enter 360ms cubic-bezier(0.22, 1, 0.36, 1) both;
                animation-delay: var(--menu-delay, 0ms);
            }
        }

        @media (max-width: 991.98px) and (prefers-reduced-motion: no-preference) {
            .sidebar-shell.offcanvas-lg {
                transition: transform 360ms cubic-bezier(0.22, 1, 0.36, 1);
            }

            .sidebar-shell:is(.showing, .show):not(.hiding) .nav-item {
                animation: sidebar-item-enter 360ms cubic-bezier(0.22, 1, 0.36, 1) both;
                animation-delay: calc(80ms + var(--menu-delay, 0ms));
            }

            .sidebar-shell:is(.showing, .show):not(.hiding) .sidebar-header {
                animation: sidebar-item-enter 320ms ease-out both;
            }
        }

        .content-shell {
            min-width: 0;
        }

        @media (min-width: 992px) {
            .sidebar-shell {
                overflow: hidden;
                transition: width 320ms ease, padding 320ms ease, opacity 240ms ease;
            }
            .sidebar-shell > * { min-width: 211px; }
            .sidebar-shell.desktop-collapsed {
                width: 0;
                padding-left: 0 !important;
                padding-right: 0 !important;
                border-right: 0;
                opacity: 0;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .sidebar-shell { transition: none !important; }
        }

        .hamburger-icon {
            display: inline-block;
            position: relative;
            width: 22px;
            height: 18px;
            vertical-align: middle;
        }

        .hamburger-icon span {
            position: absolute;
            left: 0;
            width: 100%;
            height: 2px;
            border-radius: 2px;
            background: currentColor;
            transition: transform 220ms ease, opacity 160ms ease;
        }

        .hamburger-icon span:nth-child(1) { top: 0; }
        .hamburger-icon span:nth-child(2) { top: 8px; }
        .hamburger-icon span:nth-child(3) { top: 16px; }
        .hamburger-icon.is-open span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .hamburger-icon.is-open span:nth-child(2) { opacity: 0; }
        .hamburger-icon.is-open span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        @media (prefers-reduced-motion: reduce) {
            .hamburger-icon span { transition: none; }
        }

        .account-summary, .account-details {
            min-width: 0;
        }

        .account-details {
            overflow-wrap: anywhere;
        }

        .account-role {
            flex-shrink: 0;
        }

        .profile-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--pards-sky);
        }

        .table-responsive {
            overflow-wrap: normal;
            word-break: normal;
        }

        .table-responsive th {
            white-space: nowrap;
        }

        .topbar-card,
        .dashboard-card,
        .summary-card {
            border: 0;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.07);
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: var(--pards-sky);
            color: var(--pards-navy);
            font-size: 1.3rem;
        }

        .page-section-title {
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 700;
        }

        .quick-stat {
            border-radius: 1rem;
            background: #f8fafc;
        }

        .flash-toast-container {
            z-index: 1080;
        }

        .flash-toast {
            min-width: 320px;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        }

        @keyframes confirmation-appear {
            from { opacity: 0; transform: translateY(-12px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes confirmation-check {
            from { transform: scale(0.6); }
            to { transform: scale(1); }
        }

        @media (prefers-reduced-motion: no-preference) {
            .flash-toast.show { animation: confirmation-appear 280ms ease-out; }
            .flash-toast.show .confirmation-check { display: inline-block; animation: confirmation-check 350ms ease-out; }
        }

        @media (max-width: 991.98px) {
            .sidebar-shell {
                --bs-offcanvas-width: min(320px, 88vw);
                width: var(--bs-offcanvas-width);
                min-height: 100%;
                overflow-y: auto;
                border-right: 0;
                background: linear-gradient(180deg, #10253f 0%, var(--pards-navy) 100%);
            }

            .mobile-app-header {
                position: sticky;
                top: 0;
                z-index: 1020;
                background: var(--pards-navy);
                box-shadow: 0 2px 12px rgba(15, 23, 42, 0.12);
            }

            .app-shell {
                display: block !important;
            }
        }

        @media (max-width: 575.98px) {
            .content-shell main {
                padding: 1rem !important;
            }

            .dashboard-card.p-4, .summary-card.p-4 {
                padding: 1rem !important;
            }

            .topbar-card, .dashboard-card, .summary-card {
                border-radius: 1rem;
            }

            .topbar-card h1 {
                font-size: 1.35rem;
            }

            .account-summary {
                flex-wrap: wrap;
                gap: 0.5rem !important;
                border-top: 1px solid #e5e7eb;
                padding-top: 0.75rem;
            }

            .account-details {
                flex: 1 1 160px;
            }

            .content-shell .btn, .mobile-app-header .btn, .sidebar-shell .btn-close {
                min-height: 44px;
            }

            .sidebar-shell .btn-close {
                min-width: 44px;
            }

            .form-control, .form-select {
                min-height: 44px;
                font-size: 1rem;
            }

            .flash-toast-container {
                width: 100%;
                padding: 1rem !important;
            }

            .flash-toast {
                min-width: 0;
                width: 100%;
            }

            .dashboard-card {
                overflow-wrap: anywhere;
            }

            .dashboard-section-heading {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .mobile-record-table thead {
                display: none;
            }

            .mobile-record-table, .mobile-record-table tbody,
            .mobile-record-table tr, .mobile-record-table td {
                display: block;
                width: 100%;
            }

            .mobile-record-table tr {
                border: 1px solid #dee2e6;
                border-radius: 0.75rem;
                margin-bottom: 1rem;
                padding: 0.75rem;
            }

            .mobile-record-table td {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.65rem 0;
                text-align: right;
                overflow-wrap: anywhere;
            }

            .mobile-record-table td[data-label]::before {
                content: attr(data-label);
                flex: 0 0 42%;
                text-align: left;
                font-weight: 600;
                color: #6b7280;
                overflow-wrap: normal;
            }

            .mobile-record-table td:last-child {
                border: 0;
            }

            .mobile-record-table .record-action .btn {
                width: 100%;
            }

            .mobile-record-table .record-empty {
                display: block;
                text-align: center;
            }
        }
    </style>
    @include('layouts.partials.button-styles')
    @stack('styles')
</head>
<body>
    @if (session('success') || session('error'))
        <div class="toast-container position-fixed top-0 end-0 p-4 flash-toast-container">
            @if (session('success'))
                <div
                    class="toast align-items-center text-bg-success border-0 flash-toast"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    data-flash-toast
                    data-bs-delay="5500"
                >
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong class="d-block mb-1"><i class="bi bi-check-circle-fill confirmation-check me-2" aria-hidden="true"></i>Success</strong>
                            {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div
                    class="toast align-items-center text-bg-danger border-0 flash-toast"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    data-flash-toast
                    data-bs-delay="4500"
                >
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong class="d-block mb-1">Action Needed</strong>
                            {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="d-lg-flex app-shell">
        @include('layouts.partials.sidebar')

        <div class="flex-grow-1 min-vh-100 content-shell">
            @include('layouts.partials.navbar')

            <main class="container-fluid py-4 px-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const navigation = document.getElementById('appNavigation');
        const navigationToggle = document.querySelector('[data-navigation-toggle]');
        const updateNavigationIcon = (isOpen) => {
            navigationToggle.setAttribute('aria-expanded', String(isOpen));
            document.querySelectorAll('.mobile-app-header .hamburger-icon, .sidebar-shell .hamburger-icon').forEach((icon) => {
                icon.classList.toggle('is-open', isOpen);
            });
        };
        navigation.addEventListener('show.bs.offcanvas', () => updateNavigationIcon(true));
        navigation.addEventListener('hide.bs.offcanvas', () => updateNavigationIcon(false));
        navigation.addEventListener('hidden.bs.offcanvas', () => updateNavigationIcon(false));

        const desktopNavigation = document.querySelector('[data-desktop-navigation]');
        const desktopViewport = window.matchMedia('(min-width: 992px)');
        let desktopCollapsed = false;
        const updateDesktopNavigation = () => {
            navigation.classList.toggle('desktop-collapsed', desktopCollapsed);
            navigation.inert = desktopViewport.matches && desktopCollapsed;
            desktopNavigation.setAttribute('aria-expanded', String(!desktopCollapsed));
            desktopNavigation.setAttribute('aria-label', desktopCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
            desktopNavigation.querySelector('.hamburger-icon').classList.toggle('is-open', !desktopCollapsed);
        };
        desktopNavigation.addEventListener('click', () => {
            desktopCollapsed = !desktopCollapsed;
            updateDesktopNavigation();
        });
        desktopViewport.addEventListener('change', updateDesktopNavigation);

        document.querySelectorAll('[data-flash-toast]').forEach((element) => {
            bootstrap.Toast.getOrCreateInstance(element).show();
        });
    </script>
    @stack('scripts')
    @include('partials.ajax-forms')
</body>
</html>
