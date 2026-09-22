@csrf

<div class="row g-4">
    <div class="col-12">
        @include('assignments.partials.department-serials')
    </div>
    <div class="col-md-6">
        <label for="property_id" class="form-label">Property</label>
        <select id="property_id" name="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
            <option value="">Select property</option>
            @foreach ($properties as $property)
                <option
                    value="{{ $property->id }}"
                    data-assignable-quantity="{{ $property->assignable_quantity }}"
                    data-department="{{ $property->department }}"
                    @selected(old('property_id', $assignment->property_id) == $property->id)
                >
                    {{ $property->property_code }}{{ $property->serial_number ? ' / SN: '.$property->serial_number : '' }} - {{ $property->property_name }}
                </option>
            @endforeach
        </select>
        @error('property_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text" id="property-availability-text"></div>
    </div>

    @if (!$assignment->exists && $selectedAssignee)
        <div class="col-md-6">
            <label for="selected_assignee" class="form-label">{{ $selectedAssignee->isTeacher() ? 'Teacher' : 'Staff' }}</label>
            <input type="text" id="selected_assignee" class="form-control" value="{{ $selectedAssignee->name }}" readonly>
            <div class="form-text text-break">{{ $selectedAssignee->email }}</div>
            <input type="hidden" name="{{ $selectedAssignee->isTeacher() ? 'teacher_id' : 'staff_id' }}" value="{{ $selectedAssignee->id }}">
            @error('teacher_id')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('staff_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    @else
    <div class="col-md-6">
        <label for="teacher_id" class="form-label">Teacher</label>
        <select
            id="teacher_id"
            name="teacher_id"
            class="form-select @error('teacher_id') is-invalid @enderror"
            data-assignee-select="teacher"
        >
            <option value="">Select teacher</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $selectedTeacherId ?? null) == $teacher->id)>
                    {{ $teacher->name }} - {{ $teacher->email }}
                </option>
            @endforeach
        </select>
        @error('teacher_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Use this if the property will be assigned to a teacher.</div>
    </div>

    <div class="col-md-6">
        <label for="staff_id" class="form-label">Staff</label>
        <select
            id="staff_id"
            name="staff_id"
            class="form-select @error('staff_id') is-invalid @enderror"
            data-assignee-select="staff"
        >
            <option value="">Select staff member</option>
            @foreach ($staffMembers as $staff)
                <option value="{{ $staff->id }}" @selected(old('staff_id', $selectedStaffId ?? null) == $staff->id)>
                    {{ $staff->name }} - {{ $staff->email }}
                </option>
            @endforeach
        </select>
        @error('staff_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Leave this blank if you selected a teacher above.</div>
    </div>
    @endif

    <div class="col-md-6">
        <label for="quantity_assigned" class="form-label">Quantity Assigned</label>
        <input
            type="number"
            id="quantity_assigned"
            name="quantity_assigned"
            min="1"
            class="form-control @error('quantity_assigned') is-invalid @enderror"
            value="{{ old('quantity_assigned', $assignment->quantity_assigned ?: 1) }}"
            required
        >
        @error('quantity_assigned')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text" id="quantity-limit-text"></div>
    </div>

    <div class="col-md-6">
        <label for="date_assigned" class="form-label">Date Assigned</label>
        <input
            type="date"
            id="date_assigned"
            name="date_assigned"
            class="form-control @error('date_assigned') is-invalid @enderror"
            value="{{ old('date_assigned', optional($assignment->date_assigned)->format('Y-m-d') ?: now()->format('Y-m-d')) }}"
            required
        >
        @error('date_assigned')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="location" class="form-label">Location</label>
        <input
            type="text"
            id="location"
            name="location"
            class="form-control @error('location') is-invalid @enderror"
            value="{{ old('location', $assignment->location) }}"
        >
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea
            id="remarks"
            name="remarks"
            rows="4"
            class="form-control @error('remarks') is-invalid @enderror"
        >{{ old('remarks', $assignment->remarks) }}</textarea>
        @error('remarks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('assignments.index') }}" class="btn btn-light border">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const propertySelect = document.getElementById('property_id');
            const teacherSelect = document.querySelector('[data-assignee-select="teacher"]');
            const staffSelect = document.querySelector('[data-assignee-select="staff"]');
            const quantityInput = document.getElementById('quantity_assigned');
            const propertyAvailabilityText = document.getElementById('property-availability-text');
            const quantityLimitText = document.getElementById('quantity-limit-text');

            if (!propertySelect || !quantityInput || !propertyAvailabilityText || !quantityLimitText) {
                return;
            }

            const syncAssigneeSelects = () => {
                if (!teacherSelect || !staffSelect) return;
                const hasTeacher = teacherSelect.value !== '';
                const hasStaff = staffSelect.value !== '';

                staffSelect.disabled = hasTeacher;
                teacherSelect.disabled = hasStaff;

                if (hasTeacher && hasStaff) {
                    staffSelect.value = '';
                    staffSelect.disabled = true;
                }
            };

            const syncAvailableQuantity = () => {
                const selectedOption = propertySelect.options[propertySelect.selectedIndex];
                const assignableQuantity = Number(selectedOption?.dataset.assignableQuantity ?? 0);

                if (!propertySelect.value) {
                    quantityInput.removeAttribute('max');
                    quantityInput.setCustomValidity('');
                    propertyAvailabilityText.textContent = '';
                    quantityLimitText.textContent = '';
                    return;
                }

                quantityInput.max = String(assignableQuantity);
                propertyAvailabilityText.textContent = `Available for assignment: ${assignableQuantity}`;
                quantityLimitText.textContent = '';

                if (Number(quantityInput.value || 0) > assignableQuantity) {
                    quantityInput.setCustomValidity(`Only ${assignableQuantity} item${assignableQuantity === 1 ? ' is' : 's are'} available.`);
                } else {
                    quantityInput.setCustomValidity('');
                }
            };

            teacherSelect?.addEventListener('change', () => {
                if (teacherSelect.value !== '') {
                    staffSelect.value = '';
                }

                syncAssigneeSelects();
            });

            staffSelect?.addEventListener('change', () => {
                if (staffSelect.value !== '') {
                    teacherSelect.value = '';
                }

                syncAssigneeSelects();
            });

            propertySelect.addEventListener('change', syncAvailableQuantity);
            propertySelect.addEventListener('change', () => {
                document.getElementById('serial_numbers').value = '';
                const departmentInput = document.getElementById('department');
                if (!departmentInput.value.trim()) departmentInput.value = propertySelect.selectedOptions[0]?.dataset.department || '';
            });
            quantityInput.addEventListener('input', syncAvailableQuantity);

            syncAssigneeSelects();
            syncAvailableQuantity();
        });
    </script>
@endpush
