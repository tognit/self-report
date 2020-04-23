@extends('layouts.crud-master')
@php $nav_path = ['organization'] @endphp
@section('page-title')
    View {{$organization->name}}
@endsection
@section('page-header-title')
    View {{$organization->name}}
@endsection
@section('page-header-breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('organization.index') }}">Organizations</a></li>
        <li class="breadcrumb-item active" aria-current="location">View {{$organization->name}}</li>
    </ol>
@endsection
@section('content')

    <organization-show :record='@json($organization)'></organization-show>

    <div class="row">
        <div class="col-md-12">
            <div class="row mt-4">
                <div class="col-md-4">
                    @if ($can_edit)
                        <a href="/organization/{{ $organization->id }}/edit" class="btn btn-primary">Edit
                            Organizations</a>
                    @endif
                </div>
                <div class="col-md-4 text-md-center mt-2 mt-md-0">
                    @if ($can_delete)
                        <form class="form" role="form" method="POST" action="/organization/{{ $organization->id }}">
                            <input type="hidden" name="_method" value="delete">
                            {{ csrf_field() }}

                            <input class="btn btn-danger" Onclick="return ConfirmDelete();" type="submit"
                                   value="Delete Organizations">

                        </form>
                    @endif
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <a href="{{ url('/organization') }}" class="btn btn-default">Return to List</a>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('scripts')
    <script>
        function ConfirmDelete() {
            var x = confirm("Are you sure you want to delete this Organizations?");
            if (x)
                return true;
            else
                return false;
        }
    </script>
@endsection
