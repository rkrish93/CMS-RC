@extends('layouts.app')

@section('title', 'Add Patient')

@section('page-actions')
    <a href="{{ route('patients.index') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')

@php
    $titles = ['Mr', 'Mrs', 'Miss', 'Rev', 'Dr'];
    $genders = ['Male', 'Female', 'Other'];
    $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    $patientTypes = ['OPD', 'Clinic', 'Emergency'];
    $relationships = ['Father', 'Mother', 'Husband', 'Wife', 'Son', 'Daughter', 'Brother', 'Sister', 'Grandfather', 'Grandmother', 'Guardian', 'Relative', 'Friend', 'Neighbour', 'Other'];
    $provinces = ['Western', 'Central', 'Southern', 'Northern', 'Eastern', 'North Western', 'North Central', 'Uva', 'Sabaragamuwa'];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('patients.store') }}">
    @csrf

    <div class="card patient-form-card">
        <div class="card-body">
            <div class="form-section">
                <div class="section-heading">
                    <i class="mdi mdi-account-outline"></i>
                    <div>
                        <h5>Personal Information</h5>
                        <p>Basic identity details for registration.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Title</label>
                        <select name="title" class="form-select">
                            <option value="">Select</option>
                            @foreach($titles as $title)
                                <option value="{{ $title }}" @selected(old('title') === $title)>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="dob" id="dob" value="{{ old('dob') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" id="age" value="{{ old('age') }}" class="form-control" min="0" max="120">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">NIC <span class="text-danger">*</span></label>
                        <input type="text" name="nic" value="{{ old('nic') }}" class="form-control" placeholder="123456789V / 200012345678" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-heading">
                    <i class="mdi mdi-phone-outline"></i>
                    <div>
                        <h5>Contact Information</h5>
                        <p>Address and reachable contact details.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Province</label>
                        <select name="province" id="province" class="form-select">
                            <option value="">Select Province</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province }}" @selected(old('province') === $province)>{{ $province }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">District</label>
                        <select name="district" id="district" class="form-select" data-selected="{{ old('district') }}">
                            <option value="">Select District</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">GS Division</label>
                        <input type="text" name="gs_division" value="{{ old('gs_division') }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-heading">
                    <i class="mdi mdi-card-account-phone-outline"></i>
                    <div>
                        <h5>Emergency Contact</h5>
                        <p>Someone to contact in urgent situations.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contact Name</label>
                        <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Relationship</label>
                        <select name="relationship" class="form-select">
                            <option value="">Select Relationship</option>
                            @foreach($relationships as $relationship)
                                <option value="{{ $relationship }}" @selected(old('relationship') === $relationship)>{{ $relationship }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-heading">
                    <i class="mdi mdi-medical-bag"></i>
                    <div>
                        <h5>Medical Information</h5>
                        <p>Clinical notes and risk indicators.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">Select</option>
                            @foreach($bloodGroups as $bloodGroup)
                                <option value="{{ $bloodGroup }}" @selected(old('blood_group') === $bloodGroup)>{{ $bloodGroup }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Patient Type</label>
                        <select name="patient_type" class="form-select">
                            <option value="">Select</option>
                            @foreach($patientTypes as $patientType)
                                <option value="{{ $patientType }}" @selected(old('patient_type') === $patientType)>{{ $patientType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Allergies</label>
                        <input type="text" name="allergies" value="{{ old('allergies') }}" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Chronic Conditions</label>
                        <textarea name="chronic_conditions" class="form-control" rows="3">{{ old('chronic_conditions') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3">
                <a href="{{ route('patients.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-gradient-primary">
                    <i class="mdi mdi-content-save me-1"></i> Save Patient
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
const districts = {
    'Western': ['Colombo', 'Gampaha', 'Kalutara'],
    'Central': ['Kandy', 'Matale', 'Nuwara Eliya'],
    'Southern': ['Galle', 'Matara', 'Hambantota'],
    'Northern': ['Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya', 'Mullaitivu'],
    'Eastern': ['Batticaloa', 'Ampara', 'Trincomalee'],
    'North Western': ['Kurunegala', 'Puttalam'],
    'North Central': ['Anuradhapura', 'Polonnaruwa'],
    'Uva': ['Badulla', 'Monaragala'],
    'Sabaragamuwa': ['Ratnapura', 'Kegalle']
};

function updateAge() {
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');
    const dob = new Date(dobInput.value);

    if (!dobInput.value || Number.isNaN(dob.getTime())) {
        ageInput.value = '';
        return;
    }

    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDifference = today.getMonth() - dob.getMonth();

    if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    ageInput.value = Math.max(age, 0);
}

function populateDistricts() {
    const province = document.getElementById('province').value;
    const districtDropdown = document.getElementById('district');
    const selectedDistrict = districtDropdown.dataset.selected;

    districtDropdown.innerHTML = '<option value="">Select District</option>';

    (districts[province] || []).forEach(function (district) {
        const option = document.createElement('option');
        option.value = district;
        option.textContent = district;
        option.selected = selectedDistrict === district;
        districtDropdown.appendChild(option);
    });
}

document.getElementById('dob').addEventListener('change', updateAge);
document.getElementById('province').addEventListener('change', function () {
    document.getElementById('district').dataset.selected = '';
    populateDistricts();
});

populateDistricts();
</script>
@endsection

@push('styles')
<style>
    .form-section {
        padding: 22px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-section:first-child {
        padding-top: 0;
    }

    .form-section:last-of-type {
        border-bottom: 0;
    }

    .section-heading {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .section-heading i {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 20px;
    }

    .section-heading h5 {
        margin: 0;
        color: #152033;
        font-weight: 800;
    }

    .section-heading p {
        margin: 3px 0 0;
        color: #667085;
        font-size: 13px;
    }

    .patient-form-card textarea.form-control {
        min-height: 90px;
        resize: vertical;
    }
</style>
@endpush
