@extends('layouts.app')

@section('title', 'Print My Accountabilities | PARDS')
@section('page_title', 'Print My Accountabilities')
@section('section_label', 'End-User Accountability Workspace')

@push('styles')
    <style>
        @media print {
            .sidebar-shell,
            .topbar-card,
            .print-actions {
                display: none !important;
            }

            .content-shell,
            .container-fluid,
            .dashboard-card {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            body {
                background: #fff !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4 print-actions">
            <div>
                <h2 class="h4 mb-1">My Assigned Accountabilities</h2>
                <p class="text-muted mb-0">Printable copy of the properties currently assigned under your accountability.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
                <a href="{{ route('teacher.properties.index') }}" class="btn btn-primary">Back to My Accountabilities</a>
            </div>
        </div>

        <div class="mb-4">
            <div class="fw-semibold">{{ $teacher->name }}</div>
            <div class="text-muted small">{{ $teacher->email }}</div>
            <div class="text-muted small">Printed on {{ $printedAt->format('F d, Y h:i A') }}</div>
        </div>

        <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                        <th>Property Code</th>
                        <th>Serial Number</th>
                        <th>Property Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Assigned Serials</th>
                        <th>Condition</th>
                        <th>Date Assigned</th>
                        <th>Assigned By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        @php($assignment = $property->assignments->first())
                        <tr>
                            <td>{{ $property->property_code }}</td>
                            <td>{{ $property->serial_number ?: 'N/A' }}</td>
                            <td>{{ $property->property_name }}</td>
                            <td>{{ $property->category ?: 'N/A' }}</td>
                            <td>{{ $assignment?->quantity_assigned ?? 0 }} {{ $property->unit }}</td>
                            <td>{{ $assignment?->propertyUnits?->pluck('serial_number')->join(', ') ?: 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</td>
                            <td>{{ optional($assignment?->date_assigned)->format('F d, Y') ?: 'N/A' }}</td>
                            <td>{{ $assignment?->assignedBy?->name ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No assigned properties found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
