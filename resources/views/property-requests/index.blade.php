@extends('layouts.app')

@section('title', 'Confirm Property Requests | PARDS')
@section('page_title', 'Confirm Property Requests')
@section('section_label', 'Supply Office Request Confirmation')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Teacher Property Requests</h2>
            <p class="text-muted mb-0">Confirm and process property requests submitted by teachers.</p>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Requester</th>
                        <th>Requested Item</th>
                        <th>Quantity</th>
                        <th>Needed By</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($propertyRequests as $propertyRequest)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $propertyRequest->requester?->name ?: 'N/A' }}</div>
                                <small class="text-muted">{{ $propertyRequest->requester?->email ?: 'N/A' }}</small>
                            </td>
                            <td>{{ $propertyRequest->requested_item_name }}</td>
                            <td>{{ $propertyRequest->requested_quantity }}</td>
                            <td>{{ optional($propertyRequest->needed_by)->format('F d, Y') ?: 'N/A' }}</td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    {{ ucfirst($propertyRequest->status) }}
                                </span>
                            </td>
                            <td>{{ $propertyRequest->processedBy?->name ?: 'Pending review' }}</td>
                            <td class="text-end">
                                <a href="{{ route('property-requests.show', $propertyRequest) }}" class="btn btn-sm btn-outline-primary">Confirm</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No property requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $propertyRequests->links() }}
        </div>
    </div>
@endsection
