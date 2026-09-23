<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | PARDS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts.partials.button-styles')
    <style>
        body {
            margin: 0;
            color: #183153;
            font-family: Arial, Helvetica, sans-serif;
            background: radial-gradient(ellipse at top left, #f4ead5, transparent 55%), #eef2f7;
        }
        .registration-page {
            min-height: 100vh;
            min-height: 100svh;
            display: grid;
            place-items: center;
            padding: 40px 24px;
        }
        .registration-shell {
            display: grid;
            grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr);
            width: min(100%, 1000px);
            border: 1px solid #dde3eb;
            border-radius: 28px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 24px 70px rgba(24, 49, 83, .1);
            animation: registration-enter 320ms ease-out both;
        }
        .registration-brand {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
            color: #fff;
            text-align: center;
            background: linear-gradient(145deg, #10253f, #254f7a);
        }
        .registration-brand::after {
            content: '';
            position: absolute;
            z-index: -1;
            width: 420px;
            height: 420px;
            bottom: -290px;
            right: -160px;
            border: 1px solid rgba(243, 223, 182, .25);
            border-radius: 50%;
            box-shadow: 0 0 0 48px rgba(255, 255, 255, .025), 0 0 0 96px rgba(255, 255, 255, .025);
        }
        .school-logo {
            width: 112px;
            height: 112px;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            padding: 3px;
            margin-bottom: 24px;
            box-shadow: 0 0 0 8px rgba(255, 255, 255, .08);
        }
        .school-name { max-width: 260px; margin: 0; font-size: 20px; font-weight: 700; line-height: 1.45; }
        .school-location { margin: 8px 0 32px; color: #d0dced; font-size: 14px; }
        .brand-divider { width: 40px; height: 3px; background: #d9a441; border-radius: 3px; margin-bottom: 24px; }
        .brand-name { font-size: 36px; font-weight: 800; letter-spacing: 5px; margin-bottom: 8px; }
        .brand-caption { max-width: 240px; color: #d0dced; font-size: 14px; line-height: 1.7; margin: 0; }
        .registration-content { min-width: 0; padding: 40px; }
        .registration-steps { display: flex; gap: 12px; list-style: none; padding: 0; margin: 0 0 28px; }
        .registration-steps li { display: flex; align-items: center; gap: 8px; color: #697586; font-size: 12px; }
        .step-number { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; background: #eef2f7; font-weight: 700; }
        .registration-steps [aria-current="step"] { color: #183153; font-weight: 700; }
        .registration-steps [aria-current="step"] .step-number { background: #f3dfb6; }
        .registration-title { font-size: clamp(26px, 4vw, 32px); font-weight: 750; letter-spacing: -.7px; margin: 0 0 26px; }
        .registration-content .form-label { font-size: 14px; font-weight: 600; margin-bottom: 8px; }
        .registration-content .form-control, .registration-content .form-select {
            min-height: 48px;
            border-color: #ced7e2;
            border-radius: 10px;
            font-size: 16px;
            color: #183153;
            background-color: #fafbfd;
        }
        .registration-content .form-control:focus, .registration-content .form-select:focus {
            border-color: #254f7a;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(37, 79, 122, .12);
        }
        .registration-content .form-control.is-invalid, .registration-content .form-select.is-invalid { border-color: #dc3545; }
        .registration-content .input-group > .form-control { border-radius: 10px 0 0 10px; }
        .registration-content .password-toggle {
            min-width: 64px;
            border: 1px solid #ced7e2;
            border-left: 0;
            border-radius: 0 10px 10px 0;
            color: #183153;
            background: #eef2f7;
            font-size: 13px;
        }
        .registration-content .btn { min-height: 48px; border-radius: 10px; }
        .registration-content .password-toggle { border-radius: 0 10px 10px 0; }
        .registration-submit { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .registration-footer { border-top: 1px solid #e7ebf1; margin-top: 26px; padding-top: 14px; text-align: center; }
        .registration-footer a { display: inline-flex; align-items: center; min-height: 44px; color: #183153; font-size: 14px; font-weight: 600; text-underline-offset: 4px; }
        .registration-footer a:focus-visible { outline: 3px solid #d9a441; outline-offset: 4px; border-radius: 3px; }
        .registration-content .alert { border-radius: 10px; font-size: 14px; overflow-wrap: anywhere; }
        .verification-recipient { padding: 16px; margin-bottom: 24px; border: 1px solid #e0e6ee; background: #f5f7fa; border-radius: 12px; overflow-wrap: anywhere; }
        .verification-recipient strong { display: block; margin-bottom: 6px; }
        .verification-recipient span { color: #637184; font-size: 13px; }
        .registration-content .otp-input { text-align: center; letter-spacing: .45em; font-size: 24px; font-weight: 700; min-height: 60px; }
        @keyframes registration-enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 767.98px) {
            .registration-page { padding: 20px 12px; }
            .registration-shell { grid-template-columns: minmax(0, 1fr); max-width: 520px; border-radius: 20px; }
            .registration-brand { padding: 26px 20px; }
            .school-logo { width: 72px; height: 72px; margin-bottom: 14px; }
            .school-name { max-width: none; font-size: 17px; }
            .school-location { margin: 6px 0 0; font-size: 12px; }
            .brand-divider, .brand-name, .brand-caption { display: none; }
            .registration-content { padding: 26px 22px; }
            .registration-steps { margin-bottom: 22px; }
        }
        @media (prefers-reduced-motion: reduce) { .registration-shell { animation: none; } }
    </style>
</head>
<body>
    <main class="registration-page">
        <div class="registration-shell">
            <header class="registration-brand">
                <img src="{{ asset('ANHS LOGO.jpg') }}" alt="Abanon National High School logo" class="school-logo" width="112" height="112">
                <p class="school-name">ABANON NATIONAL HIGH SCHOOL</p>
                <p class="school-location">San Carlos City, Pangasinan</p>
                <div class="brand-divider" aria-hidden="true"></div>
                <div class="brand-name">PARDS</div>
                <p class="brand-caption">Property Accountability Records and Disposal System</p>
            </header>
            <section class="registration-content" aria-labelledby="registration-title">
                <ol class="registration-steps" aria-label="Registration progress">
                    <li @if (! request()->routeIs('register.verify')) aria-current="step" @endif><span class="step-number">1</span>Account details</li>
                    <li @if (request()->routeIs('register.verify')) aria-current="step" @endif><span class="step-number">2</span>Email verification</li>
                </ol>
                <h1 id="registration-title" class="registration-title">@yield('heading')</h1>
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </section>
        </div>
    </main>
    @yield('scripts')
    @include('partials.ajax-forms')
</body>
</html>
