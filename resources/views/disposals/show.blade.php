@extends('layouts.app')

@section('title', 'Disposal Request Details | PARDS')
@section('page_title', 'Disposal Request Details')
@section('section_label', 'Disposal Request Module')

@section('content')
    <div class="dashboard-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="h4 mb-1">{{ $disposal->property?->property_name }}</h2>
                <p class="text-muted mb-0">
                    Disposal request for {{ $disposal->property?->property_code }}
                    @if ($disposal->property?->serial_number)
                        | Serial Number: {{ $disposal->property?->serial_number }}
                    @endif
                </p>
            </div>
            <a href="{{ route('disposals.index') }}" class="btn btn-primary">Back to Disposals</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Disposal Information</h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Property Code</div>
                            <div class="fw-semibold">{{ $disposal->property?->property_code }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Serial Number</div>
                            <div class="fw-semibold">{{ $disposal->property?->serial_number ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Submitted By</div>
                            <div class="fw-semibold">{{ $disposal->disposedBy?->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Quantity Disposed</div>
                            <div class="fw-semibold">{{ $disposal->quantity_disposed }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Disposal Date</div>
                            <div class="fw-semibold">{{ optional($disposal->disposal_date)->format('F d, Y') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Disposal Method</div>
                            <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $disposal->disposal_method)) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Status</div>
                            <div class="fw-semibold">{{ ucfirst($disposal->status) }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Disposal Reason</div>
                            <div class="fw-semibold">{{ $disposal->disposal_reason }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Remarks</div>
                            <div class="fw-semibold">{{ $disposal->remarks ?: 'No remarks provided.' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Admin Response Notes</div>
                            <div class="fw-semibold">{{ $disposal->response_notes ?: 'No admin response notes provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">{{ $canReviewDisposal ? 'Review Request' : 'Request Summary' }}</h3>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Property Name</div>
                    <div class="fw-semibold">{{ $disposal->property?->property_name }}</div>
                </div>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Location</div>
                    <div class="fw-semibold">{{ $disposal->property?->location ?: 'N/A' }}</div>
                </div>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Processed By</div>
                    <div class="fw-semibold">{{ $disposal->processedBy?->name ?: 'Pending review' }}</div>
                    <div class="small text-muted">{{ optional($disposal->processed_at)->format('F d, Y h:i A') ?: 'Not processed yet' }}</div>
                </div>
                <div class="border rounded-4 p-3">
                    <div class="text-muted small">Current Property Status</div>
                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $disposal->property?->status ?? 'N/A')) }}</div>
                </div>

                @if ($canReviewDisposal)
                    <div class="border rounded-4 p-3 mt-3">
                        <h4 class="h6 mb-3">Approve or Disapprove</h4>
                        <form method="POST" action="{{ route('disposals.status', $disposal) }}">
                            @csrf
                            @method('PATCH')

                            <label for="status" class="form-label">Decision</label>
                            <select
                                id="status"
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required
                            >
                                <option value="{{ \App\Models\Disposal::STATUS_APPROVED }}" @selected(old('status') === \App\Models\Disposal::STATUS_APPROVED)>
                                    Approve
                                </option>
                                <option value="{{ \App\Models\Disposal::STATUS_CANCELLED }}" @selected(old('status') === \App\Models\Disposal::STATUS_CANCELLED)>
                                    Disapprove
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label for="response_notes" class="form-label mt-3">Admin Notes</label>
                            <textarea
                                id="response_notes"
                                name="response_notes"
                                rows="4"
                                class="form-control @error('response_notes') is-invalid @enderror"
                            >{{ old('response_notes', $disposal->response_notes) }}</textarea>
                            @error('response_notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <button type="submit" class="btn btn-primary w-100 mt-3">Save Decision</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
