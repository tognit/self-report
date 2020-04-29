@extends('layouts.crud-master')
@php $nav_path = ['admin', 'invite'] @endphp
@section('page-title', 'User Invitations')
@section('page-header-title', 'User Invitations')
@section('page-help-link', '#TODO')
@section('page-header-breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="location">User Invitations</li>
    </ol>
@endsection
@section('content')
    <invite-grid :params="{
        Page: '{{ $page }}',
        Search: '{{ $search }}',
        sortOrder: '{{ $direction }}',
        sortKey: '{{ $column }}',
        CanAdd: '{{ $can_add }}',
        CanEdit: '{{ $can_edit }}',
        CanShow: '{{ $can_show }}',
        CanDelete: '{{ $can_delete }}',
        CanExcel: '{{ $can_excel }}'
        }"></invite-grid>
@endsection
