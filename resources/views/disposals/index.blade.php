@extends('layouts.app')

@section('title', ($isAdmin ? 'Disposal Requests' : 'My Disposal Requests').' | PARDS')
@section('page_title', $isAdmin ? 'Disposal Requests' : 'My Disposal Requests')
@section('section_label', $isAdmin ? 'Disposal Approval Module' : 'Disposal Request Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">{{ $isAdmin ? 'Disposal Records and Requests' : 'My Disposal Requests' }}</h2>
                <p class="text-muted mb-0">
                    {{ $isAdmin
                        ? 'View disposed properties, donation requests, transfers, and other disposal records.'
                        : 'Track the disposal requests you submitted for admin review.' }}
                </p>
            </div>
            <a href="{{ route('disposals.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>{{ $isAdmin ? 'Record Disposal' : 'Request Disposal' }}
            </a>
        </div>

        <form method="GET" action="{{ route('disposals.index') }}" class="row g-3 mb-4">
            <div class="col-lg-4">
                <label for="search" class="form-label">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Property, serial, office, submitted by"
                >
            </div>

            <div class="col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label for="method" class="form-label">Type / Method</label>
                <select id="method" name="method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach ($methods as $method)
                        <option value="{{ $method }}" @selected(request('method') === $method)>
                            {{ ucfirst(str_replace('_', ' ', $method)) }}
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
                    <i class="bi bi-funnel me-2"></i>Filter
                </button>
                <a href="{{ route('disposals.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Quantity</th>
                        <th>Disposal Date</th>
                        <th>Method</th>
                        <th>Submitted By</th>
                        <th>Processed By</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($disposals as $disposal)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $disposal->property?->property_name }}</div>
                                <small class="text-muted d-block">{{ $disposal->property?->property_code }}</small>
                                <small class="text-muted d-block">SN: {{ $disposal->property?->serial_number ?: 'N/A' }}</small>
                                <small class="text-muted d-block">{{ $disposal->property?->office ?: 'No office listed' }}</small>
                            </td>
                            <td>{{ $disposal->quantity_disposed }}</td>
                            <td>{{ optional($disposal->disposal_date)->format('F d, Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $disposal->disposal_method)) }}</td>
                            <td>{{ $disposal->disposedBy?->name }}</td>
                            <td>{{ $disposal->processedBy?->name ?: 'Pending review' }}</td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    {{ ucfirst(str_replace('_', ' ', $disposal->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('disposals.show', $disposal) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No disposal records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $disposals->links() }}
        </div>
    </div>
@endsection
