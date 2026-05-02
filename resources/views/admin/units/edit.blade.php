@extends('layouts.app')

@section('title', 'Edit Unit')

@section('page-actions')
    <a href="{{ route('units.index') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-1">{{ $unit->unit_name }}</h4>
        <p class="text-muted mb-4">Update unit details and status.</p>

        <form method="POST" action="{{ route('units.update', $unit->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="unit_name"
                           value="{{ old('unit_name', $unit->unit_name) }}"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">In-Charge Officer</label>
                    <input type="text"
                           name="incharge_name"
                           value="{{ old('incharge_name', $unit->incharge_name) }}"
                           class="form-control">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Description</label>
                    <input type="text"
                           name="description"
                           value="{{ old('description', $unit->description) }}"
                           class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected((string) old('status', $unit->status) === '1')>Active</option>
                        <option value="0" @selected((string) old('status', $unit->status) === '0')>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('units.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-gradient-primary">
                    <i class="mdi mdi-content-save me-1"></i> Update Unit
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
