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

        <form method="GET" class="row g-2 align-items-center">
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
                <button class="btn btn-outline-primary">Search</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle prescription-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient Code</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Prescription</th>
                        <th>Total Qty (P/G/R)</th>
                        <th>Medicine Qty</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $item)
                        @php
                            $statusName = $item->pharmacy_status ?? 'pending';
                            $isDispensed = $statusName === 'dispensed';
                            $isLocked = (bool) ($item->is_locked ?? false);
                            $prescribedQty = (int) ($item->prescribed_quantity ?? 0);
                            $givenQty = (int) ($item->dispensed_quantity ?? 0);
                            $remainingQty = $prescribedQty > 0 ? max($prescribedQty - $givenQty, 0) : 0;
                            $items = [];
                            preg_match_all('/(?:^|[,;\n\r]|\s{1,})([A-Za-z0-9][A-Za-z0-9\s\-\/.\(\)]*?)\s*[-:]\s*(\d+)/u', (string) ($item->prescription ?? ''), $matches, PREG_SET_ORDER);
                            foreach ($matches as $match) {
                                $name = trim((string) ($match[1] ?? ''));
                                $qty = (int) ($match[2] ?? 0);
                                if ($name !== '' && $qty > 0) {
                                    $key = strtolower(preg_replace('/\s+/', '', $name));
                                    $givenPerItem = (int) (is_array($item->dispensed_breakdown ?? null) ? ($item->dispensed_breakdown[$key] ?? 0) : 0);
                                    $stockAvailable = (int) (($stockByMedicine[$key] ?? 0));
                                    $dispenseLimit = min(max($qty - $givenPerItem, 0), max($stockAvailable, 0));
                                    $items[] = [
                                        'name' => $name,
                                        'prescribed' => $qty,
                                        'given' => $givenPerItem,
                                        'remaining' => max($qty - $givenPerItem, 0),
                                        'stock' => $stockAvailable,
                                        'dispense_limit' => $dispenseLimit,
                                    ];
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($item->patient)->patient_code ?? 'N/A' }}</td>
                            <td>
                                {{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: 'N/A' }}
                            </td>
                            <td>{{ optional($item->doctor)->name ?? 'N/A' }}</td>
                            <td class="prescription-cell" style="min-width: 260px">
                                <div class="prescription-text">{{ $item->prescription }}</div>
                            </td>
                            <td>
                                {{ $prescribedQty > 0 ? $prescribedQty : '-' }}/{{ $givenQty }}/{{ $prescribedQty > 0 ? $remainingQty : '-' }}
                            </td>
                            <td style="min-width:260px">
                                @if(count($items))
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($items as $med)
                                            <div class="medicine-line">
                                                <strong>{{ $med['name'] }}</strong>: {{ $med['prescribed'] }}/{{ $med['given'] }}/{{ $med['remaining'] }}
                                                <small class="ms-1 {{ $med['stock'] > 0 ? 'text-muted' : 'text-danger fw-semibold' }}">
                                                    Stock: {{ $med['stock'] }}{{ $med['stock'] <= 0 ? ' (Out of stock)' : '' }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">Use format Name-Qty</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $isDispensed ? 'success' : ($statusName === 'partial' ? 'info' : 'warning') }}">
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
                                        <div class="d-inline-flex flex-column align-items-end gap-2 prescription-actions">
                                            <form action="{{ route('pharmacy.prescriptions.dispense', $item->id) }}" method="POST" class="d-inline-flex gap-1 align-items-center js-dispense-form flex-wrap justify-content-end">
                                                @csrf
                                                @if(count($items))
                                                    <select name="medicine_name"
                                                            class="form-select form-select-sm js-medicine-select"
                                                            style="width:180px"
                                                            @disabled($isLocked)
                                                            required>
                                                        @foreach($items as $index => $med)
                                                            @if($med['remaining'] > 0)
                                                                <option value="{{ $med['name'] }}"
                                                                        data-limit="{{ $med['dispense_limit'] }}"
                                                                        @selected($index === 0)>
                                                                    {{ $med['name'] }} (R: {{ $med['remaining'] }}, S: {{ $med['stock'] }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="text"
                                                           name="medicine_name"
                                                           class="form-control form-control-sm"
                                                           style="width:140px"
                                                           placeholder="Tablet name"
                                                           @disabled($isLocked)
                                                           required>
                                                @endif
                                                <input type="number"
                                                       name="dispense_quantity"
                                                       min="1"
                                                       max="{{ $prescribedQty > 0 ? $remainingQty : 99999 }}"
                                                       value="1"
                                                       class="form-control form-control-sm js-dispense-qty"
                                                       @disabled($isLocked)
                                                       style="width:88px">
                                                <button type="submit" class="btn btn-sm btn-success js-add-given-btn" @disabled($isLocked) title="{{ $isLocked ? 'Disabled: SMS already sent and prescription is locked.' : '' }}">Add Given</button>
                                            </form>

                                            <form action="{{ route('pharmacy.prescriptions.send-sms', $item->id) }}" method="POST" class="d-inline-flex js-sms-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary js-send-sms-btn" @disabled($isLocked) title="{{ $isLocked ? 'Disabled: SMS already sent and prescription is locked.' : '' }}">Send SMS</button>
                                            </form>

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
@endsection

@push('styles')
<style>
    .prescription-table td,
    .prescription-table th {
        vertical-align: top;
    }

    .prescription-text {
        white-space: pre-wrap;
        line-height: 1.35;
    }

    .medicine-line {
        line-height: 1.3;
    }

    .prescription-actions {
        min-width: 290px;
    }

    @media (max-width: 768px) {
        .prescription-actions {
            min-width: 220px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[action*="/pharmacy-prescriptions/"][action*="/dispense"]');

    forms.forEach((form) => {
        const select = form.querySelector('.js-medicine-select');
        const qty = form.querySelector('.js-dispense-qty');

        if (!select || !qty) {
            return;
        }

        const syncMax = () => {
            const selected = select.options[select.selectedIndex];
            const limit = parseInt(selected?.dataset?.limit || '1', 10);
            const safeLimit = Number.isNaN(limit) || limit < 1 ? 1 : limit;
            qty.max = String(safeLimit);

            const current = parseInt(qty.value || '1', 10);
            if (Number.isNaN(current) || current < 1 || current > safeLimit) {
                qty.value = '1';
            }
        };

        syncMax();
        select.addEventListener('change', syncMax);
    });

    const lockFormControls = (form) => {
        if (!form) {
            return;
        }

        const controls = form.querySelectorAll('select, input, button');
        controls.forEach((control) => {
            control.disabled = true;
        });
    };

    document.querySelectorAll('.js-dispense-form').forEach((form) => {
        form.addEventListener('submit', function () {
            lockFormControls(form);
        });
    });

    document.querySelectorAll('.js-sms-form').forEach((smsForm) => {
        smsForm.addEventListener('submit', function () {
            lockFormControls(smsForm);

            const row = smsForm.closest('tr');
            if (!row) {
                return;
            }

            const dispenseForm = row.querySelector('.js-dispense-form');
            lockFormControls(dispenseForm);
        });
    });
});
</script>
@endpush
