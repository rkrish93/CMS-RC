<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PharmacyStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicalStockReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeReports($request);
        $filters = $this->filtersFromRequest($request);
        $query = $this->buildFilteredQuery($filters);

        $stocks = (clone $query)
            ->latest('id')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $allStocksForSummary = (clone $query)->get();

        $today = now()->startOfDay()->format('Y-m-d');
        $expiringSoonThreshold = now()->addDays(30)->endOfDay()->format('Y-m-d');

        $summary = [
            'total_items' => $allStocksForSummary->count(),
            'total_quantity' => (int) $allStocksForSummary->sum('quantity'),
            'out_of_stock' => $allStocksForSummary->filter(fn ($s) => $s->quantity <= 0)->count(),
            'low_stock' => $allStocksForSummary->filter(function ($s) {
                $reorder = $s->product?->reorder_level ?? 10;
                return $s->quantity > 0 && $s->quantity <= $reorder;
            })->count(),
            'expired' => $allStocksForSummary->filter(function ($s) use ($today) {
                return $s->expiry_date && $s->expiry_date->format('Y-m-d') < $today;
            })->count(),
            'expiring_soon' => $allStocksForSummary->filter(function ($s) use ($today, $expiringSoonThreshold) {
                if (! $s->expiry_date) return false;
                $exp = $s->expiry_date->format('Y-m-d');
                return $exp >= $today && $exp <= $expiringSoonThreshold;
            })->count(),
        ];

        return view('reports.medical-stocks.index', [
            'stocks' => $stocks,
            'summary' => $summary,
            'search' => $filters['search'],
            'stockStatus' => $filters['stock_status'],
            'isActive' => $filters['is_active'],
            'perPage' => $filters['per_page'],
        ]);
    }

    public function print(Request $request)
    {
        $this->authorizeReports($request);
        $this->authorizeExport($request);
        $filters = $this->filtersFromRequest($request);
        $stocks = $this->buildFilteredQuery($filters)->latest('id')->get();

        return view('reports.medical-stocks.print', [
            'stocks' => $stocks,
            'filters' => $filters,
            'printMode' => true,
        ]);
    }

    public function pdf(Request $request)
    {
        $this->authorizeReports($request);
        $this->authorizeExport($request);
        $filters = $this->filtersFromRequest($request);
        $stocks = $this->buildFilteredQuery($filters)->latest('id')->get();

        $pdf = Pdf::loadView('reports.medical-stocks.pdf', [
            'stocks' => $stocks,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('medical-stock-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorizeReports($request);
        $this->authorizeExport($request);
        $filters = $this->filtersFromRequest($request);
        $stocks = $this->buildFilteredQuery($filters)->latest('id')->get();

        $fileName = 'medical-stock-report-' . now()->format('Ymd-His') . '.csv';

        $today = now()->startOfDay()->format('Y-m-d');
        $expiringSoonThreshold = now()->addDays(30)->endOfDay()->format('Y-m-d');

        return response()->streamDownload(function () use ($stocks, $today, $expiringSoonThreshold) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Product Code',
                'Medicine Name',
                'Generic Name',
                'Unit',
                'Batch No',
                'Quantity',
                'Reorder Level',
                'Expiry Date',
                'Stock Alert',
                'Status',
            ]);

            foreach ($stocks as $stock) {
                $reorderLevel = $stock->product?->reorder_level ?? 10;
                $stockAlert = '-';

                if ($stock->quantity <= 0) {
                    $stockAlert = 'Out of Stock';
                } elseif ($stock->quantity <= $reorderLevel) {
                    $stockAlert = 'Low Stock';
                }

                if ($stock->expiry_date) {
                    $exp = $stock->expiry_date->format('Y-m-d');
                    if ($exp < $today) {
                        $stockAlert = $stockAlert === '-' ? 'Expired' : $stockAlert . ' (Expired)';
                    } elseif ($exp <= $expiringSoonThreshold) {
                        $stockAlert = $stockAlert === '-' ? 'Expiring Soon' : $stockAlert . ' (Expiring Soon)';
                    }
                }

                fputcsv($handle, [
                    $stock->product?->product_code ?? '-',
                    $stock->medicine_name ?? '-',
                    $stock->generic_name ?? '-',
                    $stock->unit ?? '-',
                    $stock->batch_no ?? '-',
                    $stock->quantity ?? 0,
                    $reorderLevel,
                    $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : '-',
                    $stockAlert,
                    $stock->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function authorizeReports(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && $user->hasAnyRole(['Admin', 'Pharmacist']),
            403
        );
    }

    private function authorizeExport(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && $user->hasAnyRole(['Admin', 'Pharmacist']),
            403
        );
    }

    private function filtersFromRequest(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        return [
            'search' => trim((string) $request->input('search')),
            'stock_status' => trim((string) $request->input('stock_status')),
            'is_active' => trim((string) $request->input('is_active')),
            'per_page' => $perPage,
        ];
    }

    private function buildFilteredQuery(array $filters)
    {
        $today = now()->startOfDay()->format('Y-m-d');
        $expiringSoonThreshold = now()->addDays(30)->endOfDay()->format('Y-m-d');

        return PharmacyStock::query()
            ->with(['product:id,product_code,reorder_level,is_active'])
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('medicine_name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('batch_no', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQ) use ($search) {
                            $productQ->where('product_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($filters['is_active'], ['1', '0'], true), function ($q) use ($filters) {
                $q->where('is_active', (int) $filters['is_active']);
            })
            ->when($filters['stock_status'] !== '', function ($q) use ($filters, $today, $expiringSoonThreshold) {
                $status = $filters['stock_status'];
                if ($status === 'out_of_stock') {
                    $q->where('quantity', '<=', 0);
                } elseif ($status === 'low_stock') {
                    $q->where('quantity', '>', 0)
                        ->where(function ($sub) {
                            $sub->whereHas('product', function ($pQ) {
                                $pQ->whereRaw('pharmacy_stocks.quantity <= medicines.reorder_level');
                            })->orWhereDoesntHave('product', function ($pQ) {
                                $pQ->whereRaw('1=1');
                            })->where('quantity', '<=', 10);
                        });
                } elseif ($status === 'expired') {
                    $q->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<', $today);
                } elseif ($status === 'expiring_soon') {
                    $q->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>=', $today)
                        ->whereDate('expiry_date', '<=', $expiringSoonThreshold);
                } elseif ($status === 'normal') {
                    $q->where('quantity', '>', 0)
                        ->where(function ($sub) use ($today) {
                            $sub->whereNull('expiry_date')
                                ->orWhereDate('expiry_date', '>=', $today);
                        });
                }
            });
    }
}
