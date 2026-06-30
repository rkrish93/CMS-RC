@extends('layouts.app')

@section('title', 'Pharmacy Prescriptions')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        @if(($newPrescriptionCount ?? 0) > 0)
            <div class="alert alert-warning mb-3">
                <strong>New Notification:</strong>
                {{ $newPrescriptionCount }} new doctor prescription(s) added in the last 6 hours.
            </div>
        @endif

        <form method="GET" class="row g-2 align-items-center prescription-filter-row">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search by patient code, name, or prescription text">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                    <option value="partial" @selected(($status ?? '') === 'partial')>Partial</option>
                    <option value="dispensed" @selected(($status ?? '') === 'dispensed')>Dispensed</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Search</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 prescription-table-header">
            <h5 class="mb-0">Prescription Queue</h5>
            <small class="text-muted">Doctor prescription, stock, dispensing, and shortage communication</small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle prescription-table">
                <thead>
                    <tr>
                        <th class="text-nowrap">Date</th>
                        <th class="text-nowrap">Patient Code</th>
                        <th class="text-nowrap">Patient Name</th>
                        <th class="text-nowrap">Doctor</th>
                        <th class="text-nowrap">Doctor Prescription</th>
                        <th class="text-nowrap">Totals</th>
                        <th class="text-nowrap">Medicine Details</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-end text-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $item)
                        @php
                            $statusName = $item->pharmacy_status ?? 'pending';
                            $isDispensed = $statusName === 'dispensed';
                            $isPartial = $statusName === 'partial';
                            $isPending = $statusName === 'pending';
                            $isLocked = (bool) ($item->is_locked ?? false);
                            $prescribedQty = (int) ($item->prescribed_quantity ?? 0);
                            $givenQty = (int) ($item->dispensed_quantity ?? 0);
                            $remainingQty = $prescribedQty > 0 ? max($prescribedQty - $givenQty, 0) : 0;
                            $dispensedBreakdown = is_array($item->dispensed_breakdown ?? null) ? $item->dispensed_breakdown : [];
                            $items = [];
                            $prescriptionRows = is_array($item->prescription_items ?? null) ? $item->prescription_items : [];

                            if (!empty($prescriptionRows)) {
                                foreach ($prescriptionRows as $prescriptionRow) {
                                    $medicineName = trim((string) ($prescriptionRow['medicine_name'] ?? ''));
                                    if ($medicineName === '') {
                                        continue;
                                    }

                                    $key = strtolower(preg_replace('/\s+/', '', $medicineName));
                                    $givenPerItem = (int) ($dispensedBreakdown[$key] ?? 0);
                                    $stockAvailable = (int) (($stockByMedicine[$key] ?? 0));

                                    $items[] = [
                                        'name' => $medicineName,
                                        'dosage' => trim((string) ($prescriptionRow['dosage'] ?? '')),
                                        'duration' => trim((string) ($prescriptionRow['duration'] ?? '')),
                                        'prescribed' => 1,
                                        'given' => min($givenPerItem, 1),
                                        'remaining' => max(1 - $givenPerItem, 0),
                                        'stock' => $stockAvailable,
                                        'dispense_limit' => ($stockAvailable > 0 && $givenPerItem < 1) ? 1 : 0,
                                    ];
                                }
                            } else {
                                $itemsByKey = [];
                                preg_match_all('/(?:^|[,;\n\r]|\s{1,})([A-Za-z0-9][A-Za-z0-9\s\-\/.\(\)]*?)\s*[-:]\s*(\d+)/u', (string) ($item->prescription ?? ''), $matches, PREG_SET_ORDER);
                                foreach ($matches as $match) {
                                    $name = trim((string) ($match[1] ?? ''));
                                    $qty = (int) ($match[2] ?? 0);
                                    if ($name !== '' && $qty > 0) {
                                        $key = strtolower(preg_replace('/\s+/', '', $name));
                                        $itemsByKey[$key] = [
                                            'name' => $itemsByKey[$key]['name'] ?? $name,
                                            'prescribed' => (int) (($itemsByKey[$key]['prescribed'] ?? 0) + $qty),
                                        ];
                                    }
                                }

                                foreach ($itemsByKey as $key => $row) {
                                    $givenPerItem = (int) ($dispensedBreakdown[$key] ?? 0);
                                    $stockAvailable = (int) (($stockByMedicine[$key] ?? 0));
                                    $prescribedForItem = (int) ($row['prescribed'] ?? 0);
                                    $remainingForItem = max($prescribedForItem - $givenPerItem, 0);

                                    $items[] = [
                                        'name' => (string) ($row['name'] ?? $key),
                                        'dosage' => '',
                                        'duration' => '',
                                        'prescribed' => $prescribedForItem,
                                        'given' => $givenPerItem,
                                        'remaining' => $remainingForItem,
                                        'stock' => $stockAvailable,
                                        'dispense_limit' => min($remainingForItem, max($stockAvailable, 0)),
                                    ];
                                }
                            }

                            $encodedAllItems = base64_encode((string) json_encode(array_values($items)));
                        @endphp
                        <tr>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($item->patient)->patient_code ?? 'N/A' }}</td>
                            <td>
                                {{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: 'N/A' }}
                            </td>
                            <td>
                                {{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: (optional($item->doctor)->name ?? 'N/A') }}
                            </td>
                            <td class="prescription-cell" style="min-width: 320px">
                                @if(count($items))
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($items as $med)
                                            <div class="prescription-summary-card">
                                                <div class="fw-semibold">{{ $med['name'] }}</div>
                                                @if(!empty($med['dosage']) || !empty($med['duration']))
                                                    <div class="small text-muted">
                                                        @if(!empty($med['dosage']))
                                                            <span class="me-2">Dosage: {{ $med['dosage'] }}</span>
                                                        @endif
                                                        @if(!empty($med['duration']))
                                                            <span>Duration: {{ $med['duration'] }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="prescription-text">{{ $item->prescription }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="qty-stack">
                                    <div><span class="qty-label">P</span> {{ $prescribedQty > 0 ? $prescribedQty : '-' }}</div>
                                    <div><span class="qty-label">G</span> {{ $givenQty }}</div>
                                    <div><span class="qty-label">R</span> {{ $prescribedQty > 0 ? $remainingQty : '-' }}</div>
                                </div>
                            </td>
                            <td class="medicine-qty-cell" style="min-width:260px">
                                @if(count($items))
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($items as $med)
                                            <div class="medicine-line hospital-medicine-line">
                                                <div>
                                                    <strong>{{ $med['name'] }}</strong>
                                                    <div class="small text-muted">
                                                        P:{{ $med['prescribed'] }} / G:{{ $med['given'] }} / R:{{ $med['remaining'] }}
                                                    </div>
                                                </div>
                                                <small class="stock-pill {{ $med['stock'] > 0 ? 'stock-pill-ok' : 'stock-pill-out' }}">
                                                    Stock {{ $med['stock'] }}{{ $med['stock'] <= 0 ? ' - Out' : '' }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">Use format Name-Qty</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-pill status-{{ $statusName }}">
                                    {{ ucfirst($statusName) }}
                                </span>
                                @if($isLocked)
                                    <div><small class="text-danger fw-semibold">Locked after SMS</small></div>
                                    <div><small class="text-muted">Why disabled: SMS already sent to patient. No further Add Given/SMS allowed.</small></div>
                                @endif
                                @if($isDispensed && $item->dispensed_at)
                                    <div><small class="text-muted">{{ $item->dispensed_at->format('Y-m-d H:i') }}</small></div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(! $isDispensed)
                                    @can('pharmacy-prescriptions-dispense')
                                        <div class="d-inline-flex flex-column align-items-end gap-2 prescription-actions prescription-actions-wrap">
                                            @if($isPending || $isPartial)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success btn-give-medicine js-open-dispense-modal"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#giveMedicineModal"
                                                    data-action-url="{{ route('pharmacy.prescriptions.dispense', $item->id) }}"
                                                    data-locked="{{ $isLocked ? '1' : '0' }}"
                                                    data-patient="{{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: 'N/A' }}"
                                                    data-patient-code="{{ optional($item->patient)->patient_code ?? 'N/A' }}"
                                                    data-doctor="{{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: (optional($item->doctor)->name ?? 'N/A') }}"
                                                    data-all-items="{{ $encodedAllItems }}"
                                                    data-fallback-max="{{ $prescribedQty > 0 ? $remainingQty : 99999 }}"
                                                    @disabled($isLocked)
                                                    title="{{ $isLocked ? 'Disabled: SMS already sent and prescription is locked.' : ($isPartial ? 'Complete remaining medicines' : '') }}">
                                                    {{ $isPartial ? 'Give Remaining Medicine' : 'Give Medicine' }}
                                                </button>
                                            @endif

                                            @if($isPartial)
                                                <form action="{{ route('pharmacy.prescriptions.send-sms', $item->id) }}" method="POST" class="d-inline-flex js-sms-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary btn-shortage-sms js-send-sms-btn" @disabled($isLocked) title="{{ $isLocked ? 'Disabled: SMS already sent and prescription is locked.' : 'Send shortage SMS to patient' }}">Send Shortage SMS</button>
                                                </form>
                                            @endif

                                            @if($isLocked)
                                                <div class="text-muted text-end" style="font-size:12px;">This row is permanently locked after SMS.</div>
                                            @endif
                                        </div>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No prescriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $prescriptions->links() }}
    </div>
</div>

<div class="modal fade" id="giveMedicineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content pharmacy-give-modal">
            <form id="giveMedicineForm" method="POST">
                @csrf
                <div class="modal-header border-0 pb-2">
                    <div>
                        <h4 class="modal-title mb-1">Pharmacy Give Medicine</h4>
                        <small class="text-muted">Review doctor prescription and enter given quantities</small>
                        <div id="modalVisitSummary" class="small text-muted mt-1"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="prescription-template-box h-100">
                                <label class="form-label mb-2">Doctor Prescription (Checked)</label>
                                <div id="modalDoctorPrescriptionList" class="doctor-prescription-list border rounded p-2 bg-light"></div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="prescription-template-box h-100">
                                <label class="form-label mb-2">Give Medicines</label>
                                <div id="modalGiveMedicineRows" class="border rounded p-2 mb-3"></div>

                                <label for="modal_pharmacy_note" class="form-label">Pharmacy Note (optional)</label>
                                <textarea id="modal_pharmacy_note" name="pharmacy_note" rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modalGiveBtn">Add Given</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .prescription-table td,
    .prescription-table th {
        vertical-align: top;
    }

    .prescription-filter-row .form-control,
    .prescription-filter-row .form-select {
        border-radius: 6px;
        border-color: #cbd5e1;
    }

    .prescription-table-header {
        border-bottom: 1px solid #dbe4ee;
        padding-bottom: 10px;
    }

    .prescription-table thead th {
        background: #eef3f8;
        color: #334155;
        font-size: 12px;
        letter-spacing: .02em;
        text-transform: uppercase;
        border-bottom: 1px solid #cfd8e3;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .prescription-table tbody tr:hover {
        background: #f6f9fc;
    }

    .prescription-text {
        white-space: pre-wrap;
        line-height: 1.35;
    }

    .prescription-summary-card {
        padding: 8px 10px;
        border: 1px solid #dbe4ee;
        border-radius: 6px;
        background: #fcfdff;
    }

    .qty-stack {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 13px;
    }

    .qty-label {
        display: inline-flex;
        min-width: 20px;
        justify-content: center;
        border-radius: 999px;
        background: #dde5ee;
        color: #0f172a;
        font-size: 11px;
        font-weight: 700;
        margin-right: 6px;
    }

    .medicine-line {
        line-height: 1.3;
    }

    .hospital-medicine-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 6px 8px;
        border: 1px solid #dbe4ee;
        border-radius: 6px;
        background: #ffffff;
    }

    .stock-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .stock-pill-ok {
        background: #edf7f1;
        color: #21633b;
        border: 1px solid #bedfca;
    }

    .stock-pill-out {
        background: #fdf0f0;
        color: #8b1e1e;
        border: 1px solid #edc2c2;
    }

    .prescription-actions {
        min-width: 360px;
    }

    .prescription-actions .js-dispense-form,
    .prescription-actions .js-sms-form {
        width: 100%;
        justify-content: flex-end;
    }

    .doctor-prescription-list,
    .give-medicine-rows {
        max-height: 220px;
        overflow-y: auto;
    }

    .doctor-prescription-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 13px;
        line-height: 1.3;
    }

    .give-row {
        display: grid;
        grid-template-columns: 24px 1fr 120px;
        gap: 10px;
        align-items: center;
        padding: 8px 6px;
        border-bottom: 1px solid #eef2f6;
    }

    .give-row:last-child {
        border-bottom: none;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .status-pill.status-pending {
        background: #fff4e5;
        color: #9a5a00;
        border-color: #edd3a4;
    }

    .status-pill.status-partial {
        background: #eef6fb;
        color: #215b75;
        border-color: #bfd8e6;
    }

    .status-pill.status-dispensed {
        background: #edf7f1;
        color: #21633b;
        border-color: #bedfca;
    }

    .prescription-actions-wrap .btn {
        min-width: 170px;
    }

    .btn-give-medicine {
        background: #1d4f91;
        border-color: #1d4f91;
        box-shadow: none;
    }

    .btn-give-medicine:hover,
    .btn-give-medicine:focus {
        background: #173f73;
        border-color: #173f73;
    }

    .prescription-template-box {
        background: #f8fafc;
        border: 1px solid #dbe4ee;
        border-radius: 6px;
        padding: 10px 12px;
    }

    .pharmacy-give-modal {
        background: #f5f7fa;
    }

    .medicine-qty-cell {
        min-width: 320px;
    }

    .medicine-qty-cell .medicine-line {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    @media (max-width: 768px) {
        .prescription-table th,
        .prescription-table td {
            font-size: 12px;
        }

        .prescription-actions {
            min-width: 260px;
        }

        .prescription-actions-wrap .btn {
            min-width: 150px;
        }

        .prescription-actions .js-dispense-form,
        .prescription-actions .js-sms-form {
            justify-content: flex-start;
        }

        .medicine-qty-cell {
            min-width: 260px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lockFormControls = (form) => {
        if (!form) {
            return;
        }

        const controls = form.querySelectorAll('select, input, button');
        controls.forEach((control) => {
            if (control.tagName === 'INPUT') {
                const inputType = (control.getAttribute('type') || '').toLowerCase();
                const inputName = control.getAttribute('name') || '';

                // Keep CSRF and hidden method/state inputs enabled so Laravel receives them.
                if (inputType === 'hidden' || inputName === '_token' || inputName === '_method') {
                    return;
                }
            }

            control.disabled = true;
        });
    };

    const giveMedicineForm = document.getElementById('giveMedicineForm');
    const giveMedicineModalEl = document.getElementById('giveMedicineModal');
    const modalGiveMedicineRows = document.getElementById('modalGiveMedicineRows');
    const modalPharmacyNote = document.getElementById('modal_pharmacy_note');
    const modalGiveBtn = document.getElementById('modalGiveBtn');
    const modalDoctorPrescriptionList = document.getElementById('modalDoctorPrescriptionList');
    const modalVisitSummary = document.getElementById('modalVisitSummary');

    const resetGiveModal = () => {
        if (!giveMedicineForm) {
            return;
        }

        giveMedicineForm.action = '';

        if (modalPharmacyNote) {
            modalPharmacyNote.value = '';
            modalPharmacyNote.disabled = false;
        }

        if (modalGiveBtn) {
            modalGiveBtn.disabled = false;
        }

        if (modalDoctorPrescriptionList) {
            modalDoctorPrescriptionList.innerHTML = '';
        }

        if (modalVisitSummary) {
            modalVisitSummary.textContent = '';
        }

        if (modalGiveMedicineRows) {
            modalGiveMedicineRows.innerHTML = '';
            modalGiveMedicineRows.classList.add('give-medicine-rows');
        }
    };

    if (giveMedicineModalEl) {
        giveMedicineModalEl.addEventListener('hidden.bs.modal', function () {
            resetGiveModal();
        });
    }

    document.querySelectorAll('.js-open-dispense-modal').forEach((button) => {
        button.addEventListener('click', function () {
            if (!giveMedicineForm) {
                return;
            }

            resetGiveModal();

            const isLocked = button.dataset.locked === '1';
            const actionUrl = button.dataset.actionUrl || '';
            const fallbackMax = parseInt(button.dataset.fallbackMax || '99999', 10);
            const patientName = button.dataset.patient || 'N/A';
            const patientCode = button.dataset.patientCode || 'N/A';
            const doctorName = button.dataset.doctor || 'N/A';
            let allItems = [];

            try {
                const encoded = button.dataset.allItems || '';
                allItems = encoded ? JSON.parse(atob(encoded)) : [];
            } catch (e) {
                allItems = [];
            }

            giveMedicineForm.action = actionUrl;

            if (modalVisitSummary) {
                modalVisitSummary.textContent = `Patient: ${patientName} (${patientCode}) | Doctor: ${doctorName}`;
            }
            if (allItems.length > 0) {
                allItems.forEach((med, index) => {
                    const itemRow = document.createElement('label');
                    itemRow.className = 'doctor-prescription-item mb-2';
                    itemRow.innerHTML = `
                        <input type="checkbox" class="form-check-input mt-1" checked disabled>
                        <span>
                            <strong>${med.name}</strong>
                            ${med.dosage ? `<span class="d-block text-muted">Dosage: ${med.dosage}</span>` : ''}
                            ${med.duration ? `<span class="d-block text-muted">Duration: ${med.duration}</span>` : ''}
                            <span class="d-block">P:${med.prescribed} / G:${med.given} / R:${med.remaining}</span>
                        </span>
                    `;
                    modalDoctorPrescriptionList.appendChild(itemRow);

                    const row = document.createElement('div');
                    row.className = 'give-row';

                    const canGive = Number(med.dispense_limit || 0) > 0 && Number(med.remaining || 0) > 0;
                    const defaultGiveQty = canGive ? Number(med.dispense_limit || 1) : 0;
                    const checkId = `give_check_${index}`;

                    row.innerHTML = `
                        <div class="form-check m-0">
                            <input id="${checkId}" class="form-check-input js-give-check" type="checkbox" ${canGive ? 'checked' : ''} ${canGive ? '' : 'disabled'}>
                        </div>
                        <div class="flex-grow-1 small">
                            <div><strong>${med.name}</strong></div>
                            ${med.dosage ? `<div class="text-muted">Dosage: ${med.dosage}</div>` : ''}
                            ${med.duration ? `<div class="text-muted">Duration: ${med.duration}</div>` : ''}
                            <div class="text-muted">Remaining: ${med.remaining} | Stock: ${med.stock}</div>
                            <input type="hidden" name="medicines[${index}][medicine_name]" value="${med.name}">
                            <input type="hidden" class="js-give-qty-hidden" name="medicines[${index}][dispense_quantity]" value="${defaultGiveQty}">
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm ${canGive ? 'btn-outline-success js-give-toggle' : 'btn-outline-danger'}" ${canGive ? '' : 'disabled'}>
                                ${canGive ? `Checked for Give (${defaultGiveQty})` : 'Out of stock'}
                            </button>
                        </div>
                    `;

                    const checkInput = row.querySelector('.js-give-check');
                    const qtyHidden = row.querySelector('.js-give-qty-hidden');
                    const toggleButton = row.querySelector('.js-give-toggle');

                    const syncGiveState = () => {
                        if (!checkInput || !qtyHidden || !toggleButton) {
                            return;
                        }

                        const checked = checkInput.checked;
                        qtyHidden.value = checked ? String(defaultGiveQty) : '0';
                        toggleButton.textContent = checked ? `Checked for Give (${defaultGiveQty})` : 'Not Selected';
                        toggleButton.classList.toggle('btn-outline-success', checked);
                        toggleButton.classList.toggle('btn-outline-secondary', !checked);
                    };

                    if (checkInput && toggleButton) {
                        checkInput.addEventListener('change', syncGiveState);
                        toggleButton.addEventListener('click', function () {
                            checkInput.checked = !checkInput.checked;
                            syncGiveState();
                        });
                        syncGiveState();
                    }

                    modalGiveMedicineRows.appendChild(row);
                });
            } else {
                modalDoctorPrescriptionList.innerHTML = '<span class="text-muted small">No parsed doctor prescription items.</span>';

                modalGiveMedicineRows.innerHTML = `
                    <div class="small text-muted mb-2">Could not parse doctor prescription items. Enter manual medicine and quantity.</div>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" name="medicine_name" placeholder="Medicine name" required>
                        <input type="number" class="form-control form-control-sm" name="dispense_quantity" min="1" max="${fallbackMax}" value="1" required style="width:95px;">
                    </div>
                `;
            }

            [modalPharmacyNote, modalGiveBtn].forEach((control) => {
                control.disabled = isLocked;
            });

            modalGiveMedicineRows.querySelectorAll('input, select, textarea, button').forEach((control) => {
                control.disabled = isLocked || control.disabled;
            });
        });
    });

    if (giveMedicineForm) {
        giveMedicineForm.addEventListener('submit', function () {
            lockFormControls(giveMedicineForm);
        });
    }

    document.querySelectorAll('.js-sms-form').forEach((smsForm) => {
        smsForm.addEventListener('submit', function () {
            lockFormControls(smsForm);

            const row = smsForm.closest('tr');
            if (!row) {
                return;
            }

            const giveBtn = row.querySelector('.js-open-dispense-modal');
            if (giveBtn) {
                giveBtn.disabled = true;
            }
        });
    });
});
</script>
@endpush
