<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PharmacyStock;
use App\Models\Product;
use App\Services\NotifyLKService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $phone = $this->resolvePatientPhone($record);
        if ($phone === '') {
            return $redirect->with('error', 'Patient phone number is missing or invalid.');
        }

        $items = $this->getPrescriptionItemsWithNames($record);
        if (empty($items)) {
            return $redirect->with('error', 'No prescription items found for this consultation.');
        }

        $metaByMedicine = $this->getPrescriptionMetaByMedicine($record);
        $dispensed = is_array($record->dispensed_breakdown) ? $record->dispensed_breakdown : [];

        $stockMap = $this->buildStockMap();
        $parts = [];

        foreach ($items as $normalized => $itemData) {
            $prescribedQty = (int) ($itemData['qty'] ?? 0);
            $displayName = (string) ($itemData['name'] ?? $normalized);

            $given = (int) ($dispensed[$normalized] ?? 0);
            $remaining = max($prescribedQty - $given, 0);

            if ($remaining <= 0) {
                continue;
            }

            $stock = (int) ($stockMap[$normalized] ?? 0);
            $detailSuffix = $this->formatDoseDurationDetails($metaByMedicine[$normalized] ?? []);

            if ($stock <= 0) {
                $parts[] = "{$displayName}{$detailSuffix}: not available (need {$remaining})";
                continue;
            }

            if ($stock < $remaining) {
                $parts[] = "{$displayName}{$detailSuffix}: need {$remaining}, available {$stock}";
            }
        }

        if (empty($parts)) {
            return $redirect->with('success', 'No shortage medicines found. SMS not sent.');
        }

        $message = 'CMS-RC Pharmacy: Some prescribed medicines are currently unavailable. Prescription details - '
            . implode('; ', array_slice($parts, 0, 4))
            . '. Please contact pharmacy.';

        $sent = $this->dispatchSms($phone, $message);

        if ($sent) {
            $record->update([
                'is_locked' => true,
            ]);

            return $redirect->with('success', 'Shortage SMS sent to patient via NotifyLK successfully.');
        }

        return $redirect->with('error', 'NotifyLK SMS sending failed. Check NotifyLK settings.');
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
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('prescription')
                        ->where('prescription', '!=', '');
                })->orWhereNotNull('prescription_items');
            })
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
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('prescription')
                        ->where('prescription', '!=', '');
                })->orWhereNotNull('prescription_items');
            })
            ->where('pharmacy_status', 'pending')
            ->where('is_locked', false)
            ->where('created_at', '>=', now()->subHours(6))
            ->count();

        $medicineKeys = [];
        foreach ($prescriptions->items() as $consultation) {
            foreach (array_keys($this->getPrescriptionItemsWithNames($consultation)) as $key) {
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
            'medicine_name' => 'nullable|string|max:150',
            'dispense_quantity' => 'nullable|integer|min:1',
            'pharmacy_note' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.medicine_name' => 'required_with:medicines|string|max:150',
            'medicines.*.dispense_quantity' => 'nullable|integer|min:0',
        ]);

        $record = Consultation::with('patient')->findOrFail($consultation);

        if ((bool) $record->is_locked) {
            return back()->with('error', 'Prescription is locked after SMS. Add Given is disabled.');
        }

        $entries = [];

        if (is_array($validated['medicines'] ?? null)) {
            foreach ($validated['medicines'] as $entry) {
                $name = trim((string) ($entry['medicine_name'] ?? ''));
                $qty = (int) ($entry['dispense_quantity'] ?? 0);

                if ($name !== '' && $qty > 0) {
                    $entries[] = [
                        'medicine_name' => $name,
                        'dispense_quantity' => $qty,
                    ];
                }
            }
        }

        if (empty($entries) && ! empty($validated['medicine_name']) && ! empty($validated['dispense_quantity'])) {
            $entries[] = [
                'medicine_name' => (string) $validated['medicine_name'],
                'dispense_quantity' => (int) $validated['dispense_quantity'],
            ];
        }

        if (empty($entries)) {
            return back()->with('error', 'Please enter at least one medicine quantity greater than zero.');
        }

        $successMessages = [];
        $errorMessages = [];
        $pharmacyNote = trim((string) ($validated['pharmacy_note'] ?? ''));

        foreach ($entries as $index => $entry) {
            $result = $this->dispenseSingleMedicine(
                $record,
                (string) $entry['medicine_name'],
                (int) $entry['dispense_quantity'],
                $index === 0 ? $pharmacyNote : ''
            );

            if ((bool) ($result['ok'] ?? false)) {
                $successMessages[] = (string) ($result['message'] ?? 'Saved.');
                /** @var Consultation $record */
                $record = $result['record'];
            } else {
                $errorMessages[] = (string) ($result['message'] ?? 'Failed to save medicine.');
            }
        }

        if (empty($successMessages)) {
            return back()->with('error', $errorMessages[0] ?? 'Unable to save given medicines.');
        }

        $record = $record->refresh();

        $summary = count($successMessages) . ' medicine item(s) saved.';

        if (($record->pharmacy_status ?? 'pending') === 'dispensed') {
            $summary .= ' Status changed to Dispensed.';
        } elseif (($record->pharmacy_status ?? 'pending') === 'partial') {
            $summary .= ' Status changed to Partial. Use Send Shortage SMS if needed.';
        }

        if (! empty($errorMessages)) {
            $summary .= ' Some items failed: ' . implode(' | ', array_slice($errorMessages, 0, 2));
        }

        return back()->with('success', $summary);
    }

    private function dispenseSingleMedicine(Consultation $record, string $medicineName, int $requestedQuantity, string $pharmacyNote = ''): array
    {
        $medicineName = trim($medicineName);
        if ($medicineName === '' || $requestedQuantity <= 0) {
            return ['ok' => false, 'message' => 'Invalid medicine or quantity.'];
        }

        $normalizedMedicine = $this->normalizeMedicineName($medicineName);
        $prescriptionItems = [];
        foreach ($this->getPrescriptionItemsWithNames($record) as $key => $item) {
            $prescriptionItems[$key] = (int) ($item['qty'] ?? 0);
        }
        $prescribedForMedicine = $prescriptionItems[$normalizedMedicine] ?? null;

        if (! empty($prescriptionItems) && $prescribedForMedicine === null) {
            return ['ok' => false, 'message' => "{$medicineName} is not in this prescription list."];
        }

        $dispensedBreakdown = is_array($record->dispensed_breakdown) ? $record->dispensed_breakdown : [];
        $alreadyDispensedForMedicine = (int) ($dispensedBreakdown[$normalizedMedicine] ?? 0);

        $stock = PharmacyStock::query()
            ->where('is_active', true)
            ->where(function ($query) use ($medicineName) {
                $query->whereRaw('LOWER(medicine_name) = ?', [strtolower($medicineName)])
                    ->orWhereRaw('LOWER(generic_name) = ?', [strtolower($medicineName)])
                    ->orWhere('medicine_name', 'like', '%' . $medicineName . '%')
                    ->orWhere('generic_name', 'like', '%' . $medicineName . '%');
            })
            ->orderBy('expiry_date')
            ->first();

        if (! $stock) {
            $remainingRequested = $prescribedForMedicine ?? $requestedQuantity;
            $sent = $this->sendShortageSms(
                $record,
                $medicineName,
                $remainingRequested,
                0,
                $remainingRequested,
                0
            );

            return ['ok' => false, 'message' => "Medicine not found in stock: {$medicineName}. " . ($sent ? 'Patient SMS sent via NotifyLK.' : 'SMS failed.')];
        }

        $availableStock = (int) $stock->quantity;
        if ($availableStock <= 0) {
            $requested = $prescribedForMedicine ?? $requestedQuantity;
            $sent = $this->sendShortageSms(
                $record,
                $medicineName,
                $requested,
                0,
                $requested,
                0
            );

            return ['ok' => false, 'message' => "Out of stock for {$medicineName}. " . ($sent ? 'Patient SMS sent via NotifyLK.' : 'SMS failed.')];
        }

        $prescribed = (int) ($record->prescribed_quantity ?? 0);
        $alreadyDispensed = (int) ($record->dispensed_quantity ?? 0);

        if ($prescribedForMedicine !== null) {
            $remainingPrescription = max($prescribedForMedicine - $alreadyDispensedForMedicine, 0);
        } else {
            $remainingPrescription = $prescribed > 0
                ? max($prescribed - $alreadyDispensed, 0)
                : $requestedQuantity;
        }

        if ($remainingPrescription <= 0) {
            return ['ok' => false, 'message' => "Prescription already completed for {$medicineName}."];
        }

        $dispenseNow = min($requestedQuantity, $availableStock, $remainingPrescription);
        $dispensedBreakdown[$normalizedMedicine] = $alreadyDispensedForMedicine + $dispenseNow;
        $newTotalDispensed = array_sum(array_map('intval', $dispensedBreakdown));

        if ($prescribed > 0 && $newTotalDispensed > $prescribed) {
            return ['ok' => false, 'message' => 'Given quantity cannot exceed prescribed quantity.'];
        }

        if ($dispenseNow <= 0) {
            return ['ok' => false, 'message' => 'Unable to dispense with current stock and prescription balance.'];
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

        $noteParts = [];
        $existingNote = trim((string) ($record->pharmacy_note ?? ''));
        if ($existingNote !== '') {
            $noteParts[] = $existingNote;
        }
        if (trim($pharmacyNote) !== '') {
            $noteParts[] = trim($pharmacyNote);
        }
        $noteParts[] = "Medicine: {$stock->medicine_name}; Given now: {$dispenseNow}";

        $record->update([
            'prescribed_quantity' => $totalPrescribed > 0 ? $totalPrescribed : $newTotalDispensed,
            'dispensed_quantity' => $newTotalDispensed,
            'dispensed_breakdown' => $dispensedBreakdown,
            'pharmacy_note' => trim(implode("\n", $noteParts)),
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
            return [
                'ok' => true,
                'record' => $record->refresh(),
                'message' => 'Prescription fully dispensed.',
            ];
        }

        return [
            'ok' => true,
            'record' => $record->refresh(),
            'message' => "Partial dispense saved for {$stock->medicine_name} ({$dispenseNow} given). Remaining: {$remainingForMedicine}.",
        ];
    }

    private function parsePrescriptionItems(string $text): array
    {
        $items = [];
        preg_match_all('/(?:^|[,;\n\r]|\s{1,})([A-Za-z0-9][A-Za-z0-9\s\-\/.\(\)]*?)\s*[-:]\s*(\d+)/u', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = trim((string) ($match[1] ?? ''));
            $qty = (int) ($match[2] ?? 0);

            if ($name !== '' && $qty > 0) {
                $normalized = $this->normalizeMedicineName($name);
                $items[$normalized] = (int) ($items[$normalized] ?? 0) + $qty;
            }
        }

        return $items;
    }

    private function parsePrescriptionItemsWithNames(string $text): array
    {
        $items = [];
        preg_match_all('/(?:^|[,;\n\r]|\s{1,})([A-Za-z0-9][A-Za-z0-9\s\-\/\.\(\)]*?)\s*[-:]\s*(\d+)/u', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = trim((string) ($match[1] ?? ''));
            $qty = (int) ($match[2] ?? 0);

            if ($name === '' || $qty <= 0) {
                continue;
            }

            $normalized = $this->normalizeMedicineName($name);
            $existingQty = (int) (($items[$normalized]['qty'] ?? 0));

            $items[$normalized] = [
                'name' => $items[$normalized]['name'] ?? $name,
                'qty' => $existingQty + $qty,
            ];
        }

        return $items;
    }

    private function getPrescriptionItemsWithNames(Consultation $record): array
    {
        $prescriptionRows = is_array($record->prescription_items ?? null) ? $record->prescription_items : [];
        $items = [];

        if (! empty($prescriptionRows)) {
            foreach ($prescriptionRows as $row) {
                $name = trim((string) ($row['medicine_name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $normalized = $this->normalizeMedicineName($name);
                $items[$normalized] = [
                    'name' => $items[$normalized]['name'] ?? $name,
                    'qty' => (int) (($items[$normalized]['qty'] ?? 0) + 1),
                ];
            }

            return $items;
        }

        return $this->parsePrescriptionItemsWithNames((string) ($record->prescription ?? ''));
    }

    private function sendShortageSummarySms(Consultation $record): bool
    {
        $phone = $this->resolvePatientPhone($record);
        if ($phone === '') {
            return false;
        }

        $items = $this->getPrescriptionItemsWithNames($record);
        $metaByMedicine = $this->getPrescriptionMetaByMedicine($record);
        $dispensed = is_array($record->dispensed_breakdown) ? $record->dispensed_breakdown : [];
        $stockMap = $this->buildStockMap();

        $parts = [];
        foreach ($items as $normalized => $itemData) {
            $prescribedQty = (int) ($itemData['qty'] ?? 0);
            $displayName = (string) ($itemData['name'] ?? $normalized);

            $given = (int) ($dispensed[$normalized] ?? 0);
            $remaining = max($prescribedQty - $given, 0);

            if ($remaining <= 0) {
                continue;
            }

            $stock = (int) ($stockMap[$normalized] ?? 0);
            $detailSuffix = $this->formatDoseDurationDetails($metaByMedicine[$normalized] ?? []);
            if ($stock <= 0) {
                $parts[] = "{$displayName}{$detailSuffix}: not available (need {$remaining})";
                continue;
            }

            if ($stock < $remaining) {
                $parts[] = "{$displayName}{$detailSuffix}: need {$remaining}, available {$stock}";
            }
        }

        if (empty($parts)) {
            return false;
        }

        $message = 'CMS-RC Pharmacy: Some prescribed medicines are currently unavailable. Prescription details - '
            . implode('; ', array_slice($parts, 0, 4))
            . '. Please contact pharmacy.';

        return $this->dispatchSms($phone, $message);
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
        $phone = $this->resolvePatientPhone($record);
        if ($phone === '') {
            return false;
        }

        $normalizedMedicine = $this->normalizeMedicineName($medicineName);
        $metaByMedicine = $this->getPrescriptionMetaByMedicine($record);
        $detailSuffix = $this->formatDoseDurationDetails($metaByMedicine[$normalizedMedicine] ?? []);

        $message = "CMS-RC Pharmacy update: {$medicineName}{$detailSuffix}. Requested {$requested} tablets, available {$availableStock}, given {$givenNow}, remaining {$remaining}. Please contact pharmacy.";

        return $this->dispatchSms($phone, $message);
    }

    private function getPrescriptionMetaByMedicine(Consultation $record): array
    {
        $rows = is_array($record->prescription_items ?? null) ? $record->prescription_items : [];
        $meta = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['medicine_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $normalized = $this->normalizeMedicineName($name);
            if (! isset($meta[$normalized])) {
                $meta[$normalized] = [
                    'dosage' => [],
                    'duration' => [],
                ];
            }

            $dosage = trim((string) ($row['dosage'] ?? ''));
            if ($dosage !== '') {
                $meta[$normalized]['dosage'][$dosage] = true;
            }

            $duration = trim((string) ($row['duration'] ?? ''));
            if ($duration !== '') {
                $meta[$normalized]['duration'][$duration] = true;
            }
        }

        foreach ($meta as $key => $item) {
            $meta[$key] = [
                'dosage' => implode(', ', array_keys($item['dosage'] ?? [])),
                'duration' => implode(', ', array_keys($item['duration'] ?? [])),
            ];
        }

        return $meta;
    }

    private function formatDoseDurationDetails(array $meta): string
    {
        $dosage = trim((string) ($meta['dosage'] ?? ''));
        $duration = trim((string) ($meta['duration'] ?? ''));

        $parts = [];
        if ($dosage !== '') {
            $parts[] = "dosage: {$dosage}";
        }

        if ($duration !== '') {
            $parts[] = "duration: {$duration}";
        }

        if (empty($parts)) {
            return '';
        }

        return ' (' . implode(', ', $parts) . ')';
    }

    private function resolvePatientPhone(Consultation $record): string
    {
        $rawPhone = trim((string) optional($record->patient)->phone);

        return $this->normalizeSriLankaPhone($rawPhone);
    }

    private function normalizeSriLankaPhone(string $phone): string
    {
        if ($phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0094') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '94') && strlen($digits) === 11) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '94' . substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '94' . $digits;
        }

        return '';
    }

    private function dispatchSms(string $phone, string $message): bool
    {
        try {
            $response = NotifyLKService::send($phone, $message);

            if (! $response->successful()) {
                Log::warning('NotifyLK SMS failed with non-success HTTP status.', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $json = $response->json();
            if (is_array($json) && array_key_exists('status', $json)) {
                $status = strtolower(trim((string) $json['status']));

                $sent = in_array($status, ['success', 'ok', '1', 'true'], true);
                if (! $sent) {
                    Log::warning('NotifyLK SMS failed with unsuccessful response status.', [
                        'phone' => $phone,
                        'notify_status' => $json['status'] ?? null,
                        'response' => $json,
                    ]);
                }

                return $sent;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('NotifyLK SMS exception.', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
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
