<style>
    /* Shared interaction states for portal and authentication screens. */
    .btn-primary {
        --bs-btn-bg: #183153;
        --bs-btn-border-color: #183153;
        --bs-btn-hover-bg: #254f7a;
        --bs-btn-hover-border-color: #254f7a;
        --bs-btn-active-bg: #10253f;
        --bs-btn-active-border-color: #10253f;
        --bs-btn-disabled-bg: #183153;
        --bs-btn-disabled-border-color: #183153;
    }

    .btn-outline-primary {
        --bs-btn-color: #183153;
        --bs-btn-border-color: #183153;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #183153;
        --bs-btn-hover-border-color: #183153;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #10253f;
        --bs-btn-active-border-color: #10253f;
        --bs-btn-disabled-color: #183153;
        --bs-btn-disabled-border-color: #183153;
    }

    .btn-outline-light {
        --bs-btn-hover-color: #10253f;
        --bs-btn-hover-bg: #f3dfb6;
        --bs-btn-hover-border-color: #f3dfb6;
        --bs-btn-active-color: #10253f;
        --bs-btn-active-bg: #d9a441;
        --bs-btn-active-border-color: #d9a441;
    }

    .btn, .btn-close, .password-toggle, .sidebar-shell .nav-link, .page-link,
    a.profile-avatar {
        transition: background-color 160ms ease, color 160ms ease,
            border-color 160ms ease, box-shadow 160ms ease,
            translate 160ms ease, opacity 160ms ease;
        -webkit-tap-highlight-color: rgba(217, 164, 65, 0.25);
        touch-action: manipulation;
    }

    .btn {
        font-weight: 600;
    }

    .btn:focus-visible, .btn-close:focus-visible, .password-toggle:focus-visible,
    .sidebar-shell .nav-link:focus-visible, .page-link:focus-visible,
    a.profile-avatar:focus-visible {
        outline: 3px solid #d9a441;
        outline-offset: 3px;
        box-shadow: 0 0 0 2px #fff;
    }

    @media (hover: hover) and (pointer: fine) {
        .btn:not(:disabled):not(.disabled):hover {
            translate: 0 -2px;
            box-shadow: 0 5px 12px rgba(24, 49, 83, 0.18);
        }

        .sidebar-shell .nav-link:hover {
            color: #f3dfb6;
            background: rgba(217, 164, 65, 0.14);
        }

        .btn-close:not(:disabled):hover, .password-toggle:not(:disabled):hover {
            opacity: 1;
            background-color: rgba(217, 164, 65, 0.18);
        }

        a.profile-avatar:hover {
            box-shadow: 0 0 0 3px #d9a441;
        }
    }

    .btn:not(:disabled):not(.disabled):active {
        translate: 0 1px;
        box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.22);
    }

    .btn.login-button:active {
        color: #fff;
        background: var(--pards-primary-dark, #10253f);
    }

    .sidebar-shell .nav-link:active, .password-toggle:not(:disabled):active {
        background-color: rgba(217, 164, 65, 0.25);
    }

    .btn-close:not(:disabled):active, a.profile-avatar:active {
        opacity: 0.7;
    }

    .btn:disabled, .btn.disabled {
        translate: none;
        box-shadow: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .btn, .btn-close, .password-toggle, .sidebar-shell .nav-link, .page-link,
        a.profile-avatar {
            transition: none;
            translate: none !important;
        }
    }
</style>
