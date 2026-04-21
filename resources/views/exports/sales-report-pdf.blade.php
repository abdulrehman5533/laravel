<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 100%;
            padding: 40px;
        }

        .header {
            border-bottom: 3px solid #d4af37;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 12px;
        }

        .date-range {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #d4af37;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #d4af37;
        }

        .metric-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .metric-value {
            color: #1a1a1a;
            font-size: 24px;
            font-weight: bold;
        }

        .metric-sub {
            color: #999;
            font-size: 11px;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        th {
            background-color: #1a1a1a;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #ddd;
        }

        td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .section-title {
            color: #1a1a1a;
            font-size: 16px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4af37;
        }

        .summary-table {
            width: 100%;
            margin: 20px 0;
        }

        .summary-table td:first-child {
            font-weight: bold;
            width: 50%;
            color: #666;
        }

        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
            color: #1a1a1a;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-completed {
            background-color: #d4f1d4;
            color: #059669;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #b45309;
        }

        .positive {
            color: #059669;
        }

        .negative {
            color: #dc2626;
        }

        .neutral {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Sales Report</h1>
            <p>Generated on {{ now()->format('F j, Y \a\t H:i') }}</p>
        </div>

        <!-- Date Range Info -->
        @if(isset($dateRange))
        <div class="date-range">
            <strong>Report Period:</strong> {{ $dateRange }}
        </div>
        @endif

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Sales</div>
                <div class="metric-value positive">{{ number_format($metrics['totalSales'] ?? 0, 2) }}</div>
                <div class="metric-sub">{{ $metrics['count'] ?? 0 }} transactions</div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Amount Paid</div>
                <div class="metric-value neutral">{{ number_format($metrics['totalPaid'] ?? 0, 2) }}</div>
                <div class="metric-sub">{{ number_format($metrics['totalOutstanding'] ?? 0, 2) }} outstanding</div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Gross Profit</div>
                <div class="metric-value positive">{{ number_format($metrics['grossProfit'] ?? 0, 2) }}</div>
                <div class="metric-sub">{{ $metrics['grossMargin'] ?? 0 }}% margin</div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Avg Order Value</div>
                <div class="metric-value neutral">{{ number_format($metrics['averageOrderValue'] ?? 0, 2) }}</div>
                <div class="metric-sub">{{ $metrics['totalItems'] ?? 0 }} items sold</div>
            </div>
        </div>

        <!-- Detailed Summary -->
        <div class="section-title">Financial Summary</div>
        <table class="summary-table">
            <tr>
                <td>Total Items Sold</td>
                <td>{{ $metrics['totalItems'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total Tax Collected</td>
                <td>{{ number_format($metrics['totalTax'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Total Discounts Given</td>
                <td>{{ number_format($metrics['totalDiscount'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Total Making Charges</td>
                <td>{{ number_format($metrics['totalMakingCharges'] ?? 0, 2) }}</td>
            </tr>
            <tr style="background-color: #f0f0f0; border-top: 2px solid #ddd;">
                <td><strong>Cost of Goods Sold</strong></td>
                <td><strong>{{ number_format($metrics['costOfGoodsSold'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr style="background-color: #f0f0f0;">
                <td><strong>Gross Profit</strong></td>
                <td><strong>{{ number_format($metrics['grossProfit'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr style="background-color: #f0f0f0; border-bottom: 2px solid #ddd;">
                <td><strong>Profit Margin</strong></td>
                <td><strong>{{ $metrics['grossMargin'] ?? 0 }}%</strong></td>
            </tr>
        </table>

        <!-- Sales Transactions -->
        @if(isset($sales) && $sales->count() > 0)
        <div class="section-title">Sales Transactions ({{ $sales->count() }} records)</div>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Outstanding</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>#{{ $sale->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_time)->format('M d, H:i') }}</td>
                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                    <td class="text-right">{{ $sale->items->count() }}</td>
                    <td class="text-right positive"><strong>{{ number_format($sale->total, 2) }}</strong></td>
                    <td class="text-right">{{ number_format($sale->tax_amount ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($sale->payments()->sum('amount'), 2) }}</td>
                    <td class="text-right">
                        @if($sale->outstanding_balance > 0)
                        <span class="negative">{{ number_format($sale->outstanding_balance, 2) }}</span>
                        @else
                        <span class="positive">Paid</span>
                        @endif
                    </td>
                    <td>
                        @if($sale->status === 'Completed')
                        <span class="badge badge-completed">✓ Completed</span>
                        @else
                        <span class="badge badge-pending">{{ $sale->status }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This report was automatically generated by {{ config('app.name', 'MAGIA LUPOS Management System') }}</p>
            <p>{{ config('app.name') }} © {{ now()->year }}</p>
        </div>
    </div>
</body>
</html>
