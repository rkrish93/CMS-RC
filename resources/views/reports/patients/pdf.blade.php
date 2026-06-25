<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Patient Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 20px;
        }

        h2, h3, h4 {
            margin: 0 0 8px 0;
        }

        .header {
            margin-bottom: 14px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
        }

        .meta {
            margin-bottom: 14px;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .stats {
            margin-bottom: 14px;
        }

        .stats table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            width: 33.33%;
        }

        .section {
            margin-top: 14px;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.grid th,
        table.grid td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        table.grid th {
            background: #f3f4f6;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Patient Report</h2>
        <div class="muted">Generated on {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td><strong>Patient Code:</strong> {{ $patient->patient_code ?? '-' }}</td>
                <td><strong>Name:</strong> {{ trim($patient->first_name . ' ' . $patient->last_name) }}</td>
                <td><strong>Gender:</strong> {{ $patient->gender ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>DOB:</strong> {{ $patient->dob ?? '-' }}</td>
                <td><strong>Type:</strong> {{ $patient->patient_type ?? '-' }}</td>
                <td><strong>Phone:</strong> {{ $patient->phone ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>NIC:</strong> {{ $patient->nic ?? '-' }}</td>
                <td colspan="2"><strong>Address:</strong> {{ $patient->address ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <table>
            <tr>
                <td><strong>Appointments</strong><br>{{ $patient->appointments_count }}</td>
                <td><strong>Consultations</strong><br>{{ $patient->consultations_count }}</td>
                <td><strong>Registered Date</strong><br>{{ optional($patient->created_at)->format('Y-m-d') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h4>Recent Vitals</h4>
        <table class="grid">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>BP</th>
                    <th>Temp</th>
                    <th>Pulse</th>
                    <th>Sugar</th>
                    <th>BMI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vitals as $vital)
                    <tr>
                        <td>{{ optional($vital->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $vital->bp ?? '-' }}</td>
                        <td>{{ $vital->temp ?? '-' }}</td>
                        <td>{{ $vital->pulse ?? '-' }}</td>
                        <td>{{ $vital->sugar ?? '-' }}</td>
                        <td>{{ $vital->bmi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No vitals found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Recent Consultations</h4>
        <table class="grid">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Diagnosis</th>
                    <th>Prescription</th>
                </tr>
            </thead>
            <tbody>
                @forelse($consultations as $consultation)
                    <tr>
                        <td>{{ optional($consultation->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ trim((optional($consultation->doctor)->fname ?? '') . ' ' . (optional($consultation->doctor)->lname ?? '')) ?: '-' }}</td>
                        <td>{{ $consultation->diagnosis ?? '-' }}</td>
                        <td>{{ $consultation->prescription ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No consultations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Recent Appointments</h4>
        <table class="grid">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td>{{ optional($appointment->appointment_date)->format('Y-m-d') ?? $appointment->appointment_date }}</td>
                        <td>{{ $appointment->appointment_time ?? '-' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $appointment->status ?? '-')) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
