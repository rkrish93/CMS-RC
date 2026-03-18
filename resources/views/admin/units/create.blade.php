@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-plus"></i>
        </span>
        Create Unit
    </h3>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Unit Information</h4>
                <p class="card-description">Enter Unit details</p>

                <form method="POST"
                      action="{{ route('units.store') }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit Name</label>
                                <input type="text"
                                       name="unit_name"
                                       class="form-control"
                                       placeholder="Enter Unit Name"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text"
                                       name="description"
                                       class="form-control"
                                       placeholder="Enter Description"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>In-Charge</label>
                                <select name="incharge_name" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            class="btn btn-gradient-primary me-2">
                        Save Unit
                    </button>

                    <a href="{{ route('units.index') }}"
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
