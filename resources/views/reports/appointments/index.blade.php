@extends('layouts.app')

@section('title', 'Appointment Reports')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
    <a href="{{ route('reports.appointments.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary">
        <i class="mdi mdi-printer me-1"></i> Print
    </a>
    <a href="{{ route('reports.appointments.pdf', request()->query()) }}" class="btn btn-outline-success">
        <i class="mdi mdi-file-pdf-box me-1"></i> PDF
    </a>
    <a href="{{ route('reports.appointments.csv', request()->query()) }}" class="btn btn-outline-info">
        <i class="mdi mdi-file-delimited me-1"></i> CSV
    </a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Patient</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Code, name, NIC, phone">
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
                <label class="form-label">Doctor</label>
                <select name="doctor_id" class="form-select">
                    <option value="">All</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected($doctorId === (int) $doctor->id)>{{ trim(($doctor->fname ?? '') . ' ' . ($doctor->lname ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="scheduled" @selected($status === 'scheduled')>Scheduled</option>
                    <option value="checked_in" @selected($status === 'checked_in')>Checked In</option>
                    <option value="triage_in_progress" @selected($status === 'triage_in_progress')>Triage In Progress</option>
                    <option value="triage_completed" @selected($status === 'triage_completed')>Triage Completed</option>
                    <option value="consultation_in_progress" @selected($status === 'consultation_in_progress')>Consultation In Progress</option>
                    <option value="consultation_completed" @selected($status === 'consultation_completed')>Consultation Completed</option>
                    <option value="dispensing" @selected($status === 'dispensing')>Dispensing</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                    <option value="no_show" @selected($status === 'no_show')>No Show</option>
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
                <label class="form-label">Token From</label>
                <input type="number" min="1" name="token_from" class="form-control" value="{{ $tokenFrom }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">Token To</label>
                <input type="number" min="1" name="token_to" class="form-control" value="{{ $tokenTo }}">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary">Filter</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('reports.appointments.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Date</th>
                        <th>Time</th>
                        <th>Token</th>
                        <th>Patient</th>
                        <th>Patient Code</th>
                        <th>Phone</th>
                        <th>Unit</th>
                        <th>Doctor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ optional($appointment->appointment_date)->format('Y-m-d') ?? $appointment->appointment_date }}</td>
                            <td>{{ $appointment->appointment_time ?? '-' }}</td>
                            <td>{{ $appointment->token_no ?? '-' }}</td>
                            <td>{{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: '-' }}</td>
                            <td>{{ optional($appointment->patient)->patient_code ?? '-' }}</td>
                            <td>{{ optional($appointment->patient)->phone ?? '-' }}</td>
                            <td>{{ optional($appointment->unit)->unit_name ?? '-' }}</td>
                            <td>{{ trim((optional(optional($appointment->consultation)->doctor)->fname ?? '') . ' ' . (optional(optional($appointment->consultation)->doctor)->lname ?? '')) ?: '-' }}</td>
                            <td>{{ (App\Enums\AppointmentStatus::fromValue($appointment->status) ?? App\Enums\AppointmentStatus::SCHEDULED)->getLabel() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No appointment report data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $appointments->links() }}
    </div>
</div>
@endsection
