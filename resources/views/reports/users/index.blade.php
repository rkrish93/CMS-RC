@extends('layouts.app')

@section('title', 'User Reports')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
    <a href="{{ route('reports.users.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary">
        <i class="mdi mdi-printer me-1"></i> Print
    </a>
    <a href="{{ route('reports.users.pdf', request()->query()) }}" class="btn btn-outline-success">
        <i class="mdi mdi-file-pdf-box me-1"></i> PDF
    </a>
    <a href="{{ route('reports.users.csv', request()->query()) }}" class="btn btn-outline-info">
        <i class="mdi mdi-file-delimited me-1"></i> CSV
    </a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Name, email, phone, NIC">
            </div>
            <div class="col-md-2">
                <label class="form-label">Designation</label>
                <select name="designation" class="form-select">
                    <option value="">All</option>
                    @foreach($designations as $designationOption)
                        <option value="{{ $designationOption }}" @selected($designation === $designationOption)>{{ $designationOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected($status === '1')>Active</option>
                    <option value="0" @selected($status === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit</label>
                <select name="unit_id" class="form-select">
                    <option value="">All</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected($unitId === (int) $unit->id)>{{ $unit->unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Per Page</label>
                <select name="per_page" class="form-select">
                    <option value="10" @selected(($perPage ?? 10) === 10)>10</option>
                    <option value="25" @selected(($perPage ?? 10) === 25)>25</option>
                    <option value="50" @selected(($perPage ?? 10) === 50)>50</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary">Filter</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('reports.users.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Total Users</small>
                <h4 class="mb-0">{{ $summary['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Active</small>
                <h4 class="mb-0">{{ $summary['active'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Inactive</small>
                <h4 class="mb-0">{{ $summary['inactive'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Doctors</small>
                <h4 class="mb-0">{{ $summary['doctors'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>NIC</th>
                        <th>Designation</th>
                        <th>Unit</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Join Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>{{ $user->nic ?? '-' }}</td>
                            <td>{{ $user->designation ?? '-' }}</td>
                            <td>{{ $user->unit->unit_name ?? '-' }}</td>
                            <td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                            <td>{{ ucfirst($user->status ?? '-') }}</td>
                            <td>{{ $user->join_date ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No user report data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</div>
@endsection
