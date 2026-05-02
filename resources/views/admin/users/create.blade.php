@extends('layouts.app')

@section('title', 'Create User')

@section('page-actions')
    <a href="{{ route('users.index') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')

@php
    $designations = ['Doctor', 'Nurse', 'Receptionist', 'Mid wife', 'PHI', 'Pharmacist'];
@endphp

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-1">User Information</h4>
        <p class="text-muted mb-4">Create a staff account and assign a role.</p>

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="fname" value="{{ old('fname') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="lname" value="{{ old('lname') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">NIC <span class="text-danger">*</span></label>
                    <input type="text" name="nic" value="{{ old('nic') }}" class="form-control" required>
                    <small class="text-muted">NIC is used as the initial password.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                    <select name="designation" class="form-select" required>
                        <option value="">Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation }}" @selected(old('designation') === $designation)>{{ $designation }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Unit <span class="text-danger">*</span></label>
                    <select name="unit_id" class="form-select" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="">Select Status</option>
                        <option value="1" @selected(old('status') === '1')>Active</option>
                        <option value="0" @selected(old('status') === '0')>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-gradient-primary">
                    <i class="mdi mdi-content-save me-1"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
