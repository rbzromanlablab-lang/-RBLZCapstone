<section class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h3 class="h5 mb-0">{{ $groupTitle }}</h3>
        <span class="badge text-bg-light border text-dark">{{ $assignees->total() }} {{ $groupLabel }}</span>
    </div>
    @forelse ($assignees as $assignee)
        <article class="border rounded-4 p-3 p-md-4 mb-4" data-assignee-id="{{ $assignee->id }}">
            <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-3 mb-3">
                <div>
                    <h4 class="h5 mb-1"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>{{ $assignee->name }}</h4>
                    <div class="text-muted small text-break">{{ $assignee->email }}</div>
                    <div class="small mt-2"><strong>Department:</strong> {{ $assignee->teacherAssignments->pluck('department')->filter()->unique()->join(', ') ?: ($assignee->department ?: 'Not yet provided') }}</div>
                </div>
                <a href="{{ route('assignments.create', [$assignee->isTeacher() ? 'teacher_id' : 'staff_id' => $assignee->id]) }}" class="btn btn-sm btn-outline-primary">Assign Property</a>
            </header>
            <div class="table-responsive">
                <table class="table align-middle mobile-record-table mb-0">
                    <thead><tr><th>Property</th><th>Department</th><th>Quantity</th><th>Date Assigned</th><th>Assigned By</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach ($assignee->teacherAssignments as $assignment)
                            <tr>
                                <td data-label="Property">
                                    <div>
                                        <div class="fw-semibold">{{ $assignment->property?->property_name ?? 'Unavailable property' }}</div>
                                        <div class="small text-muted">{{ $assignment->property?->property_code }}</div>
                                        <div class="small text-break">Serial: {{ $assignment->propertyUnits->pluck('serial_number')->join(', ') ?: ($assignment->property?->serial_number ?: 'N/A') }}</div>
                                    </div>
                                </td>
                                <td data-label="Department">{{ $assignment->department ?: 'Not yet provided' }}</td>
                                <td data-label="Quantity">{{ $assignment->quantity_assigned }} {{ $assignment->property?->unit }}</td>
                                <td data-label="Date Assigned">{{ $assignment->date_assigned?->format('M d, Y') }}</td>
                                <td data-label="Assigned By">{{ $assignment->assignedBy?->name ?? 'System' }}</td>
                                <td data-label="Status"><span class="badge {{ $assignment->status === 'active' ? 'text-bg-success' : 'text-bg-light border text-dark' }} align-self-start">{{ ucfirst($assignment->status) }}</span></td>
                                <td class="record-action">
                                    <div class="d-flex flex-wrap gap-2 w-100">
                                        @if ($assignment->status === 'active')
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('assignments.edit', $assignment) }}">Edit</a>
                                        @endif
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('assignments.show', $assignment) }}">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    @empty
        <div class="border rounded-4 p-4 text-muted text-center">No {{ $groupLabel }} with assignment records found.</div>
    @endforelse
    {{ $assignees->links() }}
</section>
