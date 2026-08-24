<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\Vital;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('patient.flow.scanner');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($appointment_id)
    {
         $user = auth()->user();
         $unitScopedRoles = ['Doctor', 'Nurse', 'Mid wife'];
         abort_unless(
             $user?->can('consultations-create') || $user?->can('vitals-create') || $user?->hasAnyRole(['Doctor', 'Admin']),
             403
         );

         $appointment = Appointment::with('patient')->findOrFail($appointment_id);

         if ($user?->hasAnyRole($unitScopedRoles) && (int) $user->unit_id !== (int) $appointment->unit_id) {
            abort(403);
         }

         if ($user?->hasAnyRole(['Nurse', 'Mid wife']) && Vital::where('appointment_id', $appointment->id)->exists()) {
            return redirect()
                ->route('appointments.today')
                ->with('error', 'Vitals already recorded for this appointment.');
         }

            if(Appointment::normalizeStatus($appointment->status) === AppointmentStatus::SCHEDULED->value){
                return redirect()
                     ->route('appointments.today')
                     ->with('error', 'Patient is not checked-in yet. Generate QR pass first.');
            }

            if(Appointment::normalizeStatus($appointment->status) === AppointmentStatus::CHECKED_IN->value){
                $appointment->update(['status' => AppointmentStatus::TRIAGE_IN_PROGRESS->value]);
            }

        // Old medical history - all consultations
        $oldConsultations = Consultation::where('patient_id',$appointment->patient_id)
                    ->latest()
                    ->get();

$latestVital = Vital::where('appointment_id', $appointment_id)
                    ->latest()
                    ->first();
                     $previousVitals = Vital::where(
        'patient_id',
        $appointment->patient_id
    )
    ->latest()
    ->take(10)
    ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('medicine_name')
            ->get(['id', 'product_code', 'medicine_name', 'generic_name']);

        return view('consultations.index', compact('appointment', 'oldConsultations', 'latestVital', 'previousVitals', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'diagnosis' => 'required|string',
            'symptoms' => 'nullable|string',
            'prescription_items' => 'nullable|array',
            'prescription_items.*.medicine_id' => 'nullable|exists:medicines,id',
            'prescription_items.*.medicine_name' => 'nullable|string|max:255',
            'prescription_items.*.duration' => 'nullable|string|max:100',
            'prescription_items.*.dosage' => 'nullable|string|max:100',
            'prescription_items.*.time_slot' => 'nullable|array|min:1',
            'prescription_items.*.time_slot.*' => 'nullable|in:morning,lunch,night',
            'prescription_items.*.food_timing' => 'nullable|in:before_food,after_food',
            'notes' => 'nullable|string',
            'next_visit' => 'nullable|date|after_or_equal:today',
        ]);

        $prescriptionItems = collect($validated['prescription_items'] ?? [])
            ->filter(function ($item) {
                return ! empty($item['medicine_id']) || ! empty($item['medicine_name']);
            })
            ->values()
            ->map(function (array $item) {
                $product = ! empty($item['medicine_id'])
                    ? Product::find($item['medicine_id'])
                    : null;

                $timeSlots = $item['time_slot'] ?? [];
                if (! is_array($timeSlots)) {
                    $timeSlots = [$timeSlots];
                }

                $timeSlots = array_values(array_filter(array_map(function ($slot) {
                    $slot = trim((string) $slot);
                    return in_array($slot, ['morning', 'lunch', 'night'], true) ? $slot : null;
                }, $timeSlots)));

                return [
                    'medicine_id' => $product?->id,
                    'product_code' => $product?->product_code,
                    'medicine_name' => $product?->medicine_name ?? trim((string) ($item['medicine_name'] ?? '')),
                    'generic_name' => $product?->generic_name,
                    'duration' => trim((string) ($item['duration'] ?? '')),
                    'dosage' => trim((string) ($item['dosage'] ?? '')),
                    'time_slot' => $timeSlots,
                    'food_timing' => trim((string) ($item['food_timing'] ?? '')),
                ];
            })
            ->filter(function (array $item) {
                return $item['medicine_name'] !== '';
            })
            ->all();

        if (empty($prescriptionItems)) {
            throw ValidationException::withMessages([
                'prescription_items' => 'Add at least one medicine row.',
            ]);
        }

        $legacyPrescription = collect($prescriptionItems)
            ->map(function (array $item) {
                $parts = [$item['medicine_name']];

                if ($item['dosage'] !== '') {
                    $parts[] = $item['dosage'];
                }

                if ($item['duration'] !== '') {
                    $parts[] = $item['duration'];
                }

                $timeSlots = $item['time_slot'] ?? [];
                if (! is_array($timeSlots)) {
                    $timeSlots = [$timeSlots];
                }

                $timeSlots = array_values(array_filter(array_map(function ($slot) {
                    $slot = trim((string) $slot);
                    return $slot !== '' ? str_replace('_', ' ', $slot) : null;
                }, $timeSlots)));

                if (! empty($timeSlots)) {
                    $parts[] = implode('/', $timeSlots);
                }

                if (($item['food_timing'] ?? '') !== '') {
                    $parts[] = str_replace('_', ' ', (string) $item['food_timing']);
                }

                return implode(' | ', $parts);
            })
            ->implode(', ');

        $validated['symptoms'] = array_values(array_filter(array_map('trim', explode(',', (string) ($validated['symptoms'] ?? '')))));

        $appointment = Appointment::findOrFail($request->appointment_id);
        // $request->validate([
        //     'diagnosis' => 'required',
        //     'bp' => 'nullable|string|max:20',
        //     'temp' => 'nullable|numeric',
        //     'sugar' => 'nullable|numeric',
        //     'pulse' => 'nullable|integer',
        // ]);

        // // Prepare vitals array only with provided values
        // $vitals = array_filter([
        //     'bp' => $request->bp ?: null,
        //     'temp' => $request->temp ?: null,
        //     'sugar' => $request->sugar ?: null,
        //     'pulse' => $request->pulse ?: null,
        // ], function ($v) {
        //     return $v !== null && $v !== '';
        // });

        Consultation::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => auth()->id(),
            'diagnosis' => $validated['diagnosis'],
            'symptoms' => $validated['symptoms'] ?? null,
            'prescription_items' => $prescriptionItems,
            'prescription' => $legacyPrescription,
            'prescribed_quantity' => count($prescriptionItems),
            'dispensed_quantity' => 0,
            'dispensed_breakdown' => [],
            'notes' => $validated['notes'] ?? null,
            'pharmacy_status' => 'pending',
            'next_visit' => $validated['next_visit'] ?? null,
        ]);

        // Appointment::where('id',$request->appointment_id)
        //     ->update(['status'=>'completed']);

        // Appointment::create([
        //     'patient_id' => $request->patient_id,
        //     // 'unit_id' => $request->unit_id,
        //     'appointment_date' => $request->appointment_date,
        //     'appointment_time' => $request->appointment_time,
        //     'token_no' => Appointment::whereDate('appointment_date',$request->appointment_date)->count() + 1,
        //     'status' => 'pending'
        //     ]);


        //  GET CURRENT APPOINTMENT
        $oldAppointment = Appointment::find($request->appointment_id);

        //  AUTO CREATE NEXT APPOINTMENT
        if ($request->next_visit) {

            //  AUTO TIME (if not given → default 9:00 AM)
            $lastTime = Appointment::where('unit_id', $oldAppointment->unit_id)
                            ->where('appointment_date', $request->next_visit)
                            ->orderByDesc('appointment_time')
                            ->value('appointment_time');

            $time = $lastTime
                    ? date('H:i:s', strtotime($lastTime . ' +15 minutes'))
                    : '09:00:00';

            //  AUTO TOKEN (Unit + Date wise)
            $lastToken = Appointment::where('unit_id', $oldAppointment->unit_id)
                            ->where('appointment_date', $request->next_visit)
                            ->max('token_no');

            $nextToken = $lastToken ? $lastToken + 1 : 1;

            Appointment::create([
                'patient_id' => $oldAppointment->patient_id,
                'unit_id' => $oldAppointment->unit_id,
                'appointment_date' => $request->next_visit,
                'appointment_time' => $time,
                'token_no' => $nextToken,
                'status' => AppointmentStatus::SCHEDULED->value,
                'notes' => 'Follow-up visit',
            ]);
        }

        //  MARK CURRENT APPOINTMENT COMPLETED
        $oldAppointment->update(['status' => AppointmentStatus::CONSULTATION_COMPLETED->value]);

         //  get fully booked dates (example: 20 per day)
        $bookedDates = Appointment::selectRaw('appointment_date, COUNT(*) as total')
            ->groupBy('appointment_date')
            ->having('total', '>=', 20)
            ->pluck('appointment_date')
            ->toArray(); // IMPORTANT

        return redirect()->route('appointments.today', compact('bookedDates'))
            ->with('success','Consultation saved & next appointment created');

    }

    private function parsePrescriptionItems(string $text): array
    {
        $items = [];
        preg_match_all('/(?:^|[,;\n\r]|\s{1,})([A-Za-z0-9][A-Za-z0-9\s\-\/.\(\)]*?)\s*[-:]\s*(\d+)/u', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = trim((string) ($match[1] ?? ''));
            $qty = (int) ($match[2] ?? 0);

            if ($name !== '' && $qty > 0) {
                $items[$this->normalizeMedicineName($name)] = $qty;
            }
        }

        return $items;
    }

    private function normalizeMedicineName(string $name): string
    {
        return strtolower(preg_replace('/\s+/', '', trim($name)));
    }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultation $consultation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        //
    }
    public function storeVitals(Request $request)
{
    $request->validate([

        'appointment_id' => 'required',

        'bp' => 'nullable|string|max:20',

        'temp' => 'nullable|numeric',

        'sugar' => 'nullable|numeric',

        'pulse' => 'nullable|integer',

        'weight' => 'nullable|numeric',

        'height' => 'nullable|numeric',

        'respiratory_rate' => 'nullable|integer',

        'oxygen_saturation' => 'nullable|numeric|min:0|max:100',

        'bmi' => 'nullable|numeric',

    ]);


    $appointment = Appointment::findOrFail($request->appointment_id);

    $weight = $request->filled('weight') ? (float) $request->weight : null;
    $height = $request->filled('height') ? (float) $request->height : null;
    $bmi = $request->filled('bmi') ? (float) $request->bmi : null;

    if ($bmi === null && $weight !== null && $height !== null && $height > 0) {
        $heightMeters = $height / 100;
        $bmi = round($weight / ($heightMeters * $heightMeters), 1);
    }


    Vital::create([

        'appointment_id' => $appointment->id,

        'patient_id' => $appointment->patient_id,

        'bp' => $request->bp,

        'temp' => $request->temp,

        'sugar' => $request->sugar,

        'pulse' => $request->pulse,

        'weight' => $weight,

        'height' => $height,

        'respiratory_rate' => $request->respiratory_rate,

        'oxygen_saturation' => $request->oxygen_saturation,

        'bmi' => $bmi,

        'created_by' => auth()->id(),

    ]);

    $designation = strtolower(trim((string) auth()->user()?->designation));
    $isNurseWorkflow = auth()->user()?->hasAnyRole(['Nurse', 'Mid wife', 'Midwife'])
        || in_array($designation, ['nurse', 'mid wife', 'midwife'], true);

    if ($isNurseWorkflow
        && ! in_array(Appointment::normalizeStatus($appointment->status), [AppointmentStatus::COMPLETED->value, AppointmentStatus::CANCELLED->value, AppointmentStatus::NO_SHOW->value], true)) {
        $appointment->update(['status' => AppointmentStatus::TRIAGE_COMPLETED->value]);
    }


    return redirect()
            ->back()
            ->with('success', 'Vitals saved successfully');
}
public function indexVitals(Request $request)
{
    abort_unless(auth()->user()?->can('vitals-view'), 403);

    $search = trim((string) $request->input('search'));

    $vitals = Vital::with(['patient'])
        ->when($search !== '', function ($query) use ($search) {
            $query->whereHas('patient', function ($patientQuery) use ($search) {
                $patientQuery
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('vitals.index', compact('vitals', 'search'));
}
}
