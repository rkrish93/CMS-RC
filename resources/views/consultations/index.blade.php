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
                                class="form-control"
                                step="0.1"
                                placeholder="Auto">
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

        @php
            $currentUser = auth()->user();
            $designation = strtolower(trim((string) ($currentUser->designation ?? '')));
            $canViewMedicalHistory = $currentUser?->can('vitals-create')
                || $currentUser?->hasAnyRole(['Doctor', 'Admin'])
                || $currentUser?->hasAnyRole(['Nurse', 'Mid wife', 'Midwife'])
                || in_array($designation, ['nurse', 'mid wife', 'midwife', 'staff nurse'], true);
        @endphp

        @if($canViewMedicalHistory)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Medical History</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 140px;">Date</th>
                                    <th style="min-width: 180px;">Diagnosis</th>
                                    <th>Symptoms</th>
                                    <th style="min-width: 260px;">Prescription</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($oldConsultations as $consultation)
                                    <tr>
                                        <td>
                                            <small class="text-muted d-block">{{ $consultation->created_at->format('d M Y') }}</small>
                                            <small>{{ $consultation->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>{{ $consultation->diagnosis ?? '-' }}</td>
                                        <td>
                                            @if(!empty($consultation->symptoms))
                                                {{ is_array($consultation->symptoms) ? implode(', ', $consultation->symptoms) : $consultation->symptoms }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($consultation->prescription_items))
                                                <ul class="mb-0 ps-3 small">
                                                    @foreach($consultation->prescription_items as $item)
                                                        <li>
                                                            {{ $item['medicine_name'] ?? 'Medicine' }}
                                                            @if(!empty($item['dosage']))
                                                                - {{ $item['dosage'] }}
                                                            @endif
                                                            @if(!empty($item['duration']))
                                                                - {{ $item['duration'] }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No medical history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                                                <input type="text" name="prescription_items[0][dosage]" class="form-control" placeholder="1 tablet twice daily" required>
                                            </td>
                                            <td>
                                                <input type="text" name="prescription_items[0][duration]" class="form-control" placeholder="5 days" required>
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
                                Search medicine by typing the first letters of the product name or code.
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

@push('scripts')

<script>
    let medicineRowCount = 1;

    document.getElementById('addMedicineRowBtn')?.addEventListener('click', function() {

        const tbody = document.getElementById('medicineTableBody');

        const newRow = document.createElement('tr');

        newRow.classList.add('medicine-row');

        newRow.innerHTML = `

        <td>

            <select name="prescription_items[${medicineRowCount}][medicine_id]" class="form-select medicine-select">

                <option value="">Select medicine...</option>

                @foreach($products as $product)

                    <option value="{{ $product->id }}">

                        {{ $product->product_code }} - {{ $product->medicine_name }}{{ $product->generic_name ? ' (' . $product->generic_name . ')' : '' }}

                    </option>

                @endforeach

            </select>

        </td>

        <td>

            <input type="text" name="prescription_items[${medicineRowCount}][dosage]" class="form-control" placeholder="1 tablet twice daily">

        </td>

        <td>

            <input type="text" name="prescription_items[${medicineRowCount}][duration]" class="form-control" placeholder="5 days">

        </td>

        <td class="text-center">

            <button type="button" class="btn btn-sm btn-outline-danger js-remove-medicine-row">&times;</button>

        </td>

    `;

        tbody.appendChild(newRow);

        medicineRowCount++;

        updateRemoveButtons();

    });

    function updateRemoveButtons() {

        const rows = document.querySelectorAll('.medicine-row');

        rows.forEach(row => {

            const btn = row.querySelector('.js-remove-medicine-row');

            btn.disabled = rows.length === 1;

            btn.onclick = function() {

                row.remove();

                updateRemoveButtons();

            };

        });

    }

    updateRemoveButtons();
</script>

@endpush
</div>
</div>
</div>


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
            const removeButton = row.querySelector('.js-remove-medicine-row');

            if (medicineSelect) medicineSelect.name = `prescription_items[${index}][medicine_id]`;
            if (dosageInput) dosageInput.name = `prescription_items[${index}][dosage]`;
            if (durationInput) durationInput.name = `prescription_items[${index}][duration]`;
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
                <input type="text" name="prescription_items[${rowCount}][dosage]" class="form-control" placeholder="1 tablet twice daily" required>
            </td>
            <td>
                <input type="text" name="prescription_items[${rowCount}][duration]" class="form-control" placeholder="5 days" required>
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