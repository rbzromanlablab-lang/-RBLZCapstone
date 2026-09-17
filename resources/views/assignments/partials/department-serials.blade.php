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
        placeholder="One serial number per line" aria-describedby="serial-help">{{ old('serial_numbers', isset($assignment) && $assignment->exists ? $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->join("\n") : '') }}</textarea>
    <div class="form-text" id="serial-help">Enter the item's actual serial number. For multiple items, enter one per line. Leave blank to use existing inventory serials or automatically generated serial numbers.</div>
    @error('serial_numbers')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
