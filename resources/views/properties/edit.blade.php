@extends('layouts.app')

@section('title', 'Edit Property | PARDS')
@section('page_title', 'Edit Property')
@section('section_label', 'Property Management')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Edit Property Record</h2>
            <p class="text-muted mb-0">Update the selected property information.</p>
        </div>

        <form method="POST" action="{{ route('properties.update', $property) }}">
            @method('PUT')
            @include('properties.partials.form', ['submitLabel' => 'Update Property'])
        </form>
    </div>
@endsection
