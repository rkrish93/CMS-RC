@extends('layouts.app')

@section('title', 'Today Queue')

@section('page-actions')
<a href="{{ route('appointments.index') }}" class="btn btn-light">
    <i class="mdi mdi-arrow-left me-1"></i> Appointments
</a>
@endsection

@section('content')

@php
$queueUser = auth()->user();
$hideUnitColumn = $queueUser?->hasAnyRole(['Doctor', 'Nurse', 'Mid wife', 'Midwife']);
$canOpenAppointment = $queueUser?->can('consultations-create')
|| $queueUser?->can('vitals-create')
|| $queueUser?->hasAnyRole(['Doctor', 'Admin']);
$canGenerateQr = $queueUser?->hasAnyRole(['Receptionist', 'Admin']);
$canMarkNoShow = $queueUser?->hasAnyRole(['Receptionist', 'Admin']);
$showActionColumn = $canOpenAppointment || $canGenerateQr;
$disableOpenAfterVitals = $queueUser?->hasAnyRole(['Nurse', 'Mid wife']);
$columnCount = $showActionColumn ? 6 : 5;
if ($hideUnitColumn) {
$columnCount--;
}
@endphp

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">Today's Queue</h4>
                <p class="text-muted mb-0">Auto refreshes every 15 seconds.</p>
            </div>
            <span class="badge bg-primary">{{ $appointments->count() }} Visits</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle queue-table">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Patient</th>
                        @unless($hideUnitColumn)
                        <th>Unit</th>
                        @endunless
                        <th>Time</th>
                        <th>Status</th>
                        @if($showActionColumn)
                        <th width="120" class="text-end">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                    @php
                    $vitalsRecorded = ($appt->vitals_count ?? 0) > 0;
                    $disabled = in_array($appt->status, ['completed', 'cancelled'])
                    || ($disableOpenAfterVitals && $vitalsRecorded);
                    $actionLabel = $disableOpenAfterVitals && $vitalsRecorded ? 'Vitals Done' : 'Open';
                    $statusClass = match($appt->status) {
                    'pending' => 'warning',
                    'checked_in' => 'info',
                    'in_progress', 'in_Progress' => 'primary',
                    'nurse_done' => 'dark',
                    'completed' => 'success',
                    'no_show' => 'secondary',
                    default => 'secondary',
                    };
                    $statusLabel = match($appt->status) {
                    'pending' => 'Waiting',
                    'checked_in' => 'Checked In',
                    'nurse_done' => 'Nurse Done',
                    'no_show' => 'No Show',
                    default => ucfirst(str_replace('_', ' ', $appt->status ?? 'pending')),
                    };
                    @endphp
                    <tr>
                        <td><span class="token-pill">{{ $appt->token_no ?? 'N/A' }}</span></td>
                        <td class="fw-semibold">{{ trim((optional($appt->patient)->first_name ?? '') . ' ' . (optional($appt->patient)->last_name ?? '')) ?: 'No Patient' }}</td>
                        @unless($hideUnitColumn)
                        <td>{{ $appt->unit->unit_name ?? 'N/A' }}</td>
                        @endunless
                        <td>{{ $appt->appointment_time ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        @if($showActionColumn)
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                @if($canOpenAppointment)
                                <a href="{{ route('consultations.create', $appt->id) }}"
                                    class="btn btn-sm btn-gradient-primary {{ $disabled ? 'disabled' : '' }}"
                                    @if($disabled) aria-disabled="true" tabindex="-1" @endif>
                                    {{ $actionLabel }}
                                </a>
                                @endif

                                @if($canGenerateQr)
                                    @if($appt->status === 'pending')
                                    <form method="POST" action="{{ route('appointments.check-in', $appt->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            Check-in
                                        </button>
                                    </form>
                                    @endif

                                    @if($appt->patient_id)
                                    <a href="{{ route('patients.qr-card', $appt->patient_id) }}" class="btn btn-sm btn-outline-dark">
                                        Patient QR
                                    </a>
                                    @endif
                                @endif

                                @if($canMarkNoShow && $appt->status === 'pending')
                                <form method="POST" action="{{ route('appointments.no-show', $appt->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Mark this appointment as no-show?')">
                                        No Show
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif

                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $columnCount }}" class="text-center text-muted py-5">No appointments in today's queue.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    setInterval(() => location.reload(), 15000);
</script>
@endsection

@push('styles')
<style>
    .queue-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #475467;
    }

    .token-pill {
        display: inline-flex;
        min-width: 42px;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: #152033;
        color: #ffffff;
        font-weight: 900;
    }
</style>
@endpush