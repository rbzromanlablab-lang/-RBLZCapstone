<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | PARDS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts.partials.button-styles')
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3 text-center">Verify Registration OTP</h1>

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="alert alert-info small">
                            We sent a 6-digit OTP to <strong>{{ $maskedEmail }}</strong>. Enter the code below within {{ $expiresInMinutes }} minutes to finish creating your account.
                        </div>

                        <form method="POST" action="{{ route('register.verify.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="otp" class="form-label">OTP Code</label>
                                <input
                                    type="text"
                                    id="otp"
                                    name="otp"
                                    class="form-control"
                                    value="{{ old('otp') }}"
                                    inputmode="numeric"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    placeholder="Enter 6-digit OTP"
                                    required
                                    autofocus
                                >
                            </div>

                            <div class="mb-3 small text-muted">
                                Verification email: {{ $email }}
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Verify and Create Account</button>
                        </form>

                        <form method="POST" action="{{ route('register.resend-otp') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">Resend OTP</button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ route('register') }}" class="text-decoration-none">Back to registration</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
