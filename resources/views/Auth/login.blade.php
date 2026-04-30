<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | CMS-RC</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
<div class="container-scroller">
<div class="container-fluid page-body-wrapper full-page-wrapper">
<div class="content-wrapper d-flex align-items-center auth">

<div class="row flex-grow">
<div class="col-lg-4 mx-auto">

<div class="auth-form-light text-left p-5 shadow rounded">

<!-- Logo -->
<div class="text-center mb-4">
    <img src="{{ asset('assets/images/cms-rc-logo1.png') }}"
         alt="logo"
         style="width:90px;">
    <h4 class="font-weight-bold mt-2">CMS-RC</h4>
    <p class="text-muted small">Clinic Management System for Rural Clinics</p>
</div>

<h5 class="mb-3 text-center">Sign in to continue</h5>

<!-- Errors -->
@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Form -->
<form method="POST" action="{{ route('login.post') }}">
@csrf

<div class="form-group mb-3">
    <input type="email"
           name="email"
           value="{{ old('email') }}"
           class="form-control form-control-lg"
           placeholder="Email address"
           required>
</div>

<div class="form-group mb-3">
    <input type="password"
           name="password"
           class="form-control form-control-lg"
           placeholder="Password"
           required>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" name="remember">
        <label class="form-check-label text-muted">
            Remember me
        </label>
    </div>

    <a href="{{ route('password.request') }}" class="text-primary small">
        Forgot password?
    </a>
</div>

<button type="submit"
        class="btn btn-gradient-primary w-100 btn-lg">
    SIGN IN
</button>

</form>

</div>
</div>
</div>

</div>
</div>
</div>
</div>

<!-- JS -->
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>

</body>
</html>
