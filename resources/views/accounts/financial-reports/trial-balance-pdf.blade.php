<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Trial Balance Report</title>
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
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .amount {
            text-align: right;
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Trial Balance Report</h1>
        <p>As at: {{ $asOfDate->format('d M Y') }}</p>
        <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Account Type</th>
                <th class="amount">Debit</th>
                <th class="amount">Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trialBalance as $item)
            <tr>
                <td>{{ $item['account']->account_code }}</td>
                <td>{{ $item['account']->account_name }}</td>
                <td>{{ $item['account']->account_type }}</td>
                <td class="amount">{{ number_format($item['debit'], 2) }}</td>
                <td class="amount">{{ number_format($item['credit'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td class="amount">{{ number_format($totalDebits, 2) }}</td>
                <td class="amount">{{ number_format($totalCredits, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
