<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medical Stocks Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; margin: 12px; color: #111827; }
        h2 { margin: 0 0 4px 0; color: #0f172a; font-size: 16px; }
        .meta { margin: 4px 0 12px; color: #4b5563; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; color: #334155; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-warning { color: #d97706; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-inactive { background: #f1f5f9; color: #64748b; }
    </style>
</head>
<body>
    <h2>Medical Stocks Inventory Report</h2>
    <div class="meta">
        Generated on {{ now()->format('Y-m-d H:i') }} | Total Items: {{ $stocks->count() }} | Total Inventory Qty: {{ number_format($stocks->sum('quantity')) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Medicine Name</th>
                <th>Generic Name</th>
                <th>Unit</th>
                <th>Batch No</th>
                <th>Qty</th>
                <th>Reorder Level</th>
                <th>Expiry Date</th>
                <th>Alert Status</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $todayStr = now()->startOfDay()->format('Y-m-d');
                $soonStr = now()->addDays(30)->endOfDay()->format('Y-m-d');
            @endphp
            @forelse($stocks as $stock)
                @php
                    $reorder = $stock->product?->reorder_level ?? 10;
                    $expStr = $stock->expiry_date ? $stock->expiry_date->format('Y-m-d') : null;
                    $isExpired = $expStr && $expStr < $todayStr;
                    $isExpiringSoon = $expStr && $expStr >= $todayStr && $expStr <= $soonStr;
                @endphp
                <tr>
                    <td>{{ $stock->product?->product_code ?? '-' }}</td>
                    <td><strong>{{ $stock->medicine_name }}</strong></td>
                    <td>{{ $stock->generic_name ?? '-' }}</td>
                    <td>{{ $stock->unit ?? '-' }}</td>
                    <td>{{ $stock->batch_no }}</td>
                    <td class="{{ $stock->quantity <= 0 ? 'text-danger' : ($stock->quantity <= $reorder ? 'text-warning' : 'text-success') }}">
                        {{ number_format($stock->quantity) }}
                    </td>
                    <td>{{ number_format($reorder) }}</td>
                    <td>
                        @if($stock->expiry_date)
                            <span class="{{ $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : '') }}">
                                {{ $stock->expiry_date->format('Y-m-d') }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($stock->quantity <= 0)
                            <span class="text-danger">Out of Stock</span>
                        @elseif($stock->quantity <= $reorder)
                            <span class="text-warning">Low Stock</span>
                        @endif

                        @if($isExpired)
                            <strong style="color:#b91c1c;">Expired</strong>
                        @elseif($isExpiringSoon)
                            <strong style="color:#b45309;">Expiring Soon</strong>
                        @endif

                        @if($stock->quantity > $reorder && !$isExpired && !$isExpiringSoon)
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $stock->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $stock->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #6b7280; padding: 12px;">
                        No medical stock report records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
