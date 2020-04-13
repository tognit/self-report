@extends('layouts.master')
@php $nav_path = ['role'] @endphp
@section('page-title')
    Add New Roles
@endsection
@section('page-header-title')
    Add New Roles
@endsection
@section('page-help-link', '#TODO')
@section('page-header-breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('role.index') }}">Roles</a></li>
        <li class="breadcrumb-item active" aria-current="location">Add New Roles</li>
    </ol>
@endsection
@section('content')
    <role-form csrf_token="{{ csrf_token() }}"></role-form>
@endsection
