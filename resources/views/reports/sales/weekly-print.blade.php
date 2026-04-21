<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Sales Report - {{ \Carbon\Carbon::parse($startDate)->format('M d') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; background: white; }
        .report-container { max-width: 210mm; margin: 0 auto; background: white; padding: 20px; }
        .header-grid { display: grid; grid-template-columns: 1fr 1fr; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .brand-section h1 { font-size: 22px; font-weight: 800; text-transform: uppercase; color: #000; margin-bottom: 5px; }
        .brand-section p { font-size: 10px; color: #444; margin-bottom: 2px; }
        .report-meta { text-align: right; }
        .report-meta h2 { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 10px; text-transform: uppercase; }
        .meta-grid { display: inline-grid; grid-template-columns: auto auto; gap: 5px 15px; text-align: left; }
        .meta-label { font-weight: 600; color: #666; text-transform: uppercase; font-size: 9px; }
        .meta-value { font-weight: 700; color: #000; }
        .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .report-table th { background: #f8f9fa; border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 8px; text-align: left; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        .report-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #1a1a1a; margin: 20px 0 10px; background: #f1f1f1; padding: 5px 10px; border-left: 4px solid #333; }
        .summary-row { background: #fdfdfd; font-weight: 700; }
        .grand-total-box { margin-top: 30px; padding: 20px; background: #0dcaf0; color: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .grand-total-label { font-size: 14px; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .grand-total-value { font-size: 24px; font-weight: 800; }
        .footer-grid { margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; text-align: center; }
        .sig-line { border-top: 1px solid #000; margin-bottom: 5px; }
        .sig-label { font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .bg-success { background: #e6fffa; color: #2c7a7b; }
        .bg-warning { background: #fffaf0; color: #9c4221; }
        @media print { body { -webkit-print-color-adjust: exact; } .report-container { width: 100%; padding: 0; } }
    </style>
</head>
<body onload="window.print()">
<div class="report-container">
    <div class="header-grid">
        <div class="brand-section">
            <h1>{{ config('app.name', 'MAGIA LUPOS') }}</h1>
            <p><strong>Gold & Diamond Merchants</strong></p>
            <p>{{ config('pos.shop_address', '123 Jewellery Street, Karachi, Pakistan') }}</p>
            <p>Phone: {{ config('pos.shop_phone', '+92 300 1234567') }}</p>
        </div>
        <div class="report-meta">
            <h2 style="color: #0dcaf0;">Weekly Sales Performance</h2>
            <div class="meta-grid">
                <span class="meta-label">Period:</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Weekly Performance Summary</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Key Performance Indicator</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Weekly Revenue</td>
                <td class="text-right fw-bold">Rs. {{ number_format($totalSales, 2) }}</td>
            </tr>
            <tr>
                <td>Total Units Sold</td>
                <td class="text-right">{{ $totalItems }} Units</td>
            </tr>
            <tr>
                <td>Total Transaction Count</td>
                <td class="text-right">{{ $count }}</td>
            </tr>
            <tr>
                <td>Average Order Value (AOV)</td>
                <td class="text-right">Rs. {{ number_format($averageOrderValue, 2) }}</td>
            </tr>
            <tr>
                <td>Gross Profit Margin</td>
                <td class="text-right fw-bold">{{ $grossMargin }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Consolidated Transaction Log</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice #</th>
                <th>Client</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Collected</th>
                <th class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales->sortByDesc('sale_time') as $sale)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_time)->format('d M Y') }}</td>
                    <td>#{{ $sale->id }}</td>
                    <td>{{ $sale->customer?->name ?? 'Private Client' }}</td>
                    <td class="text-right fw-bold">Rs. {{ number_format($sale->total, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($sale->payments()->sum('amount'), 2) }}</td>
                    <td class="text-right">
                        @if($sale->outstanding_balance > 0)
                            <span class="badge bg-warning">CREDIT</span>
                        @else
                            <span class="badge bg-success">SETTLED</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No transactions recorded for this period</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="grand-total-box">
        <div class="grand-total-label">Total Weekly Collection</div>
        <div class="grand-total-value">Rs. {{ number_format($totalPaid, 2) }}</div>
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Prepared By (Business Analytics)</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Validated By (Finance Controller)</div></div>
    </div>
</div>
</body>
</html>