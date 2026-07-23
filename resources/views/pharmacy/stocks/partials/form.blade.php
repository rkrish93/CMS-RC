@php
    $useOld = $useOld ?? true;
    $fieldKey = isset($stock) ? (string) $stock->id : 'new';

    $valueOrDefault = function (string $name, $default = null) use ($useOld) {
        return $useOld ? old($name, $default) : $default;
    };
@endphp

<div class="stock-form-container">
    <!-- Section 1: Medicine & Batch Selection -->
    <div class="form-section-card mb-3">
        <div class="form-section-title mb-2 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="mdi mdi-pill text-primary fs-5"></i> Medicine & Batch Information
        </div>
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label fw-medium text-secondary">Medicine <span class="text-danger">*</span></label>
                <select id="product_id_{{ $fieldKey }}" name="product_id" class="form-select form-select-custom" data-placeholder="Search medicine by name or code..." required>
                    <option value="">Search and select medicine...</option>
                    @foreach(($products ?? []) as $product)
                        <option
                            value="{{ $product->id }}"
                            @selected(isset($stock) && (string) $stock->product_id === (string) $product->id)
                            data-medicine-name="{{ $product->medicine_name }}"
                            data-generic-name="{{ $product->generic_name }}"
                            data-unit="{{ $product->unit }}"
                            data-expiry-date="{{ $product->expiry_date ? \Illuminate\Support\Carbon::parse($product->expiry_date)->format('Y-m-d') : '' }}"
                            data-product-code="{{ $product->product_code }}"
                        >
                            {{ $product->product_code }} - {{ $product->medicine_name }}{{ $product->generic_name ? ' (' . $product->generic_name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text small text-muted">Search by code, name or generic name</div>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-medium text-secondary">Batch No <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="mdi mdi-barcode"></i></span>
                    <input type="text" name="batch_no" class="form-control" value="{{ $valueOrDefault('batch_no', $stock->batch_no ?? '') }}" placeholder="e.g. BAT-2026-001" autocomplete="off" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Auto-filled Medicine Details -->
    <div class="form-section-card mb-3 p-3 bg-light rounded-3 border-0">
        <div class="form-section-title mb-2 fw-medium text-muted small text-uppercase tracking-wider">
            Auto-Filled Medicine Details
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-secondary">Medicine Name</label>
                <input type="text" id="medicine_name_{{ $fieldKey }}" name="medicine_name" class="form-control bg-white" value="{{ $valueOrDefault('medicine_name', $stock->medicine_name ?? '') }}" placeholder="Auto-filled" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-secondary">Generic Name</label>
                <input type="text" id="generic_name_{{ $fieldKey }}" name="generic_name" class="form-control bg-white" value="{{ $valueOrDefault('generic_name', $stock->generic_name ?? '') }}" placeholder="Auto-filled" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-secondary">Unit</label>
                <input type="text" id="unit_{{ $fieldKey }}" name="unit" class="form-control bg-white" value="{{ $valueOrDefault('unit', $stock->unit ?? '') }}" placeholder="Auto-filled" readonly>
            </div>
        </div>
    </div>

    <!-- Section 3: Quantity, Expiry & Status -->
    <div class="form-section-card">
        <div class="form-section-title mb-2 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="mdi mdi-cube-outline text-primary fs-5"></i> Inventory & Status
        </div>
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label fw-medium text-secondary">Expiry Date</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="mdi mdi-calendar"></i></span>
                    <input type="date" id="expiry_date_{{ $fieldKey }}" name="expiry_date" class="form-control" value="{{ $valueOrDefault('expiry_date', isset($stock) && $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : '') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium text-secondary">Quantity <span class="text-danger">*</span></label>
                <input type="number" min="0" name="quantity" class="form-control" value="{{ $valueOrDefault('quantity', $stock->quantity ?? 0) }}" autocomplete="off" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium text-secondary">Reorder Level <span class="text-danger">*</span></label>
                <input type="number" min="0" name="reorder_level" class="form-control" value="{{ $valueOrDefault('reorder_level', $stock->reorder_level ?? 10) }}" autocomplete="off" required>
            </div>
            <div class="col-12 mt-3 pt-2 border-top">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fw-semibold text-dark d-block">Item Status</span>
                        <small class="text-muted">Enable to make this stock item active for dispensing.</small>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input type="checkbox" class="form-check-input" style="width: 2.5em; height: 1.3em; cursor: pointer;" id="is_active_{{ $stock->id ?? 'new' }}" name="is_active" value="1" @checked($valueOrDefault('is_active', $stock->is_active ?? true))>
                        <label class="form-check-label ms-2 fw-medium" for="is_active_{{ $stock->id ?? 'new' }}">Active</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id_{{ $fieldKey }}');
    const medicineNameInput = document.getElementById('medicine_name_{{ $fieldKey }}');
    const genericNameInput = document.getElementById('generic_name_{{ $fieldKey }}');
    const unitInput = document.getElementById('unit_{{ $fieldKey }}');
    const expiryDateInput = document.getElementById('expiry_date_{{ $fieldKey }}');

    if (!productSelect) return;

    const productMap = {};
    Array.from(productSelect.options).forEach(function (option) {
        if (!option.value) return;

        productMap[String(option.value)] = {
            medicine_name: option.dataset.medicineName || '',
            generic_name: option.dataset.genericName || '',
            unit: option.dataset.unit || '',
            expiry_date: option.dataset.expiryDate || ''
        };
    });

    const syncProductFields = (selectedValue = null) => {
        const value = String(selectedValue || productSelect.value || '');
        const selectedProduct = productMap[value] || null;

        if (!selectedProduct) {
            medicineNameInput.value = '';
            genericNameInput.value = '';
            unitInput.value = '';
            expiryDateInput.value = '';
            return;
        }

        medicineNameInput.value = selectedProduct.medicine_name || '';
        genericNameInput.value = selectedProduct.generic_name || '';
        unitInput.value = selectedProduct.unit || '';
        expiryDateInput.value = selectedProduct.expiry_date || '';
    };

    const tom = new TomSelect(productSelect, {
        valueField: 'id',
        labelField: 'text',
        searchField: ['text', 'product_code', 'medicine_name', 'generic_name'],
        render: {
            item: function(data, escape) {
                return '<div>' +
                    '<span class="badge bg-light text-dark me-2">' + escape(data.product_code) + '</span>' +
                    '<span>' + escape(data.medicine_name) + '</span>' +
                    (data.generic_name ? ' <small class="text-muted">(' + escape(data.generic_name) + ')</small>' : '') +
                '</div>';
            },
            option: function(data, escape) {
                return '<div>' +
                    '<span class="badge bg-light text-dark me-2">' + escape(data.product_code) + '</span>' +
                    '<strong>' + escape(data.medicine_name) + '</strong>' +
                    (data.generic_name ? '<br><small class="text-muted">' + escape(data.generic_name) + '</small>' : '') +
                '</div>';
            }
        },
        onChange: function(value) {
            syncProductFields(value);
        }
    });

    productSelect.addEventListener('change', syncProductFields);
    tom.on('change', function () {
        window.setTimeout(function () {
            syncProductFields(tom.getValue());
        }, 0);
    });

    syncProductFields();
});
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush
