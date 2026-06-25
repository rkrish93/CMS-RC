<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>User Report PDF</title>
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
    <h2>User Report</h2>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} | Total records: {{ $users->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>NIC</th>
                <th>Designation</th>
                <th>Unit</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Join Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email ?? '-' }}</td>
                    <td>{{ $user->phone ?? '-' }}</td>
                    <td>{{ $user->nic ?? '-' }}</td>
                    <td>{{ $user->designation ?? '-' }}</td>
                    <td>{{ $user->unit->unit_name ?? '-' }}</td>
                    <td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                    <td>{{ ucfirst($user->status ?? '-') }}</td>
                    <td>{{ $user->join_date ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9">No user report data found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
