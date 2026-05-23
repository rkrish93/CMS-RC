<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotifyLKService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
          if(auth()->user()->hasRole('doctor')) {

        $appointments = Appointment::with(['patient', 'unit'])
            ->where('unit_id', auth()->user()->unit_id)
            ->latest()
            ->paginate(10);

    } else {

        $appointments = Appointment::with(['patient', 'unit'])
            ->latest()
            ->paginate(10);

    }
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

        $patients = Patient::all();
        $units = Unit::all();
        return view('appointments.create', compact('patients','units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()?->can('appointments-create'), 403);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'unit_id' => 'required|exists:units,id',
            'appointment_date' => 'required|date|after_or_equal:today',
        ]);

        try {
            $appointmentDate = $validated['appointment_date'];
            $clinicOpenTime = Carbon::parse($appointmentDate . ' 09:00');
            $clinicCloseTime = Carbon::parse($appointmentDate . ' 15:00');
            $slotDuration = 15;

            // Get the last appointment for this date
            $lastAppointment = Appointment::whereDate( 'appointment_date',$appointmentDate
    )->where('unit_id', $validated['unit_id'])->orderByDesc('appointment_time')->first();

            if ($lastAppointment) {
                $lastTime = Carbon::parse($lastAppointment->appointment_time);
                $nextTime = $lastTime->copy()->addMinutes($slotDuration);

                if ($nextTime->greaterThan($clinicCloseTime)) {
                    return back()
                        ->withInput()
                        ->with('error', 'No slots available for this date. All appointment slots are fully booked.');
                }

                $appointmentTime = $nextTime->format('H:i:s');
                $tokenNo = $lastAppointment->token_no + 1;
            } else {
                $appointmentTime = $clinicOpenTime->format('H:i:s');
                $tokenNo = 1;
            }

            Appointment::create([
                'patient_id' => $validated['patient_id'],
                'unit_id' => $validated['unit_id'],
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'token_no' => $tokenNo,
                'status' => 'pending'
            ]);

            // Get patient details
            $patient = Patient::find($validated['patient_id']);

            // SMS message
            $message = "Dear {$patient->first_name}, Your appointment is confirmed on {$appointmentDate} at {$appointmentTime}. Token No: {$tokenNo}.";

            $phone = preg_replace('/\D/', '', $patient->phone);

            if (str_starts_with($phone, '0')) {
                $phone = '94' . substr($phone, 1);
            }

            // Send SMS
            // NotifyLKService::send($phone, $message);

            return redirect()->route('appointments.index')
                ->with('success', "Appointment created successfully. Token #$tokenNo at $appointmentTime");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create appointment. Please try again.');
        }
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

    // Auto cancel pending after 4 PM
    if (now()->format('H:i') >= '16:00') {

        Appointment::whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }


    // DOCTOR → ONLY OWN UNIT
    if(auth()->user()->hasRole('doctor')) {

        $appointments = Appointment::with(['patient', 'unit'])

            ->where('unit_id', auth()->user()->unit_id)

            ->whereDate('appointment_date', $today)

            ->orderBy('token_no')

            ->get();

    } else {

        // ADMIN / RECEPTIONIST → ALL
        $appointments = Appointment::with(['patient', 'unit'])

            ->whereDate('appointment_date', $today)

            ->orderBy('token_no')

            ->get();
    }

    return view(
        'appointments.today',
        compact('appointments')
    );
}

    public function searchPatient(Request $request)
    {
        $query = $request->get('query');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $patients = Patient::where('phone', 'like', "%{$query}%")
            ->orWhere('nic', 'like', "%{$query}%")
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($patients);
    }
}
