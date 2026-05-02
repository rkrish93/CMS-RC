@extends('layouts.app')

@section('title', 'Appointments')

@section('page-actions')
    @can('appointments-create')
        <button class="btn btn-gradient-primary shadow-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#appointmentModal">
            <i class="mdi mdi-calendar-plus me-1"></i> Add Appointment
        </button>
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
                                    $statusClass = match($app->status) {
                                        'pending' => 'warning',
                                        'in_progress', 'in_Progress' => 'primary',
                                        'completed' => 'success',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $app->status ?? 'pending')) }}
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

        <div class="mt-3">
            {{ $appointments->links() }}
        </div>
    </div>
</div>

@can('appointments-create')
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
@endcan

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
</style>
@endpush
