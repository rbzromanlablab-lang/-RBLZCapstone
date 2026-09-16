@extends('layouts.app')

@section('title', ($isAdmin ? 'Record Disposal' : 'Request Disposal').' | PARDS')
@section('page_title', $isAdmin ? 'Record Disposal' : 'Request Disposal')
@section('section_label', $isAdmin ? 'Property Disposal Module' : 'Disposal Request Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">{{ $isAdmin ? 'Create Disposal Record' : 'Create Disposal Request' }}</h2>
            <p class="text-muted mb-0">
                {{ $isAdmin
                    ? 'Record the disposal of a property and save it to disposal history.'
                    : 'Submit a disposal request for admin approval before inventory is updated.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('disposals.store') }}">
            @csrf
            @if ($assignment)
                <input type="hidden" name="assignment_id" value="{{ old('assignment_id', $assignment->id) }}">
                <input type="hidden" name="property_id" value="{{ old('property_id', $assignment->property_id) }}">
            @endif

            <div class="row g-4">
                @if ($assignment)
                    <div class="col-12">
                        <div class="alert alert-warning border mb-0">
                            <div class="fw-semibold mb-2">{{ $isAdmin ? 'Assignment Disposal' : 'Assignment Disposal Request' }}</div>
                            <p class="small text-muted mb-3">
                                {{ $isAdmin
                                    ? 'This disposal will deduct from the assigned quantity instead of the available stock quantity.'
                                    : 'This request will be sent to admin. The assigned quantity will only be updated after approval.' }}
                            </p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="small text-muted">Property</div>
                                    <div class="fw-semibold">{{ $assignment->property?->property_name }}</div>
                                    <div class="small text-muted">{{ $assignment->property?->property_code }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Assigned To</div>
                                    <div class="fw-semibold">{{ $assignment->assignee?->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Assigned Quantity</div>
                                    <div class="fw-semibold">{{ $assignment->quantity_assigned }}</div>
                                </div>
                            </div>
                        </div>
                        @error('property_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('assignment_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text" id="disposal-availability-text"></div>
                    </div>
                @else
                    <div class="col-md-6">
                        <label for="property_id" class="form-label">Property</label>
                        <select id="property_id" name="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
                            <option value="">Select property</option>
                            @foreach ($properties as $property)
                                <option
                                    value="{{ $property->id }}"
                                    data-disposal-limit="{{ $property->disposal_limit }}"
                                    @selected(old('property_id') == $property->id)
                                >
                                    {{ $property->property_code }} - {{ $property->property_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('property_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" id="disposal-availability-text"></div>
                    </div>
                @endif

                <div class="col-md-6">
                    <label for="quantity_disposed" class="form-label">Quantity Disposed</label>
                    <input
                        type="number"
                        id="quantity_disposed"
                        name="quantity_disposed"
                        min="1"
                        data-assignment-limit="{{ $assignment?->quantity_assigned ?? '' }}"
                        class="form-control @error('quantity_disposed') is-invalid @enderror"
                        value="{{ old('quantity_disposed', 1) }}"
                        required
                    >
                    @error('quantity_disposed')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" id="disposal-limit-text"></div>
                </div>

                <div class="col-md-6">
                    <label for="disposal_date" class="form-label">Disposal Date</label>
                    <input
                        type="date"
                        id="disposal_date"
                        name="disposal_date"
                        class="form-control @error('disposal_date') is-invalid @enderror"
                        value="{{ old('disposal_date', now()->format('Y-m-d')) }}"
                        required
                    >
                    @error('disposal_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="disposal_method" class="form-label">Disposal Method</label>
                    <select id="disposal_method" name="disposal_method" class="form-select @error('disposal_method') is-invalid @enderror" required>
                        <option value="">Select method</option>
                        @foreach (\App\Models\Disposal::methods() as $method)
                            <option value="{{ $method }}" @selected(old('disposal_method') === $method)>
                                {{ ucfirst(str_replace('_', ' ', $method)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('disposal_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="disposal_reason" class="form-label">Disposal Reason</label>
                    <textarea
                        id="disposal_reason"
                        name="disposal_reason"
                        rows="3"
                        class="form-control @error('disposal_reason') is-invalid @enderror"
                        required
                    >{{ old('disposal_reason') }}</textarea>
                    @error('disposal_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea
                        id="remarks"
                        name="remarks"
                        rows="3"
                        class="form-control @error('remarks') is-invalid @enderror"
                    >{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('disposals.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isAdmin ? 'Save Disposal' : 'Submit Disposal Request' }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const propertySelect = document.getElementById('property_id');
            const quantityInput = document.getElementById('quantity_disposed');
            const availabilityText = document.getElementById('disposal-availability-text');
            const limitText = document.getElementById('disposal-limit-text');
            const assignmentLimit = Number(quantityInput?.dataset.assignmentLimit ?? 0);

            if (!quantityInput || !availabilityText || !limitText) {
                return;
            }

            const syncDisposalAvailability = () => {
                if (assignmentLimit > 0) {
                    quantityInput.max = String(assignmentLimit);
                    availabilityText.textContent = `Assigned quantity available for disposal: ${assignmentLimit}`;
                    limitText.textContent = '';

                    if (Number(quantityInput.value || 0) > assignmentLimit) {
                        quantityInput.setCustomValidity(`Only ${assignmentLimit} item${assignmentLimit === 1 ? ' is' : 's are'} assigned and available for disposal.`);
                    } else {
                        quantityInput.setCustomValidity('');
                    }

                    return;
                }

                const selectedOption = propertySelect?.options[propertySelect.selectedIndex];
                const availableQuantity = Number(selectedOption?.dataset.disposalLimit ?? 0);

                if (!propertySelect?.value) {
                    quantityInput.removeAttribute('max');
                    quantityInput.setCustomValidity('');
                    availabilityText.textContent = '';
                    limitText.textContent = '';
                    return;
                }

                quantityInput.max = String(availableQuantity);
                availabilityText.textContent = `Available: ${availableQuantity}`;
                limitText.textContent = '';

                if (Number(quantityInput.value || 0) > availableQuantity) {
                    quantityInput.setCustomValidity(`Only ${availableQuantity} item${availableQuantity === 1 ? ' is' : 's are'} available for disposal.`);
                } else {
                    quantityInput.setCustomValidity('');
                }
            };

            propertySelect?.addEventListener('change', syncDisposalAvailability);
            quantityInput.addEventListener('input', syncDisposalAvailability);

            syncDisposalAvailability();
        });
    </script>
@endpush
