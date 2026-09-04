@extends('layouts.app')

@section('title', 'Medical Stocks Report')

@section('page-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light me-1">
        <i class="mdi mdi-arrow-left me-1"></i> Dashboard
    </a>
    @if(auth()->user()?->hasAnyRole(['Admin', 'Pharmacist']))
        <a href="{{ route('reports.medical-stocks.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary me-1">
            <i class="mdi mdi-printer me-1"></i> Print
        </a>
        <a href="{{ route('reports.medical-stocks.pdf', request()->query()) }}" class="btn btn-outline-success me-1">
            <i class="mdi mdi-file-pdf-box me-1"></i> PDF
        </a>
        <a href="{{ route('reports.medical-stocks.csv', request()->query()) }}" class="btn btn-outline-info">
            <i class="mdi mdi-file-delimited me-1"></i> CSV
        </a>
    @endif
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-13 fw-medium text-uppercase d-block mb-1">Total Stock Items</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_items'] ?? 0) }}</h3>
                        <small class="text-muted">Total Qty: {{ number_format($summary['total_quantity'] ?? 0) }}</small>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="mdi mdi-package-variant-closed fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-13 fw-medium text-uppercase d-block mb-1">Low Stock Warning</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format($summary['low_stock'] ?? 0) }}</h3>
                        <small class="text-muted">Below reorder level</small>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="mdi mdi-alert-outline fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-13 fw-medium text-uppercase d-block mb-1">Out of Stock</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($summary['out_of_stock'] ?? 0) }}</h3>
                        <small class="text-muted">Needs urgent restock</small>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="mdi mdi-close-circle-outline fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-13 fw-medium text-uppercase d-block mb-1">Expiring / Expired</span>
                        <h3 class="fw-bold mb-0 text-info">{{ number_format(($summary['expiring_soon'] ?? 0) + ($summary['expired'] ?? 0)) }}</h3>
                        <small class="text-muted">{{ $summary['expiring_soon'] ?? 0 }} soon | {{ $summary['expired'] ?? 0 }} expired</small>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="mdi mdi-clock-alert-outline fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Search Medicine</label>
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Medicine, generic, batch, code">
            </div>
     
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Active Status</label>
                <select name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected($isActive === '1')>Active</option>
                    <option value="0" @selected($isActive === '0')>Inactive</option>
                </select>
            </div>
           
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary">Filter</button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('reports.medical-stocks.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Medicine & Generic Name</th>
                        <th>Unit</th>
                        <th>Batch No</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Expiry Date</th>
                        <th class="pe-3">Active</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $todayStr = now()->startOfDay()->format('Y-m-d');
                        $soonStr = now()->addDays(30)->endOfDay()->format('Y-m-d');
                    @endphp
                    @forelse($stocks as $stock)
                        @php
                            $reorder = $stock->product?->reorder_level ?? 10;
                            $expStr = $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : null;
                            $isExpired = $expStr && $expStr < $todayStr;
                            $isExpiringSoon = $expStr && $expStr >= $todayStr && $expStr <= $soonStr;
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold text-secondary">{{ $stock->product?->product_code ?? '-' }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $stock->medicine_name }}</div>
                                @if($stock->generic_name)
                                    <small class="text-muted">{{ $stock->generic_name }}</small>
                                @endif
                            </td>
                            <td>{{ $stock->unit ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $stock->batch_no }}</span></td>
                            <td class="fw-bold {{ $stock->quantity <= 0 ? 'text-danger' : ($stock->quantity <= $reorder ? 'text-warning' : 'text-success') }}">
                                {{ number_format($stock->quantity) }}
                            </td>
                            <td>{{ number_format($reorder) }}</td>
                            <td>
                                @if($stock->expiry_date)
                                    <span class="{{ $isExpired ? 'text-danger fw-bold' : ($isExpiringSoon ? 'text-warning fw-bold' : '') }}">
                                        {{ $stock->expiry_date->format('Y-m-d') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                    
                            <td class="pe-3">
                                <span class="badge bg-{{ $stock->is_active ? 'success' : 'secondary' }}">
                                    {{ $stock->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="mdi mdi-package-variant-closed fs-1 text-secondary d-block mb-2"></i>
                                No medical stock records found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($stocks->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $stocks->links() }}
        </div>
    @endif
</div>
@endsection
