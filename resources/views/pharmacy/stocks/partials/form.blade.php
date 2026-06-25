@php
    $useOld = $useOld ?? true;
    $fieldKey = isset($stock) ? (string) $stock->id : 'new';

    $valueOrDefault = function (string $name, $default = null) use ($useOld) {
        return $useOld ? old($name, $default) : $default;
    };
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Product *</label>
        <select id="product_id_{{ $fieldKey }}" name="product_id" class="form-select" data-placeholder="Search product..." required>
            <option value="">Search and select product...</option>
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
        <small class="text-muted">Search by code, name or generic name</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Batch No *</label>
        <input type="text" name="batch_no" class="form-control" value="{{ $valueOrDefault('batch_no', $stock->batch_no ?? '') }}" autocomplete="off" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Medicine Name</label>
        <input type="text" id="medicine_name_{{ $fieldKey }}" name="medicine_name" class="form-control" value="{{ $valueOrDefault('medicine_name', $stock->medicine_name ?? '') }}" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label">Generic Name</label>
        <input type="text" id="generic_name_{{ $fieldKey }}" name="generic_name" class="form-control" value="{{ $valueOrDefault('generic_name', $stock->generic_name ?? '') }}" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label">Unit</label>
        <input type="text" id="unit_{{ $fieldKey }}" name="unit" class="form-control" value="{{ $valueOrDefault('unit', $stock->unit ?? '') }}" readonly>
        <small class="text-muted">Auto-filled from selected product</small>
    </div>
    <div class="col-md-4">
        <label class="form-label">Expiry Date</label>
        <input type="date" id="expiry_date_{{ $fieldKey }}" name="expiry_date" class="form-control" value="{{ $valueOrDefault('expiry_date', isset($stock) && $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : '') }}" autocomplete="off">
    </div>
    <div class="col-md-4">
        <label class="form-label">Quantity *</label>
        <input type="number" min="0" name="quantity" class="form-control" value="{{ $valueOrDefault('quantity', $stock->quantity ?? 0) }}" autocomplete="off" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Reorder Level *</label>
        <input type="number" min="0" name="reorder_level" class="form-control" value="{{ $valueOrDefault('reorder_level', $stock->reorder_level ?? 10) }}" autocomplete="off" required>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active_{{ $stock->id ?? 'new' }}" name="is_active" value="1" @checked($valueOrDefault('is_active', $stock->is_active ?? true))>
            <label class="form-check-label" for="is_active_{{ $stock->id ?? 'new' }}">Active</label>
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
