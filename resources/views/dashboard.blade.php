@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $user = auth()->user();
    $canViewDashboard = $user->hasRole('Receptionist') || $user->can('dashboard-view');
@endphp

<div class="dashboard-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3 p-4 rounded-3 shadow-sm bg-white">
    <div>
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted mb-0">
            Welcome back, {{ $user->name }}. Role: {{ $user->getRoleNames()->first() ?? 'Unknown' }}
            @if($user->hasAnyRole(['Doctor', 'Nurse', 'Mid wife', 'Midwife']))
                @if($user->unit)
                    | Unit: <strong>{{ $user->unit->unit_name }}</strong>
                @endif
            @endif
        </p>
    </div>

    <div>
        @if($user->hasRole('Doctor'))
            <span class="badge bg-primary">Doctor Dashboard</span>
        @elseif($user->hasRole('Nurse'))
            <span class="badge bg-success">Nurse Dashboard</span>
        @elseif($user->hasRole('Mid wife'))
            <span class="badge bg-info text-white">Mid wife Dashboard</span>
        @elseif($user->hasRole('PHI'))
            <span class="badge bg-info">PHI Dashboard</span>
        @elseif($user->hasRole('Pharmacist'))
            <span class="badge bg-warning text-dark">Pharmacist Dashboard</span>
        @elseif($user->hasRole('Receptionist'))
            <span class="badge bg-purple text-white">Receptionist Dashboard</span>
        @elseif($user->hasRole('Admin'))
            <span class="badge bg-dark">Admin Dashboard</span>
        @else
            <span class="badge bg-secondary">General Dashboard</span>
        @endif
    </div>
</div>

