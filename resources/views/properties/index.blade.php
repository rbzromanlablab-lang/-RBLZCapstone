@extends('layouts.app')

@section('title', 'Properties | PARDS')
@section('page_title', 'Properties')
@section('section_label', 'Property Management')

@section('content')
    <div class="dashboard-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Property List</h2>
                <p class="text-muted mb-0">Search, filter, and manage school property records.</p>
            </div>
            <a href="{{ route('properties.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Property
            </a>
        </div>

        <form method="GET" action="{{ route('properties.index') }}" class="row g-3 mb-4">
            <div class="col-lg-4">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search name, code, serial, brand, model, office, location"
                    value="{{ request('search') }}"
                >
            </div>

            <div class="col-lg-2">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select name="condition_status" class="form-select">
                    <option value="">All Conditions</option>
                    @foreach ($conditionStatuses as $condition)
                        <option value="{{ $condition }}" @selected(request('condition_status') === $condition)>
                            {{ ucfirst(str_replace('_', ' ', $condition)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <input
                    type="text"
                    name="location"
                    class="form-control"
                    placeholder="Location"
                    value="{{ request('location') }}"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-funnel me-2"></i>Filter
                </button>
                <a href="{{ route('properties.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Property Code</th>
                            <th>Serial Number</th>
                            <th>Property Name</th>
                            <th>Category</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Assigned</th>
                        <th>Condition</th>
                        <th>Status</th>
                        <th>Office</th>
                        <th>Location</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        <tr>
                            <td class="fw-semibold">{{ $property->property_code }}</td>
                            <td>{{ $property->serial_number ?: 'N/A' }}</td>
                            <td>{{ $property->property_name }}</td>
                            <td>{{ $property->category ?: 'N/A' }}</td>
                            <td>{{ $property->tracked_quantity }} {{ $property->unit }}</td>
                            <td>{{ $property->available_quantity }} {{ $property->unit }}</td>
                            <td>{{ $property->active_assigned_quantity }} {{ $property->unit }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                                </span>
                            </td>
                            <td>{{ $property->office ?: 'N/A' }}</td>
                            <td>{{ $property->location ?: 'N/A' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Delete this property?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">No properties found.</td>
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
