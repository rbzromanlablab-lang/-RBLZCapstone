@extends('layouts.registration')

@section('title', 'Verify OTP')
@section('heading', 'Verify your email')

@section('content')
    <div class="verification-recipient">
        <strong>{{ $maskedEmail }}</strong>
        <span>Code expires in {{ $expiresInMinutes }} minutes</span>
    </div>
    <form method="POST" action="{{ route('register.verify.store') }}">
        @csrf
        <div class="mb-4">
            <label for="otp" class="form-label">OTP Code</label>
            <input type="text" id="otp" name="otp" class="form-control otp-input @error('otp') is-invalid @enderror"
                value="{{ old('otp') }}" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6"
                autocomplete="one-time-code" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify and Create Account</button>
    </form>
    <form method="POST" action="{{ route('register.resend-otp') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-outline-primary w-100">Resend OTP</button>
    </form>
    <div class="registration-footer">
        <a href="{{ route('register') }}">Back to registration</a>
    </div>
@endsection
