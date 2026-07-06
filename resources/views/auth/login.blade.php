@php
    $siteName = app(\Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface::class)->get('site.name', config('app.name'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · {{ $siteName }}</title>
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
            <div class="row sign-in-content-bg">
                <div class="col-lg-6 image-contentbox d-none d-lg-block">
                    <div class="form-container">
                        <div class="signup-content mt-4">
                            <img alt="{{ $siteName }}" class="img-fluid" src="{{ asset(config('sjadminpanel.theme.logo')) }}">
                        </div>
                        <div class="signup-bg-img">
                            <img alt="" class="img-fluid" src="{{ asset('vendor/sjadminpanel/images/login/01.png') }}">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 form-contentbox">
                    <div class="form-container">
                        <form class="app-form rounded-control" method="POST" action="{{ route('sjadmin.login.attempt') }}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-5 text-center text-lg-start">
                                        <h2 class="text-primary-dark f-w-600">Welcome back!</h2>
                                        <p>Sign in with your admin credentials to continue</p>
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
                                        <label class="form-label" for="email">Email</label>
                                        <input class="form-control" id="email" name="email" placeholder="Enter your email"
                                               type="email" value="{{ old('email') }}" required autofocus>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="password">Password</label>
                                        <input class="form-control" id="password" name="password"
                                               placeholder="Enter your password" type="password" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                                        <label class="form-check-label text-secondary" for="remember">Remember me</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <button class="btn btn-light-primary w-100" type="submit">Sign In</button>
                                    </div>
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
