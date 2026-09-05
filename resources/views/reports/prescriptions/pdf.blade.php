<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Prescriptions Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; margin: 10px; color: #111827; }
        h2 { margin: 0; font-size: 16px; }
        .meta { margin: 6px 0 10px; color: #4b5563; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 5px; font-size: 9px; border-radius: 3px; font-weight: bold; }
        .bg-success { background-color: #d1fae5; color: #065f46; }
        .bg-info { background-color: #e0f2fe; color: #075985; }
        .bg-warning { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <h2>Prescriptions Report</h2>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} | Total Prescriptions: {{ $prescriptions->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Patient Name</th>
                <th>Patient Code</th>
                <th>Doctor</th>
                <th>Unit</th>
                <th>Prescribed Medicines</th>
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
                    <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8">No prescription report records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
