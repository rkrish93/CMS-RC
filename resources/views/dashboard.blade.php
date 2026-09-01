@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $user = auth()->user();
    $canViewDashboard = $user->hasRole('Receptionist') || $user->can('dashboard-view');
@endphp

<!-- Modern Hero / Welcome Header -->
<div class="dashboard-hero card border-0 mb-4 overflow-hidden shadow-sm">
    <div class="card-body p-4 position-relative">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-avatar flex-shrink-0">
                    <span class="avatar-text">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0 text-dark fs-5">Welcome back, {{ $user->name }} 👋</h4>
                        @php
                            $roleName = $user->getRoleNames()->first() ?? 'User';
                            $roleBadgeClass = match($roleName) {
                                'Doctor' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                'Nurse' => 'bg-success-subtle text-success border border-success-subtle',
                                'Pharmacist' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                'Admin' => 'bg-dark text-white',
                                'Receptionist' => 'bg-purple-subtle text-purple border border-purple-subtle',
                                'PHI' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                            };
                        @endphp
                        <span class="badge rounded-pill px-2.5 py-1 fs-12 fw-semibold {{ $roleBadgeClass }}">
                            <i class="mdi mdi-shield-account me-1"></i>{{ $roleName }} Dashboard
                        </span>
                    </div>
                    <p class="text-muted mb-0 fs-13">
                        @if($user->hasAnyRole(['Doctor', 'Nurse', 'Mid wife', 'Midwife']) && $user->unit)
                            Unit: <strong class="text-dark">{{ $user->unit->unit_name }}</strong> &bull;
                        @endif
                        Clinic Management System &bull; {{ date('l, F j, Y') }}
                    </p>
                </div>
            </div>

            @if($user?->can('appointments-view') || $user?->can('pharmacy-prescriptions-view') || $user?->hasAnyRole(['Receptionist', 'Admin', 'Doctor', 'Nurse', 'Mid wife', 'Midwife', 'Pharmacist']))
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('patient.flow.scanner') }}" class="btn btn-primary shadow-sm rounded-3 d-inline-flex align-items-center gap-2 fw-semibold px-4 py-2">
                        <i class="mdi mdi-qrcode-scan fs-5"></i>
                        <span>Today Queue</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@if($canViewDashboard)
    <!-- Dynamic Stat Cards -->
    <div class="row g-3 mb-4">
        @can('patients-view')
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2 primary p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label mb-1">Total Patients</p>
                            <h3 class="stat-value mb-0">{{ number_format($patients ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="mdi mdi-account-group-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('menu-users')
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2 dark p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label mb-1">Total System Users</p>
                            <h3 class="stat-value mb-0">{{ number_format($users ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="mdi mdi-badge-account-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('appointments-view')
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2 success p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label mb-1">Today Appointments</p>
                            <h3 class="stat-value mb-0">{{ number_format($todayAppointments ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="mdi mdi-calendar-check-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('appointments-view')
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2 warning p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label mb-1">Waiting Queue</p>
                            <h3 class="stat-value mb-0">{{ number_format($waiting ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="mdi mdi-account-clock-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('units-view')
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2 purple p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label mb-1">Active Units</p>
                            <h3 class="stat-value mb-0">{{ number_format($units ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="mdi mdi-office-building-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('reports-view')
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2 info p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label mb-1">Completed Today</p>
                            <h3 class="stat-value mb-0">{{ number_format($completed ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    <!-- Role Specific Action Hub -->
    @if($user->hasRole('Admin'))
        <div class="action-hub-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Admin Quick Overview & Shortcut Panel</h5>
                    <p class="text-muted fs-14 mb-0">System administration and user role configurations.</p>
                </div>
                <span class="badge bg-dark rounded-pill px-3 py-2">System Admin</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('users.index') }}" class="action-btn-pill action-btn-dark">
                    <i class="mdi mdi-account-cog-outline"></i> Manage Users
                </a>
                <a href="{{ route('roles.index') }}" class="action-btn-pill action-btn-primary">
                    <i class="mdi mdi-shield-key-outline"></i> Manage Roles
                </a>
                <a href="{{ route('permissions.index') }}" class="action-btn-pill action-btn-purple">
                    <i class="mdi mdi-lock-pattern"></i> Manage Permissions
                </a>
            </div>
        </div>

    @elseif($user->hasRole('Doctor'))
        <div class="action-hub-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Doctor Quick Actions</h5>
                    <p class="text-muted fs-14 mb-0">Direct access to patient consultations and clinical queue.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">Doctor Panel</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('consultations-view')
                    <a href="{{ route('consultations.index') }}" class="action-btn-pill action-btn-primary">
                        <i class="mdi mdi-stethoscope"></i> View Consultations
                    </a>
                @endcan
                @can('appointments-view')
                    <a href="{{ route('appointments.today') }}" class="action-btn-pill action-btn-success">
                        <i class="mdi mdi-calendar-today"></i> Open Today's Queue
                    </a>
                @endcan
            </div>
        </div>

    @elseif($user->hasAnyRole(['Nurse', 'Mid wife', 'Midwife']))
        <div class="action-hub-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Nurse & Midwife Portal</h5>
                    <p class="text-muted fs-14 mb-0">Quick access for patient care, vitals collection, and queue management.</p>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">Clinical Care</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('patient.flow.scanner') }}" class="action-btn-pill action-btn-dark">
                    <i class="mdi mdi-account-clock-outline"></i> Today Queue
                </a>
                @can('vitals-view')
                    <a href="{{ route('vitals.index') }}" class="action-btn-pill action-btn-primary">
                        <i class="mdi mdi-heart-pulse"></i> My Vitals List
                    </a>
                @endcan
                @can('patients-view')
                    <a href="{{ route('patients.index') }}" class="action-btn-pill action-btn-success">
                        <i class="mdi mdi-account-multiple-outline"></i> Patient List
                    </a>
                @endcan
            </div>
        </div>

        <!-- System Vitals Check Card -->
        <div class="dashboard-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">System Vitals Overview</h5>
                    <p class="text-muted fs-14 mb-0">Summary of recent patient vitals recorded today.</p>
                </div>
                @if(auth()->user()->can('vitals-view') || auth()->user()->hasRole('Admin'))
                    <a href="{{ route('vitals.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        View Full Vitals <i class="mdi mdi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>



            <h6 class="fw-bold mb-3 text-dark fs-14 text-uppercase tracking-wider">Latest Vitals Records</h6>
            <div class="table-responsive rounded-3 border">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>BP</th>
                            <th>Temp (°C)</th>
                            <th>Pulse</th>
                            <th>SpO₂ (%)</th>
                            <th>Sugar (mg/dL)</th>
                            <th>Wt / Ht / BMI</th>
                            <th>Resp Rate</th>
                            <th>Recorded Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestVitals ?? [] as $lv)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $lv['patient'] }}</td>
                                <td><span class="badge bg-light text-dark border px-2 py-1">{{ $lv['bp'] ?? '—' }}</span></td>
                                <td>{{ $lv['temp'] ? $lv['temp'] . ' °C' : '—' }}</td>
                                <td>{{ $lv['pulse'] ? $lv['pulse'] . ' bpm' : '—' }}</td>
                                <td>
                                    @if(isset($lv['spo2']) && $lv['spo2'])
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $lv['spo2'] }}%</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ isset($lv['sugar']) && $lv['sugar'] ? $lv['sugar'] . ' mg/dL' : '—' }}</td>
                                <td>
                                    @php
                                        $wtHtBmi = [];
                                        if (isset($lv['weight']) && $lv['weight']) $wtHtBmi[] = $lv['weight'] . 'kg';
                                        if (isset($lv['height']) && $lv['height']) $wtHtBmi[] = $lv['height'] . 'cm';
                                        if (isset($lv['bmi']) && $lv['bmi']) $wtHtBmi[] = 'BMI ' . $lv['bmi'];
                                    @endphp
                                    {{ count($wtHtBmi) > 0 ? implode(' / ', $wtHtBmi) : '—' }}
                                </td>
                                <td>{{ isset($lv['resp_rate']) && $lv['resp_rate'] ? $lv['resp_rate'] . ' /min' : '—' }}</td>
                                <td class="text-muted fs-13">{{ $lv['time'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No vitals data recorded today.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($user->hasRole('PHI'))
        <div class="action-hub-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">PHI Public Health Panel</h5>
                    <p class="text-muted fs-14 mb-0">Public health data monitoring and facility units overview.</p>
                </div>
                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2">PHI Hub</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('analytics-view')
                    <a href="#" class="action-btn-pill action-btn-purple">
                        <i class="mdi mdi-chart-box-outline"></i> View Analytics
                    </a>
                @endcan
                @can('units-view')
                    <a href="{{ route('units.index') }}" class="action-btn-pill action-btn-dark">
                        <i class="mdi mdi-office-building-cog-outline"></i> Manage Units
                    </a>
                @endcan
            </div>
        </div>

    @elseif($user->hasRole('Pharmacist') || $user->hasRole('Admin'))
        <div class="action-hub-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Pharmacist Operations Panel</h5>
                    <p class="text-muted fs-14 mb-0">Medication distribution, stock inventory, and consultation prescriptions.</p>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2">
                    <i class="mdi mdi-pill me-1"></i>Pharmacy Operations
                </span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('consultations-view')
                    <a href="{{ route('consultations.index') }}" class="action-btn-pill action-btn-warning">
                        <i class="mdi mdi-clipboard-list-outline"></i> Consultation List
                    </a>
                @endcan
                @can('patients-view')
                    <a href="{{ route('patients.index') }}" class="action-btn-pill action-btn-success">
                        <i class="mdi mdi-folder-account-outline"></i> Patient Records
                    </a>
                @endcan
                @can('pharmacy-stocks-view')
                    <a href="{{ route('pharmacy-stocks.index') }}" class="action-btn-pill action-btn-primary">
                        <i class="mdi mdi-package-variant-closed"></i> Manage Pharmacy Stock
                    </a>
                @endcan
                @can('pharmacy-prescriptions-view')
                    <a href="{{ route('pharmacy.prescriptions.index') }}" class="action-btn-pill action-btn-dark">
                        <i class="mdi mdi-rx"></i> Prescription View
                    </a>
                @endcan
            </div>
        </div>

        @can('pharmacy-dashboard-view')
            <div class="dashboard-card p-4 mb-4">
                @if(($newPrescriptionNotificationCount ?? 0) > 0)
                    <div class="alert alert-warning border border-warning-subtle rounded-3 d-flex align-items-center justify-content-between mb-4 p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-circle bg-warning text-dark fs-5">
                                <i class="mdi mdi-bell-ring-outline"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark mb-0">New Prescriptions Alert</strong>
                                <span class="fs-14 text-muted">{{ $newPrescriptionNotificationCount }} new prescription(s) issued by doctors in the last 6 hours.</span>
                            </div>
                        </div>
                        @can('pharmacy-prescriptions-view')
                            <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">
                                View Prescriptions <i class="mdi mdi-arrow-right ms-1"></i>
                            </a>
                        @endcan
                    </div>
                @endif

                <!-- Pharmacy KPI Summary -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fs-13 d-block mb-1 fw-semibold">Total Stock Items</span>
                                <span class="fs-3 fw-bold text-dark">{{ number_format($pharmacySummary['total_items'] ?? 0) }}</span>
                            </div>
                            <div class="p-3 rounded-3 bg-primary-subtle text-primary fs-4">
                                <i class="mdi mdi-pill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fs-13 d-block mb-1 fw-semibold">Low Stock Items</span>
                                <span class="fs-3 fw-bold text-danger">{{ number_format($pharmacySummary['low_stock'] ?? 0) }}</span>
                            </div>
                            <div class="p-3 rounded-3 bg-danger-subtle text-danger fs-4">
                                <i class="mdi mdi-alert-outline"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fs-13 d-block mb-1 fw-semibold">Today Prescriptions</span>
                                <span class="fs-3 fw-bold text-success">{{ number_format($pharmacySummary['active_prescriptions'] ?? 0) }}</span>
                            </div>
                            <div class="p-3 rounded-3 bg-success-subtle text-success fs-4">
                                <i class="mdi mdi-script-text-outline"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock & Latest Prescriptions Split -->
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0 text-dark fs-15">
                                <i class="mdi mdi-alert-circle text-danger me-1"></i> Low Stock Medicines
                            </h6>
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 fs-12">Action Needed</span>
                        </div>
                        <div class="table-responsive rounded-3 border">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Medicine Name</th>
                                        <th class="text-center">Current Qty</th>
                                        <th class="text-center">Reorder Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lowStocks ?? [] as $stock)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $stock->medicine_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">
                                                    {{ $stock->quantity }}
                                                </span>
                                            </td>
                                            <td class="text-center text-muted fw-semibold">{{ $stock->reorder_level }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="mdi mdi-check-circle-outline text-success fs-4 d-block mb-1"></i>
                                                All stock levels are healthy.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0 text-dark fs-15">
                                <i class="mdi mdi-history me-1 text-primary"></i> Latest Prescriptions Activity
                            </h6>
                            @can('pharmacy-prescriptions-view')
                                <a href="{{ route('pharmacy.prescriptions.index') }}" class="fs-13 text-primary text-decoration-none fw-semibold">
                                    View All <i class="mdi mdi-chevron-right"></i>
                                </a>
                            @endcan
                        </div>
                        <div class="table-responsive rounded-3 border">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient Code</th>
                                        <th>Doctor</th>
                                        <th class="text-center">Qty (P/G/R)</th>
                                        <th>Status</th>
                                        <th>Prescription</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingPrescriptions ?? [] as $consult)
                                        @php
                                            $statusName = $consult->pharmacy_status ?? 'pending';
                                            $isDispensed = $statusName === 'dispensed';
                                            $prescribedQty = (int) ($consult->prescribed_quantity ?? 0);
                                            $givenQty = (int) ($consult->dispensed_quantity ?? 0);
                                            $remainingQty = $prescribedQty > 0 ? max($prescribedQty - $givenQty, 0) : 0;
                                        @endphp
                                        <tr>
                                            <td class="text-muted fs-13">{{ $consult->created_at->format('Y-m-d') }}</td>
                                            <td><span class="badge bg-light text-dark border font-monospace">{{ optional($consult->patient)->patient_code ?? 'N/A' }}</span></td>
                                            <td class="fw-semibold text-dark">{{ optional($consult->doctor)->name ?? 'N/A' }}</td>
                                            <td class="text-center font-monospace fs-13">
                                                {{ $prescribedQty > 0 ? $prescribedQty : '-' }}/{{ $givenQty }}/{{ $prescribedQty > 0 ? $remainingQty : '-' }}
                                            </td>
                                            <td>
                                                @if($isDispensed)
                                                    <span class="status-pill status-dispensed">
                                                        <span class="status-pill-dot"></span> Dispensed
                                                    </span>
                                                @elseif($statusName === 'partial')
                                                    <span class="status-pill status-partial">
                                                        <span class="status-pill-dot"></span> Partial
                                                    </span>
                                                @else
                                                    <span class="status-pill status-pending">
                                                        <span class="status-pill-dot"></span> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-muted fs-13" title="{{ $consult->prescription }}">
                                                {{ \Illuminate\Support\Str::limit($consult->prescription, 30) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No recent prescriptions recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

    @elseif($user->hasRole('Receptionist') || $user->hasRole('Admin'))
        <div class="action-hub-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Reception Desk Overview</h5>
                    <p class="text-muted fs-14 mb-0">Quick actions for patient registration and queue scheduling.</p>
                </div>
                <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-3 py-2">Reception Hub</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('patients-create')
                    <a href="{{ route('patients.create') }}" class="action-btn-pill action-btn-primary">
                        <i class="mdi mdi-account-plus-outline"></i> Add New Patient
                    </a>
                @endcan
                @can('appointments-create')
                    <a href="{{ route('appointments.create') }}" class="action-btn-pill action-btn-success">
                        <i class="mdi mdi-calendar-plus"></i> New Appointment
                    </a>
                @endcan
            </div>
        </div>
    @endif

    <!-- Analytics & Queue Grid -->
    <div class="row g-4">
        @can('appointments-view')
            <div class="col-lg-7">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">Today's Appointments Activity</h5>
                            <p class="text-muted fs-14 mb-0">Hourly appointment distribution for today (09:00 AM - 03:00 PM).</p>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-12">
                            <i class="mdi mdi-chart-line me-1"></i> Live Metrics
                        </span>
                    </div>
                    <div class="position-relative" style="height: 260px;">
                        <canvas id="appointmentChart"></canvas>
                    </div>
                </div>
            </div>
        @endcan

        @can('appointments-view')
            <div class="col-lg-5">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">Today's Appointment Queue</h5>
                            <p class="text-muted fs-14 mb-0">Live flow of patient appointments today.</p>
                        </div>
                        <a href="{{ route('appointments.today') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            Open Queue <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayQueue as $q)
                                    <tr>
                                        <td class="fw-semibold text-dark">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-sm-circle bg-light text-primary fw-bold fs-12 d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px;">
                                                    {{ strtoupper(substr($q->patient->first_name ?? 'P', 0, 1)) }}
                                                </div>
                                                <span>{{ $q->patient->first_name ?? 'N/A' }} {{ $q->patient->last_name ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-muted fs-13">{{ $q->appointment_time ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $queueStatus = App\Enums\AppointmentStatus::fromValue($q->status) ?? App\Enums\AppointmentStatus::SCHEDULED;
                                            @endphp
                                            <span class="badge bg-{{ $queueStatus->getBadgeColor() }}-subtle text-{{ $queueStatus->getBadgeColor() }} border border-{{ $queueStatus->getBadgeColor() }}-subtle rounded-pill px-2.5 py-1">
                                                {{ $queueStatus->getLabel() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="mdi mdi-calendar-blank-outline fs-4 d-block mb-1"></i>
                                            No appointments scheduled in queue for today.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@else
    <div class="alert alert-warning border border-warning-subtle rounded-3 p-4 text-center">
        <i class="mdi mdi-shield-alert-outline fs-1 text-warning d-block mb-2"></i>
        <h5 class="fw-bold text-dark">Dashboard Access Restricted</h5>
        <p class="text-muted mb-0">You do not have permission to view dashboard details. Please contact your system administrator.</p>
    </div>
@endif

@endsection

@section('scripts')
<script>
const ctx = document.getElementById('appointmentChart');

if (ctx) {
    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDays ?? ['Mon','Tue','Wed','Thu','Fri','Sat']) !!},
            datasets: [{
                label: 'Appointments',
                data: {!! json_encode($chartData ?? [0,0,0,0,0,0]) !!},
                borderColor: '#2563eb',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 12 },
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}
</script>
@endsection

@push('styles')
<style>
    /* CMS Dashboard Custom Aesthetic Design System */
    .dashboard-hero {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }

    .hero-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #2563eb;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        box-shadow: none;
    }

    .stat-card-v2 {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
    }

    .stat-card-v2:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    .stat-card-v2 .stat-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-card-v2.primary .stat-icon-wrapper {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
    }

    .stat-card-v2.success .stat-icon-wrapper {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-card-v2.warning .stat-icon-wrapper {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-card-v2.info .stat-icon-wrapper {
        background: rgba(6, 182, 212, 0.1);
        color: #06b6d4;
    }

    .stat-card-v2.purple .stat-icon-wrapper {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .stat-card-v2.dark .stat-icon-wrapper {
        background: rgba(30, 41, 59, 0.1);
        color: #1e293b;
    }

    .stat-card-v2 .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }

    .stat-card-v2 .stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }

    /* Action Hub Cards & Pill Buttons */
    .action-hub-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
    }

    .action-btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .action-btn-pill:hover {
        transform: translateY(-2px);
    }

    .action-btn-primary {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .action-btn-primary:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .action-btn-success {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .action-btn-success:hover {
        background: #10b981;
        color: #ffffff;
        border-color: #10b981;
    }

    .action-btn-warning {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .action-btn-warning:hover {
        background: #f59e0b;
        color: #ffffff;
        border-color: #f59e0b;
    }

    .action-btn-dark {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .action-btn-dark:hover {
        background: #1e293b;
        color: #ffffff;
        border-color: #1e293b;
    }

    .action-btn-purple {
        background: #f5f3ff;
        color: #6d28d9;
        border: 1px solid #ddd6fe;
    }
    .action-btn-purple:hover {
        background: #7c3aed;
        color: #ffffff;
        border-color: #7c3aed;
    }

    /* Dashboard Cards */
    .dashboard-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
    }

    /* Table Styles */
    .custom-table {
        margin-bottom: 0;
    }
    .custom-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        color: #1e293b;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }
    .custom-table tbody tr:hover td {
        background: #f8fafc;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
    }
    .status-pill-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-dispensed {
        background: #dcfce7;
        color: #15803d;
    }
    .status-dispensed .status-pill-dot { background: #22c55e; }

    .status-partial {
        background: #e0f2fe;
        color: #0369a1;
    }
    .status-partial .status-pill-dot { background: #0ea5e9; }

    .status-pending {
        background: #fef3c7;
        color: #b45309;
    }
    .status-pending .status-pill-dot { background: #f59e0b; }

    .fs-12 { font-size: 0.75rem !important; }
    .fs-13 { font-size: 0.8125rem !important; }
    .fs-14 { font-size: 0.875rem !important; }
    .fs-15 { font-size: 0.9375rem !important; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-purple-subtle { background-color: #f3e8ff !important; }
    .text-purple { color: #7e22ce !important; }
    .border-purple-subtle { border-color: #d8b4fe !important; }

    .bg-primary-subtle { background-color: #eff6ff !important; }
    .bg-success-subtle { background-color: #ecfdf5 !important; }
    .bg-warning-subtle { background-color: #fffbeb !important; }
    .bg-danger-subtle { background-color: #fef2f2 !important; }
    .bg-info-subtle { background-color: #ecfeff !important; }
</style>
@endpush
