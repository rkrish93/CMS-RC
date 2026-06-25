@extends('layouts.app')

@section('title', 'Consultation Reports')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
    <a href="{{ route('reports.consultations.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary">
        <i class="mdi mdi-printer me-1"></i> Print
    </a>
    <a href="{{ route('reports.consultations.pdf', request()->query()) }}" class="btn btn-outline-success">
        <i class="mdi mdi-file-pdf-box me-1"></i> PDF
    </a>
    <a href="{{ route('reports.consultations.csv', request()->query()) }}" class="btn btn-outline-info">
        <i class="mdi mdi-file-delimited me-1"></i> CSV
    </a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Patient, diagnosis, prescription">
            </div>
            <div class="col-md-2">
                <label class="form-label">Doctor</label>
                <select name="doctor_id" class="form-select">
                    <option value="">All</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected($doctorId === (int) $doctor->id)>{{ trim(($doctor->fname ?? '') . ' ' . ($doctor->lname ?? '')) }}</option>
                    @endforeach
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
            <div class="col-md-2">
                <label class="form-label">Pharmacy Status</label>
                <select name="pharmacy_status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" @selected($pharmacyStatus === 'pending')>Pending</option>
                    <option value="partial" @selected($pharmacyStatus === 'partial')>Partial</option>
                    <option value="dispensed" @selected($pharmacyStatus === 'dispensed')>Dispensed</option>
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
                <a href="{{ route('reports.consultations.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Total Consultations</small>
                <h4 class="mb-0">{{ $summary['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Pending</small>
                <h4 class="mb-0">{{ $summary['pending'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Partial</small>
                <h4 class="mb-0">{{ $summary['partial'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted">Dispensed</small>
                <h4 class="mb-0">{{ $summary['dispensed'] }}</h4>
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
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Code</th>
                        <th>Doctor</th>
                        <th>Unit</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Pharmacy Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                        <tr>
                            <td>{{ optional($consultation->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ trim((optional($consultation->patient)->first_name ?? '') . ' ' . (optional($consultation->patient)->last_name ?? '')) ?: '-' }}</td>
                            <td>{{ optional($consultation->patient)->patient_code ?? '-' }}</td>
                            <td>{{ trim((optional($consultation->doctor)->fname ?? '') . ' ' . (optional($consultation->doctor)->lname ?? '')) ?: '-' }}</td>
                            <td>{{ optional(optional($consultation->appointment)->unit)->unit_name ?? '-' }}</td>
                            <td>{{ $consultation->diagnosis ?? '-' }}</td>
                            <td>{{ $consultation->prescription ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $consultation->pharmacy_status ?? '-')) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No consultation report data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $consultations->links() }}
    </div>
</div>
@endsection
