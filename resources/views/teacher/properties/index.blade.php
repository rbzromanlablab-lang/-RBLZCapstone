@extends('layouts.app')

@section('title', 'My Accountabilities | PARDS')
@section('page_title', 'My Accountabilities')
@section('section_label', 'End-User Accountability Workspace')

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Assigned Property Records</h2>
            </div>
            <a href="{{ route('teacher.properties.print') }}" class="btn btn-outline-primary">
                <i class="bi bi-printer me-2"></i>Print My Accountabilities
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mobile-record-table">
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
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        @php($assignment = $property->assignments->first())
                        <tr>
                            <td data-label="Property Code" class="fw-semibold">{{ $property->property_code }}</td>
                            <td data-label="Serial Number">{{ $property->serial_number ?: 'N/A' }}</td>
                            <td data-label="Property Name">{{ $property->property_name }}</td>
                            <td data-label="Category">{{ $property->category ?: 'N/A' }}</td>
                            <td data-label="Quantity">{{ $assignment?->quantity_assigned ?? 0 }} {{ $property->unit }}</td>
                            <td data-label="Assigned Serials">{{ $assignment?->propertyUnits?->pluck('serial_number')->join(', ') ?: 'N/A' }}</td>
                            <td data-label="Condition">{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</td>
                            <td data-label="Date Assigned">{{ optional($assignment?->date_assigned)->format('F d, Y') }}</td>
                            <td class="text-end record-action">
                                <a href="{{ route('teacher.properties.show', $property) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted record-empty">No assigned properties found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $properties->links() }}
        </div>
    </div>
@endsection
