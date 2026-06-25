<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Report Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 18px;
            color: #111827;
        }

        h2 {
            margin: 0;
        }

        .meta {
            margin: 8px 0 14px;
            color: #4b5563;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }

        @media print {
            body {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <h2>Appointment Report</h2>
    <div class="meta">
        Generated on {{ now()->format('Y-m-d H:i') }} | Total records: {{ $appointments->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Token</th>
                <th>Patient</th>
                <th>Code</th>
                <th>Phone</th>
                <th>Unit</th>
                <th>Doctor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $appointment)
                <tr>
                    <td>{{ optional($appointment->appointment_date)->format('Y-m-d') ?? $appointment->appointment_date }}</td>
                    <td>{{ $appointment->appointment_time ?? '-' }}</td>
                    <td>{{ $appointment->token_no ?? '-' }}</td>
                    <td>{{ trim((optional($appointment->patient)->first_name ?? '') . ' ' . (optional($appointment->patient)->last_name ?? '')) ?: '-' }}</td>
                    <td>{{ optional($appointment->patient)->patient_code ?? '-' }}</td>
                    <td>{{ optional($appointment->patient)->phone ?? '-' }}</td>
                    <td>{{ optional($appointment->unit)->unit_name ?? '-' }}</td>
                    <td>{{ trim((optional(optional($appointment->consultation)->doctor)->fname ?? '') . ' ' . (optional(optional($appointment->consultation)->doctor)->lname ?? '')) ?: '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $appointment->status ?? '-')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No appointment report data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($printMode ?? false)
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
</body>
</html>
