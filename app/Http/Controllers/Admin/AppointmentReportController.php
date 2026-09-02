<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppointmentReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeReports($request);

        $filters = $this->filtersFromRequest($request);

        $query = $this->buildFilteredQuery($filters);

        $appointments = (clone $query)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->paginate(10)
            ->appends($request->query());

        $summary = [
            'total' => (clone $query)->count(),
            'scheduled' => (clone $query)->where('status', AppointmentStatus::SCHEDULED->value)->count(),
            'checked_in' => (clone $query)->where('status', AppointmentStatus::CHECKED_IN->value)->count(),
            'triage_in_progress' => (clone $query)->where('status', AppointmentStatus::TRIAGE_IN_PROGRESS->value)->count(),
            'triage_completed' => (clone $query)->where('status', AppointmentStatus::TRIAGE_COMPLETED->value)->count(),
            'consultation_in_progress' => (clone $query)->where('status', AppointmentStatus::CONSULTATION_IN_PROGRESS->value)->count(),
            'consultation_completed' => (clone $query)->where('status', AppointmentStatus::CONSULTATION_COMPLETED->value)->count(),
            'dispensing' => (clone $query)->where('status', AppointmentStatus::DISPENSING->value)->count(),
            'completed' => (clone $query)->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'cancelled' => (clone $query)->where('status', AppointmentStatus::CANCELLED->value)->count(),
            'no_show' => (clone $query)->where('status', AppointmentStatus::NO_SHOW->value)->count(),
        ];

        $units = Unit::query()->orderBy('unit_name')->get(['id', 'unit_name']);
        $doctors = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Doctor');
            })
            ->orderBy('fname')
            ->orderBy('lname')
            ->get(['id', 'fname', 'lname']);

        return view('reports.appointments.index', [
            'appointments' => $appointments,
            'summary' => $summary,
            'units' => $units,
            'doctors' => $doctors,
            'search' => $filters['search'],
            'status' => $filters['status'],
            'unitId' => $filters['unit_id'],
            'doctorId' => $filters['doctor_id'],
            'tokenFrom' => $filters['token_from'],
            'tokenTo' => $filters['token_to'],
            'fromDate' => $filters['from_date'],
            'toDate' => $filters['to_date'],
        ]);
    }

    public function print(Request $request)
    {
        $this->authorizeReports($request);

        $filters = $this->filtersFromRequest($request);
        $appointments = $this->buildFilteredQuery($filters)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get();

        return view('reports.appointments.print', [
            'appointments' => $appointments,
            'filters' => $filters,
            'printMode' => true,
        ]);
    }

    public function pdf(Request $request)
    {
        $this->authorizeReports($request);

        $filters = $this->filtersFromRequest($request);
        $appointments = $this->buildFilteredQuery($filters)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get();

        $pdf = Pdf::loadView('reports.appointments.pdf', [
            'appointments' => $appointments,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('appointment-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorizeReports($request);

        $filters = $this->filtersFromRequest($request);
        $appointments = $this->buildFilteredQuery($filters)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get();

        $fileName = 'appointment-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($appointments) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Time',
                'Token',
                'Patient Name',
                'Patient Code',
                'Phone',
                'Unit',
                'Doctor',
                'Status',
            ]);

            foreach ($appointments as $appointment) {
                $doctorName = trim((optional(optional($appointment->consultation)->doctor)->fname ?? '') . ' ' . (optional(optional($appointment->consultation)->doctor)->lname ?? ''));

                fputcsv($handle, [
                    optional($appointment->appointment_date)->format('Y-m-d') ?? $appointment->appointment_date,
                    $appointment->appointment_time ?? '-',
                    $appointment->token_no ?? '-',
                    trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: '-',
                    optional($appointment->patient)->patient_code ?? '-',
                    optional($appointment->patient)->phone ?? '-',
                    optional($appointment->unit)->unit_name ?? '-',
                    $doctorName !== '' ? $doctorName : '-',
                    (AppointmentStatus::fromValue($appointment->status) ?? AppointmentStatus::SCHEDULED)->getLabel(),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function authorizeReports(Request $request): void
    {
        abort_unless(
            $request->user()?->can('reports-view') || $request->user()?->hasRole('Admin'),
            403
        );

        Appointment::syncPastNoShows();
    }

    private function filtersFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search')),
            'status' => trim((string) $request->input('status')),
            'unit_id' => (int) $request->input('unit_id', 0),
            'doctor_id' => (int) $request->input('doctor_id', 0),
            'token_from' => $request->filled('token_from') ? (int) $request->input('token_from') : null,
            'token_to' => $request->filled('token_to') ? (int) $request->input('token_to') : null,
            'from_date' => trim((string) $request->input('from_date')),
            'to_date' => trim((string) $request->input('to_date')),
        ];
    }

    private function buildFilteredQuery(array $filters)
    {
        $allowedStatuses = AppointmentStatus::values();

        return Appointment::query()
            ->with([
                'patient:id,patient_code,first_name,last_name,phone,nic',
                'unit:id,unit_name',
                'consultation:id,appointment_id,doctor_id',
                'consultation.doctor:id,fname,lname',
            ])
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->whereHas('patient', function ($patientQ) use ($search) {
                    $patientQ->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nic', 'like', "%{$search}%");
                });
            })
            ->when($filters['unit_id'] > 0, function ($q) use ($filters) {
                $q->where('unit_id', $filters['unit_id']);
            })
            ->when(in_array($filters['status'], $allowedStatuses, true), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->when($filters['doctor_id'] > 0, function ($q) use ($filters) {
                $q->whereHas('consultation', function ($consultationQ) use ($filters) {
                    $consultationQ->where('doctor_id', $filters['doctor_id']);
                });
            })
            ->when($filters['token_from'] !== null, function ($q) use ($filters) {
                $q->where('token_no', '>=', $filters['token_from']);
            })
            ->when($filters['token_to'] !== null, function ($q) use ($filters) {
                $q->where('token_no', '<=', $filters['token_to']);
            })
            ->when($filters['from_date'] !== '', function ($q) use ($filters) {
                $q->whereDate('appointment_date', '>=', $filters['from_date']);
            })
            ->when($filters['to_date'] !== '', function ($q) use ($filters) {
                $q->whereDate('appointment_date', '<=', $filters['to_date']);
            });
    }
}
