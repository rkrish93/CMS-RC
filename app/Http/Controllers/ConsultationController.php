<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Vital;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('appointments.today');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($appointment_id)
    {
         $user = auth()->user();
         abort_unless(
             $user?->can('consultations-create') || $user?->can('vitals-create') || $user?->hasAnyRole(['Doctor', 'Admin']),
             403
         );

         $appointment = Appointment::with('patient')->findOrFail($appointment_id);

         if ($user?->hasAnyRole(['Nurse', 'Mid wife']) && Vital::where('appointment_id', $appointment->id)->exists()) {
            return redirect()
                ->route('appointments.today')
                ->with('error', 'Vitals already recorded for this appointment.');
         }

         if($appointment->status == 'pending'){
        $appointment->update(['status' => 'in_progress']);
        }


        $history = Consultation::where('patient_id',$appointment->patient_id)
                    ->latest()
                    ->take(10)
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
        return view('consultations.index', compact('appointment','history', 'latestVital','previousVitals'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'diagnosis' => $request->diagnosis,
            'next_visit' => $request->next_visit,
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
                'status' => 'pending',
                'notes' => 'Follow-up visit',
            ]);
        }

        //  MARK CURRENT APPOINTMENT COMPLETED
        $oldAppointment->update(['status' => 'completed']);

         //  get fully booked dates (example: 20 per day)
        $bookedDates = Appointment::selectRaw('appointment_date, COUNT(*) as total')
            ->groupBy('appointment_date')
            ->having('total', '>=', 20)
            ->pluck('appointment_date')
            ->toArray(); // IMPORTANT

        return redirect()->route('appointments.today', compact('bookedDates'))
            ->with('success','Consultation saved & next appointment created');

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

        'temp' => 'nullable',

        'sugar' => 'nullable',

        'pulse' => 'nullable',

    ]);


    $appointment = Appointment::findOrFail($request->appointment_id);


    Vital::create([

        'appointment_id' => $appointment->id,

        'patient_id' => $appointment->patient_id,

        'bp' => $request->bp,

        'temp' => $request->temp,

        'sugar' => $request->sugar,

        'pulse' => $request->pulse,

        'created_by' => auth()->id(),

    ]);


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
