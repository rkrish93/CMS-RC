@extends('layouts.app')

@section('title', 'All Patient Prescriptions')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Notifications -->
    @if(session('success') || session('error'))
        <div class="row mb-3">
            <div class="col-12">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-2 shadow-xs border-0 rounded-3" role="alert">
                        <i class="mdi mdi-check-circle-outline fs-5 me-2"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-2 shadow-xs border-0 rounded-3" role="alert">
                        <i class="mdi mdi-alert-circle-outline fs-5 me-2"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="mdi mdi-pill text-primary fs-3"></i> All Patient Prescriptions
            </h4>
            <p class="text-muted small mb-0">Pharmacist Desk &bull; Table view of all doctor prescriptions, stock availability & dispensing actions.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-light border btn-sm shadow-xs fw-semibold">
                <i class="mdi mdi-refresh me-1"></i> Refresh List
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-xs rounded-3 overflow-hidden bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase text-muted fw-bold small tracking-wider" style="font-size: 11px;">Total Prescriptions</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($summaryStats['total'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar-circle bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="mdi mdi-file-document-multiple-outline fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-xs rounded-3 overflow-hidden bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase text-warning fw-bold small tracking-wider" style="font-size: 11px;">Pending Dispense</div>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($summaryStats['pending'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar-circle bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="mdi mdi-clock-outline fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-xs rounded-3 overflow-hidden bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase text-info fw-bold small tracking-wider" style="font-size: 11px;">Partial Dispense</div>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($summaryStats['partial'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar-circle bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="mdi mdi-progress-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-xs rounded-3 overflow-hidden bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase text-success fw-bold small tracking-wider" style="font-size: 11px;">Completed</div>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($summaryStats['dispensed'] ?? 0) }}</h3>
                    </div>
                    <div class="avatar-circle bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="mdi mdi-check-circle-outline fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="mdi mdi-filter-variant text-primary fs-5"></i> Search & Filter
            </h6>
            @if(!empty($search) || !empty($status) || !empty($doctorId) || !empty($fromDate) || !empty($toDate))
                <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-link text-danger btn-sm p-0 text-decoration-none fw-semibold">
                    <i class="mdi mdi-close-circle me-1"></i> Clear Filters
                </a>
            @endif
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('pharmacy.prescriptions.index') }}" class="row g-3">
                <!-- Search Box -->
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-muted fw-semibold mb-1">Search Keywords</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="mdi mdi-magnify"></i></span>
                        <input type="text" 
                               name="search" 
                               class="form-control form-control-sm border-start-0 ps-0" 
                               placeholder="Patient Name, Code, Phone, Doctor, Medicine..." 
                               value="{{ $search }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted fw-semibold mb-1">Pharmacy Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="" {{ $status === '' ? 'selected' : '' }}>All Statuses</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="dispensed" {{ $status === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                    </select>
                </div>

                <!-- Doctor Filter -->
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted fw-semibold mb-1">Prescribing Doctor</label>
                    <select name="doctor_id" class="form-select form-select-sm">
                        <option value="" {{ empty($doctorId) ? 'selected' : '' }}>All Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ (int)$doctorId === (int)$doc->id ? 'selected' : '' }}>
                                Dr. {{ trim($doc->fname . ' ' . $doc->lname) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- From Date Filter -->
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small text-muted fw-semibold mb-1">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
                </div>

                <!-- To Date Filter -->
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small text-muted fw-semibold mb-1">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
                </div>

                <!-- Per Page & Action Buttons -->
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted mb-0 me-1">Show:</label>
                        <select name="per_page" class="form-select form-select-sm" style="width: 80px;" onchange="this.form.submit()">
                            <option value="10" {{ (int)$perPage === 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ (int)$perPage === 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ (int)$perPage === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ (int)$perPage === 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="small text-muted">per page</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                            <i class="mdi mdi-refresh me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                            <i class="mdi mdi-filter-check me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Simple Data Table View -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="mdi mdi-table text-primary fs-5"></i> Prescriptions List
            </h6>
            <span class="badge bg-light text-secondary border font-monospace">
                Showing {{ $prescriptions->firstItem() ?? 0 }} - {{ $prescriptions->lastItem() ?? 0 }} of {{ $prescriptions->total() }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-prescription-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;"># / Date</th>
                        <th style="width: 220px;">Patient Details</th>
                        <th style="width: 180px;">Doctor</th>
                        <th>Prescribed Medicines & Stock Status</th>
                        <th style="width: 130px;" class="text-center">Status</th>
                        <th style="width: 130px;" class="text-center">Action</th>
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
                            $dispensedBreakdown = is_array($item->dispensed_breakdown ?? null) ? $item->dispensed_breakdown : [];
                            $items = [];
                            $prescriptionRows = is_array($item->prescription_items ?? null) ? $item->prescription_items : [];
                            $hasStockShortage = false;

                            if (!empty($prescriptionRows)) {
                                $itemsByKey = [];
                                foreach ($prescriptionRows as $prescriptionRow) {
                                    $medicineName = trim((string) ($prescriptionRow['medicine_name'] ?? ''));
                                    if ($medicineName === '') continue;

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
                                    if ($dosage !== '') $itemsByKey[$key]['dosage_list'][$dosage] = true;

                                    if ($duration !== '') $itemsByKey[$key]['duration_list'][$duration] = true;

                                    foreach ($timeSlots as $timeSlot) {
                                        $timeSlot = trim((string) $timeSlot);
                                        if ($timeSlot !== '') $itemsByKey[$key]['time_slot_list'][$timeSlot] = true;
                                    }

                                    $foodTiming = trim((string) ($prescriptionRow['food_timing'] ?? ''));
                                    if ($foodTiming !== '') $itemsByKey[$key]['food_timing_list'][$foodTiming] = true;
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
                        @endphp

                        <tr>
                         
                            <td>
                                <span class="fw-bold text-dark font-monospace">#-{{ $item->id }}</span>
                                <div class="small text-muted" style="font-size: 11px;">
                                    {{ $item->created_at->format('d M Y') }}<br>
                                    <span class="text-secondary">{{ $item->created_at->format('h:i A') }}</span>
                                </div>
                            </td>

                            <!-- Patient Info -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 14px;">
                                        <i class="mdi mdi-account"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">
                                            {{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: 'N/A' }}
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                            <span class="badge bg-light text-primary border font-monospace px-1.5 py-0.5" style="font-size: 10px;">
                                                {{ optional($item->patient)->patient_code ?? 'N/A' }}
                                            </span>
                                            @if(optional($item->patient)->phone)
                                                <span class="small text-muted font-monospace" style="font-size: 11px;">
                                                    <i class="mdi mdi-phone me-0.5"></i>{{ optional($item->patient)->phone }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Prescribing Doctor -->
                            <td>
                                <div class="fw-semibold text-dark" style="font-size: 13px;">
                                    <i class="mdi mdi-doctor text-primary me-1"></i>Dr. {{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: 'N/A' }}
                                </div>
                            </td>

                            <!-- Prescribed Medicines & Stock -->
                            <td>
                                @if(count($items))
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($items as $med)
                                            <div class="d-flex align-items-center justify-content-between bg-light rounded-2 px-2 py-1 gap-2">
                                                <div class="text-truncate" style="max-width: 280px;">
                                                    <strong class="text-dark" style="font-size: 12.5px;">{{ $med['name'] }}</strong>
                                                    @if(!empty($med['dosage']))
                                                        <span class="small text-muted ms-1">({{ $med['dosage'] }})</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                    <span class="badge bg-white text-dark border font-monospace px-1.5 py-0.5" style="font-size: 10px;" title="Prescribed / Given / Remaining">
                                                        P:{{ $med['prescribed'] }} | G:{{ $med['given'] }} | R:{{ $med['remaining'] }}
                                                    </span>
                                                    @if($med['stock'] <= 0)
                                                        <span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 10px;">Out of Stock</span>
                                                    @elseif($med['stock'] < $med['remaining'])
                                                        <span class="badge bg-warning text-dark px-1.5 py-0.5" style="font-size: 10px;">Stock: {{ $med['stock'] }}</span>
                                                    @else
                                                        <span class="badge bg-success text-white px-1.5 py-0.5" style="font-size: 10px;">Stock: {{ $med['stock'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="small text-dark font-monospace text-truncate" style="max-width: 320px;">
                                        {{ $item->prescription }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="text-center">
                                <span class="status-pill status-{{ $statusName }} px-2.5 py-1 fw-bold text-uppercase" style="font-size: 11px;">
                                    {{ ucfirst($statusName) }}
                                </span>
                                @if($isLocked)
                                    <div class="mt-1">
                                        <span class="badge bg-danger text-white px-1.5 py-0.5" style="font-size: 10px;" title="Locked after SMS sent">
                                            <i class="mdi mdi-lock me-0.5"></i>Locked
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="text-center">
                                @if(!$isDispensed)
                                    <button type="button" 
                                            class="btn btn-primary btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#dispenseModal{{ $item->id }}">
                                        <i class="mdi mdi-pill"></i> Dispense
                                    </button>
                                @else
                                    <button type="button" 
                                            class="btn btn-outline-success btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#dispenseModal{{ $item->id }}">
                                        <i class="mdi mdi-eye-outline"></i> View Details
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Dispense Modal for row -->
                        <div class="modal fade" id="dispenseModal{{ $item->id }}" tabindex="-1" aria-labelledby="dispenseModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light py-3 px-4 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
                                                <i class="mdi mdi-account"></i>
                                            </div>
                                            <div>
                                                <h5 class="modal-title fw-bold text-dark mb-0" id="dispenseModalLabel{{ $item->id }}">
                                                    {{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: 'N/A' }}
                                                </h5>
                                                <div class="small text-muted">
                                                    Code: <span class="font-monospace fw-bold text-primary">{{ optional($item->patient)->patient_code ?? 'N/A' }}</span> &bull; 
                                                    Dr. {{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: 'N/A' }} &bull; 
                                                    Rx #{{ $item->id }} ({{ $item->created_at->format('d M Y, h:i A') }})
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-auto">
                                            <span class="status-pill status-{{ $statusName }} px-3 py-1 fw-bold text-uppercase">
                                                {{ ucfirst($statusName) }}
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                    </div>

                                    @if(! $isDispensed)
                                        <form action="{{ route('pharmacy.prescriptions.dispense', $item->id) }}" method="POST" autocomplete="off" class="js-dispense-card-form">
                                            @csrf
                                            <div class="modal-body p-4">
                                                <div class="row g-4">
                                                    <!-- Left: Doctor Prescription Items -->
                                                    <div class="col-lg-6 border-end-lg">
                                                        <h6 class="text-uppercase text-muted fw-bold mb-3 small tracking-wider">
                                                            <i class="mdi mdi-clipboard-text-outline me-1 text-primary"></i> Doctor Prescribed Items
                                                        </h6>

                                                        @if(count($items))
                                                            <div class="d-flex flex-column gap-2.5">
                                                                @foreach($items as $med)
                                                                    <div class="p-3 bg-light rounded-3 border-start border-4 {{ $med['stock'] <= 0 ? 'border-danger' : ($med['remaining'] <= 0 ? 'border-success' : 'border-primary') }} shadow-xs">
                                                                        <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
                                                                            <span class="fw-bold text-dark fs-6">{{ $med['name'] }}</span>
                                                                            <span class="badge bg-white text-dark border font-monospace">
                                                                                Prescribed: {{ $med['prescribed'] }} | Given: {{ $med['given'] }} | Rem: {{ $med['remaining'] }}
                                                                            </span>
                                                                        </div>

                                                                        <div class="mt-1 mb-2">
                                                                            @if($med['stock'] <= 0)
                                                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5"><i class="mdi mdi-alert-circle me-1"></i>Stock: Out of stock (0)</span>
                                                                            @elseif($med['stock'] < $med['remaining'])
                                                                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-0.5"><i class="mdi mdi-alert me-1"></i>Stock: Low ({{ $med['stock'] }} available, need {{ $med['remaining'] }})</span>
                                                                            @else
                                                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5"><i class="mdi mdi-check-circle me-1"></i>Stock: Available ({{ $med['stock'] }})</span>
                                                                            @endif
                                                                        </div>

                                                                        @if(!empty($med['dosage']) || !empty($med['duration']) || !empty($med['time_slot']) || !empty($med['food_timing']))
                                                                            <div class="d-flex flex-wrap gap-1.5 mt-2">
                                                                                @if(!empty($med['dosage']))
                                                                                    <span class="badge bg-white text-secondary border px-2 py-1"><i class="mdi mdi-pill me-1 text-info"></i>Dosage: {{ $med['dosage'] }}</span>
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
                                                            <div class="p-3 bg-light rounded-3 text-dark font-monospace border">
                                                                {{ $item->prescription }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Right: Give Medicines & Note -->
                                                    <div class="col-lg-6">
                                                        <h6 class="text-uppercase text-muted fw-bold mb-3 small tracking-wider">
                                                            <i class="mdi mdi-pill me-1 text-primary"></i> Dispense & Give Medicines
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
                                                                                <div class="small text-muted mt-0.5">
                                                                                    <span>Remaining: <strong>{{ $med['remaining'] }}</strong></span>
                                                                                    <span class="mx-1.5">•</span>
                                                                                    <span>Stock: <strong class="{{ $med['stock'] > 0 ? 'text-success' : 'text-danger' }}">{{ $med['stock'] }}</strong></span>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                @if($med['stock'] <= 0)
                                                                                    <span class="badge bg-danger text-white">Out of Stock</span>
                                                                                @elseif($med['remaining'] <= 0)
                                                                                    <span class="badge bg-success text-white"><i class="mdi mdi-check me-0.5"></i>Given</span>
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
                                                            <label class="form-label small text-muted fw-semibold">Pharmacy Instructions / Note (Optional)</label>
                                                            <textarea name="pharmacy_note" rows="2" class="form-control form-control-sm" placeholder="Add optional note for patient..." @disabled($isLocked)></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer bg-light py-3 px-4 border-top d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if($isLocked)
                                                        <small class="text-danger fw-semibold">
                                                            <i class="mdi mdi-lock me-1"></i> Locked after SMS sent.
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-light border btn-sm px-3" data-bs-dismiss="modal">Close</button>
                                                    
                                                    @can('pharmacy-prescriptions-dispense')
                                                        @if($hasStockShortage || $isPending || $isPartial)
                                                            <button type="button" class="btn btn-outline-warning text-dark btn-sm px-3 js-trigger-sms" data-action="{{ route('pharmacy.prescriptions.send-sms', $item->id) }}" @disabled($isLocked)>
                                                                <i class="mdi mdi-cellphone-message me-1"></i> Send Shortage SMS
                                                            </button>
                                                        @endif

                                                        <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold js-submit-dispense-btn" @disabled($isLocked)>
                                                            <i class="mdi mdi-pill me-1"></i> {{ $isPartial ? 'Save Remaining Medicine' : 'Add Given / Save Medicine' }}
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        </form>
                                    @else
                                        <!-- View Mode when already dispensed -->
                                        <div class="modal-body p-4">
                                            <div class="row g-4">
                                                <div class="col-lg-7 border-end-lg">
                                                    <h6 class="text-uppercase text-muted fw-bold mb-3 small tracking-wider">
                                                        <i class="mdi mdi-pill me-1 text-primary"></i> Prescribed & Dispensed Medicines
                                                    </h6>
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($items as $med)
                                                            <div class="p-3 bg-light rounded-3 border-start border-4 border-success shadow-xs">
                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                    <span class="fw-bold text-dark fs-6">{{ $med['name'] }}</span>
                                                                    <span class="badge bg-white text-success border"><i class="mdi mdi-check me-1"></i>Given: {{ $med['given'] }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-lg-5 d-flex align-items-center justify-content-center">
                                                    <div class="text-center p-4">
                                                        <i class="mdi mdi-check-circle-outline display-4 text-success mb-2"></i>
                                                        <h5 class="fw-bold text-dark">Dispensing Fully Completed</h5>
                                                        @if($item->dispensed_at)
                                                            <p class="text-muted small mb-0">Completed at: {{ $item->dispensed_at->format('d M Y, h:i A') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-3 px-4 border-top text-end">
                                            <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="mdi mdi-clipboard-search-outline display-4 mb-2 text-secondary opacity-50 d-block"></i>
                                <h5 class="fw-bold text-dark">No Prescriptions Found</h5>
                                <p class="mb-3 text-muted">No patient prescriptions match your current search or filter criteria.</p>
                                @if(!empty($search) || !empty($status) || !empty($doctorId) || !empty($fromDate) || !empty($toDate))
                                    <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-primary btn-sm px-4 fw-semibold">
                                        <i class="mdi mdi-refresh me-1"></i> Reset All Filters
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination links -->
    <div class="mt-3 d-flex justify-content-end">
        {{ $prescriptions->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-prescription-table thead th {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475467;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 14px;
    }
    .custom-prescription-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-prescription-table tbody tr:hover {
        background-color: #f8fafc;
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
    @media (min-width: 992px) {
        .border-end-lg {
            border-right: 1px solid #e2e8f0 !important;
        }
    }
    .bg-primary-subtle {
        background-color: #eef2ff !important;
    }
    .bg-warning-subtle {
        background-color: #fffbe6 !important;
    }
    .bg-info-subtle {
        background-color: #e6f7ff !important;
    }
    .bg-success-subtle {
        background-color: #f6ffed !important;
    }
    .bg-danger-subtle {
        background-color: #fff2f0 !important;
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
