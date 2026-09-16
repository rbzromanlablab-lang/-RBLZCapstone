<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property QR Details | PARDS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-4 px-3 py-2 bg-primary-subtle text-primary">
                                <i class="bi bi-qr-code-scan fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">PARDS QR Property Details</div>
                                <h1 class="h3 mb-0">{{ $property->property_name }}</h1>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Property Name</div>
                                    <div class="fw-semibold">{{ $property->property_name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Property Code</div>
                                    <div class="fw-semibold">{{ $property->property_code }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Serial Number</div>
                                    <div class="fw-semibold">{{ $property->serial_number ?: 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Assigned Teacher/Person</div>
                                    <div class="fw-semibold">{{ $activeAssignment?->teacher?->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Status</div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $property->status)) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Condition</div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-semibold">{{ $activeAssignment?->location ?: 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded-4 p-3">
                                    <div class="text-muted small">Description</div>
                                    <div class="fw-semibold">{{ $property->description ?: 'No description provided.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
