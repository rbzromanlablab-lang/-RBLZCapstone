@extends('layouts.app')

@section('title', 'Add Property | PARDS')
@section('page_title', 'Add Property')
@section('section_label', 'Property Management')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Create Property Record</h2>
            <p class="text-muted mb-0">Add a new property to the PARDS inventory list.</p>
        </div>

        <form method="POST" action="{{ route('properties.store') }}">
            @include('properties.partials.form', ['submitLabel' => 'Save Property'])
        </form>
    </div>
@endsection
