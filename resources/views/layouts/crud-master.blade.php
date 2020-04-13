<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('img/favicon.ico') }}">
    <link href="/css/crud-app.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <title>@yield('page-title') - {{ config('app.alias',"APP") }}</title>

    @yield('css')

</head>


@php
    if ( array_key_exists('BACKGROUND_COLOR', $_ENV )) {
        echo '<body role="document" class="body-with-nav" style="background-color: ' . $_ENV['BACKGROUND_COLOR'] . '; ">';
    } else {
        echo '<body role="document" class="body-with-nav" style="background-color: #ddddb8;">';
    }
@endphp
<a href="#app" class="skip-link sr-only sr-only-focusable">Skip to main content</a>

<!-- "Loading" overlay (off at page load) -->
<div class="loading-overlay global"></div>
<div class="loading-message global">
<!-- <div><img src="{{ asset('img/loading-icon.gif') }}" alt="Loading icon"> Loading</div> -->
</div>

<!-- Header/menu -->

@include('layouts.crud-nav')


<main id="app" class="container" <?php /*tabindex="-1"*/ ?>>
    <!-- Help link, if available -->
<?php /*
            @if($__env->yieldContent('page-help-link'))
                <div class="page-help-link">
                    <a href="@yield('page-help-link')" onclick="return confirm('TODO: modal? link?')">
                        <img src="{{ asset('img/icons/help.svg') }}" class="svg-icon svg-icon-help" alt="Help icon" role="presentation">Help
                    </a>
                </div>
            @endif
            */ ?>

<!-- Page title and breadcrumbs -->
    <div class="page-header">

        @if($__env->yieldContent('page-header-breadcrumbs'))
            <nav class="breadcrumb-wrap mt-1" aria-label="breadcrumb">
                <img src="{{ asset('img/icons/square.svg') }}"
                     class="svg-icon svg-icon-square d-none d-lg-inline-block d-xl-inline-block" alt="Breadcrumb icon"
                     role="presentation">
                @yield('page-header-breadcrumbs')
            </nav>
        @endif
        @if($__env->yieldContent('page-header-title'))
            <h1 class="mb-0">@yield('page-header-title')</h1>
        @endif
    </div>

    <!-- Session message(s) (global) -->
    <div class="alerts global mb-5">
        @if ( Session::has('flash_error_message'))
            <div class="alert alert-danger" role="alert">
                <img src="{{ asset('img/icons/danger.svg') }}"
                     class="svg-icon svg-icon-danger svg-icon-error d-none d-lg-inline-block d-xl-inline-block"
                     alt="Alert" title="Alert" role="presentation">
                {{ Session::get('flash_error_message') }}
            </div>
        @endif
        @if ( Session::has('flash_info_message'))
            <div class="alert alert-warning" role="alert">
                <img src="{{ asset('img/icons/warning.svg') }}"
                     class="svg-icon svg-icon-warning d-none d-lg-inline-block d-xl-inline-block" alt="Info"
                     title="Info" role="presentation">
                {{ Session::get('flash_info_message') }}
            </div>
        @endif
        @if ( Session::has('flash_success_message'))
            <div class="alert alert-success" role="alert">
                <img src="{{ asset('img/icons/success.svg') }}"
                     class="svg-icon svg-icon-success d-none d-lg-inline-block d-xl-inline-block" alt="Checkmark"
                     title="Success" role="presentation">
                {{ Session::get('flash_success_message') }}
            </div>
        @endif
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                <img src="{{ asset('img/icons/success.svg') }}"
                     class="svg-icon svg-icon-success d-none d-lg-inline-block d-xl-inline-block" alt="Checkmark"
                     title="Success" role="presentation">
                {{ session('status') }}
            </div>
        @endif
    </div>

    <!-- Page content -->
    @yield('content')

    @include('layouts.bottom')
</main><!-- /.container -->

<!-- Bootstrap core JavaScript -->
<script src="{{ mix('js/app.js') }}"></script>
@yield('scripts')
</body>
</html>
