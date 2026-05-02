@extends('layouts.app')

@section('title', 'Edit User')

@section('page-actions')
    <div class="d-flex gap-2">
        @can('users-view')
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-light">
                <i class="mdi mdi-eye me-1"></i> View
            </a>
        @endcan
        <a href="{{ route('users.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-1"></i> Back
        </a>
    </div>
@endsection

@section('content')

@php
    $designations = ['Doctor', 'Nurse', 'Receptionist', 'Mid wife', 'PHI', 'Pharmacist'];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            @if($user->image)
                <img src="{{ asset('assets/images/profiles/' . $user->image) }}"
                     alt="{{ $user->name }}"
                     class="edit-avatar">
            @else
                <span class="edit-avatar placeholder-avatar">
                    <i class="mdi mdi-account"></i>
                </span>
            @endif
            <div>
                <h4 class="card-title mb-1">{{ $user->name ?: 'User Account' }}</h4>
                <p class="text-muted mb-0">Update staff account details and role assignment.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="fname" value="{{ old('fname', $user->fname) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="lname" value="{{ old('lname', $user->lname) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">NIC <span class="text-danger">*</span></label>
                    <input type="text" name="nic" value="{{ old('nic', $user->nic) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                    <select name="designation" class="form-select" required>
                        <option value="">Select Designation</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation }}" @selected(old('designation', $user->designation) === $designation)>{{ $designation }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Unit <span class="text-danger">*</span></label>
                    <select name="unit_id" class="form-select" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $user->unit_id) == $unit->id)>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Join Date <span class="text-danger">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date', $user->join_date) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected((string) old('status', $user->status) === '1')>Active</option>
                        <option value="0" @selected((string) old('status', $user->status) === '0')>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
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
                    <i class="mdi mdi-content-save me-1"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    .edit-avatar {
        width: 64px;
        height: 64px;
        flex: 0 0 64px;
        border-radius: 50%;
        object-fit: cover;
    }

    .placeholder-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 32px;
    }
</style>
@endpush
