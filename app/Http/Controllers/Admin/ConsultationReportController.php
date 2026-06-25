<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultationReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $query = $this->buildFilteredQuery($filters);

        $consultations = (clone $query)
            ->latest('id')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $summary = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('pharmacy_status', 'pending')->count(),
            'partial' => (clone $query)->where('pharmacy_status', 'partial')->count(),
            'dispensed' => (clone $query)->where('pharmacy_status', 'dispensed')->count(),
        ];

        $doctors = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Doctor');
            })
            ->orderBy('fname')
            ->orderBy('lname')
            ->get(['id', 'fname', 'lname']);

        $units = Unit::query()
            ->orderBy('unit_name')
            ->get(['id', 'unit_name']);

        return view('reports.consultations.index', [
            'consultations' => $consultations,
            'summary' => $summary,
            'doctors' => $doctors,
            'units' => $units,
            'search' => $filters['search'],
            'doctorId' => $filters['doctor_id'],
            'unitId' => $filters['unit_id'],
            'pharmacyStatus' => $filters['pharmacy_status'],
            'fromDate' => $filters['from_date'],
            'toDate' => $filters['to_date'],
            'perPage' => $filters['per_page'],
        ]);
    }

    public function print(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $consultations = $this->buildFilteredQuery($filters)->latest('id')->get();

        return view('reports.consultations.print', [
            'consultations' => $consultations,
            'filters' => $filters,
            'printMode' => true,
        ]);
    }

    public function pdf(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $consultations = $this->buildFilteredQuery($filters)->latest('id')->get();

        $pdf = Pdf::loadView('reports.consultations.pdf', [
            'consultations' => $consultations,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('consultation-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $consultations = $this->buildFilteredQuery($filters)->latest('id')->get();

        $fileName = 'consultation-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($consultations) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Patient', 'Code', 'Doctor', 'Unit', 'Diagnosis', 'Prescription', 'Pharmacy Status']);

            foreach ($consultations as $consultation) {
                fputcsv($handle, [
                    optional($consultation->created_at)->format('Y-m-d H:i'),
                    trim((optional($consultation->patient)->first_name ?? '') . ' ' . (optional($consultation->patient)->last_name ?? '')) ?: '-',
                    optional($consultation->patient)->patient_code ?? '-',
                    trim((optional($consultation->doctor)->fname ?? '') . ' ' . (optional($consultation->doctor)->lname ?? '')) ?: '-',
                    optional(optional($consultation->appointment)->unit)->unit_name ?? '-',
                    $consultation->diagnosis ?? '-',
                    $consultation->prescription ?? '-',
                    ucfirst(str_replace('_', ' ', $consultation->pharmacy_status ?? '-')),
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
    }

    private function filtersFromRequest(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        return [
            'search' => trim((string) $request->input('search')),
            'doctor_id' => (int) $request->input('doctor_id', 0),
            'unit_id' => (int) $request->input('unit_id', 0),
            'pharmacy_status' => trim((string) $request->input('pharmacy_status')),
            'from_date' => trim((string) $request->input('from_date')),
            'to_date' => trim((string) $request->input('to_date')),
            'per_page' => $perPage,
        ];
    }

    private function buildFilteredQuery(array $filters)
    {
        $allowedPharmacyStatuses = ['pending', 'partial', 'dispensed'];

        return Consultation::query()
            ->with([
                'patient:id,patient_code,first_name,last_name,phone',
                'doctor:id,fname,lname',
                'appointment:id,unit_id,appointment_date,appointment_time,token_no',
                'appointment.unit:id,unit_name',
            ])
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('diagnosis', 'like', "%{$search}%")
                        ->orWhere('prescription', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($patientQ) use ($search) {
                            $patientQ->where('patient_code', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['doctor_id'] > 0, function ($q) use ($filters) {
                $q->where('doctor_id', $filters['doctor_id']);
            })
            ->when($filters['unit_id'] > 0, function ($q) use ($filters) {
                $q->whereHas('appointment', function ($appointmentQ) use ($filters) {
                    $appointmentQ->where('unit_id', $filters['unit_id']);
                });
            })
            ->when(in_array($filters['pharmacy_status'], $allowedPharmacyStatuses, true), function ($q) use ($filters) {
                $q->where('pharmacy_status', $filters['pharmacy_status']);
            })
            ->when($filters['from_date'] !== '', function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['from_date']);
            })
            ->when($filters['to_date'] !== '', function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['to_date']);
            });
    }
}
