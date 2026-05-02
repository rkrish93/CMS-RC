@extends('layouts.app')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-account-circle"></i>
            </span>
            User Details
        </h3>
        <p class="text-muted mb-0">View detailed information for this user.</p>
    </div>
    <div>
        <a href="{{ route('users.index') }}" class="btn btn-light">Back to List</a>
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-gradient-info">Edit User</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                @if($user->image)
                    <img src="{{ asset('assets/images/profiles/'.$user->image) }}"
                         alt="{{ $user->name }}"
                         class="rounded-circle mb-3"
                         style="width:120px; height:120px; object-fit:cover;">
                @else
                    <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3"
                         style="width:120px; height:120px;">
                        <i class="mdi mdi-account-circle text-muted" style="font-size:72px"></i>
                    </div>
                @endif

                <h4 class="card-title">{{ $user->name }}</h4>
                <p class="text-muted mb-1">{{ $user->designation }}</p>
                <p class="text-muted">{{ $user->unit->unit_name ?? 'No unit assigned' }}</p>

                <div class="mt-4">
                    <span class="badge bg-primary">{{ ucfirst($user->getRoleNames()->first() ?? 'No role') }}</span>
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
                            <label class="form-label">First Name</label>
                            <div class="form-control-plaintext">{{ $user->fname }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <div class="form-control-plaintext">{{ $user->lname }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="form-control-plaintext">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <div class="form-control-plaintext">{{ $user->phone }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">NIC</label>
                            <div class="form-control-plaintext">{{ $user->nic }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Joining Date</label>
                            <div class="form-control-plaintext">{{ $user->join_date }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Designation</label>
                            <div class="form-control-plaintext">{{ $user->designation }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Unit</label>
                            <div class="form-control-plaintext">{{ $user->unit->unit_name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <div class="form-control-plaintext">{{ ucfirst($user->getRoleNames()->first() ?? '—') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-control-plaintext">
                                {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
