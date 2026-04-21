<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dues Audit - {{ now()->format('M d, Y') }}</title>
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
            <h2>Supplier Dues Audit</h2>
            <div class="meta-grid">
                <span class="meta-label">Status as of:</span>
                <span class="meta-value">{{ now()->format('d M Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Liability Summary</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Outstanding Liability</td>
                <td class="text-right fw-bold">Rs. {{ number_format($suppliers->sum('outstanding_balance'), 2) }}</td>
            </tr>
            <tr>
                <td>Affected Vendors</td>
                <td class="text-right">{{ $suppliers->count() }} Suppliers</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Outstanding Accounts Ledger</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Supplier / Company</th>
                <th class="text-center">POs</th>
                <th>Oldest Aging</th>
                <th class="text-right">Balance Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <strong>{{ $supplier->name }}</strong><br>
                        <small>{{ $supplier->company_name }}</small>
                    </td>
                    <td class="text-center">{{ $supplier->purchases->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])->count() }}</td>
                    <td>
                        @php 
                            $oldest = $supplier->purchases->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])->min('po_date');
                            $days = $oldest ? now()->diffInDays($oldest) : 0;
                        @endphp
                        {{ $oldest ? \Carbon\Carbon::parse($oldest)->format('d M Y') : 'N/A' }}<br>
                        <small style="color: #d9534f;">{{ $days }} DAYS AGED</small>
                    </td>
                    <td class="text-right fw-bold" style="color: #d9534f;">Rs. {{ number_format($supplier->outstanding_balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No outstanding supplier liabilities found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="grand-total-box">
        <div class="grand-total-label">Total Payables</div>
        <div class="grand-total-value">Rs. {{ number_format($suppliers->sum('outstanding_balance'), 2) }}</div>
    </div>

    <div style="margin-top: 30px; padding: 15px; border: 1px solid #dee2e6; background: #f8f9fa; font-size: 10px;">
        <strong>Audit Disclosure:</strong> This report represents finalized balances owed to procurement partners. 
        Aging is calculated from the date of Purchase Order issuance. Accounts aged over 30 days are flagged for treasury priority.
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Accounts Payable</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Procurement Head</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Treasury Approval</div></div>
    </div>
</div>
</body>
</html>
