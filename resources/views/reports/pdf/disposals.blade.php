@extends('reports.pdf.layout')

@section('pdf-content')
    <table>
        <thead>
            <tr>
                <th>Property Code</th>
                <th>Property Name</th>
                <th>Quantity Disposed</th>
                <th>Disposal Date</th>
                <th>Method</th>
                <th>Disposed By</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($disposals as $disposal)
                <tr>
                    <td>{{ $disposal->property?->property_code }}</td>
                    <td>{{ $disposal->property?->property_name }}</td>
                    <td>{{ $disposal->quantity_disposed }}</td>
                    <td>{{ optional($disposal->disposal_date)->format('F d, Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $disposal->disposal_method)) }}</td>
                    <td>{{ $disposal->disposedBy?->name ?: 'N/A' }}</td>
                    <td>{{ ucfirst($disposal->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
