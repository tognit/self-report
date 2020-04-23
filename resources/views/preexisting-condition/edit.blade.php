@extends('layouts.crud-master')
@php $nav_path = ['preexisting-condition'] @endphp
@section('page-title')
    Edit {{$preexisting_condition->name}}
@endsection
@section('page-header-title')
    Edit {{$preexisting_condition->name}}
@endsection
@section('page-help-link', '#TODO')
@section('page-header-breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('preexisting-condition.index') }}">Preexisting Conditions</a></li>
        <li class="breadcrumb-item active" aria-current="location">Edit {{$preexisting_condition->name}}</li>
    </ol>
@endsection
@section('content')
    <preexisting-condition-form csrf_token="{{ csrf_token() }}"
                                :record='@json($preexisting_condition)'></preexisting-condition-form>
@endsection
