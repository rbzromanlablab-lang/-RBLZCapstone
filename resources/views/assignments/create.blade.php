@extends('layouts.app')

@section('title', 'Assign Property | PARDS')
@section('page_title', 'Assign Property')
@section('section_label', 'Property Assignment Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Create Assignment</h2>
        </div>

        @if ($selectedAssignee)
            <div class="alert alert-info border mb-4">
                <div class="fw-semibold mb-1">Assigning To {{ $selectedAssignee->name }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('assignments.store') }}">
            @include('assignments.partials.form', ['submitLabel' => 'Save Assignment'])
        </form>
    </div>
@endsection
