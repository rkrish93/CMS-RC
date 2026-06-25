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
$disableOpenAfterVitals = $queueUser?->hasAnyRole(['Nurse', 'Mid wife']);
$columnCount = $canOpenAppointment ? 6 : 5;
if ($hideUnitColumn) {
$columnCount--;
}
@endphp

<div class="card">
    <div class="card-body">
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
                        @if($canOpenAppointment)
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
                    'in_progress', 'in_Progress' => 'primary',
                    'nurse_done' => 'dark',
                    'completed' => 'success',
                    default => 'secondary',
                    };
                    $statusLabel = match($appt->status) {
                    'pending' => 'Waiting',
                    'nurse_done' => 'Nurse Done',
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
                        @if($canOpenAppointment)
                        <td class="text-end">
                            <a href="{{ route('consultations.create', $appt->id) }}"
                                class="btn btn-sm btn-gradient-primary {{ $disabled ? 'disabled' : '' }}"
                                @if($disabled) aria-disabled="true" tabindex="-1" @endif>
                                {{ $actionLabel }}
                            </a>
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