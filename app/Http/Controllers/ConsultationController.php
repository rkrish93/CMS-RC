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
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($appointment_id)
    {
         $appointment = Appointment::with('patient')->findOrFail($appointment_id);

        $history = Consultation::where('patient_id',$appointment->patient_id)
                    ->latest()
                    ->take(10)
                    ->get();

        return view('consultations.create', compact('appointment','history'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'diagnosis' => 'required'
        ]);

        Consultation::create([
            'appointment_id' => $request->appointment_id,
            'patient_id' => $request->patient_id,
            'doctor_id' => auth()->id(),

            'vitals' => [
                'temp' => $request->temp,
                'bp' => $request->bp,
                'pulse' => $request->pulse,
                'weight' => $request->weight,
                'spo2' => $request->spo2,
            ],

            'symptoms' => $request->symptoms,
            'clinical_notes' => $request->clinical_notes,
            'icd_code' => $request->icd_code,
            'diagnosis' => $request->diagnosis,
            'treatment_plan' => $request->treatment_plan,
            'next_visit' => $request->next_visit,
        ]);

        Appointment::where('id',$request->appointment_id)
            ->update(['status'=>'completed']);

        return redirect()->route('appointments.today')
            ->with('success','Consultation Completed');

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
