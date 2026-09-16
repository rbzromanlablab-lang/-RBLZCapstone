@extends('layouts.app')

@section('title', 'Assign Property | PARDS')
@section('page_title', 'Assign Property')
@section('section_label', 'Property Assignment Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Create Assignment</h2>
            <p class="text-muted mb-0">Assign a property item to either a teacher or a staff member and save it to history.</p>
        </div>

        @if ($selectedAssignee)
            <div class="alert alert-info border mb-4">
                <div class="fw-semibold mb-1">Assigning To {{ $selectedAssignee->name }}</div>
                <div class="small text-muted">The {{ $selectedAssignee->role }} has already been selected for you. Choose the property details below.</div>
            </div>
        @endif

        <form method="POST" action="{{ route('assignments.store') }}">
            @include('assignments.partials.form', ['submitLabel' => 'Save Assignment'])
        </form>
    </div>
@endsection
