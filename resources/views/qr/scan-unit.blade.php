<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Unit QR Details | PARDS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --pards-primary: #2d2078;
            --pards-primary-dark: #170a58;
            --pards-accent: #f1c94c;
        }

        body {
            min-height: 100vh;
            color: #17133d;
            background: #f4f6fb;
        }

        .scan-header {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            padding: 28px 0 72px;
            color: #fff;
            background: linear-gradient(120deg, var(--pards-primary-dark), var(--pards-primary) 60%, #4434a2);
            box-shadow: 0 8px 28px rgba(23, 10, 88, .2);
        }

        .scan-header::before,
        .scan-header::after {
            position: absolute;
            z-index: -1;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 50%;
            content: '';
        }

        .scan-header::before {
            width: 290px;
            height: 290px;
            top: -180px;
            right: 8%;
        }

        .scan-header::after {
            width: 170px;
            height: 170px;
            right: 23%;
            bottom: -130px;
            background: rgba(255, 255, 255, .04);
        }

        .school-logo {
            width: 66px;
            height: 66px;
            flex: 0 0 66px;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, .9);
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 8px 22px rgba(0, 0, 0, .18);
        }

        .school-name {
            margin-bottom: 2px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .system-name {
            margin: 0;
            color: rgba(255, 255, 255, .72);
            font-size: .82rem;
        }

        .scan-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            color: #fff;
            font-size: .78rem;
            font-weight: 600;
        }

        .scan-label::before {
            width: 22px;
            height: 3px;
            border-radius: 999px;
            background: var(--pards-accent);
            content: '';
        }

        .scan-page {
            position: relative;
            z-index: 1;
            margin-top: -44px;
            padding-bottom: 48px;
        }

        .details-card {
            box-shadow: 0 14px 36px rgba(31, 27, 80, .12) !important;
        }

        @media (max-width: 575.98px) {
            .scan-header {
                padding: 20px 0 62px;
            }

            .school-logo {
                width: 54px;
                height: 54px;
                flex-basis: 54px;
            }

            .school-name {
                font-size: .86rem;
                letter-spacing: .04em;
            }

            .system-name {
                font-size: .74rem;
                line-height: 1.35;
            }

            .scan-page {
                margin-top: -34px;
                padding-bottom: 24px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <header class="scan-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 d-flex align-items-center gap-3">
                    <img
                        src="{{ asset('ANHS LOGO.jpg') }}"
                        alt="Abanon National High School logo"
                        class="school-logo"
                    >
                    <div>
                        <div class="school-name">Abanon National High School</div>
                        <p class="system-name"><strong>PARDS</strong> &middot; Property Accountability Records and Disposal System</p>
                        <div class="scan-label">Official QR Property Record</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container scan-page">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card details-card border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-4 px-3 py-2 bg-primary-subtle text-primary">
                                <i class="bi bi-qr-code-scan fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">PARDS QR Property Unit Details</div>
                                <h1 class="h3 mb-0">{{ $property?->property_name }}</h1>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Property Name</div>
                                    <div class="fw-semibold">{{ $property?->property_name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Property Code</div>
                                    <div class="fw-semibold">{{ $property?->property_code }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Unit Serial Number</div>
                                    <div class="fw-semibold">{{ $propertyUnit->serial_number }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Office</div>
                                    <div class="fw-semibold">{{ $property?->office ?: 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Assigned Teacher/Person</div>
                                    <div class="fw-semibold">{{ $activeAssignment?->assignee?->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Unit Status</div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $propertyUnit->status)) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-semibold">{{ $activeAssignment?->location ?: $property?->location ?: 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded-4 p-3">
                                    <div class="text-muted small">Description</div>
                                    <div class="fw-semibold">{{ $property?->description ?: 'No description provided.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
