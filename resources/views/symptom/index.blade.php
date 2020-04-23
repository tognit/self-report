@extends('layouts.crud-master')
@php $nav_path = ['symptom'] @endphp
@section('page-title', 'Symptoms')
@section('page-header-title', 'Symptoms')
@section('page-help-link', '#TODO')
@section('page-header-breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="location">Symptoms</li>
    </ol>
@endsection
@section('content')
    <symptom-grid :params="{
        Page: '{{ $page }}',
        Search: '{{ $search }}',
        sortOrder: '{{ $direction }}',
        sortKey: '{{ $column }}',
        CanAdd: '{{ $can_add }}',
        CanEdit: '{{ $can_edit }}',
        CanShow: '{{ $can_show }}',
        CanDelete: '{{ $can_delete }}',
        CanExcel: '{{ $can_excel }}'
        }"></symptom-grid>
@endsection
