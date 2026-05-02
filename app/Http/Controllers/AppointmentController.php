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
        abort_unless(auth()->user()?->can('appointments-view'), 403);

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
        abort_unless(auth()->user()?->can('appointments-create'), 403);

        // $patients = Patient::all();
        // $units = Unit::all();
        // return view('appointments.create', compact('patients','units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    abort_unless($request->user()?->can('appointments-create'), 403);

    Appointment::create([
    'patient_id' => $request->patient_id,
    'unit_id' => $request->unit_id,
    'appointment_date' => $request->appointment_date,
    'appointment_time' => $request->appointment_time,
    'token_no' => Appointment::whereDate('appointment_date',$request->appointment_date)->count() + 1,
    'status' => 'pending'
    ]);

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
        abort_unless(auth()->user()?->can('appointments-edit'), 403);

        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_unless($request->user()?->can('appointments-edit'), 403);

        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_unless(auth()->user()?->can('appointments-delete'), 403);

        //
    }

    public function todayQueue()
    {
    abort_unless(auth()->user()?->can('appointments-view'), 403);

    $today = now()->toDateString();

    // Only run after 4:00 PM
    if (now()->format('H:i') >= '16:00') {

    Appointment::whereDate('appointment_date', $today)
        ->where('status', 'pending')
        ->update(['status' => 'cancelled']);
    }
    
    $appointments = Appointment::with('patient')
        ->whereDate('appointment_date', $today)
        ->orderBy('token_no')
        ->get();

    return view('appointments.today', compact('appointments'));
    }
}
