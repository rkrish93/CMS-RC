@extends('layouts.app')

@section('title','Create Appointment')

@section('content')

<div class="card">
    <div class="card-body">

        <h4 class="mb-3">Schedule Appointment</h4>

        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-3">
                <i class="mdi mdi-alert-circle me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger mb-3">
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
                        @if(isset($prefilledPatient) && $prefilledPatient)
                            <option value="{{ $prefilledPatient->id }}" selected>
                                {{ $prefilledPatient->patient_code }} - {{ trim(($prefilledPatient->first_name ?? '') . ' ' . ($prefilledPatient->last_name ?? '')) }} ({{ $prefilledPatient->phone }})
                            </option>
                        @endif
                    </select>
                    @if(isset($prefilledPatient) && $prefilledPatient)
                        <small class="text-success">Patient loaded from QR: {{ $prefilledPatient->patient_code }}</small>
                    @endif
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
                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" required>
                    <small class="text-muted">Clinic Hours: 09:00 AM - 03:00 PM (Appointments after 3:00 PM not allowed)</small>
                    <div class="alert alert-danger mt-2 mb-0" id="after_3pm_warning" style="display: none;">
                        <i class="mdi mdi-clock-alert me-1"></i> <strong>Appointments cannot be scheduled after 3:00 PM.</strong> Booking for today is closed. Please select a future date.
                    </div>
                </div>

                <!-- TIME INFO -->
                <div class="col-md-12">
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="mdi mdi-information me-1"></i>
                            <strong>Appointment time will be automatically assigned</strong> based on available slots (15-minute intervals between 09:00 AM - 03:00 PM). <em>Appointments after 3:00 PM are not allowed.</em>
                        </small>
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary" id="submit_btn">
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

const appointmentDateInput = document.getElementById('appointment_date');
const after3pmWarning = document.getElementById('after_3pm_warning');

function check3pmRestriction() {
    if (!appointmentDateInput || !after3pmWarning) return false;

    const selectedDate = appointmentDateInput.value;
    const now = new Date();
    const todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');

    if (selectedDate === todayStr && now.getHours() >= 15) {
        after3pmWarning.style.display = 'block';
        return true;
    } else {
        after3pmWarning.style.display = 'none';
        return false;
    }
}

if (appointmentDateInput) {
    appointmentDateInput.addEventListener('change', check3pmRestriction);
}

// Form validation
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    let patientId = document.getElementById('patient_select').value;
    if(!patientId) {
        e.preventDefault();
        alert('Please select a patient');
        return false;
    }

    if (check3pmRestriction()) {
        e.preventDefault();
        alert('Appointments cannot be scheduled after 3:00 PM for today.');
        return false;
    }
});
</script>

@endsection
