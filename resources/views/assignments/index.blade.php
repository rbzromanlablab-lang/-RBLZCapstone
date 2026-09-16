@extends('layouts.app')

@section('title', 'Assignments | PARDS')
@section('page_title', 'Assignments')
@section('section_label', 'Property Assignment Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Assignment History</h2>
                <p class="text-muted mb-0">View property accountability history separated for teachers and staff.</p>
            </div>
            <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Assign Property
            </a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('assignments.index', ['search' => $search ?: null]) }}" class="btn {{ $activeFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                All
            </a>
            <a href="{{ route('assignments.index', ['type' => 'teachers', 'search' => $search ?: null]) }}" class="btn {{ $activeFilter === 'teachers' ? 'btn-primary' : 'btn-outline-primary' }}">
                Teachers
            </a>
            <a href="{{ route('assignments.index', ['type' => 'staff', 'search' => $search ?: null]) }}" class="btn {{ $activeFilter === 'staff' ? 'btn-primary' : 'btn-outline-primary' }}">
                Staff
            </a>
        </div>

        <form method="GET" action="{{ route('assignments.index') }}" class="row g-3 mb-4">
            <input type="hidden" name="type" value="{{ $activeFilter !== 'all' ? $activeFilter : '' }}">

            <div class="col-lg-8">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search property code, serial number, property name, assignee name, or email"
                    value="{{ $search }}"
                >
            </div>

            <div class="col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-2"></i>Search
                </button>
                <a href="{{ route('assignments.index', ['type' => $activeFilter !== 'all' ? $activeFilter : null]) }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        @if ($activeFilter !== 'staff')
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 mb-1">Teacher Assignments</h3>
                    <p class="text-muted mb-0">Properties currently or previously assigned to teachers.</p>
                </div>
                <span class="badge text-bg-light border text-dark">{{ $teacherAssignments->total() }} records</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Teacher</th>
                            <th>Quantity</th>
                            <th>Date Assigned</th>
                            <th>Assigned By</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teacherAssignments as $assignment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $assignment->property?->property_name }}</div>
                                    <small class="text-muted d-block">{{ $assignment->property?->property_code }}</small>
                                    <small class="text-muted d-block">SN: {{ $assignment->property?->serial_number ?: 'N/A' }}</small>
                                    <small class="text-muted d-block">Assigned SN: {{ $assignment->propertyUnits->pluck('serial_number')->join(', ') ?: 'N/A' }}</small>
                                </td>
                                <td>{{ $assignment->assignee?->name }}</td>
                                <td>{{ $assignment->quantity_assigned }}</td>
                                <td>{{ optional($assignment->date_assigned)->format('F d, Y') }}</td>
                                <td>{{ $assignment->assignedBy?->name ?? 'System' }}</td>
                                <td>
                                    <span class="badge text-bg-light border text-dark">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if ($assignment->status === \App\Models\Assignment::STATUS_ACTIVE)
                                            <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        @endif
                                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No teacher assignment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $teacherAssignments->links() }}
            </div>
        </div>
        @endif

        @if ($activeFilter !== 'teachers')
        <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 mb-1">Staff Assignments</h3>
                    <p class="text-muted mb-0">Properties currently or previously assigned to staff members.</p>
                </div>
                <span class="badge text-bg-light border text-dark">{{ $staffAssignments->total() }} records</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Staff</th>
                            <th>Quantity</th>
                            <th>Date Assigned</th>
                            <th>Assigned By</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staffAssignments as $assignment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $assignment->property?->property_name }}</div>
                                    <small class="text-muted d-block">{{ $assignment->property?->property_code }}</small>
                                    <small class="text-muted d-block">SN: {{ $assignment->property?->serial_number ?: 'N/A' }}</small>
                                    <small class="text-muted d-block">Assigned SN: {{ $assignment->propertyUnits->pluck('serial_number')->join(', ') ?: 'N/A' }}</small>
                                </td>
                                <td>{{ $assignment->assignee?->name }}</td>
                                <td>{{ $assignment->quantity_assigned }}</td>
                                <td>{{ optional($assignment->date_assigned)->format('F d, Y') }}</td>
                                <td>{{ $assignment->assignedBy?->name ?? 'System' }}</td>
                                <td>
                                    <span class="badge text-bg-light border text-dark">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if ($assignment->status === \App\Models\Assignment::STATUS_ACTIVE)
                                            <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        @endif
                                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No staff assignment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $staffAssignments->links() }}
            </div>
        </div>
        @endif
    </div>
@endsection
