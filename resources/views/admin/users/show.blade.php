@extends('layouts.app')

@section('title', 'User Details')

@section('content')

@php
    $roleName = $user->getRoleNames()->first() ?: 'N/A';
    $unitName = $user->unit->unit_name ?? 'N/A';
    $profileImage = $user->image ? asset('assets/images/profiles/' . $user->image) : null;
    $joinedDate = $user->join_date ? \Illuminate\Support\Carbon::parse($user->join_date)->format('d M Y') : 'N/A';
@endphp

<div class="d-flex justify-content-end gap-2 mb-3">
    @can('users-view')
        <a href="{{ route('users.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-1"></i> Back
        </a>
    @endcan

    @can('users-edit')
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-gradient-info">
            <i class="mdi mdi-account-edit me-1"></i> Edit User
        </a>
    @endcan
</div>

<div class="row">
    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                @if($profileImage)
                    <img src="{{ $profileImage }}"
                         alt="{{ $user->name ?: 'User profile' }}"
                         class="rounded-circle mb-3"
                         style="width:120px; height:120px; object-fit:cover;">
                @else
                    <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3"
                         style="width:120px; height:120px;">
                        <i class="mdi mdi-account-circle text-muted" style="font-size:72px"></i>
                    </div>
                @endif

                <h4 class="card-title mb-1">{{ $user->name ?: 'N/A' }}</h4>
                <p class="text-muted mb-1">{{ $user->designation ?: 'N/A' }}</p>
                <p class="text-muted mb-0">{{ $unitName }}</p>

                <div class="mt-4">
                    <span class="badge bg-primary">{{ $roleName }}</span>
                    <span class="badge bg-{{ $user->status == 1 ? 'success' : 'danger' }} ms-1">
                        {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Profile Information</h4>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">First Name</label>
                            <div class="fw-semibold">{{ $user->fname ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Last Name</label>
                            <div class="fw-semibold">{{ $user->lname ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Email</label>
                            <div class="fw-semibold">{{ $user->email ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Phone</label>
                            <div class="fw-semibold">{{ $user->phone ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">NIC</label>
                            <div class="fw-semibold">{{ $user->nic ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Joining Date</label>
                            <div class="fw-semibold">{{ $joinedDate }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Designation</label>
                            <div class="fw-semibold">{{ $user->designation ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Unit</label>
                            <div class="fw-semibold">{{ $unitName }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Role</label>
                            <div class="fw-semibold">{{ $roleName }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">Status</label>
                            <div class="fw-semibold">{{ $user->status == 1 ? 'Active' : 'Inactive' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
