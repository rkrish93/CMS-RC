@extends('layouts.app')

@section('title', 'Patient Consultation')

@section('page-actions')
    <a href="{{ route('appointments.today') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Queue
    </a>
@endsection

@section('content')

<form method="POST" action="{{ route('consultations.store') }}">
    @csrf
    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card consultation-summary">
                <div class="card-body">
                    <h4 class="card-title mb-3">Patient Details</h4>

                    <div class="patient-badge mb-3">
                        <i class="mdi mdi-account-heart-outline"></i>
                    </div>

                    <h5 class="mb-1">
                        {{ $appointment->patient->first_name ?? 'N/A' }}
                        {{ $appointment->patient->last_name ?? '' }}
                    </h5>
                    <p class="text-muted mb-4">{{ $appointment->patient->patient_code ?? 'No patient code' }}</p>

                    <div class="detail-list">
                        <div>
                            <span>Age</span>
                            <strong>{{ $appointment->patient->age ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Gender</span>
                            <strong>{{ $appointment->patient->gender ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Contact</span>
                            <strong>{{ $appointment->patient->phone ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Appointment</span>
                            <strong>{{ $appointment->appointment_date }} at {{ $appointment->appointment_time }}</strong>
                        </div>
                        <div>
                            <span>Token</span>
                            <strong>{{ $appointment->token_no ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    @if($appointment->patient)
                        <a href="{{ route('patients.show', $appointment->patient->id) }}" class="btn btn-outline-primary w-100 mt-4">
                            <i class="mdi mdi-eye me-1"></i> View Full Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title mb-3">Vitals</h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="bp" class="form-control" placeholder="120/80">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Temperature (C)</label>
                            <input type="text" name="temp" class="form-control" placeholder="37.0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Diabetes</label>
                            <input type="text" name="sugar" class="form-control" placeholder="mg/dL">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pulse</label>
                            <input type="text" name="pulse" class="form-control" placeholder="72">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title mb-3">Clinical Notes</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" rows="3" class="form-control" placeholder="Enter diagnosis"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Prescription</label>
                            <textarea name="prescription" rows="3" class="form-control" placeholder="Medicine, dosage and instructions"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Next Visit</h4>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Next Visit Date</label>
                            <input type="date" id="next_visit_date" name="next_visit" class="form-control">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="note" class="form-control" placeholder="Follow-up reason">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('appointments.today') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-gradient-primary">
                    <i class="mdi mdi-check me-1"></i> Save Consultation
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const bookedDates = @json($bookedDates ?? []);

flatpickr('#next_visit_date', {
    minDate: 'today',
    disable: [
        function(date) {
            return date.getDay() === 0;
        },
        ...bookedDates
    ]
});
</script>
@endsection

@push('styles')
<style>
    .patient-badge {
        width: 76px;
        height: 76px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eff6ff;
        color: #2563eb;
        font-size: 42px;
    }

    .detail-list {
        display: grid;
        gap: 12px;
    }

    .detail-list div {
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
    }

    .detail-list span {
        display: block;
        margin-bottom: 2px;
        color: #667085;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .detail-list strong {
        color: #152033;
    }
</style>
@endpush
