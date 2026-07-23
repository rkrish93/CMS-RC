@extends('layouts.app')

@section('title', 'Pharmacy Prescriptions')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
@endsection

@section('content')
@if(session('success') || session('error') || (($consultationId ?? 0) > 0))
<div class="card mb-3">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-2">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-2">{{ session('error') }}</div>
        @endif

        @if(($consultationId ?? 0) > 0)
         
        @endif
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 prescription-table-header">
            <h5 class="mb-0">Prescription Queue</h5>
            <small class="text-muted">Doctor prescription, stock, dispensing, and shortage communication</small>
        </div>

        <div class="prescription-cards-container">
            <div class="row g-3">
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
                        $hasStockShortage = false;

                        if (!empty($prescriptionRows)) {
                            $itemsByKey = [];

                            foreach ($prescriptionRows as $prescriptionRow) {
                                $medicineName = trim((string) ($prescriptionRow['medicine_name'] ?? ''));
                                if ($medicineName === '') {
                                    continue;
                                }

                                $key = strtolower(preg_replace('/\s+/', '', $medicineName));

                                if (!isset($itemsByKey[$key])) {
                                    $itemsByKey[$key] = [
                                        'name' => $medicineName,
                                        'dosage_list' => [],
                                        'duration_list' => [],
                                        'time_slot_list' => [],
                                        'food_timing_list' => [],
                                        'prescribed' => 0,
                                    ];
                                }

                                $duration = trim((string) ($prescriptionRow['duration'] ?? ''));
                                $days = 1;
                                if ($duration !== '' && preg_match('/(\d+)/', $duration, $m)) {
                                    $days = max((int) $m[1], 1);
                                }

                                $timeSlots = $prescriptionRow['time_slot'] ?? [];
                                if (!is_array($timeSlots)) {
                                    $timeSlots = array_filter([(string) $timeSlots]);
                                } else {
                                    $timeSlots = array_filter($timeSlots);
                                }
                                $slotCount = max(count($timeSlots), 1);

                                $calcQty = $days * $slotCount;
                                $itemsByKey[$key]['prescribed'] += $calcQty;

                                $dosage = trim((string) ($prescriptionRow['dosage'] ?? ''));
                                if ($dosage !== '') {
                                    $itemsByKey[$key]['dosage_list'][$dosage] = true;
                                }

                                if ($duration !== '') {
                                    $itemsByKey[$key]['duration_list'][$duration] = true;
                                }

                                foreach ($timeSlots as $timeSlot) {
                                    $timeSlot = trim((string) $timeSlot);
                                    if ($timeSlot !== '') {
                                        $itemsByKey[$key]['time_slot_list'][$timeSlot] = true;
                                    }
                                }

                                $foodTiming = trim((string) ($prescriptionRow['food_timing'] ?? ''));
                                if ($foodTiming !== '') {
                                    $itemsByKey[$key]['food_timing_list'][$foodTiming] = true;
                                }
                            }

                            foreach ($itemsByKey as $key => $row) {
                                $givenPerItem = (int) ($dispensedBreakdown[$key] ?? 0);
                                $stockAvailable = (int) (($stockByMedicine[$key] ?? 0));
                                $prescribedForItem = (int) ($row['prescribed'] ?? 0);
                                $remainingForItem = max($prescribedForItem - $givenPerItem, 0);

                                $items[] = [
                                    'name' => (string) ($row['name'] ?? $key),
                                    'dosage' => implode(', ', array_keys($row['dosage_list'] ?? [])),
                                    'duration' => implode(', ', array_keys($row['duration_list'] ?? [])),
                                    'time_slot' => implode(', ', array_keys($row['time_slot_list'] ?? [])),
                                    'food_timing' => implode(', ', array_keys($row['food_timing_list'] ?? [])),
                                    'prescribed' => $prescribedForItem,
                                    'given' => $givenPerItem,
                                    'remaining' => $remainingForItem,
                                    'stock' => $stockAvailable,
                                    'dispense_limit' => min($remainingForItem, max($stockAvailable, 0)),
                                ];

                                if ($stockAvailable <= 0 || $stockAvailable < $remainingForItem) {
                                    $hasStockShortage = true;
                                }
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
                                    'time_slot' => '',
                                    'food_timing' => '',
                                    'prescribed' => $prescribedForItem,
                                    'given' => $givenPerItem,
                                    'remaining' => $remainingForItem,
                                    'stock' => $stockAvailable,
                                    'dispense_limit' => min($remainingForItem, max($stockAvailable, 0)),
                                ];

                                if ($stockAvailable <= 0 || $stockAvailable < $remainingForItem) {
                                    $hasStockShortage = true;
                                }
                            }
                        }

                        $encodedAllItems = base64_encode((string) json_encode(array_values($items)));
                    @endphp

                    <div class="col-12">
                        <div class="card border shadow-sm rounded-3 prescription-queue-card overflow-hidden">
                            <!-- Card Header -->
                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 18px;">
                                        <i class="mdi mdi-account text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h5 class="mb-0 fw-bold text-dark">{{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: 'N/A' }}</h5>
                                            <span class="badge bg-light text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-1">
                                                {{ optional($item->patient)->patient_code ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="mdi mdi-doctor me-1 text-secondary"></i>Dr. {{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: (optional($item->doctor)->name ?? 'N/A') }}
                                            <span class="mx-2 text-muted">•</span>
                                            <i class="mdi mdi-clock-outline me-1 text-secondary"></i>{{ $item->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-pill status-{{ $statusName }} px-3 py-1 fw-semibold text-uppercase" style="border-radius: 999px;">
                                        {{ ucfirst($statusName) }}
                                    </span>
                                    @if($isLocked)
                                        <span class="badge bg-danger text-white px-2 py-1" title="Locked after SMS sent">
                                            <i class="mdi mdi-lock me-1"></i>Locked
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if(! $isDispensed)
                                <!-- Form wrapper for dispensing -->
                                <form action="{{ route('pharmacy.prescriptions.dispense', $item->id) }}" method="POST" autocomplete="off" class="js-dispense-card-form">
                                    @csrf
                                    <!-- Card Body -->
                                    <div class="card-body p-4">
                                        <div class="row g-4">
                                            <!-- Left Column: Doctor Prescription (Checked) -->
                                            <div class="col-lg-6 border-end-lg">
                                                <h6 class="text-uppercase text-muted fw-semibold mb-3 small tracking-wider">
                                                    <i class="mdi mdi-clipboard-text-outline me-1 text-primary"></i> Doctor Prescription (Checked)
                                                </h6>
                                                @if(count($items))
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($items as $med)
                                                            <div class="p-3 bg-light rounded-3 border-start border-4 border-primary shadow-xs">
                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                    <span class="fw-bold text-dark fs-6">{{ $med['name'] }}</span>
                                                                    <span class="badge bg-white text-dark border font-monospace">P:{{ $med['prescribed'] }} / G:{{ $med['given'] }} / R:{{ $med['remaining'] }}</span>
                                                                </div>
                                                                @if(!empty($med['dosage']) || !empty($med['duration']) || !empty($med['time_slot']) || !empty($med['food_timing']))
                                                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                                                        @if(!empty($med['dosage']))
                                                                            <span class="badge bg-white text-secondary border px-2 py-1"><i class="mdi mdi-needle me-1 text-info"></i>Dosage: {{ $med['dosage'] }}</span>
                                                                        @endif
                                                                        @if(!empty($med['duration']))
                                                                            <span class="badge bg-white text-secondary border px-2 py-1"><i class="mdi mdi-calendar-range me-1 text-warning"></i>Duration: {{ $med['duration'] }}</span>
                                                                        @endif
                                                                        @if(!empty($med['time_slot']))
                                                                            <span class="badge bg-white text-secondary border px-2 py-1"><i class="mdi mdi-clock-fast me-1 text-success"></i>Time: {{ str_replace('_', ' ', $med['time_slot']) }}</span>
                                                                        @endif
                                                                        @if(!empty($med['food_timing']))
                                                                            <span class="badge bg-white text-secondary border px-2 py-1"><i class="mdi mdi-food-apple me-1 text-primary"></i>Food: {{ str_replace('_', ' ', $med['food_timing']) }}</span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="p-3 bg-light rounded-3 text-dark font-monospace">
                                                        {{ $item->prescription }}
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Right Column: Give Medicines & Note -->
                                            <div class="col-lg-6">
                                                <h6 class="text-uppercase text-muted fw-semibold mb-3 small tracking-wider">
                                                    <i class="mdi mdi-pill me-1 text-primary"></i> Give Medicines
                                                </h6>

                                                @if(count($items))
                                                    <div class="d-flex flex-column gap-3 mb-3">
                                                        @foreach($items as $idx => $med)
                                                            @php
                                                                $canGiveItem = $med['stock'] > 0 && $med['remaining'] > 0;
                                                                $maxGive = $canGiveItem ? min($med['remaining'], $med['stock']) : 0;
                                                            @endphp
                                                            <div class="p-3 border rounded-3 bg-white shadow-xs">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <div class="fw-bold text-dark">{{ $med['name'] }}</div>
                                                                        <div class="small text-muted mt-1">
                                                                            <span>Remaining: <strong>{{ $med['remaining'] }}</strong></span>
                                                                            <span class="mx-1">•</span>
                                                                            <span>Stock: <strong class="{{ $med['stock'] > 0 ? 'text-success' : 'text-danger' }}">{{ $med['stock'] }}</strong></span>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        @if($med['stock'] <= 0)
                                                                            <span class="badge bg-danger text-white">Out of Stock</span>
                                                                        @elseif($med['remaining'] <= 0)
                                                                            <span class="badge bg-success text-white">Given</span>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <input type="hidden" name="medicines[{{ $idx }}][medicine_name]" value="{{ $med['name'] }}">
                                                                <div class="d-flex align-items-center gap-2 mt-2">
                                                                    <label class="small text-muted mb-0 me-1">Dispense Qty:</label>
                                                                    <input type="number"
                                                                           name="medicines[{{ $idx }}][dispense_quantity]"
                                                                           class="form-control form-control-sm js-qty-input"
                                                                           value="{{ $maxGive }}"
                                                                           min="0"
                                                                           max="{{ $maxGive }}"
                                                                           placeholder="0"
                                                                           style="width: 110px;"
                                                                           @disabled($isLocked || !$canGiveItem)>
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-outline-primary js-fill-max-btn"
                                                                            data-max="{{ $maxGive }}"
                                                                            @disabled($isLocked || !$canGiveItem)>
                                                                        Max Qty ({{ $maxGive }})
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="p-3 border rounded-3 bg-white mb-3">
                                                        <div class="small text-muted mb-2">Enter medicine name & quantity manually:</div>
                                                        <div class="row g-2">
                                                            <div class="col-8">
                                                                <input type="text" class="form-control form-control-sm" name="medicine_name" placeholder="Medicine name" required @disabled($isLocked)>
                                                            </div>
                                                            <div class="col-4">
                                                                <input type="number" min="1" class="form-control form-control-sm" name="dispense_quantity" placeholder="Qty" required @disabled($isLocked)>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="mt-3">
                                                    <label class="form-label small text-muted fw-semibold">Pharmacy Note (optional)</label>
                                                    <textarea name="pharmacy_note" rows="2" class="form-control form-control-sm" placeholder="Add optional pharmacy note for patient..." @disabled($isLocked)></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Footer Actions -->
                                    <div class="card-footer bg-light py-3 px-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
                                        <div>
                                            @if($isLocked)
                                                <small class="text-danger fw-semibold d-flex align-items-center gap-1">
                                                    <i class="mdi mdi-information-outline"></i> Prescription is permanently locked after sending SMS.
                                                </small>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-auto">
                                            @can('pharmacy-prescriptions-dispense')
                                                @if($hasStockShortage || $isPending || $isPartial)
                                                    <button type="button" class="btn btn-outline-warning text-dark btn-sm px-3 js-trigger-sms" data-action="{{ route('pharmacy.prescriptions.send-sms', $item->id) }}" @disabled($isLocked) title="{{ $isLocked ? 'Disabled: SMS already sent' : 'Send shortage SMS to patient' }}">
                                                        <i class="mdi mdi-cellphone-message me-1"></i> Send Shortage SMS
                                                    </button>
                                                @endif

                                                <button type="submit" class="btn btn-success btn-gradient-primary btn-sm px-4 fw-semibold js-submit-dispense-btn" @disabled($isLocked)>
                                                    <i class="mdi mdi-pill me-1"></i> {{ $isPartial ? 'Save Remaining Medicine' : 'Add Given / Save Medicine' }}
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </form>
                            @else
                                <!-- Display only if already dispensed -->
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-lg-7 border-end-lg">
                                            <h6 class="text-uppercase text-muted fw-semibold mb-3 small tracking-wider">
                                                <i class="mdi mdi-pill me-1 text-primary"></i> Doctor Prescribed Medicines
                                            </h6>
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($items as $med)
                                                    <div class="p-3 bg-light rounded-3 border-start border-4 border-success shadow-xs">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <span class="fw-bold text-dark fs-6">{{ $med['name'] }}</span>
                                                            <span class="badge bg-white text-success border">Given: {{ $med['given'] }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-lg-5 d-flex align-items-center justify-content-center">
                                            <div class="text-center p-4">
                                                <i class="mdi mdi-check-circle-outline display-4 text-success mb-2"></i>
                                                <h5 class="fw-bold text-dark">Dispensing Completed</h5>
                                                @if($item->dispensed_at)
                                                    <p class="text-muted small mb-0">Dispensed at: {{ $item->dispensed_at->format('d M Y, h:i A') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light py-3 px-4 border-top text-end">
                                    <span class="badge bg-success text-white px-3 py-2 border rounded-pill fs-7">
                                        <i class="mdi mdi-check-circle me-1"></i> Dispensing Completed
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm p-5 text-center text-muted">
                            <i class="mdi mdi-file-document-outline display-4 mb-2 text-secondary"></i>
                            <h5>No prescriptions found</h5>
                            <p class="mb-0">All active doctor prescriptions will appear here in queue format.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{ $prescriptions->links() }}
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
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 10px 10px;
        border: 1px solid #dbe4ee;
        border-radius: 10px;
        background: #ffffff;
        margin-bottom: 8px;
    }

    .give-row:last-child {
        margin-bottom: 0;
    }

    .give-row-content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .give-row .js-give-toggle {
        min-width: 128px;
        align-self: center;
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
    // Fill Max Quantity button handler
    document.addEventListener('click', function (e) {
        const fillBtn = e.target.closest('.js-fill-max-btn');
        if (fillBtn) {
            const maxVal = fillBtn.dataset.max;
            const input = fillBtn.previousElementSibling;
            if (input && maxVal !== undefined) {
                input.value = maxVal;
            }
        }

        const smsBtn = e.target.closest('.js-trigger-sms');
        if (smsBtn) {
            const actionUrl = smsBtn.dataset.action;
            if (!actionUrl || smsBtn.disabled) return;

            if (confirm('Send shortage SMS notification to patient?')) {
                smsBtn.disabled = true;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = @json(csrf_token());
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }
        }
    });

    // Form submit state handler for inline card forms
    document.querySelectorAll('.js-dispense-card-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const submitBtn = form.querySelector('.js-submit-dispense-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Saving...';
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .prescription-queue-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        border-color: #e2e8f0 !important;
    }
    .prescription-queue-card:hover {
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.06) !important;
    }
    @media (min-width: 992px) {
        .border-end-lg {
            border-right: 1px solid #e2e8f0 !important;
        }
    }
    .fs-7 {
        font-size: 0.8rem !important;
    }
    .tracking-wider {
        letter-spacing: 0.05em !important;
    }
</style>
@endpush
