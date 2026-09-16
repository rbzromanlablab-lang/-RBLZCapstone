@extends('layouts.app')

@section('title', 'Confirm Property Request | PARDS')
@section('page_title', 'Confirm Property Request')
@section('section_label', 'Supply Office Request Confirmation')

@section('content')
    <div class="dashboard-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="h4 mb-1">{{ $propertyRequest->requested_item_name }}</h2>
                <p class="text-muted mb-0">Submitted by {{ $propertyRequest->requester?->name ?: 'N/A' }}</p>
            </div>
            <a href="{{ route('property-requests.index') }}" class="btn btn-primary">Back to Requests</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Request Information</h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Requester</div>
                            <div class="fw-semibold">{{ $propertyRequest->requester?->name ?: 'N/A' }}</div>
                            <div class="small text-muted">{{ $propertyRequest->requester?->email ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Requested Quantity</div>
                            <div class="fw-semibold">{{ $propertyRequest->requested_quantity }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Needed By</div>
                            <div class="fw-semibold">{{ optional($propertyRequest->needed_by)->format('F d, Y') ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Current Status</div>
                            <div class="fw-semibold">{{ ucfirst($propertyRequest->status) }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Purpose</div>
                            <div class="fw-semibold">{{ $propertyRequest->purpose }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Additional Notes</div>
                            <div class="fw-semibold">{{ $propertyRequest->additional_notes ?: 'No additional notes provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Confirm Request</h3>

                <div class="border rounded-4 p-3 mb-4 bg-light-subtle">
                    <div class="text-muted small">Last Processed By</div>
                    <div class="fw-semibold">{{ $propertyRequest->processedBy?->name ?: 'Pending review' }}</div>
                    <div class="small text-muted">{{ optional($propertyRequest->processed_at)->format('F d, Y h:i A') ?: 'Not processed yet' }}</div>
                </div>

                <form method="POST" action="{{ route('property-requests.status', $propertyRequest) }}">
                    @csrf
                    @method('PATCH')

                    <label for="status" class="form-label">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="form-select @error('status') is-invalid @enderror"
                        required
                    >
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $propertyRequest->status) === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <label for="response_notes" class="form-label mt-3">Supply Office Notes</label>
                    <textarea
                        id="response_notes"
                        name="response_notes"
                        rows="5"
                        class="form-control @error('response_notes') is-invalid @enderror"
                    >{{ old('response_notes', $propertyRequest->response_notes) }}</textarea>
                    @error('response_notes')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-primary w-100 mt-3">Save Confirmation</button>
                </form>
            </div>
        </div>
    </div>
@endsection
