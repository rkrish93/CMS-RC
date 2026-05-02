@extends('layouts.app')

@php
    $hideSidebar = true;
@endphp

@section('title', 'Change Password')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <span class="password-icon">
                        <i class="mdi mdi-lock-reset"></i>
                    </span>
                    <h4 class="card-title mt-3 mb-1">Update Your Password</h4>
                    <p class="text-muted mb-0">For security reasons, please choose a new password.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('update.password') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter new password"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Confirm password"
                               required>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <button type="submit" class="btn btn-gradient-primary">
                            Update Password
                        </button>

                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="btn btn-light">
                            Logout
                        </a>
                    </div>
                </form>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .password-icon {
        width: 64px;
        height: 64px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 34px;
    }
</style>
@endpush
