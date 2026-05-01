@extends('layouts.app')

@section('content')

<div class="container-fluid">

<!-- ================= HEADER ================= -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

    <h4 class="fw-bold mb-0">
        <i class="mdi mdi-stethoscope text-primary"></i>
        Patient Consultation
    </h4>

    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back
    </a>

</div>

<!-- ================= MAIN CARD ================= -->
<div class="card border-0 shadow-sm rounded-4">
<div class="card-body p-4">

<form method="POST" action="{{ route('consultations.store') }}">
@csrf

<input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

<div class="row g-4">

<!-- ================= LEFT SIDE ================= -->
<div class="col-lg-4">

    <!-- Patient Info -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">

        <h6 class="fw-bold text-primary mb-3">
            <i class="mdi mdi-account"></i> Patient Details
        </h6>

        <p class="mb-1"><strong>Name:</strong>
            {{ $appointment->patient->first_name }}
            {{ $appointment->patient->last_name }}
        </p>

        <p class="mb-1"><strong>Age:</strong>
            {{ $appointment->patient->age ?? '-' }}
        </p>

        <p class="mb-1"><strong>Gender:</strong>
            {{ $appointment->patient->gender ?? '-' }}
        </p>

        <p class="mb-0"><strong>Contact:</strong>
            {{ $appointment->patient->phone ?? '-' }}
        </p>

        <!-- BUTTON -->
        <div class="mt-3 text-end">
            <a href="{{ route('patients.show', $appointment->patient->id) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-eye"></i> View Full Details
            </a>
        </div>

    </div>
</div>

    <!-- Appointment Info -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">

            <h6 class="fw-bold text-success mb-3">
                <i class="mdi mdi-calendar"></i> Appointment Info
            </h6>

            <p class="mb-1"><strong>Date:</strong>
                {{ $appointment->appointment_date }}
            </p>

            <p class="mb-1"><strong>Time:</strong>
                {{ $appointment->appointment_time }}
            </p>

            <p class="mb-0"><strong>Token:</strong>
                {{ $appointment->token_no }}
            </p>

        </div>
    </div>

</div>

<!-- ================= RIGHT SIDE ================= -->
<div class="col-lg-8">

    <!-- Vitals -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">

            <h6 class="fw-bold text-danger mb-3">
                <i class="mdi mdi-heart-pulse"></i> Vitals
            </h6>

            <div class="row g-3">

                <div class="col-md-3">
                    <label>Blood Pressure</label>
                    <input type="text" name="bp" class="form-control" placeholder="120/80">
                </div>

                <div class="col-md-3">
                    <label>Temperature (°C)</label>
                    <input type="text" name="temp" class="form-control" placeholder="37.0">
                </div>

                <div class="col-md-3">
                    <label>Diabetes (mg/dL)</label>
                    <input type="text" name="sugar" class="form-control" placeholder="100">
                </div>

                <div class="col-md-3">
                    <label>Pulse</label>
                    <input type="text" name="pulse" class="form-control" placeholder="72">
                </div>

            </div>

        </div>
    </div>

    <!-- Diagnosis -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">

            <h6 class="fw-bold text-warning mb-3">
                <i class="mdi mdi-clipboard-text"></i> Diagnosis
            </h6>

            <textarea name="diagnosis"
                      rows="3"
                      class="form-control"
                      placeholder="Enter diagnosis..."></textarea>

        </div>
    </div>

    <!-- Prescription -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">

            <h6 class="fw-bold text-info mb-3">
                <i class="mdi mdi-pill"></i> Prescription
            </h6>

            <textarea name="prescription"
                      rows="3"
                      class="form-control"
                      placeholder="Medicine / dosage / instructions"></textarea>

        </div>
    </div>

    <!-- Notes -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">

            <h6 class="fw-bold text-secondary mb-3">
                <i class="mdi mdi-note-text"></i> Additional Notes
            </h6>

            <textarea name="notes"
                      rows="2"
                      class="form-control"></textarea>

        </div>
    </div>

</div>

</div>

<!-- ================= NEXT VISIT ================= -->
<div class="card shadow-sm border-0 rounded-4 mb-3">
<div class="card-body">

<h6 class="fw-bold text-success mb-3">
    <i class="mdi mdi-calendar-clock"></i> Next Visit
</h6>

<div class="row g-2">

    <div class="col-md-4">
        <label class="form-label">Next Visit Date</label>
        <input type="date"
               id="next_visit_date"
               name="next_visit"
               class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Remarks</label>
        <input type="text"
               name="note"
               class="form-control"
               placeholder="Follow-up reason">
    </div>

</div>

</div>
</div>

<!-- ================= FOOTER ================= -->
<div class="text-end mt-4">

    <button class="btn btn-success px-4 shadow-sm">
        <i class="mdi mdi-check"></i> Save Consultation
    </button>

</div>

</form>

</div>
</div>

</div>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
let bookedDates = @json($bookedDates ?? []);

flatpickr("#next_visit_date", {
    minDate: "today",

    disable: [
        function(date) {
            return date.getDay() === 0; // Sunday
        },
        ...bookedDates   // ⭐ spread operator
    ]
});
</script>

@endsection

<style>

body{
    background:#f4f6f9;
}

.card{
    border-radius:14px;
}

</style>
