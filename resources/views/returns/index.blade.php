@extends('layouts.app')

@section('title', 'Return Logs | PARDS')
@section('page_title', 'Return Logs')
@section('section_label', 'Return Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Returned Property Logs</h2>
                <p class="text-muted mb-0">Track who received property items and who recorded their return.</p>
            </div>
            <a href="{{ route('assignments.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-journal-check me-2"></i>View Assignments
            </a>
        </div>

        <form method="GET" action="{{ route('returns.index') }}" class="row g-3 mb-4">
            <div class="col-lg-4">
                <label for="search" class="form-label">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Property, serial, assignee, returned by"
                >
            </div>

            <div class="col-lg-2">
                <label for="assignee_id" class="form-label">Given To</label>
                <select id="assignee_id" name="assignee_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee->id }}" @selected((string) request('assignee_id') === (string) $assignee->id)>
                            {{ $assignee->name }} ({{ ucfirst($assignee->role) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label for="returned_by" class="form-label">Returned By</label>
                <select id="returned_by" name="returned_by" class="form-select">
                    <option value="">All</option>
                    @foreach ($returnRecorders as $recorder)
                        <option value="{{ $recorder->id }}" @selected((string) request('returned_by') === (string) $recorder->id)>
                            {{ $recorder->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label for="date_from" class="form-label">From</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-lg-2">
                <label for="date_to" class="form-label">To</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-funnel me-2"></i>Filter Logs
                </button>
                <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Serial Number/s</th>
                        <th>Given To</th>
                        <th>Returned By</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th class="text-end">Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $return->assignment?->property?->property_name ?: 'N/A' }}</div>
                                <small class="text-muted d-block">{{ $return->assignment?->property?->property_code ?: 'N/A' }}</small>
                                <small class="text-muted d-block">{{ $return->assignment?->property?->office ?: 'No office listed' }}</small>
                            </td>
                            <td>{{ $return->returned_serial_numbers ?: ($return->assignment?->propertyUnits?->pluck('serial_number')->join(', ') ?: ($return->assignment?->property?->serial_number ?: 'N/A')) }}</td>
                            <td>
                                <div>{{ $return->assignment?->assignee?->name ?: 'N/A' }}</div>
                                <small class="text-muted">{{ ucfirst($return->assignment?->assignee?->role ?? 'user') }}</small>
                            </td>
                            <td>{{ $return->returnedBy?->name ?: 'N/A' }}</td>
                            <td>{{ optional($return->return_date)->format('F d, Y') ?: 'N/A' }}</td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    {{ ucfirst(str_replace('_', ' ', $return->status)) }}
                                </span>
                            </td>
                            <td>{{ $return->remarks ?: 'None' }}</td>
                            <td class="text-end">
                                @if ($return->assignment)
                                    <a href="{{ route('assignments.show', $return->assignment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No return logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $returns->links() }}
        </div>
    </div>
@endsection
