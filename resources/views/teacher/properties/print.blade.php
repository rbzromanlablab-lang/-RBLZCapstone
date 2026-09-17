<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountability Record</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #000; background: #fff; font: 12px Arial, sans-serif; line-height: 1.4; }
        .page { max-width: 1100px; margin: auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header p { margin: 2px 0; }
        h1 { font-size: 19px; margin: 12px 0 4px; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 5px 0; }
        .records { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 11px; }
        .records th, .records td { border: 1px solid #000; padding: 7px 5px; text-align: left; vertical-align: top; overflow-wrap: anywhere; word-wrap: break-word; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .empty { text-align: center !important; padding: 28px !important; }
        .acknowledgment { margin-top: 24px; break-inside: avoid; page-break-inside: avoid; }
        .signatures { width: 100%; margin-top: 24px; }
        .signatures td { width: 50%; vertical-align: top; padding-right: 40px; }
        .signature-line { margin-top: 48px; border-top: 1px solid #000; padding-top: 5px; text-align: center; }
        .signature-caption { text-align: center; font-size: 11px; }
        .date-line { margin-top: 18px; }
        .print-actions { max-width: 1100px; margin: auto; padding: 16px 20px; font-size: 14px; }
        .action-buttons { display: flex; flex-wrap: wrap; gap: 10px; }
        .print-actions a, .print-actions button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; border: 1px solid #000; border-radius: 6px; padding: 10px 16px; color: #000; background: #fff; font: inherit; text-decoration: none; cursor: pointer; }
        .print-actions a:hover, .print-actions button:hover { background: #000; color: #fff; }
        .print-actions a:focus-visible, .print-actions button:focus-visible { outline: 2px solid #000; outline-offset: 3px; }
        .print-actions a:active, .print-actions button:active { background: #333; color: #fff; }
        @media screen and (max-width: 767px) {
            .page { padding: 16px; }
            .records-scroll { overflow-x: auto; }
            .records { min-width: 950px; }
            .action-buttons > * { flex: 1 1 100%; }
            .signatures td { padding-right: 16px; }
        }
        @media print {
            .print-actions { display: none !important; }
            .page { max-width: none; padding: 0; margin: 0; }
            .records-scroll { overflow: visible; }
            .records { min-width: 0; }
        }
    </style>
</head>
<body>
    @unless ($isPdf)
        <div class="print-actions">
            <div class="action-buttons">
                <a href="{{ route('teacher.properties.print', ['pdf' => 1]) }}">Download Printable PDF</a>
                <button type="button" onclick="window.print()">Print Form</button>
                <a href="{{ route('teacher.properties.index') }}">Back to My Accountabilities</a>
            </div>
            <p>For a clean copy without browser headers, download and print the PDF. For browser printing, turn off "Headers and footers" in the print settings.</p>
        </div>
    @endunless
    <main class="page">
        <div class="header">
            @include('partials.print-logo')
            <p><strong>ABANON NATIONAL HIGH SCHOOL</strong></p>
            <p>San Carlos City, Pangasinan</p>
            <h1>PROPERTY ACCOUNTABILITY RECORD</h1>
            <p>List of Currently Assigned Properties</p>
        </div>
        <div class="meta">
            <p><strong>Accountable End-User:</strong> {{ $teacher->name }}</p>
            <p><strong>Email:</strong> {{ $teacher->email }}</p>
            <p><strong>Employee Number:</strong> {{ $teacher->employee_number ?: '____________________' }}</p>
            <p><strong>Date Prepared:</strong> {{ $printedAt->format('F d, Y') }}</p>
        </div>
        <div class="records-scroll">
            <table class="records">
                <thead>
                    <tr>
                        <th>Property Code</th><th>Serial Number</th><th>Property Name</th>
                        <th>Category</th><th>Quantity</th><th>Assigned Serials</th>
                        <th>Condition</th><th>Date Assigned</th><th>Assigned By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        @foreach ($property->assignments as $assignment)
                            <tr>
                                <td>{{ $property->property_code }}</td>
                                <td>{{ $property->serial_number ?: 'N/A' }}</td>
                                <td>{{ $property->property_name }}</td>
                                <td>{{ $property->category ?: 'N/A' }}</td>
                                <td>{{ $assignment->quantity_assigned }} {{ $property->unit }}</td>
                                <td>{{ $assignment->propertyUnits->pluck('serial_number')->join(', ') ?: 'N/A' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</td>
                                <td>{{ optional($assignment->date_assigned)->format('M d, Y') ?: 'N/A' }}</td>
                                <td>{{ $assignment->assignedBy?->name ?: 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="9" class="empty">No assigned properties found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="acknowledgment">
            <p>I acknowledge that the properties listed above are recorded under my accountability and undertake to exercise proper care and safekeeping of these items.</p>
            <table class="signatures">
                <tr>
                    <td>
                        <strong>Acknowledged by:</strong>
                        <div class="signature-line">{{ $teacher->name }}</div>
                        <div class="signature-caption">Signature over Printed Name of Accountable End-User</div>
                        <div class="date-line">Date: ____________________</div>
                    </td>
                    <td>
                        <strong>Verified by:</strong>
                        <div class="signature-line">&nbsp;</div>
                        <div class="signature-caption">Signature over Printed Name of Supply / Property Officer</div>
                        <div class="date-line">Date: ____________________</div>
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>
</html>
