@csrf

<div class="row g-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Full Name</label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email) }}"
            required
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">All login accounts must use a Gmail address.</div>
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label">Role</label>
        <select
            id="role"
            name="role"
            class="form-select @error('role') is-invalid @enderror"
            required
        >
            <option value="">Select role</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="employee_number" class="form-label">Employee Number</label>
        <div class="form-text mb-2">Enter the official school-issued employee ID. It must be unique and will appear automatically on accountability forms.</div>
        <input
            type="text"
            id="employee_number"
            name="employee_number"
            class="form-control @error('employee_number') is-invalid @enderror"
            value="{{ old('employee_number', $user->employee_number) }}"
        >
        @error('employee_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Used for staff or teacher profile records in the normalized database tables.</div>
    </div>

    <div class="col-md-6">
        <label for="department" class="form-label">Department</label>
        <input
            type="text"
            id="department"
            name="department"
            class="form-control @error('department') is-invalid @enderror"
            value="{{ old('department', $user->department) }}"
        >
        @error('department')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Used for admin or staff profile records.</div>
    </div>

    <div class="col-md-6">
        <label for="subject_area" class="form-label">Subject Area</label>
        <input
            type="text"
            id="subject_area"
            name="subject_area"
            class="form-control @error('subject_area') is-invalid @enderror"
            value="{{ old('subject_area', $user->subject_area) }}"
        >
        @error('subject_area')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Used for teacher profile records.</div>
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <input
                type="password"
                id="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                @if (! $user->exists) required @endif
            >
            <button
                type="button"
                class="btn btn-outline-secondary"
                data-password-toggle
                data-target="password"
            >
                Show
            </button>
        </div>
        @if ($user->exists)
            <div class="form-text">Leave blank if you do not want to change the password.</div>
        @endif
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <div class="input-group">
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-control"
                @if (! $user->exists) required @endif
            >
            <button
                type="button"
                class="btn btn-outline-secondary"
                data-password-toggle
                data-target="password_confirmation"
            >
                Show
            </button>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-check mt-2">
            <input
                type="checkbox"
                id="is_active"
                name="is_active"
                value="1"
                class="form-check-input"
                @checked(old('is_active', $user->exists ? $user->is_active : true))
            >
            <label for="is_active" class="form-check-label">Active account</label>
        </div>
        <div class="form-text">Inactive users cannot log in, but their records stay in the system.</div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('users.index') }}" class="btn btn-light border">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);

            if (! input) {
                return;
            }

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.textContent = isHidden ? 'Hide' : 'Show';
        });
    });
</script>
