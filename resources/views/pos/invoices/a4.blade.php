<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice_title ?? ($sale->invoice_type === 'estimate' ? 'WHOLESALE ESTIMATE' : 'TAX INVOICE') }} - {{ $sale->invoice_no }}</title>
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
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.4;
            background: white;
        }
        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
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
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            color: #000;
            margin-bottom: 5px;
        }
        .brand-section p {
            font-size: 9px;
            color: #444;
            margin-bottom: 2px;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta h2 {
            font-size: 20px;
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
            font-size: 8px;
        }
        .meta-value {
            font-weight: 700;
            color: #000;
        }

        /* Parties Section */
        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        .party-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        .party-details p {
            margin-bottom: 3px;
        }
        .party-name {
            font-size: 12px;
            font-weight: 700;
            color: #000;
            margin-bottom: 5px !important;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #f8f9fa;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 8px 5px;
            text-align: left;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            color: #333;
        }
        .items-table td {
            padding: 10px 5px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .item-desc { font-weight: 600; color: #000; font-size: 10px; }
        .item-sku { font-size: 8px; color: #666; margin-top: 2px; }
        
        /* Summary Section */
        .summary-wrapper {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            page-break-inside: avoid;
        }
        .amount-words {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #333;
        }
        .words-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 3px;
        }
        .words-value {
            font-size: 10px;
            font-weight: 600;
            font-style: italic;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 0;
        }
        .total-label {
            text-align: right;
            padding-right: 15px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
        }
        .total-value {
            text-align: right;
            font-weight: 700;
            width: 100px;
            font-size: 11px;
        }
        .grand-total-row td {
            border-top: 1px solid #333;
            border-bottom: 2px solid #333;
            padding: 10px 0;
            background: #fafafa;
        }
        .grand-total-label {
            color: #000;
            font-size: 10px;
        }
        .grand-total-value {
            font-size: 14px;
            color: #000;
        }

        /* Footer */
        .footer-grid {
            margin-top: 60px;
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
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .terms-section {
            margin-top: 40px;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .terms-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        /* Tax Breakdown */
        .tax-breakdown {
            margin-top: 15px;
            font-size: 8px;
            color: #666;
        }
        .tax-table {
            width: 100%;
            border-collapse: collapse;
        }
        .tax-table th, .tax-table td {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
            .invoice-container { width: 100%; }
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <!-- Header -->
    <div class="header-grid">
        <div class="brand-section" style="display: flex; align-items: center;">
            <svg width="60" height="60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;">
                <path d="M50 95C74.8528 95 95 74.8528 95 50C95 25.1472 74.8528 5 50 5C25.1472 5 5 25.1472 5 50C5 74.8528 25.1472 95 50 95Z" stroke="#000" stroke-width="2"/>
                <path d="M50 20L35 45L50 80L65 45L50 20Z" fill="#000"/>
                <path d="M50 20L25 40L50 80L75 40L50 20Z" stroke="#000" stroke-width="1"/>
                <path d="M42 35L45 30L50 33L55 30L58 35" stroke="white" stroke-width="2" fill="none"/>
            </svg>
            <div>
                <h1>{{ $shop_name ?? config('app.name', 'MAGIA LUPOS') }}</h1>
                <p><strong>Gold & Diamond Merchants</strong></p>
                <p>{{ $shop_address ?? config('pos.shop_address', '123 Jewellery Street, Karachi, Pakistan') }}</p>
                <p>Phone: {{ $shop_phone ?? config('pos.shop_phone', '+92 300 1234567') }}</p>
                <p>Email: {{ $shop_email ?? 'info@magialupos.com' }}</p>
                @if($sale->invoice_type !== 'estimate')
                    <p>{{ $shop_ntn ?? ('NTN: ' . config('pos.shop_ntn', '1234567-8')) }} | {{ $shop_gst ?? ('GST: ' . config('pos.shop_gst', '12-34-5678-901-23')) }}</p>
                @endif
            </div>
        </div>
        <div class="invoice-meta">
            <h2>{{ $invoice_title ?? ($sale->invoice_type === 'estimate' ? 'Wholesale Estimate' : 'Tax Invoice') }}</h2>
            <div class="meta-grid">
                <span class="meta-label">Invoice No:</span>
                <span class="meta-value">{{ $sale->invoice_no }}</span>
                <span class="meta-label">Date:</span>
                <span class="meta-value">{{ $sale->sale_time->format('d-m-Y') }}</span>
                <span class="meta-label">Status:</span>
                <span class="meta-value">{{ strtoupper($sale->payment_status) }}</span>
                @if($sale->payment_method)
                    <span class="meta-label">Method:</span>
                    <span class="meta-value">{{ strtoupper($sale->payment_method) }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties-grid">
        <div class="customer-section">
            <div class="party-title">Bill To</div>
            <div class="party-details">
                @if($sale->customer)
                    <p class="party-name">{{ $sale->customer->name }}</p>
                    <p>{{ $sale->customer->address ?? 'No Address Provided' }}</p>
                    <p>Phone: {{ $sale->customer->phone ?? 'N/A' }}</p>
                    @if($sale->invoice_type !== 'estimate' && $sale->customer->tax_id)
                        <p>Customer NTN: {{ $sale->customer->tax_id }}</p>
                    @endif
                @else
                    <p class="party-name">Walk-in Customer</p>
                @endif
            </div>
        </div>
        <div class="shipping-section">
            {{-- Optional: Delivery details or additional info --}}
            <div class="party-title">Store Details</div>
            <div class="party-details">
                <p><strong>Branch:</strong> {{ $sale->branch->name ?? 'Main Branch' }}</p>
                <p><strong>Served By:</strong> {{ $sale->createdBy->name ?? 'System' }}</p>
                <p><strong>Currency:</strong> {{ $sale->currency }}</p>
            </div>
        </div>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="20%">Item Description</th>
                <th width="8%" class="text-right">Gross Wt</th>
                <th width="8%" class="text-right">Stone Wt</th>
                <th width="8%" class="text-right">Net Wt</th>
                <th width="6%" class="text-center">Purity</th>
                <th width="8%" class="text-right">Fine Wt</th>
                <th width="10%" class="text-right">Gold Rate</th>
                <th width="8%" class="text-right">Making</th>
                <th width="8%" class="text-right">Stone Value</th>
                <th width="13%" class="text-right">Total ({{ $sale->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                @php
                    $inventory = $item->inventoryProduct;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="item-desc">{{ $item->description }}</div>
                        <div class="item-sku">SKU: {{ $item->sku }}</div>
                        @if($item->stone_count > 0 || $item->stone_carat > 0)
                            <div class="item-sku" style="color: #555;">
                                Stones: {{ $item->stone_type }} | 
                                {{ $item->stone_count > 0 ? $item->stone_count . ' pcs' : '' }} 
                                {{ $item->stone_carat > 0 ? '| ' . number_format($item->stone_carat, 3) . ' ct' : '' }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->gross_weight > 0 ? $item->gross_weight : $item->weight, 3) }}g</td>
                    <td class="text-right">{{ number_format($item->stone_weight ?? 0, 3) }}g</td>
                    <td class="text-right">{{ number_format($item->net_weight > 0 ? $item->net_weight : $item->weight, 3) }}g</td>
                    <td class="text-center">{{ $item->gold_purity ?? $inventory->purity->name ?? '—' }}</td>
                    <td class="text-right">{{ number_format($item->fine_weight ?? 0, 3) }}g</td>
                    <td class="text-right">{{ number_format($item->gold_rate, 2) }}</td>
                    <td class="text-right">
                        {{ number_format($item->making_charge_amount ?? 0, 2) }}
                        @if($item->making_charge > 0)
                            <div style="font-size: 7px; color: #777;">
                                ({{ number_format($item->making_charge, 2) }} 
                                @if($item->making_charge_type === 'per_gram')/g @elseif($item->making_charge_type === 'per_piece')/pc @endif)
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->stone_price ?? 0, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $totalGross = $sale->items->sum(function($i) { return $i->gross_weight > 0 ? $i->gross_weight : $i->weight; });
        $totalStone = $sale->items->sum('stone_weight');
        $totalNet = $sale->items->sum(function($i) { return $i->net_weight > 0 ? $i->net_weight : $i->weight; });
        $totalFine = $sale->items->sum('fine_weight');
    @endphp

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 8px; border: 1px solid #eee; padding: 10px; background: #fafafa;">
        <div><strong>Total Gross Weight:</strong> {{ number_format($totalGross, 3) }}g</div>
        <div><strong>Total Stone Weight:</strong> {{ number_format($totalStone, 3) }}g</div>
        <div><strong>Total Net Weight:</strong> {{ number_format($totalNet, 3) }}g</div>
        <div><strong>Total Fine Weight:</strong> {{ number_format($totalFine, 3) }}g</div>
    </div>

    <!-- Summary -->
    <div class="summary-wrapper">
        <div class="left-summary">
            <div class="amount-words">
                <div class="words-label">Amount in Words</div>
                <div class="words-value">{{ \App\Helpers\CurrencyHelper::spellOut($sale->total) }} {{ \App\Helpers\CurrencyHelper::name($sale->currency) }} Only</div>
            </div>

            @if($sale->invoice_type !== 'estimate' && $sale->tax_amount > 0)
                <div class="tax-breakdown">
                    <div class="party-title" style="border:none; margin-top: 20px;">Tax Summary</div>
                    <table class="tax-table">
                        <thead>
                            <tr>
                                <th align="left">Type</th>
                                <th align="right">Base Amount</th>
                                <th align="right">Rate</th>
                                <th align="right">Tax Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>GST / Sales Tax</td>
                                <td align="right">{{ number_format($sale->subtotal - $sale->discount, 2) }}</td>
                                <td align="right">{{ number_format($sale->items->avg('tax_percent'), 2) }}%</td>
                                <td align="right">{{ number_format($sale->tax_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="right-summary">
            <table class="totals-table">
                <tr>
                    <td class="total-label">Subtotal</td>
                    <td class="total-value">{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->making_charges > 0)
                <tr>
                    <td class="total-label">Making Charges</td>
                    <td class="total-value">{{ number_format($sale->making_charges, 2) }}</td>
                </tr>
                @endif
                @if($sale->wastage_amount > 0)
                <tr>
                    <td class="total-label">Wastage Value</td>
                    <td class="total-value">{{ number_format($sale->wastage_amount, 2) }}</td>
                </tr>
                @endif
                @if($sale->discount > 0)
                    <tr>
                        <td class="total-label">Discount</td>
                        <td class="total-value">-{{ number_format($sale->discount, 2) }}</td>
                    </tr>
                @endif
                @if($sale->tax_amount > 0)
                    <tr>
                        <td class="total-label">Total Tax</td>
                        <td class="total-value">{{ number_format($sale->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand-total-row">
                    <td class="total-label grand-total-label">Grand Total</td>
                    <td class="total-value grand-total-value">{{ number_format($sale->total, 2) }}</td>
                </tr>
                @php
                    $totalPaid = $sale->payments->where('status', 'completed')->sum('amount');
                @endphp
                @if($totalPaid > 0)
                    <tr>
                        <td class="total-label">Amount Paid</td>
                        <td class="total-value">{{ number_format($totalPaid, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Balance Due</td>
                        <td class="total-value" style="color: #d32f2f;">{{ number_format($sale->outstanding_balance, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="footer-grid">
        <div class="signature">
            <div class="sig-line"></div>
            <div class="sig-label">Customer Signature</div>
        </div>
        <div class="signature">
            <div class="sig-line"></div>
            <div class="sig-label">Prepared By</div>
        </div>
        <div class="signature">
            <div class="sig-line"></div>
            <div class="sig-label">Authorized Signatory</div>
        </div>
    </div>

    <!-- Terms -->
    <div class="terms-section">
        <div class="terms-title">Terms & Conditions</div>
        <p>1. Prices are subject to gold market fluctuations until full payment is received.</p>
        <p>2. Goods once sold will be exchanged or taken back according to the company's buy-back policy.</p>
        <p>3. This is a computer generated document and does not require a physical signature for validity.</p>
        @if($sale->invoice_type === 'estimate')
            <p><strong>4. This document is a Wholesale Estimate and is NOT valid for tax deduction purposes.</strong></p>
        @else
            <p>4. This is an official Tax Invoice compliant with Sales Tax regulations.</p>
        @endif
    </div>
</div>
</body>
</html>
