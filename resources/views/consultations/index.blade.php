@extends('layouts.app')

@section('title', 'Patient Consultation')

@section('page-actions')
    <a href="{{ route('appointments.today') }}" class="btn btn-light">
        <i class="mdi mdi-arrow-left me-1"></i> Queue
    </a>
@endsection

@section('content')



    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card consultation-summary">
                <div class="card-body">
                    <h4 class="card-title mb-3">Patient Details</h4>

                    <div class="patient-badge mb-3">
                        <i class="mdi mdi-account-heart-outline"></i>
                    </div>

                    <h5 class="mb-1">
                        {{ $appointment->patient->first_name ?? 'N/A' }}
                        {{ $appointment->patient->last_name ?? '' }}
                    </h5>
                    <p class="text-muted mb-4">{{ $appointment->patient->patient_code ?? 'No patient code' }}</p>

                    <div class="detail-list">
                        <div>
                            <span>Age</span>
                            <strong>{{ $appointment->patient->age ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Gender</span>
                            <strong>{{ $appointment->patient->gender ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Contact</span>
                            <strong>{{ $appointment->patient->phone ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span>Appointment</span>
                            <strong>{{ $appointment->appointment_date }} at {{ $appointment->appointment_time }}</strong>
                        </div>
                        <div>
                            <span>Token</span>
                            <strong>{{ $appointment->token_no ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    @if($appointment->patient)
                        <a href="{{ route('patients.show', $appointment->patient->id) }}" class="btn btn-outline-primary w-100 mt-4">
                            <i class="mdi mdi-eye me-1"></i> View Full Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>



        {{-- RIGHT SIDE --}}
        <div class="col-lg-8">

            {{-- VITALS --}}
          {{-- VITALS FORM --}}
@can('vitals-create')

<form method="POST" action="{{ route('vitals.store') }}">

    @csrf

    <input type="hidden"
           name="appointment_id"
           value="{{ $appointment->id }}">

    <div class="card mb-3">

        <div class="card-body">

            <h4 class="card-title mb-3">
                Vitals
            </h4>

            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Blood Pressure</label>

                 <input type="text"
       name="bp"
       class="form-control"
       placeholder="120/80">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Temperature</label>

                   <input type="text"
       name="temp"
       class="form-control"
       placeholder="37.0">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Diabetes</label>

                   <input type="text"
       name="sugar"
       class="form-control"
       placeholder="mg/dL">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Pulse</label>

                   <input type="text"
       name="pulse"
       class="form-control"
       placeholder="72">
                </div>

            </div>

        </div>

    </div>



    <div class="mb-3 text-end">

        <button type="submit"
                class="btn btn-info">

            <i class="mdi mdi-heart-pulse me-1"></i>
            Save Vitals

        </button>

    </div>

</form>
@endcan

@if($latestVital)

<div class="card mb-3">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="card-title mb-0">
        Latest Recorded Vitals
    </h4>

  <button type="button"
        class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#vitalsHistoryModal">
    Previous Vitals
</button>
</div>

        <div class="row">

            <div class="col-md-3">
                <strong>BP</strong><br>
                {{ $latestVital->bp ?? '-' }}
            </div>

            <div class="col-md-3">
                <strong>Temperature</strong><br>
                {{ $latestVital->temp ?? '-' }}
            </div>

            <div class="col-md-3">
                <strong>Sugar</strong><br>
                {{ $latestVital->sugar ?? '-' }}
            </div>

            <div class="col-md-3">
                <strong>Pulse</strong><br>
                {{ $latestVital->pulse ?? '-' }}
            </div>

        </div>

    </div>
</div>

@endif



            {{-- DOCTOR ONLY SECTION --}}
            @hasanyrole('Doctor|Admin')

            {{-- CLINICAL NOTES --}}
            <div class="card mb-3">
<form method="POST" action="{{ route('consultations.store') }}">
    @csrf
    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                <div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3">

    <h4 class="card-title mb-0">
        Clinical Notes
    </h4>
<button type="button"
        class="btn btn-sm btn-info"
        data-bs-toggle="modal"
        data-bs-target="#consultationHistoryModal">
    Previous Consultations
</button>

</div>

                    <div class="row g-3">

                        <div class="col-md-12">

                            <label class="form-label">
                                Diagnosis
                            </label>

                            <textarea name="diagnosis"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Enter diagnosis"></textarea>

                        </div>


                        <div class="col-md-12">

                            <label class="form-label">
                                Prescription
                            </label>

                            <textarea name="prescription"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Format: Panadol-60, VitaminC-50"></textarea>

                            <small class="text-muted">
                                Use comma-separated format with quantity per medicine (example: Panadol-60, VitaminC-50).
                            </small>

                        </div>

                        <div class="col-md-12">

                            <label class="form-label">
                                Additional Notes
                            </label>

                            <textarea name="notes"
                                      rows="2"
                                      class="form-control"></textarea>

                        </div>

                    </div>

                </div>

            </div>



            {{-- NEXT VISIT --}}
            <div class="card">

                <div class="card-body">

                    <h4 class="card-title mb-3">
                        Next Visit
                    </h4>

                    <div class="row g-3">

                        <div class="col-md-5">

                            <label class="form-label">
                                Next Visit Date
                            </label>

                            <input type="date"
                                   id="next_visit_date"
                                   name="next_visit"
                                   class="form-control">

                        </div>


                        <div class="col-md-7">

                            <label class="form-label">
                                Remarks
                            </label>

                            <input type="text"
                                   name="note"
                                   class="form-control"
                                   placeholder="Follow-up reason">

                        </div>

                    </div>

                </div>

            </div>



            {{-- DOCTOR SAVE BUTTON --}}
            <div class="d-flex justify-content-end gap-2 mt-3">

                <a href="{{ route('appointments.today') }}"
                   class="btn btn-light">
                    Cancel
                </a>

                <button type="submit"
                        name="action"
                        value="save_consultation"
                        class="btn btn-gradient-primary">

                    <i class="mdi mdi-check me-1"></i>
                    Save Consultation

                </button>

            </div>

            @endhasanyrole

        </div>

    </div>

</form>
<div class="modal fade" id="consultationHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Previous Consultations
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                @forelse($history as $consultation)

                    <div class="border rounded p-3 mb-2">

                        <small class="text-muted">
                            {{ $consultation->created_at->format('d-m-Y H:i') }}
                        </small>

                        <div class="mt-2">
                            <strong>Diagnosis:</strong>
                            {{ $consultation->diagnosis }}
                        </div>

                    </div>

                @empty

                    <p>No consultation history found</p>

                @endforelse

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="vitalsHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Previous Vitals
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>BP</th>
                            <th>Temp</th>
                            <th>Sugar</th>
                            <th>Pulse</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($previousVitals as $vital)

                        <tr>
                            <td>{{ $vital->created_at->format('d-m-Y H:i') }}</td>
                            <td>{{ $vital->bp }}</td>
                            <td>{{ $vital->temp }}</td>
                            <td>{{ $vital->sugar }}</td>
                            <td>{{ $vital->pulse }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">
                                No vitals found
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</div>
@endsection


@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

const bookedDates = @json($bookedDates ?? []);

flatpickr('#next_visit_date', {

    minDate: 'today',

    disable: [

        function(date) {
            return date.getDay() === 0;
        },

        ...bookedDates

    ]

});




</script>

@endsection



@push('styles')

<style>

.patient-badge {
    width: 76px;
    height: 76px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eff6ff;
    color: #2563eb;
    font-size: 42px;
}

.detail-list {
    display: grid;
    gap: 12px;
}

.detail-list div {
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f8fafc;
}

.detail-list span {
    display: block;
    margin-bottom: 2px;
    color: #667085;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

.detail-list strong {
    color: #152033;
}

</style>

@endpush
