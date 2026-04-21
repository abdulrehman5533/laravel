<!DOCTYPE html>
<html>
<head>
    <title>General Ledger Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-end { text-align: right; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>General Ledger Report</h1>
        <p>{{ $fromDate }} to {{ $toDate }}</p>
    </div>

    @if($account)
    <div class="summary">
        <table>
            <tr>
                <th width="25%">Account Name:</th>
                <td width="75%">{{ $account->account_code }} - {{ $account->account_name }}</td>
            </tr>
            <tr>
                <th>Opening Balance:</th>
                <td>Rs. {{ number_format($openingBalance, 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = $openingBalance; @endphp
            @forelse($ledgerEntries as $entry)
                @php $balance += ($entry->debit - $entry->credit); @endphp
                <tr>
                    <td>{{ $entry->date->format('d M Y') }}</td>
                    <td>{{ $entry->journalEntry->reference_number ?? 'N/A' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td class="text-end">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                    <td class="text-end">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                    <td class="text-end">{{ number_format($balance, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No entries found for this period</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y H:i:s') }}
    </div>
</body>
</html>
