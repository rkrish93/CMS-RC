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

                <div class="mt-4 p-3 bg-light rounded border text-center">
                    <div class="small text-muted mb-1">Past Records: <strong>{{ $oldConsultations->count() }} Visits</strong></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-bs-toggle="modal" data-bs-target="#medicalHistoryModal">
                        <i class="mdi mdi-history me-1"></i> View Medical History Modal
                    </button>
                </div>
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

                            <input type="number"
                                name="temp"
                                class="form-control"
                                step="0.1"
                                placeholder="37.0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Diabetes</label>

                            <input type="number"
                                name="sugar"
                                class="form-control"
                                step="0.1"
                                placeholder="mg/dL">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Pulse</label>

                            <input type="number"
                                name="pulse"
                                class="form-control"
                                placeholder="72">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>

                            <input type="number"
                                name="weight"
                                id="vitals_weight"
                                class="form-control"
                                step="0.1"
                                placeholder="65.5">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Height (cm)</label>

                            <input type="number"
                                name="height"
                                id="vitals_height"
                                class="form-control"
                                step="0.1"
                                placeholder="170">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Respiratory Rate</label>

                            <input type="number"
                                name="respiratory_rate"
                                class="form-control"
                                placeholder="16">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">SpO2 (%)</label>

                            <input type="number"
                                name="oxygen_saturation"
                                class="form-control"
                                step="0.1"
                                min="0"
                                max="100"
                                placeholder="98">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">BMI</label>

                            <input type="number"
                                name="bmi"
                                id="vitals_bmi"
                                class="form-control bg-light"
                                step="0.1"
                                placeholder="Auto"
                                readonly>
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

        @if($latestVital || ($previousVitals->count() > 0))

        <div class="card mb-3">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        Vitals Recorded
                    </h4>

                    <button type="button"
                        class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#vitalsHistoryModal">
                        Previous Vitals
                    </button>
                </div>

                @if($latestVital)
                <div class="row g-3">

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

                    <div class="col-md-3">
                        <strong>Weight (kg)</strong><br>
                        {{ $latestVital->weight ?? '-' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Height (cm)</strong><br>
                        {{ $latestVital->height ?? '-' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Resp. Rate</strong><br>
                        {{ $latestVital->respiratory_rate ?? '-' }}
                    </div>

                    <div class="col-md-3">
                        <strong>SpO2 (%)</strong><br>
                        {{ $latestVital->oxygen_saturation ?? '-' }}
                    </div>

                    <div class="col-md-3">
                        <strong>BMI</strong><br>
                        {{ $latestVital->bmi ?? '-' }}
                    </div>

                </div>
                @else
                <p class="text-muted mb-0">No vitals recorded for this appointment yet. You can still review previous vitals.</p>
                @endif

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
                        <div class="d-flex gap-2"></div>

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
                                Symptoms
                            </label>

                            <textarea name="symptoms"
                                rows="2"
                                class="form-control"
                                placeholder="Fever, cough, body pain"></textarea>

                            <small class="text-muted">Separate symptoms with commas.</small>

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                Prescription Medicines
                            </label>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-2" id="medicineTable">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 260px;">Medicine</th>
                                            <th style="min-width: 160px;">Dosage</th>
                                            <th style="min-width: 160px;">Duration</th>
                                            <th style="min-width: 140px;">Time Slot</th>
                                            <th style="min-width: 170px;">Food Timing</th>
                                            <th style="width: 56px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="medicineTableBody">
                                        <tr class="medicine-row">
                                            <td>
                                                <select name="prescription_items[0][medicine_id]" class="form-select medicine-select" required>
                                                    <option value="">Select medicine...</option>
                                                    @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->product_code }} - {{ $product->medicine_name }}{{ $product->generic_name ? ' (' . $product->generic_name . ')' : '' }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="prescription_items[0][dosage]" class="form-control" placeholder="1 tablet twice daily">
                                            </td>
                                            <td>
                                                <input type="text" name="prescription_items[0][duration]" class="form-control" placeholder="5 days">
                                            </td>
                                            <td>
                                                <div class="time-slot-group">
                                                    <div class="form-check">
                                                        <input class="form-check-input time-slot-checkbox" type="checkbox" name="prescription_items[0][time_slot][]" value="morning" id="time_slot_0_morning">
                                                        <label class="form-check-label" for="time_slot_0_morning">Morning</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input time-slot-checkbox" type="checkbox" name="prescription_items[0][time_slot][]" value="lunch" id="time_slot_0_lunch">
                                                        <label class="form-check-label" for="time_slot_0_lunch">Lunch</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input time-slot-checkbox" type="checkbox" name="prescription_items[0][time_slot][]" value="night" id="time_slot_0_night">
                                                        <label class="form-check-label" for="time_slot_0_night">Night</label>
                                                    </div>
                                                </div>
                                                <small class="text-muted">You can select multiple time slots.</small>
                                            </td>
                                            <td>
                                                <select name="prescription_items[0][food_timing]" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="before_food">Before Food</option>
                                                    <option value="after_food">After Food</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger js-remove-medicine-row" disabled>&times;</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="addMedicineRowBtn">
                                <i class="mdi mdi-plus me-1"></i> Add Medicine
                            </button>

                            <small class="text-muted d-block mt-2">
                                Search medicine by typing the first letters of the medicine name or code.
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
                            <th>Weight</th>
                            <th>Height</th>
                            <th>Resp. Rate</th>
                            <th>SpO2</th>
                            <th>BMI</th>
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
                            <td>{{ $vital->weight ?? '-' }}</td>
                            <td>{{ $vital->height ?? '-' }}</td>
                            <td>{{ $vital->respiratory_rate ?? '-' }}</td>
                            <td>{{ $vital->oxygen_saturation ?? '-' }}</td>
                            <td>{{ $vital->bmi ?? '-' }}</td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="10" class="text-center">
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const bookedDates = <?php echo json_encode($bookedDates ?? []); ?>;

    flatpickr('#next_visit_date', {

        minDate: 'today',

        disable: [

            function(date) {
                return date.getDay() === 0;
            },

            ...bookedDates

        ]

    });

    const vitalsWeightInput = document.getElementById('vitals_weight');
    const vitalsHeightInput = document.getElementById('vitals_height');
    const vitalsBmiInput = document.getElementById('vitals_bmi');

    function updateBmiFromInputs() {
        if (!vitalsWeightInput || !vitalsHeightInput || !vitalsBmiInput) {
            return;
        }

        const weight = parseFloat(vitalsWeightInput.value);
        const heightCm = parseFloat(vitalsHeightInput.value);

        if (!Number.isNaN(weight) && !Number.isNaN(heightCm) && heightCm > 0) {
            const heightMeters = heightCm / 100;
            const bmi = weight / (heightMeters * heightMeters);
            vitalsBmiInput.value = bmi.toFixed(1);
        }
    }

    if (vitalsWeightInput && vitalsHeightInput) {
        vitalsWeightInput.addEventListener('input', updateBmiFromInputs);
        vitalsHeightInput.addEventListener('input', updateBmiFromInputs);
    }

    const medicineTableBody = document.getElementById('medicineTableBody');
    const addMedicineRowBtn = document.getElementById('addMedicineRowBtn');

    function initMedicineSelect(selectElement) {
        if (!selectElement || selectElement.tomselect) {
            return;
        }

        new TomSelect(selectElement, {
            create: false,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            searchField: ['text']
        });
    }

    function refreshMedicineRowNames() {
        const rows = medicineTableBody ? medicineTableBody.querySelectorAll('.medicine-row') : [];
        rows.forEach(function(row, index) {
            const medicineSelect = row.querySelector('.medicine-select');
            const dosageInput = row.querySelector('input[name*="[dosage]"]');
            const durationInput = row.querySelector('input[name*="[duration]"]');
            const timeSlotInputs = row.querySelectorAll('input.time-slot-checkbox');
            const foodTimingInput = row.querySelector('select[name*="[food_timing]"]');
            const removeButton = row.querySelector('.js-remove-medicine-row');

            if (medicineSelect) medicineSelect.name = `prescription_items[${index}][medicine_id]`;
            if (dosageInput) dosageInput.name = `prescription_items[${index}][dosage]`;
            if (durationInput) durationInput.name = `prescription_items[${index}][duration]`;
            if (timeSlotInputs.length) {
                timeSlotInputs.forEach(function(input) {
                    const slot = input.value;
                    input.name = `prescription_items[${index}][time_slot][]`;
                    input.id = `time_slot_${index}_${slot}`;
                    const label = input.closest('.form-check')?.querySelector('label');
                    if (label) {
                        label.setAttribute('for', input.id);
                    }
                });
            }
            if (foodTimingInput) foodTimingInput.name = `prescription_items[${index}][food_timing]`;
            if (removeButton) removeButton.disabled = index === 0;

            initMedicineSelect(medicineSelect);
        });
    }

    if (addMedicineRowBtn && medicineTableBody) {
        addMedicineRowBtn.addEventListener('click', function() {
            const rowCount = medicineTableBody.querySelectorAll('.medicine-row').length;
            const newRow = document.createElement('tr');
            newRow.className = 'medicine-row';
            newRow.innerHTML = `
            <td>
                <select name="prescription_items[${rowCount}][medicine_id]" class="form-select medicine-select" required>
                    <option value="">Select medicine...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_code }} - {{ $product->medicine_name }}{{ $product->generic_name ? ' (' . $product->generic_name . ')' : '' }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="prescription_items[${rowCount}][dosage]" class="form-control" placeholder="1 tablet twice daily">
            </td>
            <td>
                <input type="text" name="prescription_items[${rowCount}][duration]" class="form-control" placeholder="5 days">
            </td>
            <td>
                <div class="time-slot-group">
                    <div class="form-check">
                        <input class="form-check-input time-slot-checkbox" type="checkbox" name="prescription_items[${rowCount}][time_slot][]" value="morning" id="time_slot_${rowCount}_morning">
                        <label class="form-check-label" for="time_slot_${rowCount}_morning">Morning</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input time-slot-checkbox" type="checkbox" name="prescription_items[${rowCount}][time_slot][]" value="lunch" id="time_slot_${rowCount}_lunch">
                        <label class="form-check-label" for="time_slot_${rowCount}_lunch">Lunch</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input time-slot-checkbox" type="checkbox" name="prescription_items[${rowCount}][time_slot][]" value="night" id="time_slot_${rowCount}_night">
                        <label class="form-check-label" for="time_slot_${rowCount}_night">Night</label>
                    </div>
                </div>
                <small class="text-muted">You can select multiple time slots.</small>
            </td>
            <td>
                <select name="prescription_items[${rowCount}][food_timing]" class="form-select">
                    <option value="">Select</option>
                    <option value="before_food">Before Food</option>
                    <option value="after_food">After Food</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-medicine-row">&times;</button>
            </td>
        `;

            medicineTableBody.appendChild(newRow);
            refreshMedicineRowNames();
        });

        medicineTableBody.addEventListener('click', function(event) {
            const button = event.target.closest('.js-remove-medicine-row');
            if (!button || button.disabled) {
                return;
            }

            const row = button.closest('.medicine-row');
            if (row) {
                row.remove();
                refreshMedicineRowNames();
            }
        });

        refreshMedicineRowNames();
    }

    const consultationForm = document.querySelector("form[action='{{ route('consultations.store') }}']");
    if (consultationForm && medicineTableBody) {
        consultationForm.addEventListener('submit', function(event) {
            const rows = medicineTableBody.querySelectorAll('.medicine-row');
            rows.forEach(function(row) {
                const timeSlotInputs = row.querySelectorAll('input.time-slot-checkbox');
                if (timeSlotInputs.length) {
                    timeSlotInputs[0].setCustomValidity('');
                }
            });
        });
    }
</script>

{{-- MEDICAL HISTORY MODAL --}}
<div class="modal fade" id="medicalHistoryModal" tabindex="-1" aria-labelledby="medicalHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-1" id="medicalHistoryModalLabel">
                        <i class="mdi mdi-history text-primary me-2"></i>Patient Medical & Vitals History
                    </h5>
                    <small class="text-muted">
                        Patient: <strong>{{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) }}</strong>
                        | Code: <strong>{{ optional($appointment->patient)->patient_code ?? 'N/A' }}</strong>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                

                <!-- Section 2: Medical Consultations & Prescription History -->
                <div>
                    <h6 class="text-uppercase text-primary fw-bold mb-3 small tracking-wider">
                        <i class="mdi mdi-medical-bag text-primary me-1"></i> Consultation & Prescription History
                    </h6>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 140px;">Date & Time</th>
                                    <th style="min-width: 150px;">Diagnosis</th>
                                    <th style="min-width: 150px;">Symptoms</th>
                                    <th style="min-width: 280px;">Prescription Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($oldConsultations as $consultation)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $consultation->created_at?->format('d M Y') }}</div>
                                            <small class="text-muted">{{ $consultation->created_at?->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $consultation->diagnosis ?: '-' }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($consultation->symptoms))
                                                <span class="badge bg-light text-dark border">{{ is_array($consultation->symptoms) ? implode(', ', $consultation->symptoms) : $consultation->symptoms }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($consultation->prescription_items))
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($consultation->prescription_items as $pItem)
                                                        <div class="p-2 bg-light rounded border-start border-3 border-primary small">
                                                            <strong>{{ $pItem['medicine_name'] ?? 'Medicine' }}</strong>
                                                            @if(!empty($pItem['dosage']))
                                                                <span class="badge bg-white text-secondary border ms-1">{{ $pItem['dosage'] }}</span>
                                                            @endif
                                                            @if(!empty($pItem['duration']))
                                                                <span class="badge bg-white text-secondary border ms-1">{{ $pItem['duration'] }}</span>
                                                            @endif
                                                            @php
                                                                $slots = $pItem['time_slot'] ?? [];
                                                                if (!is_array($slots)) $slots = [$slots];
                                                                $slots = array_filter(array_map('trim', $slots));
                                                            @endphp
                                                            @if(!empty($slots))
                                                                <span class="badge bg-white text-info border ms-1">{{ implode(', ', array_map('ucfirst', $slots)) }}</span>
                                                            @endif
                                                            @if(!empty($pItem['food_timing']))
                                                                <span class="badge bg-white text-primary border ms-1">{{ str_replace('_', ' ', ucfirst($pItem['food_timing'])) }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif(!empty($consultation->prescription))
                                                <span class="font-monospace small">{{ $consultation->prescription }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No consultation history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection



@push('styles')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">

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