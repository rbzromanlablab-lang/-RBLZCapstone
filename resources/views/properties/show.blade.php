@extends('layouts.app')

@section('title', 'Property Details | PARDS')
@section('page_title', 'Property Details')
@section('section_label', 'Property Management')

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
            <div class="d-flex gap-2">
                <a href="{{ route('qr.show', $property) }}" class="btn btn-outline-dark">View QR</a>
                <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-secondary">Edit</a>
                <a href="{{ route('properties.index') }}" class="btn btn-primary">Back to List</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">Property Information</h3>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="text-muted small">Department</div>
                    <div class="fw-semibold">{{ $property->department ?: 'Not yet provided' }}</div>
                </div>

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
                            <div class="text-muted small">Brand</div>
                            <div class="fw-semibold">{{ $property->brand ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Model</div>
                            <div class="fw-semibold">{{ $property->model ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Total Quantity</div>
                            <div class="fw-semibold">{{ $property->tracked_quantity }} {{ $property->unit }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Available Quantity</div>
                            <div class="fw-semibold">{{ $property->available_quantity }} {{ $property->unit }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Assigned Quantity</div>
                            <div class="fw-semibold">{{ $property->active_assigned_quantity }} {{ $property->unit }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Unit Cost</div>
                            <div class="fw-semibold">
                                {{ $property->unit_cost !== null ? 'PHP '.number_format((float) $property->unit_cost, 2) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Total Cost</div>
                            <div class="fw-semibold">
                                {{ $property->total_cost !== null ? 'PHP '.number_format($property->total_cost, 2) : 'N/A' }}
                            </div>
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
                            <div class="text-muted small">Status</div>
                            <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $property->status)) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Disposed Quantity</div>
                            <div class="fw-semibold">{{ $property->disposed_quantity }} {{ $property->unit }}</div>
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
                            <div class="text-muted small">Location</div>
                            <div class="fw-semibold">{{ $property->location ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Office</div>
                            <div class="fw-semibold">{{ $property->office ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">QR Token</div>
                            <div class="fw-semibold text-break">{{ $property->qr_token ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">QR Code Path</div>
                            <div class="fw-semibold text-break">{{ $property->qr_code_path ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-4 p-3">
                            <div class="text-muted small">Description</div>
                            <div class="fw-semibold">{{ $property->description ?: 'No description provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card p-4 h-100">
                <h3 class="h5 mb-4">QR Code</h3>

                <div class="border rounded-4 p-3 mb-4 bg-light-subtle">
                    <div class="text-muted small">QR Scan URL</div>
                    <div class="fw-semibold text-break">{{ $property->qr_scan_url }}</div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <a href="{{ route('qr.show', $property) }}" class="btn btn-outline-primary">Open QR Page</a>
                    <a href="{{ route('qr.download', $property) }}" class="btn btn-primary">Download QR Code</a>
                </div>

                <h3 class="h5 mb-3">Quick Actions</h3>

                <div class="d-grid gap-2">
                    <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-primary">Edit Property</a>
                    <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Delete this property?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">Delete Property</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
