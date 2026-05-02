

@extends('layouts.app')

@php
$hideSidebar = true;
@endphp

@section('title','Change Password')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-lock-reset"></i>
        </span>
        Change Password
    </h3>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Update Your Password</h4>
                <p class="card-description">
                    For security reasons, please change your password.
                </p>

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('update.password') }}">
                    @csrf

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter new password"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Confirm password"
                               required>
                    </div>

                    <div class="d-flex justify-content-between mt-3">

                        <button type="submit"
                                class="btn btn-gradient-primary">
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


<style>
.form-control {
    height: 45px;
    border-radius: 6px;
}

label {
    margin-bottom: 5px;
    margin-top: 10px;
}
.w-100 {
    width: 100% !important;
}
</style>
