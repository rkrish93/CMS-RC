@extends('layouts.app')

@section('title', 'Prescriptions Report')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light me-1">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
    <a href="{{ route('reports.prescriptions.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary me-1">
        <i class="mdi mdi-printer me-1"></i> Print
    </a>
    <a href="{{ route('reports.prescriptions.pdf', request()->query()) }}" class="btn btn-outline-success me-1">
        <i class="mdi mdi-file-pdf-box me-1"></i> PDF
    </a>
    <a href="{{ route('reports.prescriptions.csv', request()->query()) }}" class="btn btn-outline-info">
        <i class="mdi mdi-file-delimited me-1"></i> CSV
    </a>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="mdi mdi-pill fs-24"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 fw-normal fs-14">Total Prescriptions</h6>
                    <h3 class="text-dark mb-0 fw-bold">{{ number_format($summary['total'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="avatar-sm rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="mdi mdi-clock-outline fs-24"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 fw-normal fs-14">Pending Dispensing</h6>
                    <h3 class="text-dark mb-0 fw-bold">{{ number_format($summary['pending'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="avatar-sm rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="mdi mdi-circle-half-full fs-24"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 fw-normal fs-14">Partially Dispensed</h6>
                    <h3 class="text-dark mb-0 fw-bold">{{ number_format($summary['partial'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="avatar-sm rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                    <i class="mdi mdi-check-circle-outline fs-24"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 fw-normal fs-14">Fully Dispensed</h6>
                    <h3 class="text-dark mb-0 fw-bold">{{ number_format($summary['dispensed'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Patient name, code, medicine...">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Doctor</label>
                <select name="doctor_id" class="form-select">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected($doctorId === (int) $doctor->id)>
                            Dr. {{ trim(($doctor->fname ?? '') . ' ' . ($doctor->lname ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Clinical Unit</label>
                <select name="unit_id" class="form-select">
                    <option value="">All Units</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected($unitId === (int) $unit->id)>
                            {{ $unit->unit_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Pharmacy Status</label>
                <select name="pharmacy_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected($pharmacyStatus === 'pending')>Pending</option>
                    <option value="partial" @selected($pharmacyStatus === 'partial')>Partially Dispensed</option>
                    <option value="dispensed" @selected($pharmacyStatus === 'dispensed')>Dispensed</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>
                </div>
            </div>
            <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="mdi mdi-filter-outline me-1"></i> Apply Filters
                </button>
                <a href="{{ route('reports.prescriptions.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="mdi mdi-refresh me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Patient Details</th>
                        <th>Doctor & Unit</th>
                        <th>Prescribed Medicines</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $item)
                        @php
                            $status = $item->pharmacy_status ?? 'pending';
                            $badgeClass = match($status) {
                                'dispensed' => 'bg-success-subtle text-success border-success-subtle',
                                'partial' => 'bg-info-subtle text-info border-info-subtle',
                                default => 'bg-warning-subtle text-warning border-warning-subtle',
                            };
                            $statusLabel = match($status) {
                                'dispensed' => 'Dispensed',
                                'partial' => 'Partially Dispensed',
                                default => 'Pending',
                            };
                            $items = is_array($item->prescription_items) ? $item->prescription_items : [];
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold text-dark">{{ optional($item->created_at)->format('Y-m-d') }}</span>
                                <div class="text-muted fs-12">{{ optional($item->created_at)->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: '-' }}
                                </div>
                                <span class="badge bg-light text-secondary border">
                                    {{ optional($item->patient)->patient_code ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark fw-semibold">
                                    Dr. {{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: '-' }}
                                </div>
                                <div class="text-muted fs-12">
                                    <i class="mdi mdi-hospital-building me-1"></i>{{ optional(optional($item->appointment)->unit)->unit_name ?? '-' }}
                                </div>
                            </td>
                            <td style="max-width: 320px;">
                                @if(count($items) > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($items as $row)
                                            <li class="mb-1 pb-1 border-bottom border-light fs-13">
                                                <i class="mdi mdi-pill text-primary me-1"></i>
                                                <strong class="text-dark">{{ $row['medicine_name'] ?? $row['product_name'] ?? 'Medicine' }}</strong>
                                                @if(!empty($row['dosage']))
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">{{ $row['dosage'] }}</span>
                                                @endif
                                               
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-dark fs-13">{{ $item->prescription ?? '-' }}</span>
                                @endif
                            </td>
                           
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fs-12 border">
                                    {{ $statusLabel }}
                                </span>
                                @if($item->dispensed_at)
                                    <div class="text-muted fs-11 mt-1">
                                        {{ $item->dispensed_at->format('M d, H:i') }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="mdi mdi-file-document-remove-outline fs-48 d-block mb-2 text-secondary"></i>
                                <h6>No prescription report records found matching your filters.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 d-flex justify-content-end">
            {{ $prescriptions->links() }}
        </div>
    </div>
</div>
@endsection
