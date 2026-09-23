<div class="mb-3">
    <label for="department" class="form-label">Department</label>
    <input type="text" id="department" name="department" maxlength="255"
        class="form-control @error('department') is-invalid @enderror"
        value="{{ old('department', $assignment->department ?? '') }}" placeholder="Example: Science Department">
    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
