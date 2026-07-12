@php
    $siteName = app(\Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface::class)->get('site.name', config('app.name'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification · {{ $siteName }}</title>
    <link rel="icon" href="{{ asset(config('sjadminpanel.theme.favicon')) }}">

    <link href="{{ asset('vendor/sjadminpanel/vendor/fontawesome/css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/ionio-icon/css/iconoir.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/tabler-icons/tabler-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sjadminpanel/css/responsive.css') }}" rel="stylesheet">
</head>
<body class="sign-in-bg">
<div class="app-wrapper d-block">
    <div class="main-container">
        <div class="container">
            <div class="row sign-in-content-bg justify-content-center">
                <div class="col-lg-6 form-contentbox">
                    <div class="form-container">
                        <form class="app-form rounded-control" method="POST" action="{{ route('sjadmin.two-factor.verify') }}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="signup-title mb-4">
                                        <h2>Two-Factor Verification</h2>
                                        <p>Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>
                                    </div>
                                </div>

                                @if ($errors->any())
                                    <div class="col-12">
                                        <div class="alert alert-light-danger">
                                            {{ $errors->first() }}
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="code">Authentication Code</label>
                                        <input class="form-control" id="code" name="code" placeholder="123456"
                                               type="text" inputmode="numeric" autocomplete="one-time-code" required autofocus>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <button class="btn btn-light-primary w-100" type="submit">Verify</button>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <a href="{{ route('sjadmin.login') }}">&larr; Back to sign in</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/sjadminpanel/js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('vendor/sjadminpanel/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
