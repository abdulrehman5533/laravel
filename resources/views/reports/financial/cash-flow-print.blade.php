<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Flow Statement - {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
            background: white;
        }
        .report-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }
        /* Header Section */
        .header-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .brand-section h1 {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            color: #000;
            margin-bottom: 5px;
        }
        .brand-section p {
            font-size: 10px;
            color: #444;
            margin-bottom: 2px;
        }
        .report-meta {
            text-align: right;
        }
        .report-meta h2 {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .meta-grid {
            display: inline-grid;
            grid-template-columns: auto auto;
            gap: 5px 15px;
            text-align: left;
        }
        .meta-label {
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            font-size: 9px;
        }
        .meta-value {
            font-weight: 700;
            color: #000;
        }

        /* Table Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .report-table th {
            background: #f8f9fa;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            color: #333;
        }
        .report-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1a1a1a;
            margin-bottom: 10px;
            background: #f1f1f1;
            padding: 5px 10px;
            border-left: 4px solid #333;
        }

        /* Summary Section */
        .summary-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .summary-box {
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #eee;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
        }
        .net-cash-flow {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #333;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 800;
        }

        /* Footer */
        .footer-grid {
            margin-top: 80px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }
        .sig-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
            .report-container { width: 100%; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
<div class="report-container">
    <!-- Header -->
    <div class="header-grid">
        <div class="brand-section">
            <h1>{{ config('app.name', 'MAGIA LUPOS') }}</h1>
            <p><strong>Gold & Diamond Merchants</strong></p>
            <p>{{ config('pos.shop_address', '123 Jewellery Street, Karachi, Pakistan') }}</p>
            <p>Phone: {{ config('pos.shop_phone', '+92 300 1234567') }}</p>
            <p>Email: info@magialupos.com</p>
        </div>
        <div class="report-meta">
            <h2>Cash Flow Statement</h2>
            <div class="meta-grid">
                <span class="meta-label">Period:</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                <span class="meta-label">Generated:</span>
                <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- CASH INFLOWS -->
    <div class="section-title">Cash Inflows (Receipts)</div>
    <table class="report-table">
        <thead>
            <tr>
                <th width="60%">Description / Category</th>
                <th width="20%">Reference</th>
                <th width="20%" class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>POS Sales Collection</td>
                <td>Direct Sales</td>
                <td class="text-right">{{ number_format($salesInflows, 2) }}</td>
            </tr>
            @foreach($inflowsByCategory as $item)
                <tr>
                    <td>{{ ucfirst($item->category) }}</td>
                    <td>External Receipt</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold" style="background: #fafafa;">
                <td colspan="2">TOTAL CASH RECEIPTS</td>
                <td class="text-right" style="border-top: 1px solid #333; border-bottom: 1px solid #333;">
                    {{ number_format($inflows + $salesInflows, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- CASH OUTFLOWS -->
    <div class="section-title">Cash Outflows (Payments)</div>
    <table class="report-table">
        <thead>
            <tr>
                <th width="60%">Description / Category</th>
                <th width="20%">Reference</th>
                <th width="20%" class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($outflowsByCategory as $item)
                <tr>
                    <td>Business Expenditure / Procurement</td>
                    <td>{{ ucfirst($item->category) }}</td>
                    <td class="text-right">({{ number_format($item->total, 2) }})</td>
                </tr>
            @endforeach
            <tr class="fw-bold" style="background: #fafafa;">
                <td colspan="2">TOTAL CASH PAYMENTS</td>
                <td class="text-right" style="border-top: 1px solid #333; border-bottom: 1px solid #333;">
                    ({{ number_format($outflows, 2) }})
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Summary & Net Cash Flow -->
    <div class="summary-wrapper">
        <div class="summary-box">
            <div class="fw-bold mb-2" style="text-decoration: underline;">Audit Note</div>
            <p style="font-size: 10px; color: #666;">
                This statement reflects the net liquidity position of the company for the specified period. 
                Inflows include direct sales and other receipts, while outflows include all business-related 
                expenditures and procurements recorded in the system.
            </p>
        </div>
        <div>
            <div class="summary-item">
                <span>Total Receipts</span>
                <span class="text-success">{{ number_format($inflows + $salesInflows, 2) }}</span>
            </div>
            <div class="summary-item">
                <span>Total Payments</span>
                <span class="text-danger">({{ number_format($outflows, 2) }})</span>
            </div>
            <div class="net-cash-flow">
                <span>NET CASH FLOW</span>
                <span class="{{ ($netCashFlow + $salesInflows) >= 0 ? 'text-success' : 'text-danger' }}">
                    Rs. {{ number_format($netCashFlow + $salesInflows, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="footer-grid">
        <div>
            <div class="sig-line"></div>
            <div class="sig-label">Prepared By</div>
        </div>
        <div>
            <div class="sig-line"></div>
            <div class="sig-label">Verified By</div>
        </div>
        <div>
            <div class="sig-line"></div>
            <div class="sig-label">Authorized Signatory</div>
        </div>
    </div>
</div>
</body>
</html>