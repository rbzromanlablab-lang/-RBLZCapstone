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
                    placeholder="Search name, email, department, property, or serial number"
                    aria-label="Search assignments"
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
            @include('assignments.partials.assignee-cards', ['assignees' => $teacherAssignees, 'groupTitle' => 'Teacher Assignments', 'groupLabel' => 'teachers'])
        @endif
        @if ($activeFilter !== 'teachers')
            @include('assignments.partials.assignee-cards', ['assignees' => $staffAssignees, 'groupTitle' => 'Staff Assignments', 'groupLabel' => 'staff members'])
        @endif
    </div>
@endsection
