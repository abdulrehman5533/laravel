<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Calculation Report - #{{ $calculation->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #C5A059;
            --deep-charcoal: #2C3E50;
            --soft-gold: #F4EBD0;
            --border-color: #E5E7EB;
            --text-main: #374151;
            --text-muted: #6B7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background: #F9FAFB;
            line-height: 1.5;
        }

        .print-wrapper {
            max-width: 850px;
            margin: 40px auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            border-top: 8px solid var(--primary-gold);
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(197, 160, 89, 0.03);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            font-weight: 700;
        }

        .content-inner {
            position: relative;
            z-index: 1;
            padding: 60px;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
            border-bottom: 2px solid var(--soft-gold);
            padding-bottom: 30px;
        }

        .brand-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--deep-charcoal);
            letter-spacing: -0.5px;
        }

        .brand-section p {
            color: var(--primary-gold);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        .report-meta {
            text-align: right;
        }

        .report-id {
            font-weight: 700;
            font-size: 18px;
            color: var(--deep-charcoal);
        }

        .report-date {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Section Titles */
        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary-gold);
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--soft-gold);
            margin-left: 15px;
        }

        /* Grid Layout */
        .params-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .param-card {
            background: #FFF;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .param-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .param-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--deep-charcoal);
        }

        /* Table Design */
        .breakdown-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 40px;
        }

        .breakdown-table th {
            text-align: left;
            padding: 15px;
            background: #F8FAFC;
            border-bottom: 2px solid var(--border-color);
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .breakdown-table td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .component-info strong {
            display: block;
            font-size: 14px;
            color: var(--deep-charcoal);
        }

        .component-info small {
            color: var(--text-muted);
            font-size: 12px;
        }

        .amount-cell {
            text-align: right;
            font-weight: 600;
            font-size: 15px;
            color: var(--deep-charcoal);
        }

        /* Total Section */
        .footer-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .total-badge {
            background: var(--deep-charcoal);
            color: white;
            padding: 30px 50px;
            border-radius: 12px;
            text-align: right;
            position: relative;
        }

        .total-badge::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 10px;
            bottom: 10px;
            width: 4px;
            background: var(--primary-gold);
            border-radius: 2px;
        }

        .total-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .total-amount {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            margin-top: 5px;
        }

        /* Footer */
        .report-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .disclaimer {
            font-size: 11px;
            color: var(--text-muted);
            max-width: 400px;
        }

        .signature-block {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid var(--deep-charcoal);
            margin-top: 40px;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--deep-charcoal);
        }

        /* Utilities */
        .no-print-zone {
            max-width: 850px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-print {
            background: var(--primary-gold);
            color: white;
        }

        .btn-back {
            background: var(--text-muted);
            color: white;
        }

        @media print {
            body { background: white; }
            .print-wrapper { 
                margin: 0; 
                box-shadow: none; 
                max-width: 100%;
            }
            .no-print-zone { display: none; }
            .content-inner { padding: 40px; }
        }
    </style>
</head>
<body>

    <div class="no-print-zone">
        <button onclick="window.print()" class="btn btn-print">Print Document</button>
        <button onclick="window.history.back()" class="btn btn-back">Return to Dashboard</button>
    </div>

    <div class="print-wrapper">
        <div class="watermark">JEWELLERY PRO</div>
        
        <div class="content-inner">
            <!-- Header -->
            <div class="header">
                <div class="brand-section">
                    <h1>JEWELLERY PRO</h1>
                    <p>Weight & Price Calculation</p>
                </div>
                <div class="report-meta">
                    <div class="report-id">Ref: #CALC-{{ str_pad($calculation->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <div class="report-date">{{ $calculation->created_at->format('d M Y | h:i A') }}</div>
                </div>
            </div>

            <!-- Summary Params -->
            <div class="section-title">Input Parameters</div>
            <div class="params-grid">
                <div class="param-card">
                    <div class="param-label">Gross Weight</div>
                    <div class="param-value">{{ $details['gross_weight_input'] }} {{ strtoupper($details['input_unit']) }}</div>
                </div>
                <div class="param-card">
                    <div class="param-label">Metal Purity</div>
                    <div class="param-value">{{ $details['karat'] }}K Gold ({{ number_format($details['purity_percentage'], 2) }}%)</div>
                </div>
                <div class="param-card">
                    <div class="param-label">Market Rate</div>
                    <div class="param-value">Rs.{{ number_format($details['rate_per_gram'], 2) }}/g</div>
                </div>
            </div>

            <!-- Price Table -->
            <div class="section-title">Valuation Breakdown</div>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Calculation Component</th>
                        <th style="text-align: right;">Valuation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="component-info">
                            <strong>Pure Metal Value</strong>
                            <small>{{ number_format($details['pure_weight'], 4) }}g Net @ {{ number_format($details['rate_per_gram'], 2) }}</small>
                        </td>
                        <td class="amount-cell">Rs.{{ number_format($details['metal_value'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="component-info">
                            <strong>Wastage Allowance ({{ $details['wastage_type'] === 'percentage' ? $details['wastage_input'].'%' : $details['wastage_input'].'g' }})</strong>
                            <small>Additional Weight: {{ number_format($details['wastage_weight'], 4) }}g</small>
                        </td>
                        <td class="amount-cell">Rs.{{ number_format($details['wastage_value'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="component-info">
                            <strong>Craftsmanship (Making)</strong>
                            <small>{{ $details['making_charge_description'] }}</small>
                        </td>
                        <td class="amount-cell">Rs.{{ number_format($details['making_charges'], 2) }}</td>
                    </tr>
                    @if($details['stone_total_cost'] > 0)
                    <tr>
                        <td class="component-info">
                            <strong>Gemstone Valuation</strong>
                            <small>{{ number_format($details['stone_weight_carats'], 4) }} ct @ Rs.{{ number_format($details['stone_price_per_carat'], 2) }}</small>
                        </td>
                        <td class="amount-cell">Rs.{{ number_format($details['stone_total_cost'], 2) }}</td>
                    </tr>
                    @endif
                    @if($details['custom_charges'] > 0)
                    <tr>
                        <td class="component-info">
                            <strong>Additional Custom Charges</strong>
                            <small>Processing & Handling Fees</small>
                        </td>
                        <td class="amount-cell">Rs.{{ number_format($details['custom_charges'], 2) }}</td>
                    </tr>
                    @endif
                    
                    @if($details['tax_amount'] > 0)
                    <tr>
                        <td class="component-info">
                            <strong>Government Taxes ({{ number_format($details['tax_percentage'], 2) }}%)</strong>
                            <small>Applied on subtotal</small>
                        </td>
                        <td class="amount-cell" style="color: #DC2626;">+ Rs.{{ number_format($details['tax_amount'], 2) }}</td>
                    </tr>
                    @endif
                    
                    @if($details['discount_amount'] > 0)
                    <tr>
                        <td class="component-info">
                            <strong>Exclusive Discount ({{ number_format($details['discount_percentage'], 2) }}%)</strong>
                            <small>Promotional reduction applied</small>
                        </td>
                        <td class="amount-cell" style="color: #059669;">- Rs.{{ number_format($details['discount_amount'], 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- Notes if any -->
            @if($calculation->notes)
            <div class="section-title">Additional Remarks</div>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 40px; font-style: italic;">
                "{{ $calculation->notes }}"
            </p>
            @endif

            <!-- Final Summary -->
            <div class="footer-summary">
                <div class="total-badge">
                    <div class="total-label">Estimated Transaction Value</div>
                    <div class="total-amount">Rs. {{ number_format($details['final_total'], 2) }}</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="report-footer">
                <div class="disclaimer">
                    <strong>Disclaimer:</strong> This calculation is an estimate based on current market rates and parameters provided. Final prices may vary at the time of transaction. Valid for 24 hours from generation.
                </div>
                <div class="signature-block">
                    <div class="signature-line">Authorized Signatory</div>
                </div>
            </div>
        </div>
    </div>

    @if(request('print'))
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>
</html>
