<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::with(['patient','unit'])
                    ->latest()
                    ->paginate(10);
        $patients = Patient::all();
        $units  = Unit::all();

    return view('appointments.index', compact('appointments','patients','units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $patients = Patient::all();
        // $units = Unit::all();
        // return view('appointments.create', compact('patients','units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'patient_id'=>'required',
        'unit_id'=>'required',
        'appointment_date'=>'required',
        'appointment_time'=>'required',
    ]);

    Appointment::create($request->all());

    return redirect()->route('appointments.index')
            ->with('success','Appointment Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
