<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit & Loss Statement - {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
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
        .fw-bold { font-weight: 700; }
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #1a1a1a; margin: 20px 0 10px; background: #f1f1f1; padding: 5px 10px; border-left: 4px solid #333; }
        .summary-row { background: #fdfdfd; font-weight: 700; }
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
            <h2>Profit & Loss Statement</h2>
            <div class="meta-grid">
                <span class="meta-label">Period:</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Revenue & Direct Income</div>
    <table class="report-table">
        <tbody>
            <tr>
                <td>Gross Sales Revenue</td>
                <td class="text-right">Rs. {{ number_format($revenue, 2) }}</td>
            </tr>
            <tr>
                <td>Making Charges Income</td>
                <td class="text-right">Rs. {{ number_format($makingCharges, 2) }}</td>
            </tr>
            <tr>
                <td>Wastage Recovery</td>
                <td class="text-right">Rs. {{ number_format($wastage, 2) }}</td>
            </tr>
            <tr class="summary-row">
                <td>TOTAL REVENUE</td>
                <td class="text-right">Rs. {{ number_format($revenue + $makingCharges + $wastage, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Cost of Goods Sold (COGS)</div>
    <table class="report-table">
        <tbody>
            <tr>
                <td>Material Cost (Gold/Silver/Gems)</td>
                <td class="text-right">Rs. {{ number_format($costOfGoodsSold, 2) }}</td>
            </tr>
            <tr>
                <td>Sales Discounts Given</td>
                <td class="text-right">(Rs. {{ number_format($discount, 2) }})</td>
            </tr>
            <tr class="summary-row">
                <td>TOTAL DIRECT COSTS</td>
                <td class="text-right">Rs. {{ number_format($costOfGoodsSold + $discount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title" style="background:#e9ecef">Gross Profit Analysis</div>
    <table class="report-table">
        <tbody>
            <tr class="fw-bold" style="font-size: 13px;">
                <td>GROSS PROFIT</td>
                <td class="text-right">Rs. {{ number_format($grossProfit, 2) }}</td>
            </tr>
            <tr>
                <td>Gross Margin Percentage</td>
                <td class="text-right">{{ $grossMargin }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Operating Expenses</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Expense Category</th>
                <th class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->category }}</td>
                    <td class="text-right">{{ number_format($expense->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center text-muted">No expenses recorded for this period</td></tr>
            @endforelse
            <tr class="summary-row">
                <td>TOTAL OPERATING EXPENSES</td>
                <td class="text-right">Rs. {{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="grand-total-box">
        <div class="grand-total-label">Net Operating Profit</div>
        <div class="grand-total-value">Rs. {{ number_format($operatingProfit, 2) }}</div>
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Accountant</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Finance Manager</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Director / Owner</div></div>
    </div>
</div>
</body>
</html>