<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password</title>

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

<!-- Logo -->
<div class="text-center mb-4">
    <img src="{{ asset('assets/images/cms-rc-logo1.png') }}"
         style="width:100px">
    <h4 class="font-weight-bold mt-2">CMS-RC</h4>
    <p class="text-muted small">Set your new password</p>
</div>

<h4 class="mb-3">Reset Password</h4>

<!-- Success / Errors -->
@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
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
<form method="POST" action="{{ route('password.update') }}">
@csrf

<input type="hidden" name="token" value="{{ $token }}">

<div class="form-group mb-3">
    <input type="email"
           name="email"
           value="{{ $email }}"
           class="form-control form-control-lg"
           placeholder="Email"
           required>
</div>

<div class="form-group mb-3">
    <input type="password"
           name="password"
           class="form-control form-control-lg"
           placeholder="New Password"
           required>
</div>

<div class="form-group mb-3">
    <input type="password"
           name="password_confirmation"
           class="form-control form-control-lg"
           placeholder="Confirm Password"
           required>
</div>

<div class="mt-3 d-grid gap-2">
    <button type="submit"
        class="btn btn-gradient-primary btn-lg">
        Reset Password
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
