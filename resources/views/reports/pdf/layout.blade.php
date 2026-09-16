<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin-bottom: 4px; font-size: 22px; }
        p { margin-top: 0; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #eff6ff; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Generated from Property Accountability Records and Disposal System (PARDS)</p>
    @yield('pdf-content')
</body>
</html>
