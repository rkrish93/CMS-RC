@extends('layouts.app')

@section('title', 'Today Queue')

@section('page-actions')
@can('appointments-view')
    @unless(auth()->user()?->hasAnyRole(['Nurse', 'Mid wife', 'Midwife', 'Doctor']))
        <a href="{{ route('appointments.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-1"></i> Appointments
        </a>
    @endunless
@endcan
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
$showActionColumn = $queueUser?->hasAnyRole(['Receptionist', 'Admin']);
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
                    $isPharmacyDispensed = in_array($appt->consultation->pharmacy_status ?? null, ['dispensed', 'partial'])
                        || $appt->status === App\Enums\AppointmentStatus::COMPLETED->value;
                    $status = App\Enums\AppointmentStatus::fromValue($appt->status) ?? App\Enums\AppointmentStatus::SCHEDULED;
                    $disabled = in_array($status->value, [App\Enums\AppointmentStatus::CANCELLED->value, App\Enums\AppointmentStatus::NO_SHOW->value]);
                    $actionLabel = 'Open';
                    $statusClass = $isPharmacyDispensed ? 'success' : $status->getBadgeColor();
                    $statusLabel = $isPharmacyDispensed ? 'Completed' : $status->getLabel();
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
                                <a href="{{ $signedScanUrls[$appt->id] ?? route('patient.flow.scan-patient', ['patient' => $appt->patient_id]) }}"
                                    class="btn btn-sm btn-gradient-primary {{ $disabled ? 'disabled' : '' }}"
                                    @if($disabled) aria-disabled="true" tabindex="-1" @endif>
                                    {{ $actionLabel }}
                                </a>
                                @endif

                                 @if($canGenerateQr || $canMarkNoShow)
                                    @if($status->value === App\Enums\AppointmentStatus::SCHEDULED->value)
                                    <form method="POST" action="{{ route('appointments.check-in', $appt->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            Check-in
                                        </button>
                                    </form>
                                    @endif

                                    @if(!$disabled && !$isPharmacyDispensed)
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModalToday{{ $appt->id }}">
                                        Cancel
                                    </button>

                                    <!-- Cancel Confirmation Modal -->
                                    <div class="modal fade text-start" id="cancelModalToday{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                        <i class="mdi mdi-alert-circle-outline text-danger fs-4"></i> Cancel Appointment
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="mb-1 text-dark">Are you sure you want to cancel this appointment for <strong>{{ trim((optional($appt->patient)->first_name ?? '') . ' ' . (optional($appt->patient)->last_name ?? '')) ?: 'this patient' }}</strong>?</p>
                                                    <small class="text-muted d-block">Token No: #{{ $appt->token_no }} &bull; Time: {{ $appt->appointment_time }}</small>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">No, Keep It</button>
                                                    <form method="POST" action="{{ route('appointments.cancel', $appt->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger px-3">
                                                            <i class="mdi mdi-close-circle me-1"></i> Yes, Cancel
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
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
