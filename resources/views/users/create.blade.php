@extends('layouts.app')

@section('title', 'Add User | PARDS')
@section('page_title', 'Add User')
@section('section_label', 'User Management')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Create User Account</h2>
            <p class="text-muted mb-0">Add a new admin, staff, or teacher account.</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}">
            @include('users.partials.form', ['roles' => $roles, 'submitLabel' => 'Save User'])
        </form>
    </div>
@endsection
