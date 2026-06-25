<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Consultation Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; margin: 14px; color: #111827; }
        h2 { margin: 0; }
        .meta { margin: 8px 0 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Consultation Report</h2>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} | Total records: {{ $consultations->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Code</th>
                <th>Doctor</th>
                <th>Unit</th>
                <th>Diagnosis</th>
                <th>Prescription</th>
                <th>Pharmacy Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consultations as $consultation)
                <tr>
                    <td>{{ optional($consultation->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ trim((optional($consultation->patient)->first_name ?? '') . ' ' . (optional($consultation->patient)->last_name ?? '')) ?: '-' }}</td>
                    <td>{{ optional($consultation->patient)->patient_code ?? '-' }}</td>
                    <td>{{ trim((optional($consultation->doctor)->fname ?? '') . ' ' . (optional($consultation->doctor)->lname ?? '')) ?: '-' }}</td>
                    <td>{{ optional(optional($consultation->appointment)->unit)->unit_name ?? '-' }}</td>
                    <td>{{ $consultation->diagnosis ?? '-' }}</td>
                    <td>{{ $consultation->prescription ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $consultation->pharmacy_status ?? '-')) }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No consultation report data found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
