<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $query = $this->buildFilteredQuery($filters);

        $users = (clone $query)
            ->latest('id')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $summary = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 1)->count(),
            'inactive' => (clone $query)->where('status', 0)->count(),
            'doctors' => (clone $query)->where('designation', 'Doctor')->count(),
        ];

        $units = Unit::query()->orderBy('unit_name')->get(['id', 'unit_name']);
        $designations = User::query()
            ->whereNotNull('designation')
            ->where('designation', '!=', '')
            ->distinct()
            ->orderBy('designation')
            ->pluck('designation');

        return view('reports.users.index', [
            'users' => $users,
            'summary' => $summary,
            'units' => $units,
            'designations' => $designations,
            'search' => $filters['search'],
            'designation' => $filters['designation'],
            'status' => $filters['status'],
            'unitId' => $filters['unit_id'],
            'perPage' => $filters['per_page'],
        ]);
    }

    public function print(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $users = $this->buildFilteredQuery($filters)->latest('id')->get();

        return view('reports.users.print', [
            'users' => $users,
            'filters' => $filters,
            'printMode' => true,
        ]);
    }

    public function pdf(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $users = $this->buildFilteredQuery($filters)->latest('id')->get();

        $pdf = Pdf::loadView('reports.users.pdf', [
            'users' => $users,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('user-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $users = $this->buildFilteredQuery($filters)->latest('id')->get();

        $fileName = 'user-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Email', 'Phone', 'NIC', 'Designation', 'Unit', 'Roles', 'Status', 'Join Date']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email ?? '-',
                    $user->phone ?? '-',
                    $user->nic ?? '-',
                    $user->designation ?? '-',
                    $user->unit->unit_name ?? '-',
                    $user->roles->pluck('name')->implode(', ') ?: '-',
                    ucfirst($user->status ?? '-'),
                    $user->join_date ?? '-',
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
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        return [
            'search' => trim((string) $request->input('search')),
            'designation' => trim((string) $request->input('designation')),
            'status' => trim((string) $request->input('status')),
            'unit_id' => (int) $request->input('unit_id', 0),
            'per_page' => $perPage,
        ];
    }

    private function buildFilteredQuery(array $filters)
    {
        return User::query()
            ->with(['unit:id,unit_name'])
            ->with('roles:id,name')
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nic', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%");
                });
            })
            ->when($filters['designation'] !== '', function ($q) use ($filters) {
                $q->where('designation', $filters['designation']);
            })
            ->when(in_array($filters['status'], ['1', '0'], true), function ($q) use ($filters) {
                $q->where('status', (int) $filters['status']);
            })
            ->when($filters['unit_id'] > 0, function ($q) use ($filters) {
                $q->where('unit_id', $filters['unit_id']);
            });
    }
}
