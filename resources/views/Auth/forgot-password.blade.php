<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password</title>

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

<div class="auth-form-light text-left p-5">

<div class="text-center mb-4">
    <img src="{{ asset('assets/images/cms-rc-logo1.png') }}"
         style="width:100px">
    <h4 class="font-weight-bold mt-2">CMS-RC</h4>
    <p class="text-muted small">Reset your password</p>
</div>

<h4>Forgot Password?</h4>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
@csrf

<div class="form-group">
    <input type="email"
           name="email"
           class="form-control form-control-lg"
           placeholder="Enter your email"
           required>
</div>

<div class="mt-3 d-grid gap-2">
    <button type="submit"
        class="btn btn-gradient-primary btn-lg">
        Send Reset Link
    </button>
</div>

<div class="text-center mt-3">
    <a href="{{ route('login') }}" class="text-primary">
        Back to Login
    </a>
</div>

</form>

</div>
</div>
</div>
</div>
</div>
</div>
</div>

<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>

</body>
</html>
