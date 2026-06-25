<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PharmacyStock;
use App\Models\Product;
use App\Services\NotifyLKService;
use Illuminate\Http\Request;

class PharmacyStockController extends Controller
{
    public function sendPatientSms(string $consultation)
    {
        abort_unless(
            auth()->user()?->can('pharmacy-prescriptions-dispense') || auth()->user()?->hasRole('Admin'),
            403
        );

        $record = Consultation::with('patient')->findOrFail($consultation);
        $redirect = redirect()->route('pharmacy.prescriptions.index');

        if ((bool) $record->is_locked) {
            return $redirect->with('success', 'Prescription already locked after SMS.');
        }

        $phone = trim((string) optional($record->patient)->phone);
        if ($phone === '') {
            return $redirect->with('error', 'Patient phone number is missing.');
        }

        $items = $this->parsePrescriptionItems((string) ($record->prescription ?? ''));
        $dispensed = is_array($record->dispensed_breakdown) ? $record->dispensed_breakdown : [];

        $stockMap = $this->buildStockMap();
        $parts = [];

        foreach ($items as $normalized => $prescribedQty) {
            $given = (int) ($dispensed[$normalized] ?? 0);
            $remaining = max((int) $prescribedQty - $given, 0);

            if ($remaining <= 0) {
                continue;
            }

            $stock = (int) ($stockMap[$normalized] ?? 0);
            $parts[] = ucfirst($normalized) . " R{$remaining} S{$stock}";
        }

        if (empty($parts)) {
            $fallbackRemaining = max((int) ($record->prescribed_quantity ?? 0) - (int) ($record->dispensed_quantity ?? 0), 0);
            if ($fallbackRemaining <= 0) {
                return $redirect->with('success', 'Prescription already completed. No SMS needed.');
            }
            $parts[] = "Remaining {$fallbackRemaining}";
        }

        $message = 'CMS-RC Pharmacy update: ' . implode('; ', array_slice($parts, 0, 4)) . '. Please visit pharmacy.';

        $sent = $this->dispatchSms($phone, $message);

        if ($sent) {
            $record->update([
                'is_locked' => true,
            ]);

            return $redirect->with('success', 'Patient SMS sent successfully.');
        }

        return $redirect->with('error', 'SMS sending failed. Check NotifyLK settings.');
    }

