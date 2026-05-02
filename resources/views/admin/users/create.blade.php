@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-plus"></i>
        </span>
        Create User
    </h3>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">User Information</h4>
                <p class="card-description">Enter user details</p>

                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST"
                      action="{{ route('users.store') }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text"
                                       name="fname"
                                       value="{{ old('fname') }}"
                                       class="form-control"
                                       placeholder="Enter first name"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text"
                                       name="lname"
                                       value="{{ old('lname') }}"
                                       class="form-control"
                                       placeholder="Enter last name"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control"
                                       placeholder="Enter email"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       class="form-control"
                                       placeholder="Enter phone"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIC</label>
                                <input type="text"
                                       name="nic"
                                       value="{{ old('nic') }}"
                                       class="form-control"
                                       placeholder="Enter NIC"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Designation</label>
                                <select name="designation" class="form-control" required>
                                <option value="">Select</option>
                                <option value="Doctor" {{ old('designation') == 'Doctor' ? 'selected' : '' }}>Doctor</option>
                                <option value="Nurse" {{ old('designation') == 'Nurse' ? 'selected' : '' }}>Nurse</option>
                                <option value="Receptionist" {{ old('designation') == 'Receptionist' ? 'selected' : '' }}>Receptionist</option>
                                <option value="Mid wife" {{ old('designation') == 'Mid wife' ? 'selected' : '' }}>Mid wife</option>
                                <option value="PHI" {{ old('designation') == 'PHI' ? 'selected' : '' }}>PHI</option>
                                <option value="Pharmesist" {{ old('designation') == 'Pharmesist' ? 'selected' : '' }}>Pharmesist</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit</label>
                                <select name="unit_id" class="form-control" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->unit_name }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Joining Date</label>
                                <input type="date"
                                    name="join_date"
                                    value="{{ old('join_date') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1" {{ old('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role_id" class="form-control" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>User Image</label>
                                <input type="file"
                                       name="image"
                                       class="form-control">
                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            class="btn btn-gradient-primary me-2">
                        Create User
                    </button>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-light">
                        Cancel
                    </a>

                </form>

            </div>
        </div>

    </div>
</div>

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
