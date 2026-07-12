@php
    $siteName = app(\Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface::class)->get('site.name', config('app.name'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('sjadminpanel.theme.rtl') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · {{ $siteName }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset(config('sjadminpanel.theme.favicon')) }}">

    <link href="{{ asset('vendor/sjadminpanel/vendor/fontawesome/css/all.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/ionio-icon/css/iconoir.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/animation/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/tabler-icons/tabler-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/flag-icons-master/flag-icon.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/apexcharts/apexcharts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/simplebar/simplebar.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/css/responsive.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="{{ session('sjadmin_layout_option', 'ltr') }}">

<div class="app-wrapper">

    <div class="loader-wrapper">
        <div class="app-loader">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>

    @include('sjadminpanel::partials.sidebar')

    <div class="app-content">
        <div class="">
            @include('sjadminpanel::partials.header')

            <main>
                <div class="container-fluid mt-3">
                    @include('sjadminpanel::partials.breadcrumb')

                    @if (session('success'))
                        <div class="alert alert-light-success" role="alert">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-light-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <div class="go-top">
        <span class="progress-value"><i class="ti ti-chevron-up"></i></span>
    </div>

    @include('sjadminpanel::partials.footer')
</div>

<script src="{{ asset('vendor/sjadminpanel/js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('vendor/sjadminpanel/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/sjadminpanel/vendor/simplebar/simplebar.js') }}"></script>
<script src="{{ asset('vendor/sjadminpanel/vendor/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('vendor/sjadminpanel/js/customizer.js') }}"></script>
<script src="{{ asset('vendor/sjadminpanel/js/script.js') }}"></script>

@stack('scripts')
</body>
</html>
