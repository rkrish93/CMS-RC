<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrescriptionReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $query = $this->buildFilteredQuery($filters);

        $prescriptions = (clone $query)
            ->latest('id')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $summary = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where(function ($q) {
                $q->whereNull('pharmacy_status')->orWhere('pharmacy_status', 'pending');
            })->count(),
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

        return view('reports.prescriptions.index', [
            'prescriptions' => $prescriptions,
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
        $prescriptions = $this->buildFilteredQuery($filters)->latest('id')->get();

        return view('reports.prescriptions.print', [
            'prescriptions' => $prescriptions,
            'filters' => $filters,
            'printMode' => true,
        ]);
    }

    public function pdf(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $prescriptions = $this->buildFilteredQuery($filters)->latest('id')->get();

        $pdf = Pdf::loadView('reports.prescriptions.pdf', [
            'prescriptions' => $prescriptions,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('prescriptions-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $prescriptions = $this->buildFilteredQuery($filters)->latest('id')->get();

        $fileName = 'prescriptions-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($prescriptions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Patient', 'Patient Code', 'Doctor', 'Unit', 'Prescribed Medicines', 'Status']);

            foreach ($prescriptions as $item) {
                $medDetails = [];
                if (is_array($item->prescription_items) && count($item->prescription_items) > 0) {
                    foreach ($item->prescription_items as $row) {
                        $medName = $row['medicine_name'] ?? $row['product_name'] ?? 'Medicine';
                        $dosage = $row['dosage'] ?? '';
                        $duration = $row['duration'] ?? '';
                        $medDetails[] = trim("{$medName} {$dosage} {$duration}");
                    }
                }
                $medString = !empty($medDetails) ? implode('; ', $medDetails) : ($item->prescription ?? '-');

                fputcsv($handle, [
                    optional($item->created_at)->format('Y-m-d H:i'),
                    trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: '-',
                    optional($item->patient)->patient_code ?? '-',
                    trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: '-',
                    optional(optional($item->appointment)->unit)->unit_name ?? '-',
                    $medString,
                    ucfirst(str_replace('_', ' ', $item->pharmacy_status ?? 'pending')),
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
            $request->user()?->hasAnyRole(['Admin', 'Pharmacist']),
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
            ->where(function ($q) {
                $q->whereNotNull('prescription_items')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('prescription')
                          ->where('prescription', '!=', '');
                  });
            })
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('prescription', 'like', "%{$search}%")
                        ->orWhere('prescription_items', 'like', "%{$search}%")
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
                if ($filters['pharmacy_status'] === 'pending') {
                    $q->where(function ($sub) {
                        $sub->whereNull('pharmacy_status')->orWhere('pharmacy_status', 'pending');
                    });
                } else {
                    $q->where('pharmacy_status', $filters['pharmacy_status']);
                }
            })
            ->when($filters['from_date'] !== '', function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['from_date']);
            })
            ->when($filters['to_date'] !== '', function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['to_date']);
            });
    }
}
