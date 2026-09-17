@extends('layouts.app')

@section('title', 'Assignment Details | PARDS')
@section('page_title', 'Assignment Details')
@section('section_label', 'Property Assignment Module')

@section('content')
    <div class="dashboard-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="h4 mb-1">{{ $assignment->property?->property_name }}</h2>
                <p class="text-muted mb-0">Assignment record for {{ $assignment->assignee?->name }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('assignments.print', $assignment) }}" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-2"></i>Print Receiving Copy
                </a>
                @if ($assignment->status === \App\Models\Assignment::STATUS_ACTIVE)
                    <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-outline-secondary">Edit Assignment</a>
                    <a href="{{ route('disposals.create', ['assignment' => $assignment->id]) }}" class="btn btn-outline-danger">
                        {{ auth()->user()?->isStaff() ? 'Request Disposal' : 'Record Disposal' }}
                    </a>
                @endif
                <a href="{{ route('assignments.index') }}" class="btn btn-primary">Back to Assignments</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Assignment Information</h3>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Department</div>
                    <div class="fw-semibold">{{ $assignment->department ?: 'Not yet provided' }}</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Property Code</div>
                            <div class="fw-semibold">{{ $assignment->property?->property_code }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Serial Number</div>
                            <div class="fw-semibold">{{ $assignment->property?->serial_number ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Assigned To</div>
                            <div class="fw-semibold">{{ $assignment->assignee?->name }}</div>
                            <div class="small text-muted">{{ ucfirst($assignment->assignee?->role ?? 'user') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Quantity Assigned</div>
                            <div class="fw-semibold">{{ $assignment->quantity_assigned }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Assigned Serial Numbers</div>
                            <div class="fw-semibold text-break">{{ $assignment->propertyUnits->pluck('serial_number')->join(', ') ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Date Assigned</div>
                            <div class="fw-semibold">{{ optional($assignment->date_assigned)->format('F d, Y') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Assigned By</div>
                            <div class="fw-semibold">{{ $assignment->assignedBy?->name ?? 'System' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Status</div>
                            <div class="fw-semibold">{{ ucfirst($assignment->status) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Date Returned</div>
                            <div class="fw-semibold">{{ optional($assignment->returned_at)->format('F d, Y') ?: 'Not returned yet' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Remarks</div>
                            <div class="fw-semibold">{{ $assignment->remarks ?: 'No remarks provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Assigned Item</h3>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Property Name</div>
                    <div class="fw-semibold">{{ $assignment->property?->property_name }}</div>
                </div>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Property Code</div>
                    <div class="fw-semibold">{{ $assignment->property?->property_code ?: 'N/A' }}</div>
                </div>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Serial Number</div>
                    <div class="fw-semibold">{{ $assignment->property?->serial_number ?: 'N/A' }}</div>
                </div>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Location</div>
                    <div class="fw-semibold">{{ $assignment->location ?: 'N/A' }}</div>
                </div>
                <div class="border rounded-4 p-3">
                    <div class="text-muted small">Current Property Status</div>
                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $assignment->property?->status ?? 'N/A')) }}</div>
                </div>

                @if ($assignment->status === \App\Models\Assignment::STATUS_ACTIVE)
                    <div class="border rounded-4 p-3 mt-3">
                        <h4 class="h6 mb-3">Return Property</h4>
                        <form method="POST" action="{{ route('assignments.return', $assignment) }}">
                            @csrf
                            @method('PATCH')

                            <label for="returned_at" class="form-label">Return Date</label>
                            <input
                                type="date"
                                id="returned_at"
                                name="returned_at"
                                class="form-control @error('returned_at') is-invalid @enderror"
                                value="{{ old('returned_at', now()->format('Y-m-d')) }}"
                                required
                            >
                            @error('returned_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <button type="submit" class="btn btn-outline-success w-100 mt-3">Mark as Returned</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
