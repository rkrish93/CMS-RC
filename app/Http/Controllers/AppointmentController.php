<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vital;
use App\Services\NotifyLKService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('appointments-view'), 403);

        $query = Appointment::with(['patient', 'unit']);

        $unitScopedRoles = ['Doctor', 'Nurse', 'Mid wife'];

        if ($request->user()->hasAnyRole($unitScopedRoles)) {
            $query->where('unit_id', $request->user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('appointment_date')) {
            $query->whereDate('appointment_date', $request->appointment_date);
        }

        $appointments = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $patients = Patient::all();
        $units = $request->user()->hasAnyRole($unitScopedRoles)
            ? Unit::where('id', $request->user()->unit_id)->get()
            : Unit::orderBy('unit_name')->get();

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

    $user = auth()->user();
    $unitScopedRoles = ['Doctor', 'Nurse', 'Mid wife'];

    $today = now()->toDateString();

    // Auto cancel pending after 4 PM
    if (now()->format('H:i') >= '16:00') {

        Appointment::whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }


    // DOCTOR / NURSE / MID WIFE -> ONLY OWN UNIT
    if ($user->hasAnyRole($unitScopedRoles)) {

        $appointments = Appointment::with(['patient', 'unit'])
            ->withCount('vitals')

            ->where('unit_id', $user->unit_id)

            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['checked_in', 'in_progress', 'nurse_done'])

            ->orderBy('token_no')

            ->get();

    } else {

        // ADMIN / RECEPTIONIST → ALL ACTIVE STATUSES (pending, in_progress, nurse_done)
        $appointments = Appointment::with(['patient', 'unit'])
            ->withCount('vitals')

            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['completed', 'cancelled', 'no_show'])

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

    public function qrPass(Appointment $appointment)
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasAnyRole(['Receptionist', 'Admin']) || $user?->can('appointments-view'),
            403
        );

        if (in_array((string) $appointment->status, ['cancelled', 'completed', 'no_show'], true)) {
            return redirect()->route('appointments.today')
                ->with('error', 'QR pass cannot be generated for cancelled/completed/no-show appointments.');
        }

        if ((string) $appointment->status === 'pending') {
            $appointment->update(['status' => 'checked_in']);
            $appointment->refresh();
        }

        $appointment->load(['patient', 'unit']);

        $scanUrl = URL::signedRoute('patient.flow.scan', ['appointment' => $appointment->id]);
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($scanUrl);

        return view('appointments.qr-pass', compact('appointment', 'scanUrl', 'qrImageUrl'));
    }

    public function scanPatientFlow(Request $request, Appointment $appointment)
    {
        abort_unless($request->hasValidSignature(), 403);

        $user = auth()->user();
        $unitScopedRoles = ['Doctor', 'Nurse', 'Mid wife', 'Midwife'];

        abort_unless(
            $user?->can('appointments-view')
                || $user?->can('consultations-create')
                || $user?->can('vitals-create')
                || $user?->can('pharmacy-prescriptions-view')
                || $user?->hasRole('Admin'),
            403
        );

        if ($user?->hasAnyRole($unitScopedRoles) && (int) $user->unit_id !== (int) $appointment->unit_id) {
            abort(403);
        }

        if (in_array((string) $appointment->status, ['cancelled', 'no_show'], true)) {
            return redirect()->route('patient.flow.scanner')
                ->with('error', 'This appointment is not eligible for scan flow.');
        }

        if ($user?->hasAnyRole($unitScopedRoles) && (string) $appointment->status === 'pending') {
            return redirect()->route('patient.flow.scanner')
                ->with('error', 'Patient is not checked-in yet. Reception must generate QR first.');
        }

        $appointment->load(['patient', 'unit']);

        $previousVitals = Vital::query()
            ->where('patient_id', $appointment->patient_id)
            ->latest()
            ->take(10)
            ->get();

        $oldConsultations = Consultation::query()
            ->where('patient_id', $appointment->patient_id)
            ->latest()
            ->take(5)
            ->get();

        $consultationForPharmacy = Consultation::query()
            ->where('appointment_id', $appointment->id)
            ->latest()
            ->first();

        return view('patient_flow.scan', compact(
            'appointment',
            'previousVitals',
            'oldConsultations',
            'consultationForPharmacy'
        ));
    }

    public function qrScanner(Request $request)
    {
        $user = auth()->user();
        $unitScopedRoles = ['Doctor', 'Nurse', 'Mid wife', 'Midwife'];
        $isPharmacyUser = (bool) ($user?->can('pharmacy-prescriptions-view') || $user?->hasRole('Pharmacist'));

        abort_unless(
            $user?->can('appointments-view')
                || $user?->can('consultations-create')
                || $user?->can('vitals-create')
                || $user?->can('pharmacy-prescriptions-view')
                || $user?->hasAnyRole(['Receptionist', 'Admin', 'Doctor', 'Nurse', 'Mid wife', 'Midwife', 'Pharmacist']),
            403
        );

        $search = trim((string) $request->input('search'));

        $appointments = Appointment::query()
            ->with(['patient:id,patient_code,first_name,last_name,phone', 'unit:id,unit_name'])
            ->whereDate('appointment_date', now()->toDateString())
            ->when($isPharmacyUser, function ($query) {
                $query->whereHas('consultation', function ($consultationQuery) {
                    $consultationQuery->where(function ($q) {
                        $q->whereNotNull('prescription_items')
                            ->orWhere(function ($subQ) {
                                $subQ->whereNotNull('prescription')
                                    ->where('prescription', '!=', '');
                            });
                    });
                });
            })
            ->when($isPharmacyUser, function ($query) {
                $query->whereNotIn('status', ['cancelled', 'no_show']);
            }, function ($query) {
                $query->whereNotIn('status', ['completed', 'cancelled', 'no_show']);
            })
            ->when($user?->hasAnyRole($unitScopedRoles), function ($query) {
                $query->whereIn('status', ['checked_in', 'in_progress', 'nurse_done']);
            })
            ->when($user?->hasAnyRole($unitScopedRoles), function ($query) use ($user) {
                $query->where('unit_id', $user->unit_id);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('token_no', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($patientQuery) use ($search) {
                            $patientQuery->where('patient_code', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('token_no')
            ->limit(30)
            ->get();

        $signedScanUrls = [];
        foreach ($appointments as $appointment) {
            $signedScanUrls[$appointment->id] = URL::signedRoute('patient.flow.scan', ['appointment' => $appointment->id]);
        }

        return view('patient_flow.scanner', compact('appointments', 'search', 'signedScanUrls'));
    }

    public function markNoShow(Appointment $appointment)
    {
        $user = auth()->user();

        abort_unless($user?->hasAnyRole(['Receptionist', 'Admin']) || $user?->can('appointments-edit'), 403);

        if (in_array((string) $appointment->status, ['completed', 'cancelled'], true)) {
            return redirect()->route('appointments.today')
                ->with('error', 'Cannot mark completed/cancelled appointment as no-show.');
        }

        $appointment->update(['status' => 'no_show']);

        return redirect()->route('appointments.today')->with('success', 'Appointment marked as no-show.');
    }
}
