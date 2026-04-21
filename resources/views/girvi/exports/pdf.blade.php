<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Girvi Registry Export</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
    h2 { text-align: center; font-size: 14px; margin-bottom: 4px; }
    p.sub { text-align: center; font-size: 9px; color: #666; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e293b; color: #fff; padding: 6px 4px; text-align: left; font-size: 9px; }
    td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
    tr:nth-child(even) td { background: #f8fafc; }
    .badge-active { color: #16a34a; font-weight: bold; }
    .badge-overdue { color: #dc2626; font-weight: bold; }
    .badge-settled { color: #2563eb; font-weight: bold; }
    .badge-auctioned { color: #d97706; font-weight: bold; }
    .text-right { text-align: right; }
    .footer { margin-top: 16px; font-size: 8px; color: #999; text-align: center; }
</style>
</head>
<body>
<h2>Girvi Loan Registry</h2>
<p class="sub">Generated: {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp; Total Records: {{ $girvis->count() }}</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Girvi No.</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Branch</th>
            <th class="text-right">Principal</th>
            <th>Rate</th>
            <th class="text-right">Outstanding</th>
            <th>Maturity</th>
            <th>Status</th>
            <th>KYC</th>
        </tr>
    </thead>
    <tbody>
        @foreach($girvis as $i => $g)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $g->girvi_number }}</strong></td>
            <td>{{ $g->girvi_date->format('d/m/Y') }}</td>
            <td>{{ $g->customer->name }}</td>
            <td>{{ $g->customer->phone }}</td>
            <td>{{ $g->branch->name }}</td>
            <td class="text-right">{{ number_format($g->loan_amount, 0) }}</td>
            <td>{{ $g->interest_rate }}%</td>
            <td class="text-right"><strong>{{ number_format($g->outstanding_amount, 0) }}</strong></td>
            <td>{{ $g->maturity_date?->format('d/m/Y') ?? 'N/A' }}</td>
            <td class="badge-{{ $g->status }}">{{ strtoupper($g->status) }}</td>
            <td>{{ $g->kyc_verified ? '✓' : '✗' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Total Outstanding: Rs. {{ number_format($girvis->sum('outstanding_amount'), 2) }} &nbsp;|&nbsp;
    Total Principal: Rs. {{ number_format($girvis->sum('loan_amount'), 2) }}
</div>
</body>
</html>
