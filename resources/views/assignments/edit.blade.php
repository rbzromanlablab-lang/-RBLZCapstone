@extends('layouts.app')

@section('title', 'Edit Assignment | PARDS')
@section('page_title', 'Edit Assignment')
@section('section_label', 'Property Assignment Module')

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Edit Assignment</h2>
                <p class="text-muted mb-0">Update assignment details or continue to disposal if the assigned item is damaged.</p>
            </div>
            <a href="{{ route('disposals.create', ['assignment' => $assignment->id]) }}" class="btn btn-outline-danger">
                <i class="bi bi-trash3 me-2"></i>{{ auth()->user()?->isStaff() ? 'Request Disposal' : 'Record Disposal' }}
            </a>
        </div>

        <form method="POST" action="{{ route('assignments.update', $assignment) }}">
            @method('PUT')
            @include('assignments.partials.form', ['submitLabel' => 'Update Assignment'])
        </form>
    </div>
@endsection
