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

        .sidebar-shell .nav-link:hover,
        .sidebar-shell .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }

        .content-shell {
            min-width: 0;
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

        @media (max-width: 991.98px) {
            .sidebar-shell {
                min-height: auto;
                width: 100%;
                border-right: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .app-shell {
                display: block !important;
            }
        }
    </style>
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
                    data-bs-delay="3500"
                >
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong class="d-block mb-1">Success</strong>
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
        document.querySelectorAll('[data-flash-toast]').forEach((element) => {
            bootstrap.Toast.getOrCreateInstance(element).show();
        });
    </script>
    @stack('scripts')
</body>
</html>
