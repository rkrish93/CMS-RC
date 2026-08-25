<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\PharmacyStock;
use App\Models\Product;
use App\Services\NotifyLKService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\User;

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

        $smsError = null;
        $sent = $this->dispatchSms($phone, $message, $smsError);

        if ($sent) {
            $record->update([
                'is_locked' => true,
                'pharmacy_status' => 'dispensed',
                'dispensed_at' => now(),
            ]);

            if ($record->appointment) {
                $record->appointment->update([
                    'status' => \App\Enums\AppointmentStatus::COMPLETED->value,
                ]);
            }

            return $redirect->with('success', 'Shortage SMS sent to patient successfully.');
        }

        $errorMsg = 'NotifyLK SMS sending failed' . ($smsError ? ": {$smsError}" : '. Check NotifyLK settings.');
        return $redirect->with('error', $errorMsg);
    }

    public function prescriptions(Request $request)
    {
        abort_unless(
            auth()->user()?->can('pharmacy-prescriptions-view') || auth()->user()?->hasRole('Admin'),
            403
        );

        $search = trim((string) $request->input('search'));
        $status = trim((string) $request->input('status'));
        $doctorId = (int) $request->input('doctor_id', 0);
        $fromDate = trim((string) $request->input('from_date'));
        $toDate = trim((string) $request->input('to_date'));
        $consultationId = (int) $request->input('consultation_id', 0);
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $baseQuery = Consultation::query()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('prescription')
                        ->where('prescription', '!=', '');
                })->orWhereNotNull('prescription_items');
            });

        $summaryStats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('pharmacy_status', 'pending')->count(),
            'partial' => (clone $baseQuery)->where('pharmacy_status', 'partial')->count(),
            'dispensed' => (clone $baseQuery)->where('pharmacy_status', 'dispensed')->count(),
        ];

        $prescriptions = (clone $baseQuery)
            ->with(['patient:id,patient_code,first_name,last_name,phone', 'doctor:id,fname,lname'])
            ->when(in_array($status, ['pending', 'partial', 'dispensed'], true), function ($query) use ($status) {
                $query->where('pharmacy_status', $status);
            })
            ->when($doctorId > 0, function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->when($consultationId > 0, function ($query) use ($consultationId) {
                $query->where('id', $consultationId);
            })
            ->when($fromDate !== '', function ($query) use ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($toDate !== '', function ($query) use ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('prescription', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($patientQuery) use ($search) {
                            $patientQuery->where('patient_code', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('doctor', function ($doctorQuery) use ($search) {
                            $doctorQuery->where('fname', 'like', "%{$search}%")
                                ->orWhere('lname', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $doctors = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Doctor');
            })
            ->orderBy('fname')
            ->orderBy('lname')
            ->get(['id', 'fname', 'lname']);

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

        return view('pharmacy.prescriptions.index', compact(
            'prescriptions',
            'search',
            'status',
            'doctorId',
            'fromDate',
            'toDate',
            'consultationId',
            'perPage',
            'doctors',
            'summaryStats',
            'newPrescriptionCount',
            'stockByMedicine'
        ));
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

        if ($record->appointment) {
            $record->appointment->update([
                'status' => \App\Enums\AppointmentStatus::COMPLETED->value,
            ]);
        }

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
            return [
                'ok' => false,
                'message' => "Medicine not found in stock: {$medicineName}. Use Send Shortage SMS to notify patient.",
            ];
        }

        $availableStock = (int) $stock->quantity;
        if ($availableStock <= 0) {
            return [
                'ok' => false,
                'message' => "Out of stock for {$medicineName}. Use Send Shortage SMS to notify patient.",
            ];
        }

        if ($requestedQuantity > $availableStock) {
            return [
                'ok' => false,
                'message' => "Entered qty ({$requestedQuantity}) exceeds available stock ({$availableStock}) for {$medicineName}.",
            ];
        }

        $prescribed = (int) ($record->prescribed_quantity ?? 0);
        $alreadyDispensed = (int) ($record->dispensed_quantity ?? 0);

        $dispenseNow = min($requestedQuantity, $availableStock);
        $dispensedBreakdown[$normalizedMedicine] = $alreadyDispensedForMedicine + $dispenseNow;
        $newTotalDispensed = array_sum(array_map('intval', $dispensedBreakdown));

        if ($dispenseNow <= 0) {
            return ['ok' => false, 'message' => 'Unable to dispense with current stock.'];
        }

        $totalPrescribed = $prescribed;
        if (! empty($prescriptionItems)) {
            $totalPrescribed = array_sum($prescriptionItems);
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
            'pharmacy_status' => 'partial',
            'dispensed_at' => now(),
        ]);

        $record = $record->refresh();
        $remainingAfterSave = $this->calculateRemainingPrescriptionQuantity($record);
        $status = $remainingAfterSave <= 0 ? 'dispensed' : 'partial';

        if (($record->pharmacy_status ?? '') !== $status) {
            $record->update([
                'pharmacy_status' => $status,
            ]);
        }

        $record = $record->refresh();

        $remainingForMedicine = $prescribedForMedicine !== null
            ? max($prescribedForMedicine - (int) ($dispensedBreakdown[$normalizedMedicine] ?? 0), 0)
            : max(((int) $record->prescribed_quantity) - $newTotalDispensed, 0);

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

                $duration = trim((string) ($row['duration'] ?? ''));
                $days = 1;
                if ($duration !== '' && preg_match('/(\d+)/', $duration, $m)) {
                    $days = max((int) $m[1], 1);
                }

                $timeSlots = $row['time_slot'] ?? [];
                if (! is_array($timeSlots)) {
                    $timeSlots = array_filter([(string) $timeSlots]);
                } else {
                    $timeSlots = array_filter($timeSlots);
                }
                $slotCount = max(count($timeSlots), 1);

                $calcQty = $days * $slotCount;

                $normalized = $this->normalizeMedicineName($name);
                $items[$normalized] = [
                    'name' => $items[$normalized]['name'] ?? $name,
                    'qty' => (int) (($items[$normalized]['qty'] ?? 0) + $calcQty),
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

        $hasAnyShortage = false;
        $parts = [];

        foreach ($items as $normalized => $itemData) {
            $prescribedQty = (int) ($itemData['qty'] ?? 0);
            $displayName = (string) ($itemData['name'] ?? $normalized);

            $given = (int) ($dispensed[$normalized] ?? 0);
            $remaining = max($prescribedQty - $given, 0);

            $stock = (int) ($stockMap[$normalized] ?? 0);
            $detailSuffix = $this->formatDoseDurationDetails($metaByMedicine[$normalized] ?? []);

            if ($remaining <= 0) {
                $parts[] = "{$displayName}{$detailSuffix}: Given";
            } elseif ($stock <= 0) {
                $hasAnyShortage = true;
                $parts[] = "{$displayName}{$detailSuffix}: OUT OF STOCK (Need {$remaining})";
            } elseif ($stock < $remaining) {
                $hasAnyShortage = true;
                $parts[] = "{$displayName}{$detailSuffix}: Shortage (Available {$stock}, Need {$remaining})";
            } else {
                $parts[] = "{$displayName}{$detailSuffix}: Available ({$stock} in stock)";
            }
        }

        if (! $hasAnyShortage || empty($parts)) {
            return false;
        }

        $patientName = trim((optional($record->patient)->first_name ?? '') . ' ' . (optional($record->patient)->last_name ?? ''));
        $patientStr = $patientName !== '' ? " for {$patientName}" : '';

        $message = "CMS-RC Pharmacy Notice{$patientStr}: Prescription details - "
            . implode('; ', $parts)
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
                    'time_slot' => [],
                    'food_timing' => [],
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

            $timeSlots = $row['time_slot'] ?? [];
            if (! is_array($timeSlots)) {
                $timeSlots = [$timeSlots];
            }

            foreach ($timeSlots as $timeSlot) {
                $timeSlot = trim((string) $timeSlot);
                if ($timeSlot !== '') {
                    $meta[$normalized]['time_slot'][$timeSlot] = true;
                }
            }

            $foodTiming = trim((string) ($row['food_timing'] ?? ''));
            if ($foodTiming !== '') {
                $meta[$normalized]['food_timing'][$foodTiming] = true;
            }
        }

        foreach ($meta as $key => $item) {
            $meta[$key] = [
                'dosage' => implode(', ', array_keys($item['dosage'] ?? [])),
                'duration' => implode(', ', array_keys($item['duration'] ?? [])),
                'time_slot' => implode(', ', array_keys($item['time_slot'] ?? [])),
                'food_timing' => implode(', ', array_keys($item['food_timing'] ?? [])),
            ];
        }

        return $meta;
    }

    private function formatDoseDurationDetails(array $meta): string
    {
        $dosage = trim((string) ($meta['dosage'] ?? ''));
        $duration = trim((string) ($meta['duration'] ?? ''));
        $timeSlot = trim((string) ($meta['time_slot'] ?? ''));
        $foodTiming = trim((string) ($meta['food_timing'] ?? ''));

        $parts = [];
        if ($dosage !== '') {
            $parts[] = "dosage: {$dosage}";
        }

        if ($duration !== '') {
            $parts[] = "duration: {$duration}";
        }

        if ($timeSlot !== '') {
            $parts[] = 'time: ' . str_replace('_', ' ', $timeSlot);
        }

        if ($foodTiming !== '') {
            $parts[] = 'food: ' . str_replace('_', ' ', $foodTiming);
        }

        if (empty($parts)) {
            return '';
        }

        return ' (' . implode(', ', $parts) . ')';
    }

    private function calculateRemainingPrescriptionQuantity(Consultation $record): int
    {
        $items = $this->getPrescriptionItemsWithNames($record);
        $dispensed = is_array($record->dispensed_breakdown) ? $record->dispensed_breakdown : [];

        if (! empty($items)) {
            $remaining = 0;

            foreach ($items as $normalized => $itemData) {
                $prescribedQty = (int) ($itemData['qty'] ?? 0);
                $givenQty = (int) ($dispensed[$normalized] ?? 0);
                $remaining += max($prescribedQty - $givenQty, 0);
            }

            return $remaining;
        }

        $prescribedQty = (int) ($record->prescribed_quantity ?? 0);
        $givenQty = (int) ($record->dispensed_quantity ?? 0);

        return max($prescribedQty - $givenQty, 0);
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

    private function dispatchSms(string $phone, string $message, ?string &$errorMessage = null): bool
    {
        try {
            $userId = trim((string) env('NOTIFY_USER_ID', env('NOTIFYLK_USER_ID')));
            $apiKey = trim((string) env('NOTIFY_API_KEY', env('NOTIFYLK_API_KEY')));
            $senderId = trim((string) env('NOTIFY_SENDER_ID', env('NOTIFYLK_SENDER_ID')));

            if ($userId === '' || $apiKey === '' || $senderId === '') {
                $errorMessage = 'NotifyLK SMS configuration missing in .env (USER_ID, API_KEY or SENDER_ID).';
                Log::warning($errorMessage);
                return false;
            }

            $response = NotifyLKService::send($phone, $message);
            $json = $response->json();

            if (is_array($json)) {
                if (isset($json['errors'])) {
                    $errorMessage = is_array($json['errors']) ? implode(', ', $json['errors']) : (string) $json['errors'];
                } elseif (isset($json['message'])) {
                    $errorMessage = (string) $json['message'];
                }
            }

            if (! $response->successful()) {
                Log::warning('NotifyLK SMS failed with non-success HTTP status.', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                if (empty($errorMessage)) {
                    $errorMessage = 'NotifyLK HTTP Error ' . $response->status();
                }
                return false;
            }

            if (is_array($json) && array_key_exists('status', $json)) {
                $status = strtolower(trim((string) $json['status']));

                $sent = in_array($status, ['success', 'ok', '1', 'true'], true);
                if (! $sent) {
                    Log::warning('NotifyLK SMS failed with unsuccessful response status.', [
                        'phone' => $phone,
                        'notify_status' => $json['status'] ?? null,
                        'response' => $json,
                    ]);
                    if (empty($errorMessage)) {
                        $errorMessage = 'NotifyLK status: ' . ($json['status'] ?? 'error');
                    }
                }

                return $sent;
            }

            return true;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
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
            'product_id' => 'required|exists:medicines,id',
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
            'product_id' => 'required|exists:medicines,id',
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
