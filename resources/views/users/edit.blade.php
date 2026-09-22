@extends('layouts.app')

@section('title', 'Edit User | PARDS')
@section('page_title', 'Edit User')
@section('section_label', 'User Management')

@section('content')
    <div class="dashboard-card p-4">
        <div class="mb-4">
            <h2 class="h4 mb-1">Update User Account</h2>
        </div>

        <form method="POST" action="{{ route('users.update', $user) }}">
            @method('PUT')
            @include('users.partials.form', ['roles' => $roles, 'submitLabel' => 'Update User'])
        </form>
    </div>
@endsection
