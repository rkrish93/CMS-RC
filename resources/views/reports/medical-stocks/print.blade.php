<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medical Stocks Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 18px; color: #111827; }
        h2 { margin: 0 0 4px 0; color: #0f172a; }
        .meta { margin: 4px 0 14px; color: #4b5563; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; color: #334155; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-warning { color: #d97706; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-inactive { background: #f1f5f9; color: #64748b; }
        @media print { body { margin: 10px; } }
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
                    <td colspan="10" style="text-align: center; color: #6b7280; padding: 16px;">
                        No medical stock report records found.
                    </td>
                </tr>
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
