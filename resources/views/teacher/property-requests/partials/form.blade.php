@csrf

<div class="row g-4">
    <div class="col-md-8">
        <label for="requested_item_name" class="form-label">Requested Item</label>
        <input
            type="text"
            id="requested_item_name"
            name="requested_item_name"
            class="form-control @error('requested_item_name') is-invalid @enderror"
            value="{{ old('requested_item_name', $propertyRequest->requested_item_name) }}"
            required
        >
        @error('requested_item_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="requested_quantity" class="form-label">Requested Quantity</label>
        <input
            type="number"
            id="requested_quantity"
            name="requested_quantity"
            min="1"
            class="form-control @error('requested_quantity') is-invalid @enderror"
            value="{{ old('requested_quantity', $propertyRequest->requested_quantity ?: 1) }}"
            required
        >
        @error('requested_quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="needed_by" class="form-label">Needed By</label>
        <input
            type="date"
            id="needed_by"
            name="needed_by"
            class="form-control @error('needed_by') is-invalid @enderror"
            value="{{ old('needed_by', optional($propertyRequest->needed_by)->format('Y-m-d')) }}"
        >
        @error('needed_by')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="purpose" class="form-label">Purpose</label>
        <textarea
            id="purpose"
            name="purpose"
            rows="4"
            class="form-control @error('purpose') is-invalid @enderror"
            required
        >{{ old('purpose', $propertyRequest->purpose) }}</textarea>
        @error('purpose')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="additional_notes" class="form-label">Additional Notes</label>
        <textarea
            id="additional_notes"
            name="additional_notes"
            rows="3"
            class="form-control @error('additional_notes') is-invalid @enderror"
        >{{ old('additional_notes', $propertyRequest->additional_notes) }}</textarea>
        @error('additional_notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('teacher.property-requests.index') }}" class="btn btn-light border">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
