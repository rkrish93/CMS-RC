@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-edit"></i>
        </span>
        Edit Unit
    </h3>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Unit Information</h4>
                <p class="card-description">Update Unit details</p>

                <form method="POST"
                      action="{{ route('units.update',$unit->id) }}">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit Name</label>
                                <input type="text"
                                       name="unit_name"
                                       value="{{ $unit->unit_name }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text"
                                       name="description"
                                       value="{{ $unit->description }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>In-Charge</label>
                                <input type="text"
                                       name="incharge_name"
                                       value="{{ $unit->incharge_name }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="1" {{ $unit->status==1?'selected':'' }}>
                                        Active
                                    </option>
                                    <option value="0" {{ $unit->status==0?'selected':'' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            class="btn btn-gradient-primary me-2">
                        Update Unit
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
