<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metal Book (Fine Weight Report) - {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; background: white; }
        .report-container { width: 100%; max-width: 297mm; margin: 0 auto; background: white; padding: 15px; }
        .header-grid { display: grid; grid-template-columns: 1fr 1fr; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .brand-section h1 { font-size: 20px; font-weight: 800; text-transform: uppercase; color: #000; }
        .brand-section p { font-size: 9px; color: #444; }
        .report-meta { text-align: right; }
        .report-meta h2 { font-size: 16px; font-weight: 700; color: #333; margin-bottom: 5px; text-transform: uppercase; }
        .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .report-table th { background: #f8f9fa; border: 1px solid #333; padding: 6px 4px; text-align: left; font-weight: 700; font-size: 9px; text-transform: uppercase; }
        .report-table td { padding: 6px 4px; border: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .bg-light { background: #f9f9f9; }
        .footer-grid { margin-top: 50px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center; }
        .sig-line { border-top: 1px solid #000; margin-bottom: 5px; }
        .sig-label { font-size: 8px; font-weight: 700; text-transform: uppercase; }
        @media print { body { -webkit-print-color-adjust: exact; } .report-container { width: 100%; padding: 0; } }
    </style>
</head>
<body onload="window.print()">
<div class="report-container">
    <div class="header-grid">
        <div class="brand-section">
            <h1>{{ config('app.name', 'MAGIA LUPOS') }}</h1>
            <p><strong>Metal Movement & Stock Register</strong></p>
            <p>{{ config('pos.shop_address', 'Karachi, Pakistan') }}</p>
        </div>
        <div class="report-meta">
            <h2>Metal Book Audit Report</h2>
            <div style="font-size: 9px; font-weight: 600;">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}<br>
                Generated: {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th width="8%">Date</th>
                <th width="12%">Transaction</th>
                <th width="15%">Product / SKU</th>
                <th width="10%">Purity</th>
                <th width="9%" class="text-right">Gross Wt</th>
                <th width="9%" class="text-right">Fine Wt</th>
                <th width="9%" class="text-right">Rate</th>
                <th width="10%" class="text-right">In (Qty)</th>
                <th width="10%" class="text-right">Out (Qty)</th>
                <th width="8%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('d/m/y') }}</td>
                    <td style="font-size: 8px;">{{ strtoupper($movement->type) }} ({{ $movement->reference_type }})</td>
                    <td>
                        <div class="fw-bold">{{ $movement->product->name }}</div>
                        <div style="font-size: 7px; color:#666;">SKU: {{ $movement->product->sku }}</div>
                    </td>
                    <td>{{ $movement->product->purity->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($movement->product->gross_weight, 3) }}g</td>
                    <td class="text-right fw-bold">{{ number_format($movement->product->fine_weight, 3) }}g</td>
                    <td class="text-right">{{ number_format($movement->product->cost_price, 2) }}</td>
                    <td class="text-right text-success">{{ $movement->quantity > 0 ? $movement->quantity : '-' }}</td>
                    <td class="text-right text-danger">{{ $movement->quantity < 0 ? abs($movement->quantity) : '-' }}</td>
                    <td class="text-center">
                        <span style="font-size: 7px;">{{ strtoupper($movement->status ?? 'VERIFIED') }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center py-5">No metal movements recorded for this period</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-light fw-bold">
                <td colspan="5" class="text-right">Current Period Totals:</td>
                <td class="text-right">{{ number_format($movements->sum(fn($m) => $m->product->fine_weight * $m->quantity), 3) }}g</td>
                <td></td>
                <td class="text-right text-success">{{ $movements->where('quantity', '>', 0)->sum('quantity') }}</td>
                <td class="text-right text-danger">{{ abs($movements->where('quantity', '<', 0)->sum('quantity')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <div style="padding: 15px; border: 1px solid #333;">
            <div class="fw-bold mb-1" style="text-transform: uppercase;">Audit Summary</div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 3px 0;">
                <span>Opening Stock (Est.)</span>
                <span>{{ number_format($openingFine, 3) }}g</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 3px 0;">
                <span>Net Movement Fine Wt</span>
                <span>{{ number_format($movements->sum(fn($m) => $m->product->fine_weight * $m->quantity), 3) }}g</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: 800; padding: 5px 0;">
                <span>Closing Balance Fine Wt</span>
                <span>{{ number_format($openingFine + $movements->sum(fn($m) => $m->product->fine_weight * $m->quantity), 3) }}g</span>
            </div>
        </div>
        <div style="font-size: 9px; color: #666; font-style: italic;">
            Note: This metal book report represents the fine weight movement of precious metals. All weights are in grams (g) and purity is adjusted to 24K equivalent (Fine Weight). Verification of physical stock against these records is mandatory for periodic audits.
        </div>
    </div>

    <div class="footer-grid">
        <div><div class="sig-line"></div><div class="sig-label">Stock In-Charge</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Audit Team</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Finance Manager</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Managing Director</div></div>
    </div>
</div>
</body>
</html>