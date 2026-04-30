@extends('layouts.app')

@section('content')

<div class="container">
    <h3 class="mb-4">🩺 Today Queue</h3>

    <table class="table table-bordered table-hover">

        <thead class="table-dark">
            <tr>
                <th>Token</th>
                <th>Patient</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($appointments as $appt)

            <tr>

                <td><strong>{{ $appt->token_no ?? '-' }}</strong></td>

                <td>{{ optional($appt->patient)->first_name . ' ' . optional($appt->patient)->last_name?? 'No Patient' }}</td>

                <td>{{ $appt->appointment_time ?? '-' }}</td>

                <td>
                    @if($appt->status == 'pending')
                        <span class="badge bg-warning">Waiting</span>
                    @elseif($appt->status == 'in_progress')
                        <span class="badge bg-primary">In Progress</span>
                    @elseif($appt->status == 'completed')
                        <span class="badge bg-success">Completed</span>
                    @else
                        <span class="badge bg-secondary">Cancelled</span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('consultations.create', $appt->id) }}"
                    class="btn btn-sm btn-primary
                    {{ in_array($appt->status, ['completed','cancelled']) ? 'disabled' : '' }}"
                    style="{{ in_array($appt->status, ['completed','cancelled']) ? 'pointer-events:none;opacity:0.6;' : '' }}">
                        Open
                    </a>
                </td>

            </tr>

            @endforeach
        </tbody>

    </table>
</div>

<script>
setInterval(() => location.reload(), 15000);
</script>

@endsection
