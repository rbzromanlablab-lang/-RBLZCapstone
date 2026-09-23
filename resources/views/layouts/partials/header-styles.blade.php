<style>
    @media screen {
        .topbar-card {
            color: #fff;
            border-top: 3px solid #d9a441;
            background:
                radial-gradient(ellipse at top right, rgba(217, 164, 65, .16), transparent 52%),
                linear-gradient(115deg, #10253f 0%, #183153 58%, #254f7a 100%);
            box-shadow: 0 12px 30px rgba(16, 37, 63, .16);
        }

        .topbar-card h1 {
            color: #fff;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .topbar-card .page-section-title {
            color: #f3dfb6;
            line-height: 1.6;
        }

        .topbar-card .header-email {
            color: #d5e2f1;
        }

        .topbar-card .account-summary {
            border-color: rgba(213, 226, 241, .25);
        }

        .topbar-card .account-role {
            color: #183153;
            background: #f3dfb6;
            border: 1px solid rgba(217, 164, 65, .5);
        }

        .topbar-card .profile-avatar {
            color: #183153;
            background: #e5eef8;
            box-shadow: 0 0 0 3px rgba(213, 226, 241, .18);
        }

        .topbar-card .profile-avatar img {
            box-shadow: none;
        }

        .topbar-card [data-desktop-navigation] {
            border-color: rgba(213, 226, 241, .55);
            background: rgba(255, 255, 255, .06);
        }

        .mobile-app-header {
            background: linear-gradient(110deg, #10253f, #254f7a);
            border-bottom: 2px solid #d9a441;
        }

        .dashboard-card h2.h4,
        .dashboard-card h3.h5 {
            padding: .85rem 1rem;
            color: #183153;
            border-left: 4px solid #d9a441;
            border-radius: .25rem .8rem .8rem .25rem;
            background: linear-gradient(110deg, #dce8f5, #f0f5fb);
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .content-shell .table > thead > tr > th {
            --bs-table-bg: #183153;
            --bs-table-color: #fff;
            color: #fff;
            background-color: #183153;
            border-bottom: 2px solid #d9a441;
            padding-top: .9rem;
            padding-bottom: .9rem;
            font-size: .85rem;
            font-weight: 600;
            vertical-align: middle;
        }

        .content-shell .table > thead > tr > th:first-child {
            border-top-left-radius: .65rem;
        }

        .content-shell .table > thead > tr > th:last-child {
            border-top-right-radius: .65rem;
        }
    }

    @media screen and (max-width: 575.98px) {
        .dashboard-card h2.h4,
        .dashboard-card h3.h5 {
            padding: .75rem .85rem;
        }
    }
</style>
