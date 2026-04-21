<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Balance Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .section-header {
            background-color: #e8e8e8;
            font-weight: bold;
            padding: 10px;
        }
        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .amount {
            text-align: right;
            width: 120px;
        }
        .section-total {
            background-color: #efefef;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Balance Sheet</h1>
        <p>As at: {{ $asAtDate->format('d M Y') }}</p>
        <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td colspan="3">ASSETS</td>
            </tr>
            @foreach($assetDetails as $item)
            <tr>
                <td>{{ $item['account']->account_code }}</td>
                <td>{{ $item['account']->account_name }}</td>
                <td class="amount">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="section-total">
                <td colspan="2">Total Assets</td>
                <td class="amount">{{ number_format($totalAssets, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr class="section-header">
                <td colspan="3">LIABILITIES</td>
            </tr>
            @foreach($liabilityDetails as $item)
            <tr>
                <td>{{ $item['account']->account_code }}</td>
                <td>{{ $item['account']->account_name }}</td>
                <td class="amount">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="section-total">
                <td colspan="2">Total Liabilities</td>
                <td class="amount">{{ number_format($totalLiabilities, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr class="section-header">
                <td colspan="3">EQUITY</td>
            </tr>
            @foreach($equityDetails as $item)
            <tr>
                <td>{{ $item['account']->account_code }}</td>
                <td>{{ $item['account']->account_name }}</td>
                <td class="amount">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="section-total">
                <td colspan="2">Total Equity</td>
                <td class="amount">{{ number_format($totalEquity, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">TOTAL LIABILITIES & EQUITY</td>
                <td class="amount">{{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
