@extends('layouts.registration')

@section('title', 'Register')
@section('heading', 'Create your account')

@section('content')
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name') }}" autocomplete="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Gmail Address</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" autocomplete="email" autocapitalize="none" spellcheck="false" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Account Role</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Select role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    autocomplete="new-password" minlength="8" required>
                <button type="button" class="btn password-toggle" data-password-toggle data-target="password"
                    aria-controls="password" aria-label="Show password" aria-pressed="false">Show</button>
            </div>
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-group">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                    autocomplete="new-password" minlength="8" required>
                <button type="button" class="btn password-toggle" data-password-toggle data-target="password_confirmation"
                    aria-controls="password_confirmation" aria-label="Show confirm password" aria-pressed="false">Show</button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary registration-submit w-100">
            <span>Send OTP</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </button>
    </form>
    <div class="registration-footer">
        <a href="{{ route('login') }}">Back to login</a>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.target);
                if (!input) return;
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                button.textContent = isHidden ? 'Hide' : 'Show';
                button.setAttribute('aria-pressed', String(isHidden));
                const fieldLabel = input.id === 'password_confirmation' ? 'confirm password' : 'password';
                button.setAttribute('aria-label', (isHidden ? 'Hide ' : 'Show ') + fieldLabel);
            });
        });
    </script>
@endsection
