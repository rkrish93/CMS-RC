<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | CMS-RC</title>

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

        .auth-card .form-control,
        .auth-card .btn {
            min-height: 48px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<main class="auth-shell">
    <section class="auth-card">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/images/cms-rc-logo1.png') }}" alt="CMS-RC" style="width:72px">
            <h4 class="fw-bold mt-3 mb-1">Forgot Password</h4>
            <p class="text-muted mb-0">Enter your email and we will send a reset link.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
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

            <button type="submit" class="btn btn-gradient-primary w-100">Send Reset Link</button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-primary">Back to Login</a>
            </div>
        </form>
    </section>
</main>

<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
</body>
</html>
