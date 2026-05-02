@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $stats = [
        ['label' => 'Total Patients', 'value' => $patients ?? 0, 'icon' => 'mdi-account-multiple', 'tone' => 'primary'],
        ['label' => 'Today Appointments', 'value' => $todayAppointments ?? 0, 'icon' => 'mdi-calendar-check', 'tone' => 'success'],
        ['label' => 'Waiting Queue', 'value' => $waiting ?? 0, 'icon' => 'mdi-timer-sand', 'tone' => 'warning'],
        ['label' => 'Completed Today', 'value' => $completed ?? 0, 'icon' => 'mdi-check-circle', 'tone' => 'info'],
    ];
@endphp

<div class="row">
    @foreach($stats as $stat)
        <div class="col-md-6 col-xl-3 grid-margin stretch-card">
            <div class="card stat-card">
                <div class="card-body">
                    <div>
                        <p class="stat-label">{{ $stat['label'] }}</p>
                        <h2 class="stat-value">{{ $stat['value'] }}</h2>
                    </div>
                    <span class="stat-icon text-{{ $stat['tone'] }}">
                        <i class="mdi {{ $stat['icon'] }}"></i>
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
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
</div>

@endsection

@section('scripts')
<script>
const ctx = document.getElementById('appointmentChart');

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
</script>
@endsection

@push('styles')
<style>
    .stat-card .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .stat-label {
        margin-bottom: 6px;
        color: #667085;
        font-size: 13px;
        font-weight: 800;
    }

    .stat-value {
        margin: 0;
        color: #152033;
        font-weight: 900;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #f8fafc;
        font-size: 28px;
    }
</style>
@endpush
