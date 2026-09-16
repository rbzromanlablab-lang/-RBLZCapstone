@extends('layouts.app')

@section('title', 'Accountability Details | PARDS')
@section('page_title', 'Accountability Details')
@section('section_label', 'End-User Accountability Workspace')

@section('content')
    <div class="dashboard-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="h4 mb-1">{{ $property->property_name }}</h2>
                <p class="text-muted mb-0">
                    Property Code: {{ $property->property_code }}
                    @if ($property->serial_number)
                        | Serial Number: {{ $property->serial_number }}
                    @endif
                </p>
            </div>
            <a href="{{ route('teacher.properties.index') }}" class="btn btn-primary">Back to My Accountabilities</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Property Information</h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Property Code</div>
                            <div class="fw-semibold">{{ $property->property_code }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Serial Number</div>
                            <div class="fw-semibold">{{ $property->serial_number ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Category</div>
                            <div class="fw-semibold">{{ $property->category ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Assigned Quantity</div>
                            <div class="fw-semibold">{{ $assignment->quantity_assigned }} {{ $property->unit }}</div>
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
                            <div class="text-muted small">Condition Status</div>
                            <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Location</div>
                            <div class="fw-semibold">{{ $assignment->location ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Date Acquired</div>
                            <div class="fw-semibold">{{ optional($property->date_acquired)->format('F d, Y') ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Date Assigned</div>
                            <div class="fw-semibold">{{ optional($assignment->date_assigned)->format('F d, Y') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Description</div>
                            <div class="fw-semibold">{{ $property->description ?: 'No description provided.' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Assignment Remarks</div>
                            <div class="fw-semibold">{{ $assignment->remarks ?: 'No remarks provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Assignment Summary</h3>

                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Property Status</div>
                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $property->status)) }}</div>
                </div>

                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Assigned By</div>
                    <div class="fw-semibold">{{ $assignment->assignedBy?->name ?? 'N/A' }}</div>
                </div>

                <div class="border rounded-4 p-3">
                    <div class="text-muted small">Read-only Access</div>
                    <div class="fw-semibold">End-users cannot edit or delete this record.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
