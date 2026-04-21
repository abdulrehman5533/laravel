<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice_title ?? ($sale->invoice_type === 'estimate' ? 'WHOLESALE ESTIMATE' : 'TAX INVOICE') }} - {{ $sale->invoice_no }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 9px;
                color: #999;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10px;
            color: #2c3e50;
            line-height: 1.5;
            background: #fff;
        }
        .page {
            width: 21cm;
            height: 29.7cm;
            padding: 15mm 12mm;
            margin: 0 auto;
            background: white;
            position: relative;
        }
        .watermark {
            position: absolute;
            opacity: 0.08;
            font-size: 80px;
            font-weight: bold;
            color: #bdc3c7;
            z-index: -1;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            white-space: nowrap;
        }
        .doc-header {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            align-items: start;
            position: relative;
            z-index: 1;
        }
        .company-info {
            padding-bottom: 10px;
            border-bottom: 3px solid {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }};
        }
        .company-name {
            font-size: 24px;
            font-weight: 900;
            color: {{ $sale->invoice_type === 'estimate' ? '#e67e22' : '#1a5490' }};
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .company-tagline {
            font-size: 8px;
            color: #7f8c8d;
            font-style: italic;
            margin-bottom: 6px;
        }
        .company-contact {
            font-size: 8px;
            color: #555;
            line-height: 1.4;
        }
        .company-contact span {
            display: block;
            margin: 1px 0;
        }
        .invoice-type-panel {
            text-align: right;
            padding: 8px 12px;
            background: {{ $sale->invoice_type === 'estimate' ? '#fff3cd' : '#e8f4f8' }};
            border: 2px solid {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }};
            border-radius: 3px;
        }
        .invoice-type-badge {
            display: block;
            font-size: 13px;
            font-weight: 900;
            color: {{ $sale->invoice_type === 'estimate' ? '#d68910' : '#0c3d66' }};
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .invoice-meta {
            font-size: 9px;
            color: #555;
        }
        .invoice-meta-row {
            display: grid;
            grid-template-columns: 50px 1fr;
            gap: 5px;
            line-height: 1.5;
            margin: 1px 0;
        }
        .meta-key {
            font-weight: 700;
            color: #2c3e50;
        }
        .meta-value {
            color: #333;
        }
        .section-divider {
            height: 8px;
            background: linear-gradient(90deg, {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }}, transparent);
            margin: 12px 0;
        }
        .parties-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .party-box {
            border: 1px solid #ddd;
            background: #fafafa;
            padding: 8px;
        }
        .party-header {
            background: {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }};
            color: white;
            padding: 5px 7px;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            margin: -8px -8px 6px -8px;
            letter-spacing: 0.5px;
        }
        .party-detail {
            font-size: 9px;
            line-height: 1.5;
            margin-bottom: 4px;
        }
        .party-label {
            font-weight: 700;
            color: #555;
            display: inline-block;
            width: 60px;
        }
        .party-value {
            color: #2c3e50;
            display: inline;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .items-table thead {
            background: {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }};
            color: white;
        }
        .items-table th {
            padding: 6px 5px;
            text-align: left;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid {{ $sale->invoice_type === 'estimate' ? '#e67e22' : '#1a5490' }};
        }
        .items-table td {
            padding: 5px 5px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .items-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .items-table tbody tr:hover {
            background: #f0f0f0;
        }
        .desc-main {
            font-weight: 600;
            color: #2c3e50;
        }
        .desc-sub {
            font-size: 8px;
            color: #999;
            margin-top: 1px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-footer {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        .terms-box {
            border: 1px solid #ddd;
            background: #fafafa;
            padding: 8px;
            font-size: 8px;
            line-height: 1.5;
        }
        .terms-header {
            background: {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }};
            color: white;
            padding: 4px 6px;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            margin: -8px -8px 5px -8px;
            letter-spacing: 0.3px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #2c3e50;
        }
        .totals-table td {
            padding: 6px 8px;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        .total-label {
            text-align: left;
            font-weight: 600;
            color: #555;
        }
        .total-value {
            text-align: right;
            font-weight: 700;
            color: #2c3e50;
        }
        .total-row.subtotal {
            background: #f0f0f0;
        }
        .total-row.tax {
            background: #f0f0f0;
        }
        .total-row.discount {
            background: #e8f8f5;
            color: #27ae60;
        }
        .total-row.discount .total-label,
        .total-row.discount .total-value {
            color: #27ae60;
            font-weight: 700;
        }
        .total-row.final {
            background: {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }};
            color: white;
        }
        .total-row.final .total-label,
        .total-row.final .total-value {
            color: white;
            font-weight: 900;
            font-size: 11px;
        }
        .footer-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-area {
            text-align: center;
            font-size: 8px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 20px;
            padding-top: 25px;
            min-height: 40px;
        }
        .signature-label {
            font-weight: 700;
            color: #2c3e50;
            font-size: 8px;
            text-transform: uppercase;
        }
        .legal-disclaimer {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 7px;
            color: #666;
            line-height: 1.4;
            text-align: justify;
        }
        .certification-box {
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            padding: 7px;
            margin-bottom: 10px;
            font-size: 8px;
            line-height: 1.4;
        }
        .cert-title {
            font-weight: 700;
            color: {{ $sale->invoice_type === 'estimate' ? '#e67e22' : '#2c3e50' }};
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 8px;
        }
        .cert-item {
            margin: 2px 0;
            padding-left: 10px;
        }
        .payment-status-good {
            color: #27ae60;
            font-weight: 700;
        }
        .payment-status-pending {
            color: #e67e22;
            font-weight: 700;
        }
        .qr-barcode {
            text-align: center;
            font-size: 8px;
            color: #999;
            margin-top: 5px;
        }
        .note-box {
            background: #fff3cd;
            border-left: 3px solid #f39c12;
            padding: 6px 8px;
            margin-top: 8px;
            font-size: 8px;
            line-height: 1.4;
        }
        .estimate-only {
            background: #fff3cd;
            border: 1px solid #f39c12;
            padding: 6px 8px;
            margin-top: 8px;
            font-size: 8px;
            color: #856404;
            line-height: 1.4;
            font-style: italic;
        }
        .tax-notice {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 6px 8px;
            margin-top: 8px;
            font-size: 8px;
            color: #155724;
            line-height: 1.4;
        }
    </style>
</head>
<body>
<div class="page">
    @if($sale->invoice_type === 'estimate')
        <div class="watermark">ESTIMATE</div>
    @else
        <div class="watermark">{{ $invoice_title ?? 'TAX INVOICE' }}</div>
    @endif

    <div class="doc-header">
        <div class="company-info" style="display: flex; align-items: flex-start; border-bottom: none;">
            <svg width="60" height="60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 15px;">
                <path d="M50 95C74.8528 95 95 74.8528 95 50C95 25.1472 74.8528 5 50 5C25.1472 5 5 25.1472 5 50C5 74.8528 25.1472 95 50 95Z" stroke="{{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }}" stroke-width="2"/>
                <path d="M50 20L35 45L50 80L65 45L50 20Z" fill="{{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }}"/>
                <path d="M50 20L25 40L50 80L75 40L50 20Z" stroke="{{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }}" stroke-width="1"/>
                <path d="M42 35L45 30L50 33L55 30L58 35" stroke="white" stroke-width="2" fill="none"/>
            </svg>
            <div style="flex: 1; border-bottom: 3px solid {{ $sale->invoice_type === 'estimate' ? '#f39c12' : '#2c3e50' }}; padding-bottom: 10px;">
                <div class="company-name">{{ $shop_name ?? config('app.name', 'MAGIA LUPOS') }}</div>
                <div class="company-contact">
                    <span><strong>{{ $shop_address ?? config('pos.shop_address', 'Shop Address') }}</strong></span>
                    <span>📱 {{ $shop_phone ?? config('pos.shop_phone', '+92-300-000-0000') }}</span>
                    <span>📧 {{ $shop_email ?? 'info@magialupos.com' }}</span>
                    @if($sale->invoice_type !== 'estimate')
                        <span style="margin-top: 2px;"><strong>{{ $shop_ntn ?? ('NTN: ' . config('pos.shop_ntn', '1234567-8')) }}</strong> | <strong>{{ $shop_gst ?? ('GST: ' . config('pos.shop_gst', 'GST-123456')) }}</strong></span>
                    @endif
                </div>
            </div>
        </div>
        <div class="invoice-type-panel">
            <div class="invoice-type-badge">✦ {{ $invoice_title ?? ($sale->invoice_type === 'estimate' ? 'WHOLESALE ESTIMATE' : 'TAX INVOICE') }} ✦</div>
            <div class="invoice-meta">
                <div class="invoice-meta-row">
                    <span class="meta-key">No.:</span>
                    <span class="meta-value"><strong>#{{ $sale->invoice_no }}</strong></span>
                </div>
                <div class="invoice-meta-row">
                    <span class="meta-key">Date:</span>
                    <span class="meta-value">{{ $sale->sale_time->format('d M Y') }}</span>
                </div>
                <div class="invoice-meta-row">
                    <span class="meta-key">Time:</span>
                    <span class="meta-value">{{ $sale->sale_time->format('h:i A') }}</span>
                </div>
                <div class="invoice-meta-row">
                    <span class="meta-key">Status:</span>
                    <span class="meta-value">
                        @if($sale->payment_status === 'paid')
                            <span class="payment-status-good">✓ PAID</span>
                        @else
                            <span class="payment-status-pending">⚠ PENDING</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <div class="parties-section">
        <div class="party-box">
            <div class="party-header">📦 Bill To</div>
            <div class="party-detail">
                @if($sale->customer)
                    <div><span class="party-label">Name:</span><span class="party-value"><strong>{{ $sale->customer->name }}</strong></span></div>
                    <div><span class="party-label">Phone:</span><span class="party-value">{{ $sale->customer->phone ?? '—' }}</span></div>
                    <div><span class="party-label">Email:</span><span class="party-value">{{ $sale->customer->email ?? '—' }}</span></div>
                    @if($sale->customer->address)
                        <div style="margin-top: 3px;"><span class="party-label">Address:</span><span class="party-value">{{ $sale->customer->address }}</span></div>
                    @endif
                    @if($sale->invoice_type !== 'estimate' && $sale->customer->tax_id)
                        <div><span class="party-label">Tax ID:</span><span class="party-value">{{ $sale->customer->tax_id }}</span></div>
                    @endif
                @else
                    <div><span class="party-value"><strong>Walk-in Customer</strong></span></div>
                    <div><span class="party-value">Cash Transaction</span></div>
                @endif
            </div>
        </div>
        <div class="party-box">
            <div class="party-header">💳 Payment Info</div>
            <div class="party-detail">
                <div><span class="party-label">Method:</span><span class="party-value">{{ $sale->payment_method ?? 'Cash' }}</span></div>
                <div><span class="party-label">Status:</span>
                    <span class="party-value">
                        @if($sale->payment_status === 'paid')
                            <span class="payment-status-good">✓ Fully Paid</span>
                        @else
                            <span class="payment-status-pending">⚠ Outstanding</span>
                        @endif
                    </span>
                </div>
                <div><span class="party-label">Currency:</span><span class="party-value">{{ $sale->currency ?? 'PKR' }}</span></div>
                @if($sale->outstanding_balance > 0)
                    <div style="margin-top: 3px; color: #c0392b; font-weight: 700;">
                        <span class="party-label">Due:</span><span class="party-value">{{ number_format($sale->outstanding_balance, 2) }}</span>
                    </div>
                @endif
                @if($sale->due_date)
                    <div><span class="party-label">Due Date:</span><span class="party-value">{{ Carbon\Carbon::parse($sale->due_date)->format('d M Y') }}</span></div>
                @endif
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="32%">Item Description & Details</th>
                <th width="10%" class="text-right">Gross Wt</th>
                <th width="10%" class="text-right">Stone Wt</th>
                <th width="10%" class="text-right">Net Wt</th>
                <th width="8%" class="text-center">Purity</th>
                <th width="10%" class="text-right">Rate</th>
                <th width="10%" class="text-right">Making</th>
                <th width="10%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->items as $item)
                <tr>
                    <td>
                        <div class="desc-main">{{ $item->description }}</div>
                        <div class="desc-sub">SKU: {{ $item->sku }}</div>
                        @if($item->stone_count > 0 || $item->stone_carat > 0)
                            <div class="desc-sub">
                                Stones: {{ $item->stone_type }} | 
                                {{ $item->stone_count > 0 ? $item->stone_count . ' pcs' : '' }} 
                                {{ $item->stone_carat > 0 ? '| ' . number_format($item->stone_carat, 3) . ' ct' : '' }}
                                | Value: {{ number_format($item->stone_price, 2) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->gross_weight > 0 ? $item->gross_weight : $item->weight, 3) }}g</td>
                    <td class="text-right">{{ number_format($item->stone_weight ?? 0, 3) }}g</td>
                    <td class="text-right">{{ number_format($item->net_weight > 0 ? $item->net_weight : $item->weight, 3) }}g</td>
                    <td class="text-center">{{ $item->gold_purity }}</td>
                    <td class="text-right">{{ number_format($item->gold_rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->making_charge_amount, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 10px; color: #999;">No items on this invoice</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $totalGross = $sale->items->sum(function($i) { return $i->gross_weight > 0 ? $i->gross_weight : $i->weight; });
        $totalStone = $sale->items->sum('stone_weight');
        $totalNet = $sale->items->sum(function($i) { return $i->net_weight > 0 ? $i->net_weight : $i->weight; });
        $totalFine = $sale->items->sum('fine_weight');
    @endphp

    <div style="display: flex; margin-bottom: 12px; font-size: 8px; border: 1px solid #ddd; background: #f8f9fa;">
        <div style="flex: 1; padding: 5px; border-right: 1px solid #ddd; text-align: center;"><strong>Gross:</strong> {{ number_format($totalGross, 3) }}g</div>
        <div style="flex: 1; padding: 5px; border-right: 1px solid #ddd; text-align: center;"><strong>Stone:</strong> {{ number_format($totalStone, 3) }}g</div>
        <div style="flex: 1; padding: 5px; border-right: 1px solid #ddd; text-align: center;"><strong>Net:</strong> {{ number_format($totalNet, 3) }}g</div>
        <div style="flex: 1; padding: 5px; text-align: center;"><strong>Fine:</strong> {{ number_format($totalFine, 3) }}g</div>
    </div>

    <div class="summary-footer">
        <div>
            <div class="terms-box">
                <div class="terms-header">📋 Terms & Conditions</div>
                @if($sale->invoice_type === 'estimate')
                    <div>
                        <strong>Wholesale Estimate Notice:</strong><br>
                        • Valid for 7 days from issue date<br>
                        • Subject to stock availability<br>
                        • Prices are approximate and subject to change<br>
                        • This is a pro-forma document for quotation purposes<br>
                        • Not valid for GST/TAX compliance
                    </div>
                @else
                    <div>
                        <strong>Tax Invoice Terms:</strong><br>
                        • Payment due within 30 days<br>
                        • Prices include all applicable taxes<br>
                        • Late payment subject to interest<br>
                        • Disputes must be raised within 14 days<br>
                        • Valid GST compliant invoice
                    </div>
                @endif
            </div>

            @if($sale->items->isNotEmpty())
                <div class="certification-box">
                    <div class="cert-title">💎 Jewelry Certification</div>
                    @php
                        $totalWeight = $sale->items->sum('weight');
                        $avgPurity = $sale->items->count() > 0 ? round($sale->items->avg('gold_rate') ?? 0, 2) : 0;
                    @endphp
                    <div class="cert-item">Total Weight: <strong>{{ number_format($totalWeight, 3) }}g</strong></div>
                    <div class="cert-item">Items: <strong>{{ $sale->items->count() }}</strong></div>
                    <div class="cert-item">Verified by: <strong>{{ auth()->user()->name ?? 'System' }}</strong></div>
                    <div class="cert-item">Date: <strong>{{ now()->format('d M Y') }}</strong></div>
                </div>
            @endif
        </div>

        <div>
            <table class="totals-table">
                <tr class="total-row subtotal">
                    <td class="total-label">Subtotal</td>
                    <td class="total-value">{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->making_charges > 0)
                    <tr class="total-row subtotal">
                        <td class="total-label">Making Charges</td>
                        <td class="total-value">{{ number_format($sale->making_charges, 2) }}</td>
                    </tr>
                @endif
                @if($sale->wastage_amount > 0)
                    <tr class="total-row subtotal">
                        <td class="total-label">Wastage Value</td>
                        <td class="total-value">{{ number_format($sale->wastage_amount, 2) }}</td>
                    </tr>
                @endif
                @if($sale->discount > 0)
                    <tr class="total-row discount">
                        <td class="total-label">Discount</td>
                        <td class="total-value">-{{ number_format($sale->discount, 2) }}</td>
                    </tr>
                @endif
                @if($sale->tax_amount > 0)
                    <tr class="total-row tax">
                        <td class="total-label">Total Tax</td>
                        <td class="total-value">{{ number_format($sale->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row final">
                    <td class="total-label">TOTAL AMOUNT</td>
                    <td class="total-value">{{ number_format($sale->total, 2) }}</td>
                </tr>
                @php
                    $totalPaid = $sale->payments->where('status', 'completed')->sum('amount');
                @endphp
                @if($totalPaid > 0)
                    <tr class="total-row subtotal">
                        <td class="total-label">Amount Paid</td>
                        <td class="total-value">{{ number_format($totalPaid, 2) }}</td>
                    </tr>
                    <tr class="total-row subtotal">
                        <td class="total-label" style="color: #c0392b;">Balance Due</td>
                        <td class="total-value" style="color: #c0392b;">{{ number_format($sale->outstanding_balance, 2) }}</td>
                    </tr>
                @endif
            </table>

            @if($sale->invoice_type !== 'estimate')
                <div class="tax-notice">
                    <strong>✓ GST Compliant Invoice</strong><br>
                    This is an officially certified tax invoice as per government regulations.
                </div>
            @else
                <div class="estimate-only">
                    <strong>⚠ Wholesale Estimate</strong><br>
                    This document is for quotation purposes only. Not valid for GST/TAX compliance.
                </div>
            @endif
        </div>
    </div>

    <div class="footer-section">
        <div class="signature-area">
            <div class="signature-line"></div>
            <div class="signature-label">Prepared By<br>Cashier</div>
        </div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <div class="signature-label">Authorized By<br>Manager</div>
        </div>
        <div class="signature-area">
            <div class="signature-line"></div>
            <div class="signature-label">Customer<br>Signature</div>
        </div>
    </div>

    <div class="legal-disclaimer">
        <strong>Legal Notice:</strong> This invoice is generated by {{ config('app.name') }} Professional Jewelry Management System. All information contained herein is accurate to the best of our knowledge. Payment terms and conditions are as stated above. This document is confidential and proprietary to {{ config('app.name') }}. Unauthorized reproduction or distribution is prohibited. For disputes, contact {{ config('pos.shop_email') }} within 14 days of invoice date.
    </div>
</div>
</body>
</html>
