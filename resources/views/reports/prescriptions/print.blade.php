<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Prescriptions Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 18px; color: #111827; }
        h2 { margin: 0; }
        .meta { margin: 8px 0 14px; color: #4b5563; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 10px; border-radius: 4px; font-weight: bold; }
        .bg-success { background-color: #d1fae5; color: #065f46; }
        .bg-info { background-color: #e0f2fe; color: #075985; }
        .bg-warning { background-color: #fef3c7; color: #92400e; }
        @media print { body { margin: 10px; } }
    </style>
</head>
<body>
    <h2>Prescriptions Report</h2>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} | Total Prescriptions: {{ $prescriptions->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Patient</th>
                <th>Code</th>
                <th>Doctor</th>
                <th>Unit</th>
                <th>Prescribed Medicines</th>
                <th>Qty (Prescribed/Dispensed)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($prescriptions as $item)
                @php
                    $status = $item->pharmacy_status ?? 'pending';
                    $badgeClass = match($status) {
                        'dispensed' => 'bg-success',
                        'partial' => 'bg-info',
                        default => 'bg-warning',
                    };
                    $statusLabel = match($status) {
                        'dispensed' => 'Dispensed',
                        'partial' => 'Partially Dispensed',
                        default => 'Pending',
                    };
                    $items = is_array($item->prescription_items) ? $item->prescription_items : [];
                @endphp
                <tr>
                    <td>{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ trim((optional($item->patient)->first_name ?? '') . ' ' . (optional($item->patient)->last_name ?? '')) ?: '-' }}</td>
                    <td>{{ optional($item->patient)->patient_code ?? '-' }}</td>
                    <td>Dr. {{ trim((optional($item->doctor)->fname ?? '') . ' ' . (optional($item->doctor)->lname ?? '')) ?: '-' }}</td>
                    <td>{{ optional(optional($item->appointment)->unit)->unit_name ?? '-' }}</td>
                    <td>
                        @if(count($items) > 0)
                            @foreach($items as $row)
                                <div>• {{ $row['medicine_name'] ?? $row['product_name'] ?? 'Medicine' }} {{ $row['dosage'] ?? '' }} ({{ $row['duration'] ?? '' }})</div>
                            @endforeach
                        @else
                            {{ $item->prescription ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $item->prescribed_quantity ?? 0 }} / {{ $item->dispensed_quantity ?? 0 }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8">No prescription report records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($printMode ?? false)
        <script>
            window.addEventListener('load', function () { window.print(); });
        </script>
    @endif
</body>
</html>
