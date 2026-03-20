@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-calendar-check"></i>
        </span>
        Appointment List
    </h3>

    <button class="btn btn-gradient-primary shadow-sm"
            data-bs-toggle="modal"
            data-bs-target="#appointmentModal">
        <i class="btn-gradient-primary btn-sm"></i> Add Appointment
    </button>
</div>

<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">

<thead class="bg-dark text-white">
<tr>
    <th>#</th>
    <th>Patient</th>
    <th>Unit</th>
    <th>Date</th>
    <th>Time</th>
    <th>Token</th>
    <th>Status</th>
    <th width="180">Action</th>
</tr>
</thead>

<tbody>

@forelse($appointments as $key => $app)

<tr>
    <td>{{ $appointments->firstItem() + $key }}</td>

    <td>{{ $app->patient->first_name ?? '' }}</td>

    <td>{{ $app->Unit->unit_name ?? '' }}</td>

    <td>{{ $app->appointment_date }}</td>

    <td>{{ $app->appointment_time }}</td>

    <td>{{ $app->token_no }}</td>

    <td>
        @if($app->status == 'Pending')
            <span class="badge badge-warning">Pending</span>

        @elseif($app->status == 'Confirmed')
            <span class="badge badge-primary">Confirmed</span>

        @elseif($app->status == 'Completed')
            <span class="badge badge-success">Completed</span>

        @else
            <span class="badge badge-danger">Cancelled</span>
        @endif
    </td>

    <td>

        <a href="{{ route('appointments.edit',$app->id) }}"
            class="btn btn-sm btn-gradient-info">
            Edit
        </a>

        <form action="{{ route('appointments.destroy',$app->id) }}"
              method="POST"
              style="display:inline">

            @csrf
            @method('DELETE')

            <button class="btn btn-sm btn-gradient-danger"
                    onclick="return confirm('Delete Appointment?')">
                Delete
            </button>

        </form>

    </td>

</tr>

@empty

<tr>
    <td colspan="8" class="text-center">
        No Appointments Found
    </td>
</tr>

@endforelse

</tbody>

</table>

<!-- ================= PROFESSIONAL APPOINTMENT MODAL ================= -->

<div class="modal fade" id="appointmentModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered appointment-modal">
<div class="modal-content border-0 shadow-lg rounded-4">

<!-- HEADER -->
<div class="modal-header text-Primary rounded-top-4">
    <h5 class="modal-title fw-semibold">
        <i class="mdi mdi-calendar-plus"></i>
        Schedule Appointment
    </h5>

    <button type="button" class="btn-close btn-close-primary"
            data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="{{ route('appointments.store') }}">
@csrf

<!-- BODY -->
<div class="modal-body px-3 py-3 bg-light">

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label fw-semibold">Patient</label>
        <select name="patient_id" class="form-select shadow-sm" required>
            <option value="">Select Patient</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}">
                    {{ $patient->first_name }} {{ $patient->last_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Clinical Unit</label>
        <select name="unit_id" class="form-select shadow-sm" required>
            <option value="">Select Unit</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}">
                    {{ $unit->unit_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Date</label>
        <input type="date" name="appointment_date"
               class="form-control shadow-sm" required>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Time</label>
        <input type="time" name="appointment_time"
               class="form-control shadow-sm" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Token</label>
        <input type="text" name="token"
               class="form-control shadow-sm" required>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select shadow-sm">
            <option value="Pending">Pending</option>
            <option value="Confirmed">Confirmed</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Notes</label>
        <textarea name="notes" rows="2"
                  class="form-control shadow-sm"></textarea>
    </div>

</div>

</div>

<!-- FOOTER -->
<div class="modal-footer bg-white rounded-bottom-4">
    <button class="btn btn-gradient-primary px-4 shadow-sm">
        <i class="mdi mdi-check"></i> Save Appointment
    </button>

    {{-- <button type="button"
            class="btn btn-outline-secondary"
            data-bs-dismiss="modal">
        Cancel
    </button> --}}
</div>

</form>

</div>
</div>
</div>

<br>



</div>
</div>



@endsection
<style>
.bg-gradient-primary{
    background: linear-gradient(45deg,#4e73df,#224abe);
}

</style>
