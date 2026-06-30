@extends('layouts.app')

@section('title', 'Patient QR Pass')

@section('page-actions')
<a href="{{ route('appointments.today') }}" class="btn btn-light">
    <i class="mdi mdi-arrow-left me-1"></i> Queue
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4" id="qrPassCard">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h4 class="mb-1">CMS-RC Patient QR Pass</h4>
                        <p class="text-muted mb-0">Reception gives this QR to the patient. Nurse, doctor, and pharmacy can scan the same QR.</p>
                    </div>
                    <span class="badge bg-primary">Token #{{ $appointment->token_no ?? '-' }}</span>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-md-7">
                        <div class="mb-2"><strong>Patient:</strong> {{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: 'N/A' }}</div>
                        <div class="mb-2"><strong>Patient Code:</strong> {{ optional($appointment->patient)->patient_code ?? 'N/A' }}</div>
                        <div class="mb-2"><strong>Unit:</strong> {{ optional($appointment->unit)->unit_name ?? 'N/A' }}</div>
                        <div class="mb-2"><strong>Date:</strong> {{ $appointment->appointment_date }}</div>
                        <div class="mb-2"><strong>Time:</strong> {{ $appointment->appointment_time }}</div>
                        <div class="mb-0"><strong>Token:</strong> {{ $appointment->token_no ?? 'N/A' }}</div>
                    </div>

                    <div class="col-md-5 text-center">
                        <img src="{{ $qrImageUrl }}" alt="Patient QR" class="img-fluid border rounded p-2 bg-white" style="max-width: 280px;">
                    </div>
                </div>

                <div class="alert alert-light border mt-4 mb-0">
                    <strong>QR Link:</strong>
                    <div class="small text-break">{{ $scanUrl }}</div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('appointments.today') }}" class="btn btn-outline-secondary">Close</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="mdi mdi-printer me-1"></i> Print QR Pass
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

    #qrPassCard,
    #qrPassCard * {
        visibility: visible;
    }

    #qrPassCard {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endpush
