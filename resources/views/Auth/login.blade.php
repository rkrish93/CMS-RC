<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | CMS-RC</title>

    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        body {
            min-height: 100vh;
            background: #f6f8fb;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
        }

        .auth-card {
            width: 100%;
            max-width: 430px;
            padding: 34px;
            border: 1px solid #e4e9f0;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 24px 70px rgba(21, 32, 51, 0.12);
        }

        .brand-mark {
            width: 74px;
            height: 74px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e4e9f0;
            border-radius: 18px;
            background: #f8fbff;
        }

        .brand-mark img {
            max-width: 58px;
            max-height: 58px;
        }

        .auth-card h1 {
            color: #152033;
            font-size: 24px;
            font-weight: 900;
        }

        .auth-card .form-control {
            min-height: 48px;
            border-radius: 10px;
        }

        .auth-card .btn {
            min-height: 48px;
            border-radius: 10px;
            font-weight: 800;
        }
    </style>
</head>
<body>
<main class="auth-shell">
    <section class="auth-card">
        <div class="text-center mb-4">
            <span class="brand-mark mb-3">
                <img src="{{ asset('assets/images/cms-rc-logo1.png') }}" alt="CMS-RC">
            </span>
            <h1 class="mb-1">CMS-RC</h1>
            <p class="text-muted mb-0">Clinic Management System for Rural Clinics</p>
        </div>

        <h5 class="mb-3">Sign in to your account</h5>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="form-control"
                       placeholder="name@example.com"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Enter password"
                       required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <label class="form-check-label text-muted">
                    <input type="checkbox" class="form-check-input me-1" name="remember">
                    Remember me
                </label>

                <a href="{{ route('password.request') }}" class="text-primary small">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-gradient-primary w-100">
                Sign In
            </button>
        </form>
    </section>
</main>

<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
</body>
</html>
