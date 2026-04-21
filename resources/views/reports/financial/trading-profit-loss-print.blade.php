<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Profit & Loss Statement - {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.6; background: white; }
        .report-container { max-width: 210mm; margin: 0 auto; background: white; padding: 20px; }
        .header-grid { display: grid; grid-template-columns: 1fr 1fr; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .brand-section h1 { font-size: 24px; font-weight: 800; text-transform: uppercase; color: #000; }
        .report-meta { text-align: right; }
        .report-meta h2 { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 10px; text-transform: uppercase; }
        .trading-account { width: 100%; border-collapse: collapse; border: 1px solid #1a1a1a; }
        .trading-account th { background: #f1f1f1; border: 1px solid #1a1a1a; padding: 12px; font-weight: 800; text-transform: uppercase; font-size: 10px; width: 50%; }
        .trading-account td { border: 1px solid #1a1a1a; padding: 12px; vertical-align: top; }
        .entry-line { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .entry-label { font-weight: 500; }
        .entry-value { font-weight: 700; }
        .total-row { border-top: 2px solid #1a1a1a; padding-top: 10px; display: flex; justify-content: space-between; font-weight: 800; }
        .footer-grid { margin-top: 80px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 40px; text-align: center; }
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
        </div>
        <div class="report-meta">
            <h2>Trading Profit & Loss Account</h2>
            <div style="font-size: 9px; font-weight: 600;">
                For the period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}<br>
                Generated: {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    <table class="trading-account">
        <thead>
            <tr>
                <th>Particulars (Debit)</th>
                <th>Particulars (Credit)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="entry-line">
                        <span class="entry-label">To Opening Stock</span>
                        <span class="entry-value">{{ number_format($openingStock, 2) }}</span>
                    </div>
                    <div class="entry-line">
                        <span class="entry-label">To Purchases</span>
                        <span class="entry-value">{{ number_format($purchases, 2) }}</span>
                    </div>
                    <div style="height: 100px;"></div> {{-- Spacer --}}
                    @if($grossProfit >= 0)
                        <div class="entry-line" style="margin-top: 20px;">
                            <span class="entry-label fw-bold">To Gross Profit c/d</span>
                            <span class="entry-value">{{ number_format($grossProfit, 2) }}</span>
                        </div>
                    @endif
                </td>
                <td>
                    <div class="entry-line">
                        <span class="entry-label">By Sales Revenue</span>
                        <span class="entry-value">{{ number_format($sales, 2) }}</span>
                    </div>
                    <div class="entry-line">
                        <span class="entry-label">By Closing Stock</span>
                        <span class="entry-value">{{ number_format($closingStock, 2) }}</span>
                    </div>
                    <div style="height: 100px;"></div> {{-- Spacer --}}
                    @if($grossProfit < 0)
                        <div class="entry-line" style="margin-top: 20px;">
                            <span class="entry-label fw-bold">By Gross Loss c/d</span>
                            <span class="entry-value">{{ number_format(abs($grossProfit), 2) }}</span>
                        </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <div class="total-row">
                        <span>TOTAL</span>
                        <span>Rs. {{ number_format(max($openingStock + $purchases + ($grossProfit > 0 ? $grossProfit : 0), $sales + $closingStock + ($grossProfit < 0 ? abs($grossProfit) : 0)), 2) }}</span>
                    </div>
                </td>
                <td>
                    <div class="total-row">
                        <span>TOTAL</span>
                        <span>Rs. {{ number_format(max($openingStock + $purchases + ($grossProfit > 0 ? $grossProfit : 0), $sales + $closingStock + ($grossProfit < 0 ? abs($grossProfit) : 0)), 2) }}</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; padding: 15px; background: #f9f9f9; border-left: 5px solid #1a1a1a;">
        <h4 style="text-transform: uppercase; font-size: 11px; font-weight: 800; margin-bottom: 5px;">Declaration</h4>
        <p style="font-size: 10px; color: #444;">
            This Trading Account represents the direct operational results of {{ config('app.name') }} for the specified period. 
            The gross profit/loss indicated is calculated based on available sales records, recorded purchases, and estimated 
            stock valuations as of {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}.
        </p>
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Prepared By</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Internal Auditor</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Authorized Signatory</div></div>
    </div>
</div>
</body>
</html>