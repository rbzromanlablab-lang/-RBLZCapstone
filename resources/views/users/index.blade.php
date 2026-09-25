@extends('layouts.app')

@section('title', 'Users | PARDS')
@section('page_title', 'Users')
@section('section_label', 'User Management')

@section('content')
    <div class="dashboard-card p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">User Accounts</h2>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add User
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php($isCurrentUser = auth()->id() === $user->id)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at_for_display?->format('M d, Y h:i A') ?: 'N/A' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    @if (in_array($user->role, [\App\Models\User::ROLE_TEACHER, \App\Models\User::ROLE_STAFF], true))
                                        <a
                                            href="{{ route('assignments.create', $user->role === \App\Models\User::ROLE_TEACHER ? ['teacher_id' => $user->id] : ['staff_id' => $user->id]) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Add Property
                                        </a>
                                    @endif
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('users.status', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            @disabled($isCurrentUser)
                                            title="{{ $isCurrentUser ? 'You cannot change your own active status while logged in.' : '' }}"
                                        >
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
@endsection
