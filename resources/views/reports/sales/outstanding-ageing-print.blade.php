<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ageing Analysis Report - {{ now()->format('M d, Y') }}</title>
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
        .period-card { padding: 10px; border: 1px solid #eee; text-align: center; }
        .period-label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: 700; margin-bottom: 5px; }
        .period-value { font-size: 14px; font-weight: 800; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
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
            <h2>Receivables Ageing</h2>
            <div class="meta-grid">
                <span class="meta-label">As of:</span>
                <span class="meta-value">{{ now()->format('d M Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Ageing Summary</div>
    <div class="summary-grid">
        @foreach($ageing as $period => $data)
            <div class="period-card">
                <div class="period-label">{{ $period }} Days</div>
                <div class="period-value" style="{{ $period == '90+' ? 'color: #d9534f;' : '' }}">
                    Rs. {{ number_format($data['amount'], 2) }}
                </div>
                <div style="font-size: 8px; color: #666;">{{ $data['count'] }} Bills</div>
            </div>
        @endforeach
    </div>

    <div class="section-title">Distribution Analysis</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Age Bracket</th>
                <th class="text-center">Invoices</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">% of Portfolio</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = collect($ageing)->sum('amount'); @endphp
            @foreach($ageing as $period => $data)
                <tr>
                    <td class="fw-bold">{{ $period }} Days</td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-right fw-bold" style="{{ $period == '90+' ? 'color: #d9534f;' : '' }}">
                        Rs. {{ number_format($data['amount'], 2) }}
                    </td>
                    <td class="text-right">
                        {{ $totalAmount > 0 ? round(($data['amount'] / $totalAmount) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="grand-total-box">
        <div class="grand-total-label">Consolidated Receivables</div>
        <div class="grand-total-value">Rs. {{ number_format($totalAmount, 2) }}</div>
    </div>

    <div style="margin-top: 30px; padding: 15px; border: 1px solid #d4af37; background: #fffcf5; font-size: 10px;">
        <strong>Risk Assessment Note:</strong> Portfolio health is monitored based on the distribution across ageing brackets. 
        Receivables exceeding 90 days ({{ $totalAmount > 0 ? round(($ageing['90+']['amount'] / $totalAmount) * 100, 1) : 0 }}%) are subject to intensified collection procedures.
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Credit Analyst</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Internal Auditor</div></div>
        <div><div class="sig-line"></div><div class="sig-label">General Manager</div></div>
    </div>
</div>
</body>
</html>
