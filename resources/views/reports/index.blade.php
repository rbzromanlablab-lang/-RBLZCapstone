@extends('layouts.app')

@section('title', 'Reports | PARDS')
@section('page_title', 'Reports')
@section('section_label', 'Report Center')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="summary-icon mb-3"><i class="bi bi-box-seam"></i></div>
                    <div class="text-muted mb-2">Property Records</div>
                    <h2 class="mb-1">{{ $propertyCount }}</h2>
                    <a href="{{ route('reports.properties') }}" class="btn btn-outline-primary btn-sm mt-3">Open Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="summary-icon mb-3"><i class="bi bi-journal-check"></i></div>
                    <div class="text-muted mb-2">Assigned Items</div>
                    <h2 class="mb-1">{{ $assignmentCount }}</h2>
                    <a href="{{ route('reports.assignments') }}" class="btn btn-outline-primary btn-sm mt-3">Open Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="summary-icon mb-3"><i class="bi bi-archive"></i></div>
                    <div class="text-muted mb-2">Disposal Records</div>
                    <h2 class="mb-1">{{ $disposalCount }}</h2>
                    <a href="{{ route('reports.disposals') }}" class="btn btn-outline-primary btn-sm mt-3">Open Report</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card p-4">
        <h2 class="h4 mb-3">Available Reports</h2>
        <div class="list-group list-group-flush">
            <a href="{{ route('reports.properties') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span>Property List Report</span>
                <i class="bi bi-chevron-right"></i>
            </a>
            <a href="{{ route('reports.assignments') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span>Assigned Items Report</span>
                <i class="bi bi-chevron-right"></i>
            </a>
            <a href="{{ route('reports.disposals') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span>Disposal Report</span>
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
@endsection
