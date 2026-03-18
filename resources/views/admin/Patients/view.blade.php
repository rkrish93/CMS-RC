@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">
        <h4>Patient Profile</h4>

        <a href="{{ route('patients.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Patient Code</label>
                    <div>{{ $patient->patient_code }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Full Name</label>
                    <div>
                        {{ $patient->title }}
                        {{ $patient->first_name }}
                        {{ $patient->last_name }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Gender</label>
                    <div>{{ $patient->gender }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">DOB</label>
                    <div>{{ $patient->dob }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Age</label>
                    <div>{{ $patient->age }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">NIC</label>
                    <div>{{ $patient->nic }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Phone</label>
                    <div>{{ $patient->phone }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Blood Group</label>
                    <div>{{ $patient->blood_group }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Patient Type</label>
                    <div>{{ $patient->patient_type }}</div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="fw-bold">Address</label>
                    <div>{{ $patient->address }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Province</label>
                    <div>{{ $patient->province }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">District</label>
                    <div>{{ $patient->district }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">GS Division</label>
                    <div>{{ $patient->gs_division }}</div>
                </div>

            </div>

            <hr>

            <h5>Emergency Contact</h5>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Name</label>
                    <div>{{ $patient->emergency_name }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Phone</label>
                    <div>{{ $patient->emergency_phone }}</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Relationship</label>
                    <div>{{ $patient->relationship }}</div>
                </div>

            </div>

            <hr>

            <div class="mb-3">
                <label class="fw-bold">Allergies</label>
                <div>{{ $patient->allergies }}</div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Chronic Conditions</label>
                <div>{{ $patient->chronic_conditions }}</div>
            </div>

        </div>
    </div>

</div>

@endsection
