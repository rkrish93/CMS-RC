@extends('layouts.app')

@section('title', 'Add Medicine')

@section('page-actions')
    <a href="{{ route('products.index') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Add New Medicine</h4>

                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Medicine Code</label>
                            <input type="text" name="product_code" value="{{ $productCode }}" class="form-control" readonly>
                            <small class="text-muted">Auto-generated</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}" @selected(old('unit') === $unit)>{{ ucfirst($unit) }}</option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Medicine Name *</label>
                            <input type="text" name="medicine_name" value="{{ old('medicine_name') }}" class="form-control" placeholder="e.g. Paracetamol" required>
                            @error('medicine_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Generic Name</label>
                            <input type="text" name="generic_name" value="{{ old('generic_name') }}" class="form-control" placeholder="e.g. Acetaminophen">
                            @error('generic_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Medicine description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', true))>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-gradient-primary">
                                <i class="mdi mdi-plus me-1"></i> Create Medicine
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
