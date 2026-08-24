<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Appointment Report PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            margin: 14px;
            color: #111827;
        }

        h2 {
            margin: 0;
        }

        .meta {
            margin: 8px 0 12px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <h2>Appointment Report</h2>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} | Total records: {{ $appointments->count() }}</div>

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
                    <td>{{ (App\Enums\AppointmentStatus::fromValue($appointment->status) ?? App\Enums\AppointmentStatus::SCHEDULED)->getLabel() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No appointment report data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
