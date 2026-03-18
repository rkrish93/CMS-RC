@extends('layouts.app')

@section('title','Add Patient')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-plus"></i>
        </span>
        Patient Registration
    </h3>


    
</div>

<div class="row">
<div class="col-md-12">

<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('patients.store') }}">
@csrf

<!-- ================= PERSONAL INFO ================= -->
<h5 class="text-primary mb-3">Personal Information</h5>

<div class="row">

<div class="col-md-2">
<label>Title</label>
<select name="title" class="form-control">
<option value="">Select</option>
<option>Mr</option>
<option>Mrs</option>
<option>Miss</option>
<option>Rev</option>
<option>Dr</option>
</select>
</div>

<div class="col-md-5">
<label>First Name</label>
<input type="text" name="first_name" class="form-control" required>
</div>

<div class="col-md-5">
<label>Last Name</label>
<input type="text" name="last_name" class="form-control" required>
</div>

<div class="col-md-3">
<label>Gender</label>
<select name="gender" class="form-control" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>
</div>

<div class="col-md-3">
<label>Date of Birth</label>
<input type="date" name="dob" id="dob" class="form-control">
</div>

<div class="col-md-3">
<label>Age</label>
<input type="number" name="age" id="age" class="form-control">
</div>

<div class="col-md-3">
<label>NIC</label>
<input type="text" name="nic" class="form-control" placeholder="123456789V / 200012345678">
</div>

</div>

<hr>

<!-- ================= CONTACT ================= -->
<h5 class="text-primary mb-3">Contact Information</h5>

<div class="row">

<div class="col-md-4">
<label>Phone Number</label>
<input type="text" name="phone" class="form-control">
</div>

<div class="col-md-8">
<label>Address</label>
<input type="text" name="address" class="form-control">
</div>

<div class="col-md-4">
<label>Province</label>
<select name="province" id="province" class="form-control">
<option value="">Select Province</option>
<option>Western</option>
<option>Central</option>
<option>Southern</option>
<option>Northern</option>
<option>Eastern</option>
<option>North Western</option>
<option>North Central</option>
<option>Uva</option>
<option>Sabaragamuwa</option>
</select>
</div>

<div class="col-md-4">
<label>District</label>
<select name="district" id="district" class="form-control">
<option value="">Select District</option>
</select>
</div>

<div class="col-md-4">
<label>GS Division</label>
<input type="text" name="gs_division" class="form-control">
</div>


</div>

<hr>

<!-- ================= EMERGENCY ================= -->
<h5 class="text-primary mb-3">Emergency Contact</h5>

<div class="row">

<div class="col-md-4">
<label>Contact Name</label>
<input type="text" name="emergency_name" class="form-control">
</div>

<div class="col-md-4">
<label>Contact Number</label>
<input type="text" name="emergency_phone" class="form-control">
</div>

<div class="col-md-4 mb-3">
    <label class="form-label">Relationship</label>
    <select name="relationship" class="form-control">
        <option value="">Select Relationship</option>
        <option>Father</option>
        <option>Mother</option>
        <option>Husband</option>
        <option>Wife</option>
        <option>Son</option>
        <option>Daughter</option>
        <option>Brother</option>
        <option>Sister</option>
        <option>Grandfather</option>
        <option>Grandmother</option>
        <option>Guardian</option>
        <option>Relative</option>
        <option>Friend</option>
        <option>Neighbour</option>
        <option>Other</option>
    </select>
</div>

</div>

<hr>

<!-- ================= MEDICAL ================= -->
<h5 class="text-primary mb-3">Medical Information</h5>

<div class="row">

<div class="col-md-3">
<label>Blood Group</label>
<select name="blood_group" class="form-control">
<option value="">Select</option>
<option>A+</option>
<option>A-</option>
<option>B+</option>
<option>B-</option>
<option>O+</option>
<option>O-</option>
<option>AB+</option>
<option>AB-</option>
</select>
</div>

<div class="col-md-3">
<label>Patient Type</label>
<select name="patient_type" class="form-control">
<option value="">Select</option>
<option>OPD</option>
<option>Clinic</option>
<option>VIP</option>
<option>Staff</option>
</select>
</div>

<div class="col-md-6">
<label>Allergies</label>
<input type="text" name="allergies" class="form-control">
</div>

<div class="col-md-12">
<label>Chronic Conditions</label>
<textarea name="chronic_conditions" class="form-control" rows="2"></textarea>
</div>

</div>

<br>

<button type="submit" class="btn btn-primary">
    Save Patient
</button>

<a href="{{ route('patients.index') }}" class="btn btn-light">
    Cancel
</a>

</form>

</div>
</div>

</div>
</div>

@endsection


@section('scripts')

<script>
document.getElementById('dob').addEventListener('change', function(){

    let dob = new Date(this.value);
    let today = new Date();

    let age = today.getFullYear() - dob.getFullYear();

    document.getElementById('age').value = age;

});

</script>
<script>

let districts = {

'Western' : ['Colombo','Gampaha','Kalutara'],

'Central' : ['Kandy','Matale','Nuwara Eliya'],

'Southern' : ['Galle','Matara','Hambantota'],

'Northern' : ['Jaffna','Kilinochchi','Mannar','Vavuniya','Mullaitivu'],

'Eastern' : ['Batticaloa','Ampara','Trincomalee'],

'North Western' : ['Kurunegala','Puttalam'],

'North Central' : ['Anuradhapura','Polonnaruwa'],

'Uva' : ['Badulla','Monaragala'],

'Sabaragamuwa' : ['Ratnapura','Kegalle']

};

document.getElementById('province').addEventListener('change', function(){

let province = this.value;

let districtDropdown = document.getElementById('district');

districtDropdown.innerHTML = '<option value="">Select District</option>';

if(districts[province]){

districts[province].forEach(function(d){

districtDropdown.innerHTML += `<option value="${d}">${d}</option>`;

});

}

});

</script>

@endsection

<style>
.form-control,
.form-select {
    height: 45px;
    border-radius: 6px;
}

textarea.form-control {
    height: 45px !important;
    resize: none;
}

label {
    /* font-weight: 600; */
    margin-bottom: 5px;
    margin-top: 10px;
}

.card-body h5 {
    border-left: 4px solid #750281;
    padding-left: 10px;
}
</style>
