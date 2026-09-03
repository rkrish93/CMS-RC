@extends('layouts.app')

@section('title', 'Patient Consultation Summary')

@section('page-actions')
<a href="{{ route('appointments.today') }}" class="btn btn-light">
    <i class="mdi mdi-arrow-left me-1"></i> Queue
</a>
@endsection

@section('content')
@php
    $vitalsDone = $previousVitals->where('appointment_id', $appointment->id)->isNotEmpty();
    $doctorDone = !is_null($consultationForPharmacy) || (string) $appointment->status === App\Enums\AppointmentStatus::CONSULTATION_COMPLETED->value || !is_null($appointment->consultation);
    $pharmacyDispensed = in_array($consultationForPharmacy->pharmacy_status ?? null, ['dispensed', 'partial'])
        || (bool) ($consultationForPharmacy->is_locked ?? false)
        || $appointment->status === App\Enums\AppointmentStatus::COMPLETED->value;
    $pharmacyReady = !is_null($consultationForPharmacy);
    $enablePharmacy = $pharmacyReady;
@endphp
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="mb-1">Patient Details</h4>
                        <p class="text-muted mb-0">Use this page after scanning the patient QR.</p>
                    </div>
                    <span class="badge bg-dark">Token #{{ $appointment->token_no ?? '-' }}</span>
                </div>

                <div class="row mt-3 g-3">
                    <div class="col-md-4"><strong>Patient:</strong> {{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: 'N/A' }}</div>
                    <div class="col-md-4"><strong>Patient Code:</strong> {{ optional($appointment->patient)->patient_code ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Unit:</strong> {{ optional($appointment->unit)->unit_name ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Date:</strong> {{ $appointment->appointment_date }}</div>
                    <div class="col-md-4"><strong>Time:</strong> {{ $appointment->appointment_time }}</div>
                    <div class="col-md-4"><strong>Status:</strong> {{ (App\Enums\AppointmentStatus::fromValue($appointment->status) ?? App\Enums\AppointmentStatus::SCHEDULED)->getLabel() }}</div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top">
                    @if(!auth()->user()?->hasRole('Doctor') && (auth()->user()?->can('vitals-create') || auth()->user()?->hasAnyRole(['Nurse', 'Mid wife', 'Midwife', 'Admin'])))
                        <a href="{{ route('consultations.create', ['appointment' => $appointment->id, 'screen' => 'nurse']) }}"
                           class="btn btn-info {{ $vitalsDone ? 'disabled' : '' }}"
                           @if($vitalsDone) aria-disabled="true" tabindex="-1" @endif>
                            <i class="mdi mdi-heart-pulse me-1"></i> Open Nurse / Midwife
                        </a>
                        <span class="badge {{ $vitalsDone ? 'bg-success' : 'bg-warning text-dark' }}">
                            Nurse Status: {{ $vitalsDone ? 'Vitals Done' : 'Vitals Pending' }}
                        </span>
                    @endif

                    @if(auth()->user()?->can('consultations-create') || auth()->user()?->hasAnyRole(['Doctor', 'Admin']))
                        <a href="{{ route('consultations.create', ['appointment' => $appointment->id, 'screen' => 'doctor']) }}" class="btn btn-primary {{ (!$vitalsDone || $doctorDone) ? 'disabled' : '' }}"
                           @if(!$vitalsDone || $doctorDone) aria-disabled="true" tabindex="-1" @endif>
                            <i class="mdi mdi-stethoscope me-1"></i> Consultation Start
                        </a>
                        <span class="badge {{ $doctorDone ? 'bg-success' : 'bg-secondary' }}">
                            Doctor Status: {{ $doctorDone ? 'Consultation Done' : 'Consultation Pending' }}
                        </span>
                    @endif

                    @can('pharmacy-prescriptions-view')
                        <a href="{{ $enablePharmacy ? route('pharmacy.prescriptions.index', ['consultation_id' => $consultationForPharmacy->id]) : '#' }}"
                           class="btn {{ $enablePharmacy ? 'btn-success' : 'btn-outline-secondary disabled' }}"
                           @if(!$enablePharmacy) aria-disabled="true" tabindex="-1" @endif>
                            <i class="mdi mdi-pill me-1"></i> Open Pharmacy Screen
                        </a>
                        <span class="badge {{ $pharmacyDispensed ? 'bg-success' : ($enablePharmacy ? 'bg-info text-white' : 'bg-secondary') }}">
                            Pharmacy Status: {{ $pharmacyDispensed ? 'Dispensed' : ($enablePharmacy ? 'Prescription Ready' : 'Waiting for Doctor Prescription') }}
                        </span>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="mdi mdi-heart-pulse text-danger me-1"></i> Previous Vitals</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>BP</th>
                                <th>Temp</th>
                                <th>Sugar</th>
                                <th>Pulse</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($previousVitals as $vital)
                            <tr>
                                <td><span class="fw-semibold text-dark">{{ $vital->created_at?->format('d M Y H:i') }}</span></td>
                                <td>
                                    @if($vital->bp)
                                        <span class="fw-semibold text-dark">{{ $vital->bp }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($vital->temp)
                                        <span class="fw-semibold text-dark">{{ $vital->temp }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($vital->sugar)
                                        <span class="fw-semibold text-dark">{{ $vital->sugar }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($vital->pulse)
                                        <span class="fw-semibold text-dark">{{ $vital->pulse }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No previous vitals found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="mdi mdi-history text-primary me-1"></i> Recent Consultation History</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Diagnosis</th>
                                <th>Prescription</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oldConsultations as $consultation)
                            <tr>
                                <td><span class="fw-semibold text-dark">{{ $consultation->created_at?->format('d M Y H:i') }}</span></td>
                                <td><span class="fw-semibold text-dark">{{ $consultation->diagnosis ?: '-' }}</span></td>
                                <td>
                                    @if(!empty($consultation->prescription_items))
                                        <ul class="mb-0 ps-3 small">
                                            @foreach($consultation->prescription_items as $pItem)
                                                <li><strong>{{ $pItem['medicine_name'] ?? 'Medicine' }}</strong> ({{ $pItem['dosage'] ?? '-' }})</li>
                                            @endforeach
                                        </ul>
                                    @elseif(!empty($consultation->prescription))
                                        <small class="text-dark">{{ $consultation->prescription }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No consultation history found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
