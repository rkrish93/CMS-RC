@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-edit"></i>
        </span>
        Edit User
    </h3>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Update User Information</h4>
                @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif
                <form method="POST"
                      action="{{ route('users.update',$user->id) }}"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text"
                                       name="fname"
                                       value="{{ $user->fname }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text"
                                       name="lname"
                                       value="{{ $user->lname }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email"
                                       name="email"
                                       value="{{ $user->email }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text"
                                       name="phone"
                                       value="{{ $user->phone }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIC</label>
                                <input type="text"
                                       name="nic"
                                       class="form-control"
                                       value="{{ $user->nic }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Designation</label>
                                <select name="designation" class="form-control" required>
                                <option value="">Select</option>
                                @foreach(['Doctor','Nurse','Receptionist','Mid wife','PHI','Pharmacist'] as $d)
                                    <option value="{{ $d }}" {{ ($user->designation ?? '') == $d ? 'selected' : '' }}>
                                        {{ $d }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit</label>
                                <select name="unit_id" class="form-control">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ ($user->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->unit_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Join Date</label>
                                <input type="date" name="join_date"
                                    id="join_date"
                                    value="{{ $user->join_date }}"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="1" {{ $user->status==1?'selected':'' }}>
                                        Active
                                    </option>
                                    <option value="0" {{ $user->status==0?'selected':'' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role_id" class="form-control" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $user->role_id == $role->id ? 'selected' : '' }}>
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
                                       class="form-control mb-2">

                                @if($user->image)
                                    <img src="{{ asset('assets/images/profiles/'.$user->image) }}"
                                         width="60"
                                         style="border-radius:50%">
                                @endif

                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            class="btn btn-gradient-primary me-2">
                        Update User
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
