@extends('layouts.app')

@section('title','Edit Patient')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-edit"></i>
        </span>
        Edit Patient
    </h3>
    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
            Back
        </a>
</div>

<div class="row">
<div class="col-md-12">

<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('patients.update',$patient->id) }}">
@csrf
@method('PUT')

<!-- ================= PERSONAL INFO ================= -->
<h5 class="text-primary mb-3">Personal Information</h5>

<div class="row">

<div class="col-md-2">
<label>Title</label>
<select name="title" class="form-control">
<option value="">Select</option>
<option {{ $patient->title=='Mr'?'selected':'' }}>Mr</option>
<option {{ $patient->title=='Mrs'?'selected':'' }}>Mrs</option>
<option {{ $patient->title=='Miss'?'selected':'' }}>Miss</option>
<option {{ $patient->title=='Rev'?'selected':'' }}>Rev</option>
<option {{ $patient->title=='Dr'?'selected':'' }}>Dr</option>
</select>
</div>

<div class="col-md-5">
<label>First Name</label>
<input type="text" name="first_name"
       value="{{ $patient->first_name }}"
       class="form-control" required>
</div>

<div class="col-md-5">
<label>Last Name</label>
<input type="text" name="last_name"
       value="{{ $patient->last_name }}"
       class="form-control" required>
</div>

<div class="col-md-3">
<label>Gender</label>
<select name="gender" class="form-control">
<option value="">Select</option>
<option {{ $patient->gender=='Male'?'selected':'' }}>Male</option>
<option {{ $patient->gender=='Female'?'selected':'' }}>Female</option>
<option {{ $patient->gender=='Other'?'selected':'' }}>Other</option>
</select>
</div>

<div class="col-md-3">
<label>Date of Birth</label>
<input type="date" name="dob"
       id="dob"
       value="{{ $patient->dob }}"
       class="form-control">
</div>

<div class="col-md-3">
<label>Age</label>
<input type="number" name="age"
       id="age"
       value="{{ $patient->age }}"
       class="form-control">
</div>

<div class="col-md-3">
<label>NIC</label>
<input type="text" name="nic"
       value="{{ $patient->nic }}"
       class="form-control">
</div>

</div>

<hr>

<!-- ================= CONTACT ================= -->
<h5 class="text-primary mb-3">Contact Information</h5>

<div class="row">

<div class="col-md-4">
<label>Phone Number</label>
<input type="text" name="phone"
       value="{{ $patient->phone }}"
       class="form-control">
</div>

<div class="col-md-8">
<label>Address</label>
<input type="text" name="address"
       value="{{ $patient->address }}"
       class="form-control">
</div>

<div class="col-md-4">
<label>Province</label>
<select name="province" id="province" class="form-control">
<option value="">Select Province</option>
<option {{ $patient->province=='Western'?'selected':'' }}>Western</option>
<option {{ $patient->province=='Central'?'selected':'' }}>Central</option>
<option {{ $patient->province=='Southern'?'selected':'' }}>Southern</option>
<option {{ $patient->province=='Northern'?'selected':'' }}>Northern</option>
<option {{ $patient->province=='Eastern'?'selected':'' }}>Eastern</option>
<option {{ $patient->province=='North Western'?'selected':'' }}>North Western</option>
<option {{ $patient->province=='North Central'?'selected':'' }}>North Central</option>
<option {{ $patient->province=='Uva'?'selected':'' }}>Uva</option>
<option {{ $patient->province=='Sabaragamuwa'?'selected':'' }}>Sabaragamuwa</option>
</select>
</div>

<div class="col-md-4">
<label>District</label>
<input type="text"
       name="district"
       value="{{ $patient->district }}"
       id="district"
       class="form-control">
</div>

<div class="col-md-4">
<label>GS Division</label>
<input type="text"
       name="gs_division"
       value="{{ $patient->gs_division }}"
       class="form-control">
</div>

</div>

<hr>

<!-- ================= EMERGENCY ================= -->
<h5 class="text-primary mb-3">Emergency Contact</h5>

<div class="row">

<div class="col-md-4">
<label>Contact Name</label>
<input type="text"
       name="emergency_name"
       value="{{ $patient->emergency_name }}"
       class="form-control">
</div>

<div class="col-md-4">
<label>Contact Number</label>
<input type="text"
       name="emergency_phone"
       value="{{ $patient->emergency_phone }}"
       class="form-control">
</div>

<div class="col-md-4">
<label>Relationship</label>
<input type="text"
       name="relationship"
       value="{{ $patient->relationship }}"
       class="form-control">
</div>

</div>

<hr>

<!-- ================= MEDICAL ================= -->
<h5 class="text-primary mb-3">Medical Information</h5>

<div class="row">

<div class="col-md-3">
<label>Blood Group</label>
<input type="text"
       name="blood_group"
       value="{{ $patient->blood_group }}"
       class="form-control">
</div>

<div class="col-md-3">
<label>Patient Type</label>
<input type="text"
       name="patient_type"
       value="{{ $patient->patient_type }}"
       class="form-control">
</div>

<div class="col-md-6">
<label>Allergies</label>
<input type="text"
       name="allergies"
       value="{{ $patient->allergies }}"
       class="form-control">
</div>

<div class="col-md-12">
<label>Chronic Conditions</label>
<textarea name="chronic_conditions"
          class="form-control">{{ $patient->chronic_conditions }}</textarea>
</div>

</div>

<br>

<button type="submit" class="btn btn-primary">
    Update Patient
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


