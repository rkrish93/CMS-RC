@extends('layouts.app')

@section('title', 'Patient QR Card')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Back to Profile
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4" id="patientQrCard">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1">Reusable Patient QR Card</h4>
                        <p class="text-muted mb-0">Use this same QR across reception, nurse, doctor, and pharmacy workflows.</p>
                    </div>
                    <span class="badge bg-primary">{{ $patient->patient_code }}</span>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-md-7">
                        <div class="mb-2"><strong>Patient:</strong> {{ trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) ?: 'N/A' }}</div>
                        <div class="mb-2"><strong>Code:</strong> {{ $patient->patient_code ?? 'N/A' }}</div>
                        <div class="mb-2"><strong>NIC:</strong> {{ $patient->nic ?? 'N/A' }}</div>
                        <div class="mb-0"><strong>Phone:</strong> {{ $patient->phone ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-5 text-center">
                        <img src="{{ $qrImageUrl }}" alt="Patient reusable QR" class="img-fluid border rounded p-2 bg-white" style="max-width: 280px;">
                    </div>
                </div>

                <div class="alert alert-light border mt-4 mb-0">
                    <strong>System QR Link:</strong>
                    <div class="small text-break">{{ $systemQrUrl }}</div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-secondary">Close</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="mdi mdi-printer me-1"></i> Print QR Card
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }

    #patientQrCard,
    #patientQrCard * {
        visibility: visible;
    }

    #patientQrCard {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endpush
