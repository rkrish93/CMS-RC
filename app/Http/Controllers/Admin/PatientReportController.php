<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Vital;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PatientReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            $request->user()?->can('reports-view') || $request->user()?->hasRole('Admin'),
            403
        );

        $search = trim((string) $request->input('search'));
        $gender = trim((string) $request->input('gender'));
        $patientType = trim((string) $request->input('patient_type'));
        $fromDate = trim((string) $request->input('from_date'));
        $toDate = trim((string) $request->input('to_date'));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $query = Patient::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('nic', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($gender, ['Male', 'Female', 'Other'], true), function ($q) use ($gender) {
                $q->where('gender', $gender);
            })
            ->when(in_array($patientType, ['OPD', 'Clinic', 'Emergency'], true), function ($q) use ($patientType) {
                $q->whereRaw('UPPER(TRIM(patient_type)) = ?', [strtoupper($patientType)]);
            })
            ->when($fromDate !== '', function ($q) use ($fromDate) {
                $q->whereDate('created_at', '>=', $fromDate);
            })
            ->when($toDate !== '', function ($q) use ($toDate) {
                $q->whereDate('created_at', '<=', $toDate);
            });

        $patients = (clone $query)
            ->withCount(['appointments', 'consultations'])
            ->latest('id')
            ->paginate($perPage)
            ->appends($request->query());

        $summary = [
            'total' => (clone $query)->count(),
            'male' => (clone $query)->where('gender', 'Male')->count(),
            'female' => (clone $query)->where('gender', 'Female')->count(),
            'other' => (clone $query)->where('gender', 'Other')->count(),
        ];

        return view('reports.patients.index', [
            'patients' => $patients,
            'summary' => $summary,
            'search' => $search,
            'gender' => $gender,
            'patientType' => $patientType,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'perPage' => $perPage,
        ]);
    }

    public function show(Request $request, Patient $patient)
    {
        abort_unless(
            $request->user()?->can('reports-view') || $request->user()?->hasRole('Admin'),
            403
        );

        $patient->loadCount(['appointments', 'consultations']);

        $appointments = Appointment::query()
            ->where('patient_id', $patient->id)
            ->latest('appointment_date')
            ->take(10)
            ->get();

        $consultations = Consultation::query()
            ->with('doctor:id,fname,lname')
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(10)
            ->get();

        $vitals = Vital::query()
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(10)
            ->get();

        return view('reports.patients.show', [
            'patient' => $patient,
            'appointments' => $appointments,
            'consultations' => $consultations,
            'vitals' => $vitals,
            'printMode' => false,
        ]);
    }

    public function print(Request $request, Patient $patient)
    {
        abort_unless(
            $request->user()?->can('reports-view') || $request->user()?->hasRole('Admin'),
            403
        );

        $patient->loadCount(['appointments', 'consultations']);

        $appointments = Appointment::query()
            ->where('patient_id', $patient->id)
            ->latest('appointment_date')
            ->take(20)
            ->get();

        $consultations = Consultation::query()
            ->with('doctor:id,fname,lname')
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(20)
            ->get();

        $vitals = Vital::query()
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(20)
            ->get();

        return view('reports.patients.show', [
            'patient' => $patient,
            'appointments' => $appointments,
            'consultations' => $consultations,
            'vitals' => $vitals,
            'printMode' => true,
        ]);
    }

    public function pdf(Request $request, Patient $patient)
    {
        abort_unless(
            $request->user()?->can('reports-view') || $request->user()?->hasRole('Admin'),
            403
        );

        $patient->loadCount(['appointments', 'consultations']);

        $appointments = Appointment::query()
            ->where('patient_id', $patient->id)
            ->latest('appointment_date')
            ->take(20)
            ->get();

        $consultations = Consultation::query()
            ->with('doctor:id,fname,lname')
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(20)
            ->get();

        $vitals = Vital::query()
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(20)
            ->get();

        $pdf = Pdf::loadView('reports.patients.pdf', [
            'patient' => $patient,
            'appointments' => $appointments,
            'consultations' => $consultations,
            'vitals' => $vitals,
            'printMode' => true,
        ])->setPaper('a4', 'portrait');

        $fileName = 'patient-report-' . ($patient->patient_code ?: $patient->id) . '.pdf';

        return $pdf->download($fileName);
    }
}
