@extends('layouts.app')

@section('title','Create Appointment')

@section('content')

<div class="card">
    <div class="card-body">

        <h4 class="mb-3">Schedule Appointment</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('appointments.store') }}" id="appointmentForm">
            @csrf

            <div class="row g-3">

                <!-- SEARCH PATIENT -->
                <div class="col-md-6">
                    <label class="form-label">Search Patient (Phone / NIC)</label>
                    <input type="text" id="search_patient" class="form-control"
                        placeholder="Enter phone or NIC (min 3 characters)">
                </div>

                <!-- RESULT -->
                <div class="col-md-6">
                    <label class="form-label">Patient</label>
                    <select name="patient_id" id="patient_select" class="form-control" required>
                        <option value="">Select or Search Patient</option>
                    </select>
                </div>

                <!-- NEW PATIENT ALERT -->
                <div class="col-md-12" id="new_patient_box" style="display:none;">
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <span>
                            <i class="mdi mdi-alert-circle me-2"></i>
                            No matching patient found
                        </span>
                        <a href="{{ route('patients.create') }}" class="btn btn-warning btn-sm">
                            <i class="mdi mdi-plus me-1"></i> Create New Patient
                        </a>
                    </div>
                </div>

                <!-- UNIT -->
                <div class="col-md-6">
                    <label class="form-label">Clinical Unit</label>
                    <select name="unit_id" class="form-control" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->unit_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- DATE -->
                <div class="col-md-6">
                    <label class="form-label">Appointment Date</label>
                    <input type="date" name="appointment_date" class="form-control" required>
                    <small class="text-muted">Clinic Hours: 09:00 AM - 03:00 PM</small>
                </div>

                <!-- TIME INFO -->
                <div class="col-md-12">
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="mdi mdi-information me-1"></i>
                            <strong>Appointment time will be automatically assigned</strong> based on available slots (10-minute intervals)
                        </small>
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-check me-1"></i> Book Appointment
                </button>

                <a href="{{ route('appointments.index') }}" class="btn btn-light">
                    <i class="mdi mdi-close me-1"></i> Cancel
                </a>
            </div>

        </form>

    </div>
</div>

@endsection

@section('scripts')

<script>
document.getElementById('search_patient').addEventListener('keyup', function(){

    let value = this.value.trim();

    if(value.length < 3) {
        document.getElementById('patient_select').innerHTML = '<option value="">Search by phone or NIC</option>';
        document.getElementById('new_patient_box').style.display = 'none';
        return;
    }

    fetch(`/search-patient?query=${encodeURIComponent(value)}`)
    .then(res => res.json())
    .then(data => {

        let select = document.getElementById('patient_select');
        select.innerHTML = '';

        if(data.length > 0){

            document.getElementById('new_patient_box').style.display = 'none';

            data.forEach(p => {
                let fullName = `${p.first_name} ${p.last_name}`.trim();
                let option = `<option value="${p.id}">
                    ${p.patient_code} - ${fullName} (${p.phone})
                </option>`;
                select.innerHTML += option;
            });

        } else {

            document.getElementById('new_patient_box').style.display = 'block';

            select.innerHTML = '<option value="">No patient found - Create new</option>';
        }

    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('patient_select').innerHTML = '<option value="">Error loading patients</option>';
    });
});

// Form validation
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    let patientId = document.getElementById('patient_select').value;
    if(!patientId) {
        e.preventDefault();
        alert('Please select a patient');
        return false;
    }
});
</script>

@endsection
