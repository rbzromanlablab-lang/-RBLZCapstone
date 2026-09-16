@extends('layouts.app')

@section('title', 'Dashboard | PARDS')
@section('page_title', $pageTitle ?? 'Dashboard')
@section('section_label', $sectionLabel ?? 'PARDS Overview')

@section('content')
    <div class="dashboard-card p-4 p-lg-5 mb-4 overflow-hidden position-relative">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge rounded-pill text-bg-light border text-dark px-3 py-2 mb-3">
                    <i class="bi bi-shield-check me-2"></i>{{ $welcomeTag ?? 'Secure asset accountability workspace' }}
                </span>
                <h2 class="display-6 fw-semibold mb-3">Property Accountability Records and Disposal System</h2>
                <p class="text-muted mb-4">
                    A centralized supply office dashboard for monitoring property inventory, end-user accountabilities,
                    disposal workflow, and QR-based asset identification in one workspace.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <div class="quick-stat px-3 py-2">
                        <div class="small text-muted">Current Role</div>
                        <div class="fw-semibold">{{ $roleName ?? ucfirst(auth()->user()?->role ?? 'guest') }}</div>
                    </div>
                    <div class="quick-stat px-3 py-2">
                        <div class="small text-muted">School Year</div>
                        <div class="fw-semibold">2026 - 2027</div>
                    </div>
                    <div class="quick-stat px-3 py-2">
                        <div class="small text-muted">System Mode</div>
                        <div class="fw-semibold">Live Database</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                    <div class="rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #183153 0%, #254f7a 100%);">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <div class="text-white-50 small">Focus Area</div>
                            <div class="h5 mb-0">Supply Office Monitoring</div>
                        </div>
                        <i class="bi bi-clipboard-data fs-1"></i>
                    </div>
                    <p class="mb-0 text-white-50">
                        This workspace helps the supply office supervise end-user records and property movement.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="summary-icon"><i class="bi bi-box-seam"></i></div>
                        <span class="badge text-bg-light">Assets</span>
                    </div>
                    <p class="text-muted mb-2">Total Properties</p>
                    <h2 class="mb-1">{{ $stats['properties'] ?? 0 }}</h2>
                    <small class="text-muted">Registered inventory items</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="summary-icon"><i class="bi bi-journal-check"></i></div>
                        <span class="badge text-bg-light">Assignments</span>
                    </div>
                    <p class="text-muted mb-2">Active Assignments</p>
                    <h2 class="mb-1">{{ $stats['activeAssignments'] ?? 0 }}</h2>
                    <small class="text-muted">Items currently assigned</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="summary-icon"><i class="bi bi-person-badge"></i></div>
                        <span class="badge text-bg-light">End-Users</span>
                    </div>
                    <p class="text-muted mb-2">Registered End-Users</p>
                    <h2 class="mb-1">{{ $stats['teachers'] ?? 0 }}</h2>
                    <small class="text-muted">Teachers with accountability access</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="summary-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="summary-icon"><i class="bi bi-archive"></i></div>
                        <span class="badge text-bg-light">Supply Office</span>
                    </div>
                    <p class="text-muted mb-2">Supply Office Users</p>
                    <h2 class="mb-1">{{ $stats['staffUsers'] ?? 0 }}</h2>
                    <small class="text-muted">Admin and staff accounts</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <p class="page-section-title mb-1">Recent Activity</p>
                            <h3 class="h5 mb-0">Supply Office Accountability Monitoring</h3>
                        </div>
                        <span class="badge rounded-pill text-bg-primary">Live Layout</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Property Code</th>
                                    <th>Property</th>
                                    <th>Status</th>
                                    <th>Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($recentProperties ?? collect()) as $property)
                                    <tr>
                                        <td>{{ $property->property_code }}</td>
                                        <td>{{ $property->property_name }}</td>
                                        <td>
                                            <span class="badge text-bg-light border text-dark">
                                                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($property->updated_at)->format('F d, Y h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No property activity found yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dashboard-card h-100">
                <div class="card-body p-4">
                    @if (($roleActions ?? []) !== [])
                        <p class="page-section-title mb-1">Role Actions</p>
                        <h3 class="h5 mb-4">{{ auth()->user()?->isAdmin() ? 'Admin Controls' : 'Staff Controls' }}</h3>

                        <div class="d-grid gap-3 mb-4">
                            @foreach ($roleActions as $action)
                                <a href="{{ $action['url'] }}" class="text-decoration-none text-reset">
                                    <div class="border rounded-4 p-3 bg-light-subtle h-100">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="summary-icon flex-shrink-0">
                                                <i class="bi {{ $action['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $action['label'] }}</div>
                                                <div class="text-muted small">{{ $action['description'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <p class="page-section-title mb-1">Quick Panel</p>
                    <h3 class="h5 mb-4">Supply Office Snapshot</h3>

                    <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                        <div class="fw-semibold">Disposal Queue</div>
                        <div class="text-muted small">{{ $stats['disposed'] ?? 0 }} completed disposal records saved in the database.</div>
                    </div>

                    <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                        <div class="fw-semibold">QR Generation</div>
                        <div class="text-muted small">{{ $stats['properties'] ?? 0 }} property records are ready for QR viewing and printing.</div>
                    </div>

                    <div class="border rounded-4 p-3 bg-light-subtle">
                        <div class="fw-semibold">End-User Accountability</div>
                        <div class="text-muted small">{{ $stats['activeAssignments'] ?? 0 }} active accountability records are currently tracked by the supply office.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
