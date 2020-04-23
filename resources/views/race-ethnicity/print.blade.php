@extends('layouts.print')
@section('page-title', 'Rax Ethnicities')
@section('table-headings-row')
    <tr>
        <th>Name</th>
    </tr>
@endsection
@section('table-data-rows')
    @foreach($data as $obj)
        <tr>
            <td>{{ $obj->name }}</td>
        </tr>
    @endforeach
@endsection
