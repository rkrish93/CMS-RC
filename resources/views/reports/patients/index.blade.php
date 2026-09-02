@extends('layouts.app')

@section('title', 'Patient Reports')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Code, name, NIC, phone">
            </div>
            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">All</option>
                    <option value="Male" @selected($gender === 'Male')>Male</option>
                    <option value="Female" @selected($gender === 'Female')>Female</option>
                    <option value="Other" @selected($gender === 'Other')>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary">Filter</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('reports.patients.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Patient Code</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>NIC</th>
                        <th>Appointments</th>
                        <th>Consultations</th>
                        <th>Registered Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td>{{ $patient->patient_code }}</td>
                            <td>{{ trim($patient->first_name . ' ' . $patient->last_name) }}</td>
                            <td>{{ $patient->gender ?? '-' }}</td>
                            <td>{{ $patient->phone ?? '-' }}</td>
                            <td>{{ $patient->nic ?? '-' }}</td>
                            <td>{{ $patient->appointments_count }}</td>
                            <td>{{ $patient->consultations_count }}</td>
                            <td>{{ optional($patient->created_at)->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('reports.patients.show', $patient) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                <a href="{{ route('reports.patients.print', $patient) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Print">
                                    <i class="mdi mdi-printer"></i>
                                </a>
                                <a href="{{ route('reports.patients.pdf', $patient) }}" class="btn btn-sm btn-outline-success" title="PDF">
                                    <i class="mdi mdi-file-pdf-box"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No patient report data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $patients->links() }}
    </div>
</div>
@endsection
