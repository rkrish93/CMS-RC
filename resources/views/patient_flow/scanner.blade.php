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
