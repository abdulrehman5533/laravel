<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill-wise Outstanding Report - {{ now()->format('M d, Y') }}</title>
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
        .grand-total-box { margin-top: 30px; padding: 20px; background: #1a1a1a; color: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .grand-total-label { font-size: 14px; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .grand-total-value { font-size: 24px; font-weight: 800; }
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
            <h2>Bill-wise Outstanding</h2>
            <div class="meta-grid">
                <span class="meta-label">As of:</span>
                <span class="meta-value">{{ now()->format('d M Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Outstanding Summary</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Pending Amount</td>
                <td class="text-right fw-bold">Rs. {{ number_format($totalOutstanding, 2) }}</td>
            </tr>
            <tr>
                <td>Number of Unpaid Invoices</td>
                <td class="text-right">{{ $outstandingSales->count() }}</td>
            </tr>
            <tr>
                <td>Unique Debtors</td>
                <td class="text-right">{{ $outstandingSales->unique('pos_customer_id')->count() }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Detailed Outstanding Ledger</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Date / Age</th>
                <th>Invoice #</th>
                <th>Customer</th>
                <th class="text-right">Total Bill</th>
                <th class="text-right">Balance Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($outstandingSales as $sale)
                <tr>
                    <td>
                        {{ \Carbon\Carbon::parse($sale->sale_time)->format('d M Y') }}<br>
                        <small style="color: #d9534f;">{{ now()->diffInDays($sale->sale_time) }} days old</small>
                    </td>
                    <td>#{{ $sale->id }}</td>
                    <td>
                        <strong>{{ $sale->customer?->name ?? 'Private Client' }}</strong><br>
                        <small>{{ $sale->customer?->phone }}</small>
                    </td>
                    <td class="text-right">Rs. {{ number_format($sale->total, 2) }}</td>
                    <td class="text-right fw-bold" style="color: #d9534f;">Rs. {{ number_format($sale->outstanding_balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No outstanding balances found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="grand-total-box">
        <div class="grand-total-label">Total Receivables</div>
        <div class="grand-total-value">Rs. {{ number_format($totalOutstanding, 2) }}</div>
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Credit Controller</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Sales Head</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Finance Director</div></div>
    </div>
</div>
</body>
</html>
