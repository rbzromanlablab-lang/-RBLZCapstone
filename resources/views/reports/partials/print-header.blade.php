<div style="text-align: center; margin-bottom: 20px; color: #000;">
    @include('partials.print-logo')
    <div style="font-size: 16px; font-weight: bold;">ABANON NATIONAL HIGH SCHOOL</div>
    <div>San Carlos City, Pangasinan</div>
</div>

@push('styles')
    <style>
        @media print {
            @page { size: A4 landscape; margin: 12mm; }
            .sidebar-shell, .mobile-app-header, .content-shell > nav,
            .report-actions, .flash-toast-container, .offcanvas-backdrop { display: none !important; }
            body, .dashboard-card { background: #fff !important; color: #000 !important; }
            .app-shell { display: block !important; }
            .content-shell, .content-shell main, .dashboard-card {
                min-height: 0 !important; width: 100% !important; margin: 0 !important;
                padding: 0 !important; box-shadow: none !important;
            }
            .table-responsive { overflow: visible; }
            .table { font-size: 10px; }
            .table th { white-space: normal; }
            .table > :not(caption) > * > * {
                color: #000; background: #fff; box-shadow: none; border: 1px solid #000;
            }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
@endpush
