@extends('layouts.app')

@section('content')

<div class="row">

    <!-- Patient Summary -->
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">

                <h5>{{ $appointment->patient->name }}</h5>
                <p>Phone: {{ $appointment->patient->phone }}</p>

                <hr>

                <h6>Previous Visits</h6>
                @foreach($history as $h)
                    <small>
                        {{ $h->created_at->format('d M Y') }} <br>
                        {{ $h->diagnosis }}
                        <hr>
                    </small>
                @endforeach

            </div>
        </div>
    </div>

    <!-- Consultation Form -->
    <div class="col-md-9">

        <form method="POST" action="{{ route('consultations.store') }}">
            @csrf

            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    Consultation Entry
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-2">
                            <label>Temp</label>
                            <input name="temp" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>BP</label>
                            <input name="bp" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Pulse</label>
                            <input name="pulse" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Weight</label>
                            <input name="weight" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>SPO2</label>
                            <input name="spo2" class="form-control">
                        </div>
                    </div>

                    <hr>

                    <label>Symptoms</label>
                    <input name="symptoms[]" class="form-control mb-2">

                    <label>Clinical Notes</label>
                    <textarea name="clinical_notes" class="form-control"></textarea>

                    <label>ICD Code</label>
                    <input name="icd_code" class="form-control">

                    <label>Diagnosis *</label>
                    <textarea name="diagnosis" class="form-control" required></textarea>

                    <label>Treatment Plan</label>
                    <textarea name="treatment_plan" class="form-control"></textarea>

                    <label>Next Visit</label>
                    <input type="date" name="next_visit" class="form-control">

                </div>

                <div class="card-footer text-end">
                    <button class="btn btn-success">
                        Save Consultation
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>

@endsection
