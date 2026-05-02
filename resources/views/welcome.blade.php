<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS-RC</title>

    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
            background: #f6f8fb;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .welcome-card {
            width: min(92vw, 520px);
            padding: 38px;
            border: 1px solid #e4e9f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 24px 70px rgba(21, 32, 51, 0.12);
            text-align: center;
        }

        .welcome-card img {
            width: 82px;
            margin: 0 auto 18px;
        }

        .welcome-card h1 {
            color: #152033;
            font-size: 28px;
            font-weight: 900;
        }

        .welcome-card p {
            color: #667085;
        }

        .welcome-card .btn {
            border-radius: 10px;
            font-weight: 800;
        }
    </style>
</head>
<body>
    <main class="welcome-card">
        <img src="{{ asset('assets/images/cms-rc-logo1.png') }}" alt="CMS-RC">
        <h1>CMS-RC</h1>
        <p class="mb-4">Clinic Management System for Rural Clinics</p>

        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-gradient-primary">Open Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-gradient-primary">Sign In</a>
        @endauth
    </main>
</body>
</html>
