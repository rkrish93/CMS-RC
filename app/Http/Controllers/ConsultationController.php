<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
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
         $appointment = Appointment::with('patient')->findOrFail($appointment_id);

         if($appointment->status == 'pending'){
        $appointment->update(['status' => 'in_progress']);
        }


        $history = Consultation::where('patient_id',$appointment->patient_id)
                    ->latest()
                    ->take(10)
                    ->get();

        return view('consultations.index', compact('appointment','history'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $appointment = Appointment::findOrFail($request->appointment_id);

        $request->validate([
            'diagnosis' => 'required'
        ]);

        Consultation::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => auth()->id(),
            'diagnosis' => $request->diagnosis,

        //     'vitals' => [
        //     'bp' => $request->bp,
        //     'temp' => $request->temp,
        //     'sugar' => $request->sugar,
        //     'pulse' => $request->pulse,
        // ],

            // 'symptoms' => $request->symptoms,
            // 'clinical_notes' => $request->clinical_notes,
            // 'icd_code' => $request->icd_code,
            // 'diagnosis' => $request->diagnosis,
            // 'treatment_plan' => $request->treatment_plan,
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
                            ->where('appointment_date', $request->next_visit_date)
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
}
