<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Sales Report - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</title>
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
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #1a1a1a; margin: 20px 0 10px; background: #f1f1f1; padding: 5px 10px; border-left: 4px solid #d4af37; }
        .summary-row { background: #fdfdfd; font-weight: 700; }
        .grand-total-box { margin-top: 30px; padding: 20px; background: #1a1a1a; color: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .grand-total-label { font-size: 14px; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .grand-total-value { font-size: 24px; font-weight: 800; color: #d4af37; }
        .footer-grid { margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 40px; text-align: center; }
        .sig-line { border-top: 1px solid #000; margin-bottom: 5px; }
        .sig-label { font-size: 9px; font-weight: 700; text-transform: uppercase; }
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
            <h2 style="color: #d4af37;">Monthly Performance Audit</h2>
            <div class="meta-grid">
                <span class="meta-label">Period:</span>
                <span class="meta-value">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Monthly Revenue Summary</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Performance Metric</th>
                <th class="text-right">Amount (Rs.) / Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Monthly Gross Revenue</td>
                <td class="text-right fw-bold">Rs. {{ number_format($totalSales, 2) }}</td>
            </tr>
            <tr>
                <td>Cost of Goods Sold (COGS)</td>
                <td class="text-right">Rs. {{ number_format($costOfGoodsSold, 2) }}</td>
            </tr>
            <tr>
                <td>Monthly Gross Profit</td>
                <td class="text-right fw-bold">Rs. {{ number_format($grossProfit, 2) }}</td>
            </tr>
            <tr>
                <td>Gross Margin Percentage</td>
                <td class="text-right">{{ $grossMargin }}%</td>
            </tr>
            <tr>
                <td>Total Items Sold</td>
                <td class="text-right">{{ $totalItems }} Units</td>
            </tr>
            <tr>
                <td>Total Transaction Volume</td>
                <td class="text-right">{{ $count }} Orders</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Revenue Components</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Component</th>
                <th class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Service & Making Charges</td>
                <td class="text-right">Rs. {{ number_format($totalMakingCharges, 2) }}</td>
            </tr>
            <tr>
                <td>Tax Liabilities Collected</td>
                <td class="text-right">Rs. {{ number_format($totalTax, 2) }}</td>
            </tr>
            <tr>
                <td>Sales Discounts Given</td>
                <td class="text-right text-danger">(Rs. {{ number_format($totalDiscount, 2) }})</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Top Performance Indicators</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <p class="fw-bold mb-2">Top Products</p>
            <table class="report-table" style="font-size: 9px;">
                @php
                    $topProducts = \App\Models\PosSaleItem::whereIn('pos_sale_id', $sales->pluck('id'))
                        ->select('description', \Illuminate\Support\Facades\DB::raw('SUM(quantity * unit_price) as revenue'))
                        ->groupBy('description')
                        ->orderByDesc('revenue')
                        ->limit(5)
                        ->get();
                @endphp
                <tbody>
                    @foreach($topProducts as $p)
                    <tr>
                        <td>{{ substr($p->description, 0, 25) }}</td>
                        <td class="text-right">Rs. {{ number_format($p->revenue, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <p class="fw-bold mb-2">Top Customers</p>
            <table class="report-table" style="font-size: 9px;">
                @php
                    $topCustomers = $sales->groupBy('pos_customer_id')
                        ->map(fn($g) => ['name' => $g->first()->customer?->name ?? 'Walk-in', 'spent' => $g->sum('total')])
                        ->sortByDesc('spent')->take(5);
                @endphp
                <tbody>
                    @foreach($topCustomers as $c)
                    <tr>
                        <td>{{ $c['name'] }}</td>
                        <td class="text-right">Rs. {{ number_format($c['spent'], 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grand-total-box">
        <div class="grand-total-label">Monthly Liquidity (Total Paid)</div>
        <div class="grand-total-value">Rs. {{ number_format($totalPaid, 2) }}</div>
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Sales Director</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Finance Manager</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Chief Auditor</div></div>
    </div>
</div>
</body>
</html>