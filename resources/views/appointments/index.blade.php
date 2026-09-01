@extends('layouts.app')

@section('title', 'Appointments')

@section('page-actions')
    @can('appointments-create')
        {{-- <button class="btn btn-gradient-primary shadow-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#appointmentModal">
            <i class="mdi mdi-calendar-plus me-1"></i> Add Appointment
        </button> --}}

        <a href="{{ route('appointments.create') }}" class="btn btn-gradient-primary shadow-sm">
            <i class="mdi mdi-account-plus me-1"></i> Add Appointment
        </a>
    @endcan

@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">Appointment Directory</h4>
                <p class="text-muted mb-0">Track patient bookings and daily tokens.</p>
            </div>
            <a href="{{ route('appointments.today') }}" class="btn btn-light">
                <i class="mdi mdi-format-list-numbered me-1"></i> Today Queue
            </a>
        </div>

        <form method="GET" action="{{ route('appointments.index') }}" class="filter-panel mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="appointment_date">Date</label>
                    <input type="date"
                           class="form-control"
                           id="appointment_date"
                           name="appointment_date"
                           value="{{ request('appointment_date') }}">
                </div>

                <div class="col-md-5 col-lg-4">
                    <label class="form-label" for="unit_id">Unit</label>
                    <select class="form-select"
                            id="unit_id"
                            name="unit_id"
                            @if(auth()->user()->hasRole('Doctor')) disabled @endif>
                        <option value="">All Units</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id || (auth()->user()->hasRole('Doctor') && auth()->user()->unit_id === $unit->id))>
                                {{ $unit->unit_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="mdi mdi-magnify me-1"></i> Search
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-light">
                        <i class="mdi mdi-refresh"></i>
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Unit</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Token</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $key => $app)
                        <tr>
                            <td class="text-muted">{{ $appointments->firstItem() + $key }}</td>
                            <td class="fw-semibold">{{ trim(($app->patient->first_name ?? '') . ' ' . ($app->patient->last_name ?? '')) ?: 'N/A' }}</td>
                            <td>{{ $app->unit->unit_name ?? 'N/A' }}</td>
                            <td>{{ $app->appointment_date ?? 'N/A' }}</td>
                            <td>{{ $app->appointment_time ?? 'N/A' }}</td>
                            <td><span class="code-pill">{{ $app->token_no ?? 'N/A' }}</span></td>
                            <td>
                                @php
                                    $isPharmacyDispensed = in_array($app->consultation->pharmacy_status ?? null, ['dispensed', 'partial'])
                                        || $app->status === App\Enums\AppointmentStatus::COMPLETED->value;
                                    $status = App\Enums\AppointmentStatus::fromValue($app->status) ?? App\Enums\AppointmentStatus::SCHEDULED;
                                    
                                    if (!$isPharmacyDispensed && in_array($status, [App\Enums\AppointmentStatus::SCHEDULED, App\Enums\AppointmentStatus::CHECKED_IN]) && !empty($app->appointment_date) && $app->appointment_date < date('Y-m-d')) {
                                        $status = App\Enums\AppointmentStatus::NO_SHOW;
                                    }

                                    $statusClass = $isPharmacyDispensed ? 'success' : $status->getBadgeColor();
                                    $statusLabel = $isPharmacyDispensed ? 'Completed' : $status->getLabel();
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

       <div class="d-flex justify-content-between align-items-center mt-3">

    <small class="text-muted">
        Showing
        {{ $appointments->firstItem() ?? 0 }}
        to
        {{ $appointments->lastItem() ?? 0 }}
        of
        {{ $appointments->total() }}
        results
    </small>

    {{ $appointments->links() }}

</div>
    </div>
</div>

{{-- @can('appointments-create')
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('appointments.store') }}">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Schedule Appointment</h5>
                        <small class="text-muted">Create a pending appointment and token.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}">
                                        {{ $patient->patient_code }} - {{ $patient->first_name }} {{ $patient->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Clinical Unit</label>
                            <select name="unit_id" class="form-select" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="appointment_date" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Time</label>
                            <input type="time" name="appointment_time" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-gradient-primary">
                        <i class="mdi mdi-check me-1"></i> Save Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan --}}

@endsection

@push('styles')
<style>
    .admin-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #475467;
    }

    .code-pill {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        padding: 5px 10px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-panel {
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
    }

    .filter-panel .form-label {
        color: #475467;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pagination {
    margin-bottom: 0 !important;
}

svg {
    width: 20px;
    height: 20px;
}
</style>
@endpush
