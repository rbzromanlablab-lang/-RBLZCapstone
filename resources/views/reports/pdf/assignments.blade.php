@extends('reports.pdf.layout')

@section('pdf-content')
    <table>
        <thead>
            <tr>
                <th>Property Code</th>
                <th>Serial Number</th>
                <th>Property Name</th>
                <th>Teacher</th>
                <th>Quantity</th>
                <th>Date Assigned</th>
                <th>Assigned By</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->property?->property_code }}</td>
                    <td>{{ $assignment->unit_serial_numbers ?: 'N/A' }}</td>
                    <td>{{ $assignment->property?->property_name }}</td>
                    <td>{{ $assignment->teacher?->name ?: 'N/A' }}</td>
                    <td>{{ $assignment->quantity_assigned }}</td>
                    <td>{{ optional($assignment->date_assigned)->format('F d, Y') }}</td>
                    <td>{{ $assignment->assignedBy?->name ?: 'N/A' }}</td>
                    <td>{{ ucfirst($assignment->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
