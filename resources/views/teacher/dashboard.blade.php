@extends('layouts.app')

@section('title', 'End-User Dashboard | PARDS')
@section('page_title', 'End-User Dashboard')
@section('section_label', 'End-User Accountability Workspace')

@section('content')
    <div class="dashboard-card p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-light border text-dark px-3 py-2 mb-3">
                    <i class="bi bi-person-workspace me-2"></i>End-User Property Access
                </span>
                <h2 class="display-6 fw-semibold mb-3">My Accountabilities</h2>
                <p class="text-muted mb-0">
                    View the property items currently assigned under your accountability. This module is read-only for end-users.
                </p>
            </div>
            <div class="col-lg-4">
                <div class="rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #183153 0%, #254f7a 100%);">
                    <div class="text-white-50 small mb-2">Current Accountabilities</div>
                    <div class="display-6 fw-semibold mb-0">{{ $assignedPropertiesCount }}</div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('teacher.property-requests.create') }}" class="btn btn-primary">
                        <i class="bi bi-clipboard-plus me-2"></i>New Property Request
                    </a>
                    <a href="{{ route('teacher.properties.print') }}" class="btn btn-outline-primary">
                        <i class="bi bi-printer me-2"></i>Print My Accountabilities
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="dashboard-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <p class="page-section-title mb-1">Recent Records</p>
                        <h3 class="h5 mb-0">Latest Accountability Records</h3>
                    </div>
                    <a href="{{ route('teacher.properties.index') }}" class="btn btn-outline-primary btn-sm">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Property Code</th>
                                <th>Property Name</th>
                                <th>Date Assigned</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAssignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->property?->property_code }}</td>
                                    <td>{{ $assignment->property?->property_name }}</td>
                                    <td>{{ optional($assignment->date_assigned)->format('F d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('teacher.properties.show', $assignment->property) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No assigned properties found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="dashboard-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <p class="page-section-title mb-1">Property Requests</p>
                        <h3 class="h5 mb-0">Request Tracking</h3>
                    </div>
                    <a href="{{ route('teacher.property-requests.index') }}" class="btn btn-outline-primary btn-sm">Open</a>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Total Requests</div>
                            <div class="h4 mb-0">{{ $propertyRequestCount }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Pending</div>
                            <div class="h4 mb-0">{{ $pendingPropertyRequestCount }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    @forelse ($recentPropertyRequests as $propertyRequest)
                        <div class="border rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $propertyRequest->requested_item_name }}</div>
                                    <div class="text-muted small">
                                        Qty: {{ $propertyRequest->requested_quantity }}
                                        @if ($propertyRequest->needed_by)
                                            | Needed: {{ $propertyRequest->needed_by->format('F d, Y') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge text-bg-light border text-dark">
                                    {{ ucfirst($propertyRequest->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="border rounded-4 p-3 bg-light-subtle text-muted small">
                            No property requests submitted yet.
                        </div>
                    @endforelse
                </div>

                <p class="page-section-title mb-1">Access Rules</p>
                <h3 class="h5 mb-4">End-User Permissions</h3>

                <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                    <div class="fw-semibold">View only</div>
                    <div class="text-muted small">End-users can open only their own accountability records.</div>
                </div>

                <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                    <div class="fw-semibold">Request submission</div>
                    <div class="text-muted small">End-users can submit property requests for supply office review.</div>
                </div>

                <div class="border rounded-4 p-3 bg-light-subtle">
                    <div class="fw-semibold">Protected access</div>
                    <div class="text-muted small">Only records and requests for the logged-in end-user are shown.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
