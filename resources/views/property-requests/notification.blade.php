@php
    $requestNotices = [];
    if (auth()->user()->isStaff()) {
        $pendingCount = \App\Models\PropertyRequestRecord::where('status', 'pending')->count();
        $approvedCount = \App\Models\PropertyRequestRecord::where('status', 'approved')->whereNotNull('reviewed_at')->whereNull('assignment_id')->count();
        if ($pendingCount) $requestNotices[] = ['status' => 'pending', 'text' => $pendingCount.' new property request(s) need staff review.'];
        if ($approvedCount) $requestNotices[] = ['status' => 'approved', 'text' => $approvedCount.' admin-approved request(s) are waiting for staff assignment. Check available stock and assign the requested property.'];
    } elseif (auth()->user()->isAdmin()) {
        $reviewCount = \App\Models\PropertyRequestRecord::where('status', 'awaiting_admin')->whereNotNull('reviewed_at')->count();
        if ($reviewCount) $requestNotices[] = ['status' => 'awaiting_admin', 'text' => $reviewCount.' request(s) forwarded by staff need your approval.'];
    }
@endphp
@foreach ($requestNotices as $notice)
    <div class="alert alert-info mt-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2" role="status">
        <span><i class="bi bi-bell me-2" aria-hidden="true"></i>{{ $notice['text'] }}</span>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('property-requests.index', ['status' => $notice['status']]) }}">View Requests</a>
    </div>
@endforeach