@if($canViewDashboard)
    <div class="row">
        @can('patients-view')
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card stat-card">
                    <div class="card-body">
                        <div>
                            <p class="stat-label">Total Patients</p>
                            <h2 class="stat-value">{{ $patients ?? 0 }}</h2>
                        </div>
                        <span class="stat-icon text-primary">
                            <i class="mdi mdi-account-multiple"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endcan

        @can('menu-users')
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card stat-card">
                    <div class="card-body">
                        <div>
                            <p class="stat-label">Total Users</p>
                            <h2 class="stat-value">{{ $users ?? 0 }}</h2>
                        </div>
                        <span class="stat-icon text-dark">
                            <i class="mdi mdi-account-group"></i>
                        </span>
                    </div>
                </div>
            </div>

    
        @endcan

        @can('appointments-view')
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card stat-card">
                    <div class="card-body">
                        <div>
                            <p class="stat-label">Today Appointments</p>
                            <h2 class="stat-value">{{ $todayAppointments ?? 0 }}</h2>
                        </div>
                        <span class="stat-icon text-success">
                            <i class="mdi mdi-calendar-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endcan

        @can('appointments-view')
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card stat-card">
                    <div class="card-body">
                        <div>
                            <p class="stat-label">Waiting Queue</p>
                            <h2 class="stat-value">{{ $waiting ?? 0 }}</h2>
                        </div>
                        <span class="stat-icon text-warning">
                            <i class="mdi mdi-timer-sand"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endcan

        @can('units-view')
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card stat-card">
                    <div class="card-body">
                        <div>
                            <p class="stat-label">Units</p>
                            <h2 class="stat-value">{{ $units ?? 0 }}</h2>
                        </div>
                        <span class="stat-icon text-secondary">
                            <i class="mdi mdi-office-building"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endcan



        @can('reports-view')
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card stat-card">
                    <div class="card-body">
                        <div>
                            <p class="stat-label">Completed Today</p>
                            <h2 class="stat-value">{{ $completed ?? 0 }}</h2>
                        </div>
                        <span class="stat-icon text-info">
                            <i class="mdi mdi-check-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    @if($user->hasRole('Admin'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Admin Overview</h4>
                <p class="text-muted mb-3">Full system access and management shortcuts.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-dark">Manage Users</a>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Manage Roles</a>
                    <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">Manage Permissions</a>
                </div>
            </div>
        </div>
    @elseif($user->hasRole('Doctor') || $user->hasRole('Admin'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Doctor Quick Actions</h4>
                <p class="text-muted mb-3">These items are visible only to doctors.</p>
                <div class="d-flex flex-wrap gap-2">
                    @can('consultations-view')
                        <a href="{{ route('consultations.index') }}" class="btn btn-outline-primary">View Consultations</a>
                    @endcan
                    @can('appointments-view')
                        <a href="{{ route('appointments.today') }}" class="btn btn-outline-success">Open Today&apos;s Queue</a>
                    @endcan
                </div>
            </div>
        </div>
    @elseif($user->hasRole('Nurse') || $user->hasRole('Admin'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Nurse Tasks</h4>
                <p class="text-muted mb-3">Nurse-specific workflow and patient follow-up.</p>
                <div class="d-flex flex-wrap gap-2">
                    @can('patients-view')
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-success">Patient List</a>
                    @endcan
                    @can('consultations-view')
                        <a href="{{ route('consultations.index') }}" class="btn btn-outline-primary">Review Consultations</a>
                    @endcan
                </div>
            </div>
        </div>
    @elseif($user->hasRole('Mid wife') || $user->hasRole('Nurse') || $user->hasRole('Admin'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Midwife Care</h4>
                <p class="text-muted mb-3">Quick access for maternal care and consultations.</p>
                <div class="d-flex flex-wrap gap-2">
                    @can('patients-view')
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-info">Patient List</a>
                    @endcan
                    @can('consultations-view')
                        <a href="{{ route('consultations.index') }}" class="btn btn-outline-primary">Open Consultations</a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Midwife System Vitals Check --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="card-title mb-1">System Vitals Check</h4>
                        <p class="text-muted mb-0">Summary of recent vitals collected today.</p>
                    </div>
                    @if(auth()->user()->can('vitals-view') || auth()->user()->hasRole('Admin'))
                        <a href="{{ route('vitals.index') }}" class="btn btn-sm btn-primary">View Full Vitals</a>
                    @endif
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded-3 shadow-sm">
                            <small class="text-muted">Avg Temperature (°C)</small>
                            <div class="h3 mb-0">{{ $vitalsSummary['avg_temp'] ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded-3 shadow-sm">
                            <small class="text-muted">Avg Pulse</small>
                            <div class="h3 mb-0">{{ $vitalsSummary['avg_pulse'] ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded-3 shadow-sm">
                            <small class="text-muted">Alerts Today</small>
                            <div class="h3 mb-0 text-danger">{{ $vitalsSummary['alerts'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-2">Latest Vitals</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>BP</th>
                                <th>Temp (°C)</th>
                                <th>Pulse</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestVitals ?? [] as $lv)
                                <tr>
                                    <td>{{ $lv['patient'] }}</td>
                                    <td>{{ $lv['bp'] ?? '—' }}</td>
                                    <td>{{ $lv['temp'] ?? '—' }}</td>
                                    <td>{{ $lv['pulse'] ?? '—' }}</td>
                                    <td>{{ $lv['time'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No vitals available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($user->hasRole('PHI'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">PHI Summary</h4>
                <p class="text-muted mb-3">Public health data and unit monitoring links.</p>
                <div class="d-flex flex-wrap gap-2">
                    @can('analytics-view')
                        <a href="#" class="btn btn-outline-info">View Analytics</a>
                    @endcan
                    @can('units-view')
                        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Manage Units</a>
                    @endcan
                </div>
            </div>
        </div>
    @elseif($user->hasRole('Pharmacist') || $user->hasRole('Admin'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Pharmacist Panel</h4>
                <p class="text-muted mb-3">Medication and consultation summary views.</p>
                <div class="d-flex flex-wrap gap-2">
                    @can('consultations-view')
                        <a href="{{ route('consultations.index') }}" class="btn btn-outline-warning">Consultation List</a>
                    @endcan
                    @can('patients-view')
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-success">Patient Records</a>
                    @endcan
                    @can('pharmacy-stocks-view')
                        <a href="{{ route('pharmacy-stocks.index') }}" class="btn btn-outline-primary">Manage Pharmacy Stock</a>
                    @endcan
                    @can('pharmacy-prescriptions-view')
                        <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-outline-dark">Prescription View</a>
                    @endcan
                </div>
            </div>
        </div>

        @can('pharmacy-dashboard-view')
            <div class="card mb-4">
                <div class="card-body">
                    @if(($newPrescriptionNotificationCount ?? 0) > 0)
                        <div class="alert alert-warning d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <strong>Prescription Notification:</strong>
                                {{ $newPrescriptionNotificationCount }} new doctor prescription(s) in the last 6 hours.
                            </div>
                            @can('pharmacy-prescriptions-view')
                                <a href="{{ route('pharmacy.prescriptions.index') }}" class="btn btn-sm btn-dark">Open Prescriptions</a>
                            @endcan
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted">Total Stock Items</small>
                                <div class="h3 mb-0">{{ $pharmacySummary['total_items'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted">Low Stock Items</small>
                                <div class="h3 mb-0 text-danger">{{ $pharmacySummary['low_stock'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted">Today Prescriptions</small>
                                <div class="h3 mb-0 text-primary">{{ $pharmacySummary['active_prescriptions'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <h6 class="mb-2">Low Stock Medicines</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Medicine</th>
                                            <th>Qty</th>
                                            <th>Reorder</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lowStocks ?? [] as $stock)
                                            <tr>
                                                <td>{{ $stock->medicine_name }}</td>
                                                <td>{{ $stock->quantity }}</td>
                                                <td>{{ $stock->reorder_level }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No low-stock items.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h6 class="mb-2">Latest Prescriptions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient Code</th>
                                            <th>Doctor</th>
                                            <th>Qty (P/G/R)</th>
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
                                                <td>{{ $consult->created_at->format('Y-m-d') }}</td>
                                                <td>{{ optional($consult->patient)->patient_code ?? 'N/A' }}</td>
                                                <td>{{ optional($consult->doctor)->name ?? 'N/A' }}</td>
                                                <td>{{ $prescribedQty > 0 ? $prescribedQty : '-' }}/{{ $givenQty }}/{{ $prescribedQty > 0 ? $remainingQty : '-' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $isDispensed ? 'success' : ($statusName === 'partial' ? 'info' : 'warning') }}">
                                                        {{ ucfirst($statusName) }}
                                                    </span>
                                                </td>
                                                <td>{{ \Illuminate\Support\Str::limit($consult->prescription, 40) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No prescriptions recorded.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    @elseif($user->hasRole('Receptionist') || $user->hasRole('Admin'))
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Receptionist Dashboard</h4>
                <p class="text-muted mb-3">Quick access for booking and patient registration.</p>
                <div class="d-flex flex-wrap gap-2">
                    @can('patients-create')
                        <a href="{{ route('patients.create') }}" class="btn btn-outline-primary">Add Patient</a>
                    @endcan
                    @can('appointments-create')
                        <a href="{{ route('appointments.create') }}" class="btn btn-outline-success">New Appointment</a>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        @can('appointments-view')
            <div class="col-lg-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="card-title mb-1">Weekly Appointments</h4>
                                <p class="text-muted mb-0">Appointment activity across the week.</p>
                            </div>
                        </div>
                        <canvas id="appointmentChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        @endcan

        @can('appointments-view')
            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="card-title mb-1">Today's Queue</h4>
                                <p class="text-muted mb-0">Current appointment flow.</p>
                            </div>
                            <a href="{{ route('appointments.today') }}" class="btn btn-sm btn-light">Open Queue</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todayQueue as $q)
                                        <tr>
                                            <td class="fw-semibold">{{ $q->patient->first_name ?? 'N/A' }}</td>
                                            <td>{{ $q->appointment_time ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $q->status === 'completed' ? 'success' : ($q->status === 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $q->status ?? 'pending')) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No appointments in queue.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@else
    <div class="alert alert-warning">
        You do not have permission to view dashboard details. Please contact your administrator.
    </div>
@endif

@endsection

@section('scripts')
<script>
const ctx = document.getElementById('appointmentChart');

if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDays ?? ['Mon','Tue','Wed','Thu','Fri','Sat']) !!},
            datasets: [{
                label: 'Appointments',
                data: {!! json_encode($chartData ?? [10,20,15,30,25,18]) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#eef2f7'
                    }
                },
                x: {
                    grid: {
                        display: false
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
    .dashboard-header {
        border: 1px solid #e8edf2;
        background: #ffffff;
    }

    .dashboard-header h2 {
        font-size: 2rem;
        font-weight: 700;
    }

    .dashboard-header p {
        margin-bottom: 0;
    }

    .stat-card {
        border: none;
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
        background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(249,250,251,0.99) 100%);
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        min-height: 130px;
    }

    .stat-label {
        margin-bottom: 8px;
        color: #67748e;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .stat-value {
        margin: 0;
        color: #111827;
        font-size: 2.25rem;
        font-weight: 800;
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: rgba(248, 250, 252, 0.95);
        font-size: 28px;
        box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    .card-title {
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .card-body p.text-muted {
        font-size: 0.95rem;
    }

    .card.mb-4 {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 18px 70px rgba(15, 23, 42, 0.05);
    }

    .table-responsive {
        border-radius: 1rem;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table td, .table th {
        vertical-align: middle;
        border-top: 0;
        padding: 1rem 1rem;
    }

    .badge.bg-purple {
        background-color: #7c3aed;
    }

    .badge.bg-teal {
        background-color: #0f766e;
    }
</style>
@endpush
