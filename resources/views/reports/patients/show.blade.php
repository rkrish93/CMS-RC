@extends('layouts.app')

@section('title', 'Patient Report')

@section('page-actions')
    @if(!($printMode ?? false))
        <a href="{{ route('reports.patients.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('reports.patients.print', $patient) }}" target="_blank" class="btn btn-outline-primary">
            <i class="mdi mdi-printer me-1"></i> Print
        </a>
        <a href="{{ route('reports.patients.pdf', $patient) }}" class="btn btn-outline-success">
            <i class="mdi mdi-file-pdf-box me-1"></i> PDF
        </a>
    @endif
@endsection

@section('content')
<div class="card mb-3 report-print-area">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h4 class="mb-1">Patient Report</h4>
                <small class="text-muted">Generated on {{ now()->format('Y-m-d H:i') }}</small>
            </div>
            <div class="text-end">
                <div><strong>Patient Code:</strong> {{ $patient->patient_code ?? '-' }}</div>
                <div><strong>Name:</strong> {{ trim($patient->first_name . ' ' . $patient->last_name) }}</div>
            </div>
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-3"><strong>Gender:</strong> {{ $patient->gender ?? '-' }}</div>
            <div class="col-md-3"><strong>DOB:</strong> {{ $patient->dob ?? '-' }}</div>
            <div class="col-md-3"><strong>Type:</strong> {{ $patient->patient_type ?? '-' }}</div>
            <div class="col-md-3"><strong>Phone:</strong> {{ $patient->phone ?? '-' }}</div>
            <div class="col-md-6"><strong>NIC:</strong> {{ $patient->nic ?? '-' }}</div>
            <div class="col-md-6"><strong>Address:</strong> {{ $patient->address ?? '-' }}</div>
        </div>

        <hr>

        <h6 class="mb-2">Recent Vitals</h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>BP</th>
                        <th>Temp</th>
                        <th>Pulse</th>
                        <th>Sugar</th>
                        <th>BMI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vitals as $vital)
                        <tr>
                            <td>{{ optional($vital->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $vital->bp ?? '-' }}</td>
                            <td>{{ $vital->temp ?? '-' }}</td>
                            <td>{{ $vital->pulse ?? '-' }}</td>
                            <td>{{ $vital->sugar ?? '-' }}</td>
                            <td>{{ $vital->bmi ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No vitals found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h6 class="mb-2">Recent Consultations</h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $consultation)
                        <tr>
                            <td>{{ optional($consultation->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($consultation->doctor)->name ?? trim((optional($consultation->doctor)->fname ?? '') . ' ' . (optional($consultation->doctor)->lname ?? '')) ?: '-' }}</td>
                            <td>{{ $consultation->diagnosis ?? '-' }}</td>
                            <td>{{ $consultation->prescription ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No consultations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h6 class="mb-2">Recent Appointments</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ optional($appointment->appointment_date)->format('Y-m-d') ?? $appointment->appointment_date }}</td>
                            <td>{{ $appointment->appointment_time ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $appointment->status ?? '-')) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .navbar,
        .sidebar,
        .footer,
        .page-header,
        .sidebar-overlay,
        .btn,
        #loader {
            display: none !important;
        }

        .main-panel,
        .content-wrapper {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .report-print-area {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@if($printMode ?? false)
    @push('scripts')
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
    @endpush
@endif
