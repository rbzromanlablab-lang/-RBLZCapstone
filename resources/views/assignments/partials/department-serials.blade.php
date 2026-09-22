<div class="mb-3">
    <label for="department" class="form-label">Department</label>
    <input type="text" id="department" name="department" maxlength="255"
        class="form-control @error('department') is-invalid @enderror"
        value="{{ old('department', $assignment->department ?? '') }}" placeholder="Example: Science Department">
    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="serial_numbers" class="form-label">Assigned Item Serial Number(s)</label>
    <textarea id="serial_numbers" name="serial_numbers" rows="3" maxlength="20000"
        class="form-control @error('serial_numbers') is-invalid @enderror"
        placeholder="One serial number per line">{{ old('serial_numbers', isset($assignment) && $assignment->exists ? $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->join("\n") : '') }}</textarea>
    @error('serial_numbers')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
