@php
    $useOld = $useOld ?? true;

    $valueOrDefault = function (string $name, $default = null) use ($useOld) {
        return $useOld ? old($name, $default) : $default;
    };
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Medicine Name</label>
        <input type="text" name="medicine_name" class="form-control" value="{{ $valueOrDefault('medicine_name', $stock->medicine_name ?? '') }}" autocomplete="off" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Generic Name</label>
        <input type="text" name="generic_name" class="form-control" value="{{ $valueOrDefault('generic_name', $stock->generic_name ?? '') }}" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Batch No</label>
        <input type="text" name="batch_no" class="form-control" value="{{ $valueOrDefault('batch_no', $stock->batch_no ?? '') }}" autocomplete="off" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Unit</label>
        <input type="text" name="unit" class="form-control" value="{{ $valueOrDefault('unit', $stock->unit ?? '') }}" autocomplete="off" placeholder="tabs, ml, bottle">
    </div>
    <div class="col-md-4">
        <label class="form-label">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control" value="{{ $valueOrDefault('expiry_date', isset($stock) && $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : '') }}" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Quantity</label>
        <input type="number" min="0" name="quantity" class="form-control" value="{{ $valueOrDefault('quantity', $stock->quantity ?? 0) }}" autocomplete="off" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Reorder Level</label>
        <input type="number" min="0" name="reorder_level" class="form-control" value="{{ $valueOrDefault('reorder_level', $stock->reorder_level ?? 10) }}" autocomplete="off" required>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active_{{ $stock->id ?? 'new' }}" name="is_active" value="1" @checked($valueOrDefault('is_active', $stock->is_active ?? true))>
            <label class="form-check-label" for="is_active_{{ $stock->id ?? 'new' }}">Active</label>
        </div>
    </div>
</div>
