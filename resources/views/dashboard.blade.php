@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="row">

    <!-- Total Patients -->
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <h6>Total Patients</h6>
                    <h2>{{ $patients }}</h2>
                </div>
                <i class="mdi mdi-account-multiple text-primary" style="font-size:40px;"></i>
            </div>
        </div>
    </div>

    <!-- Today Appointments -->
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <h6>Today Appointments</h6>
                    <h2>{{ $todayAppointments }}</h2>
                </div>
                <i class="mdi mdi-calendar-check text-success" style="font-size:40px;"></i>
            </div>
        </div>
    </div>

    <!-- Waiting Queue -->
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <h6>Waiting Queue</h6>
                    <h2>{{ $waiting }}</h2>
                </div>
                <i class="mdi mdi-timer-sand text-warning" style="font-size:40px;"></i>
            </div>
        </div>
    </div>

    <!-- Completed -->
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <h6>Completed Today</h6>
                    <h2>{{ $completed }}</h2>
                </div>
                <i class="mdi mdi-check-circle text-info" style="font-size:40px;"></i>
            </div>
        </div>
    </div>

</div>

<!-- CHART + QUEUE -->
<div class="row mt-4">

    <!-- Chart -->
    <div class="col-md-7">
        <div class="card p-3">
            <h5>Weekly Appointments</h5>
            <canvas id="appointmentChart"></canvas>
        </div>
    </div>

    <!-- Today Queue -->
    <div class="col-md-5">
        <div class="card p-3">
            <h5>Today's Queue</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayQueue as $q)
                    <tr>
                        <td>{{ $q->patient->first_name }}</td>
                        <td>{{ $q->appointment_time }}</td>
                        <td>
                            <span class="badge
                                @if($q->status=='pending') bg-warning
                                @elseif($q->status=='completed') bg-success
                                @else bg-danger @endif">
                                {{ ucfirst($q->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
            borderWidth: 2,
            tension: 0.3
        }]
    }
});
</script>

@endsection

<style>
    .card h2 {
    font-weight: 700;
    }

    .table td {
        vertical-align: middle;
    }
</style>