    public function prescriptions(Request $request)
    {
        abort_unless(
            auth()->user()?->can('pharmacy-prescriptions-view') || auth()->user()?->hasRole('Admin'),
            403
        );

        $search = trim((string) $request->input('search'));
        $status = trim((string) $request->input('status'));

        $prescriptions = Consultation::query()
            ->with(['patient:id,patient_code,first_name,last_name', 'doctor:id,fname,lname'])
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->when(in_array($status, ['pending', 'partial', 'dispensed']), function ($query) use ($status) {
                $query->where('pharmacy_status', $status);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where('prescription', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('patient_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $newPrescriptionCount = Consultation::query()
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->where('pharmacy_status', 'pending')
            ->where('is_locked', false)
            ->where('created_at', '>=', now()->subHours(6))
            ->count();

        $medicineKeys = [];
        foreach ($prescriptions->items() as $consultation) {
            $parsed = $this->parsePrescriptionItems((string) ($consultation->prescription ?? ''));
            foreach (array_keys($parsed) as $key) {
                $medicineKeys[$key] = true;
            }
        }

        $stockByMedicine = [];
        if (! empty($medicineKeys)) {
            $stocks = PharmacyStock::query()
                ->where('is_active', true)
                ->get(['medicine_name', 'generic_name', 'quantity']);

            foreach ($stocks as $stock) {
                $qty = (int) $stock->quantity;

                $medNameKey = $this->normalizeMedicineName((string) $stock->medicine_name);
                if ($medNameKey !== '') {
                    $stockByMedicine[$medNameKey] = ($stockByMedicine[$medNameKey] ?? 0) + $qty;
                }

                $genericKey = $this->normalizeMedicineName((string) ($stock->generic_name ?? ''));
                if ($genericKey !== '') {
                    $stockByMedicine[$genericKey] = ($stockByMedicine[$genericKey] ?? 0) + $qty;
                }
            }
        }

        return view('pharmacy.prescriptions.index', compact('prescriptions', 'search', 'status', 'newPrescriptionCount', 'stockByMedicine'));
    }

    public function markDispensed(Request $request, string $consultation)
    {
        abort_unless(
            auth()->user()?->can('pharmacy-prescriptions-dispense') || auth()->user()?->hasRole('Admin'),
            403
        );

        $validated = $request->validate([
            'medicine_name' => 'required|string|max:150',
            'dispense_quantity' => 'required|integer|min:1',
            'pharmacy_note' => 'nullable|string',
        ]);

        $record = Consultation::with('patient')->findOrFail($consultation);

        if ((bool) $record->is_locked) {
            return back()->with('error', 'Prescription is locked after SMS. Add Given is disabled.');
        }

        $normalizedMedicine = $this->normalizeMedicineName($validated['medicine_name']);
        $prescriptionItems = $this->parsePrescriptionItems((string) ($record->prescription ?? ''));
        $prescribedForMedicine = $prescriptionItems[$normalizedMedicine] ?? null;

        if (! empty($prescriptionItems) && $prescribedForMedicine === null) {
            return back()->with('error', "{$validated['medicine_name']} is not in this prescription list.");
        }

        $dispensedBreakdown = is_array($record->dispensed_breakdown) ? $record->dispensed_breakdown : [];
        $alreadyDispensedForMedicine = (int) ($dispensedBreakdown[$normalizedMedicine] ?? 0);

        $stock = PharmacyStock::query()
            ->where('is_active', true)
            ->where(function ($query) use ($validated) {
                $query->whereRaw('LOWER(medicine_name) = ?', [strtolower($validated['medicine_name'])])
                    ->orWhereRaw('LOWER(generic_name) = ?', [strtolower($validated['medicine_name'])])
                    ->orWhere('medicine_name', 'like', '%' . $validated['medicine_name'] . '%')
                    ->orWhere('generic_name', 'like', '%' . $validated['medicine_name'] . '%');
            })
            ->orderBy('expiry_date')
            ->first();

        if (! $stock) {
            $remainingRequested = $prescribedForMedicine ?? (int) $validated['dispense_quantity'];
            $sent = $this->sendShortageSms(
                $record,
                $validated['medicine_name'],
                $remainingRequested,
                0,
                $remainingRequested,
                0
            );

            return back()->with('error', "Medicine not found in stock: {$validated['medicine_name']}. " . ($sent ? 'Patient SMS sent via NotifyLK.' : 'SMS failed.'));
        }

        $availableStock = (int) $stock->quantity;
        if ($availableStock <= 0) {
            $requested = $prescribedForMedicine ?? (int) $validated['dispense_quantity'];
            $sent = $this->sendShortageSms(
                $record,
                $validated['medicine_name'],
                $requested,
                0,
                $requested,
                0
            );

            return back()->with('error', "Out of stock for {$validated['medicine_name']}. " . ($sent ? 'Patient SMS sent via NotifyLK.' : 'SMS failed.'));
        }

        $prescribed = (int) ($record->prescribed_quantity ?? 0);
        $alreadyDispensed = (int) ($record->dispensed_quantity ?? 0);

        if ($prescribedForMedicine !== null) {
            $remainingPrescription = max($prescribedForMedicine - $alreadyDispensedForMedicine, 0);
        } else {
            $remainingPrescription = $prescribed > 0
                ? max($prescribed - $alreadyDispensed, 0)
                : (int) $validated['dispense_quantity'];
        }

        if ($remainingPrescription <= 0) {
            return back()->with('error', 'Prescription already completed.');
        }

        $requestedQuantity = (int) $validated['dispense_quantity'];
        $dispenseNow = min($requestedQuantity, $availableStock, $remainingPrescription);
        $dispensedBreakdown[$normalizedMedicine] = $alreadyDispensedForMedicine + $dispenseNow;
        $newTotalDispensed = array_sum(array_map('intval', $dispensedBreakdown));

        if ($prescribed > 0 && $newTotalDispensed > $prescribed) {
            return back()->with('error', 'Given quantity cannot exceed prescribed quantity.');
        }

        if ($dispenseNow <= 0) {
            return back()->with('error', 'Unable to dispense with current stock and prescription balance.');
        }

        $totalPrescribed = $prescribed;
        if (! empty($prescriptionItems)) {
            $totalPrescribed = array_sum($prescriptionItems);
        }

        $status = 'dispensed';
        if ($totalPrescribed > 0) {
            $status = $newTotalDispensed >= $totalPrescribed ? 'dispensed' : 'partial';
        }

        $stock->update([
            'quantity' => max($availableStock - $dispenseNow, 0),
        ]);

        $record->update([
            'prescribed_quantity' => $totalPrescribed > 0 ? $totalPrescribed : $newTotalDispensed,
            'dispensed_quantity' => $newTotalDispensed,
            'dispensed_breakdown' => $dispensedBreakdown,
            'pharmacy_note' => trim(($validated['pharmacy_note'] ?? $record->pharmacy_note ?? '') . "\nMedicine: {$stock->medicine_name}; Given now: {$dispenseNow}"),
            'pharmacy_status' => $status,
            'dispensed_at' => $status === 'dispensed' ? now() : null,
        ]);

        $remainingForMedicine = $prescribedForMedicine !== null
            ? max($prescribedForMedicine - (int) ($dispensedBreakdown[$normalizedMedicine] ?? 0), 0)
            : max(((int) $record->prescribed_quantity) - $newTotalDispensed, 0);

        if ($dispenseNow < $requestedQuantity || $remainingForMedicine > 0) {
            $requestedForMsg = $prescribedForMedicine ?? $requestedQuantity;
            $availableAfter = max($availableStock - $dispenseNow, 0);
            $this->sendShortageSms(
                $record,
                $stock->medicine_name,
                $requestedForMsg,
                $dispenseNow,
                $remainingForMedicine,
                $availableAfter
            );
        }

        if ($status === 'dispensed') {
            return back()->with('success', 'Prescription fully dispensed.');
        }

        return back()->with('success', "Partial dispense saved for {$stock->medicine_name} ({$dispenseNow} given). Remaining: {$remainingForMedicine}.");
    }

    private function parsePrescriptionItems(string $text): array
    {
        $items = [];
        preg_match_all('/(?:^|[,;\n\r]|\s{1,})([A-Za-z0-9][A-Za-z0-9\s\-\/.\(\)]*?)\s*[-:]\s*(\d+)/u', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = trim((string) ($match[1] ?? ''));
            $qty = (int) ($match[2] ?? 0);

            if ($name !== '' && $qty > 0) {
                $items[$this->normalizeMedicineName($name)] = $qty;
            }
        }

        return $items;
    }

    private function normalizeMedicineName(string $name): string
    {
        return strtolower(preg_replace('/\s+/', '', trim($name)));
    }

    private function sendShortageSms(
        Consultation $record,
        string $medicineName,
        int $requested,
        int $givenNow,
        int $remaining,
        int $availableStock
    ): bool
    {
        $phone = trim((string) optional($record->patient)->phone);
        if ($phone === '') {
            return false;
        }

        $message = "CMS-RC Pharmacy update: {$medicineName}. Requested {$requested} tablets, available {$availableStock}, given {$givenNow}, remaining {$remaining}. Please contact pharmacy.";

        return $this->dispatchSms($phone, $message);
    }

    private function dispatchSms(string $phone, string $message): bool
    {
        try {
            $response = NotifyLKService::send($phone, $message);

            if (! $response->successful()) {
                return false;
            }

            $json = $response->json();
            if (is_array($json) && array_key_exists('status', $json)) {
                $status = strtolower(trim((string) $json['status']));

                return in_array($status, ['success', 'ok', '1', 'true'], true);
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function buildStockMap(): array
    {
        $stockByMedicine = [];

        $stocks = PharmacyStock::query()
            ->where('is_active', true)
            ->get(['medicine_name', 'generic_name', 'quantity']);

        foreach ($stocks as $stock) {
            $qty = (int) $stock->quantity;

            $medNameKey = $this->normalizeMedicineName((string) $stock->medicine_name);
            if ($medNameKey !== '') {
                $stockByMedicine[$medNameKey] = ($stockByMedicine[$medNameKey] ?? 0) + $qty;
            }

            $genericKey = $this->normalizeMedicineName((string) ($stock->generic_name ?? ''));
            if ($genericKey !== '') {
                $stockByMedicine[$genericKey] = ($stockByMedicine[$genericKey] ?? 0) + $qty;
            }
        }

        return $stockByMedicine;
    }

    public function index(Request $request)
    {
        abort_unless(
            auth()->user()?->can('pharmacy-stocks-view') || auth()->user()?->hasRole('Admin'),
            403
        );

        $search = trim((string) $request->input('search'));

        $stocks = PharmacyStock::query()
            ->with('product:id,product_code,medicine_name,generic_name,unit,expiry_date')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('medicine_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('batch_no', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('product_code', 'like', "%{$search}%")
                            ->orWhere('medicine_name', 'like', "%{$search}%")
                            ->orWhere('generic_name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('medicine_name')
            ->get(['id', 'product_code', 'medicine_name', 'generic_name', 'unit', 'expiry_date']);

        return view('pharmacy.stocks.index', compact('stocks', 'search', 'products'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('pharmacy-stocks-create') || auth()->user()?->hasRole('Admin'), 403);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_no' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $validated['medicine_name'] = $product->medicine_name;
        $validated['generic_name'] = $product->generic_name;
        $validated['unit'] = $product->unit;
        $validated['is_active'] = $request->boolean('is_active', true);

        PharmacyStock::create($validated);

        return back()->with('success', 'Pharmacy stock item created successfully.');
    }

    public function update(Request $request, string $id)
    {
        abort_unless(auth()->user()?->can('pharmacy-stocks-edit') || auth()->user()?->hasRole('Admin'), 403);

        $stock = PharmacyStock::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_no' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $validated['medicine_name'] = $product->medicine_name;
        $validated['generic_name'] = $product->generic_name;
        $validated['unit'] = $product->unit;
        $validated['is_active'] = $request->boolean('is_active');

        $stock->update($validated);

        return back()->with('success', 'Pharmacy stock item updated successfully.');
    }

    public function destroy(string $id)
    {
        abort_unless(auth()->user()?->can('pharmacy-stocks-delete') || auth()->user()?->hasRole('Admin'), 403);

        PharmacyStock::findOrFail($id)->delete();

        return back()->with('success', 'Pharmacy stock item deleted successfully.');
    }
}
