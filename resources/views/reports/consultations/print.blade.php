<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Consultation Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 18px; color: #111827; }
        h2 { margin: 0; }
        .meta { margin: 8px 0 14px; color: #4b5563; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        @media print { body { margin: 10px; } }
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
                </tr>
            @empty
                <tr><td colspan="7">No consultation report data found.</td></tr>
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
