@extends('layouts.app')

@section('title', 'Today Queue')

@section('page-actions')
<a href="{{ route('dashboard') }}" class="btn btn-light">
    <i class="mdi mdi-arrow-left me-1"></i> Dashboard
</a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <h4 class="card-title mb-1">Today Queue Scanner</h4>
        <p class="text-muted mb-3">Search today's queue and open the patient summary page directly.</p>

        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Search by token, patient code, patient name, or phone">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('patient.flow.scanner') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Token</th>
                        <th>Patient</th>
                        <th>Patient Code</th>
                        <th>Unit</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        @php
                            $isPharmacyDispensed = in_array($appointment->consultation->pharmacy_status ?? null, ['dispensed', 'partial'])
                                || $appointment->status === App\Enums\AppointmentStatus::COMPLETED->value;
                            $statusEnum = App\Enums\AppointmentStatus::fromValue($appointment->status) ?? App\Enums\AppointmentStatus::SCHEDULED;
                        @endphp
                        <tr>
                            <td>{{ $appointment->token_no ?? '-' }}</td>
                            <td>{{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: 'N/A' }}</td>
                            <td>{{ optional($appointment->patient)->patient_code ?? 'N/A' }}</td>
                            <td>{{ optional($appointment->unit)->unit_name ?? 'N/A' }}</td>
                            <td>{{ $appointment->appointment_time ?? 'N/A' }}</td>
                            <td>
                                @if($isPharmacyDispensed)
                                    <span class="badge bg-success">
                                        Completed
                                    </span>
                                @else
                                    <span class="badge bg-{{ $statusEnum->getBadgeColor() }}">
                                        {{ $statusEnum->getLabel() }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    <a href="{{ $signedScanUrls[$appointment->id] ?? '#' }}" class="btn btn-sm btn-outline-dark">
                                        Open
                                    </a>
                                    @if(auth()->user()?->hasAnyRole(['Receptionist', 'Admin']) || auth()->user()?->can('appointments-edit'))
                                        @if(!in_array($statusEnum->value, [App\Enums\AppointmentStatus::COMPLETED->value, App\Enums\AppointmentStatus::CANCELLED->value, App\Enums\AppointmentStatus::NO_SHOW->value]) && !$isPharmacyDispensed)
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModalScanner{{ $appointment->id }}">
                                                Cancel
                                            </button>

                                            <!-- Cancel Confirmation Modal -->
                                            <div class="modal fade text-start" id="cancelModalScanner{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow">
                                                        <div class="modal-header border-bottom-0 pb-0">
                                                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                                <i class="mdi mdi-alert-circle-outline text-danger fs-4"></i> Cancel Appointment
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body py-3">
                                                            <p class="mb-1 text-dark">Are you sure you want to cancel this appointment for <strong>{{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: 'this patient' }}</strong>?</p>
                                                            <small class="text-muted d-block">Token No: #{{ $appointment->token_no }} &bull; Time: {{ $appointment->appointment_time }}</small>
                                                        </div>
                                                        <div class="modal-footer border-top-0 pt-0">
                                                            <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">No, Keep It</button>
                                                            <form method="POST" action="{{ route('appointments.cancel', $appointment->id) }}" class="d-inline">
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No active appointments found for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
