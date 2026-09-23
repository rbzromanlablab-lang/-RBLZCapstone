@extends('reports.pdf.layout')

@section('pdf-content')
    <table>
        <thead>
            <tr>
                <th>Property Code</th>
                <th>Serial Number</th>
                <th>Property Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Available</th>
                <th>Assigned</th>
                <th>Office</th>
                <th>Location</th>
                <th>Condition</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($properties as $property)
                <tr>
                    <td>{{ $property->property_code }}</td>
                    <td>{{ $property->unit_serial_numbers ?: 'N/A' }}</td>
                    <td>{{ $property->property_name }}</td>
                    <td>{{ $property->category ?: 'N/A' }}</td>
                    <td>{{ $property->tracked_quantity }} {{ $property->unit }}</td>
                    <td>{{ $property->available_quantity }} {{ $property->unit }}</td>
                    <td>{{ $property->active_assigned_quantity }} {{ $property->unit }}</td>
                    <td>{{ $property->office ?: 'N/A' }}</td>
                    <td>{{ $property->location ?: 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $property->condition_status)) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $property->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
