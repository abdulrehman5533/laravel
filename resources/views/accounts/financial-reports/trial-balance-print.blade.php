<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Balance - {{ $asOfDate->format('d M Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: white;
            color: #1a1a1a;
            padding: 40px;
        }
        .fw-800 { font-weight: 800; }
        .fw-700 { font-weight: 700; }
        .fw-600 { font-weight: 600; }
        
        .report-header {
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-logo {
            font-size: 24px;
            color: #d4af37;
        }
        .text-gold { color: #d4af37; }
        .bg-premium-dark { background: #1a1a1a !important; color: white !important; }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .account-code {
            font-family: 'Monaco', 'Courier New', monospace;
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        .totals-row {
            background-color: #1a1a1a !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
        }
        
        .footer-sign {
            margin-top: 50px;
            border-top: 1px solid #1a1a1a;
            padding-top: 10px;
            display: inline-block;
            min-width: 200px;
        }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .totals-row {
                background-color: #1a1a1a !important;
                color: white !important;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="no-print mb-4 text-end">
            <button onclick="window.print()" class="btn btn-dark"><i class="fas fa-print me-2"></i>Print</button>
            <button onclick="window.close()" class="btn btn-outline-secondary ms-2">Close</button>
        </div>

        <div class="report-header d-flex justify-content-between align-items-end">
            <div>
                <div class="company-logo fw-800 mb-1">
                    <i class="fas fa-gem me-2"></i>JEWELLERY PRO ERP
                </div>
                <div class="text-muted small">Professional Jewelry Management System</div>
            </div>
            <div class="text-end">
                <h1 class="fw-800 mb-0">TRIAL BALANCE</h1>
                <div class="fw-700 text-uppercase">As of {{ $asOfDate->format('d F Y') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="p-3 border rounded">
                    <div class="small text-muted text-uppercase fw-700">Financial Summary</div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <div class="small">Total Debits</div>
                            <div class="fw-800 fs-5">Rs. {{ number_format($totalDebits, 2) }}</div>
                        </div>
                        <div class="col-6 text-end border-start">
                            <div class="small">Total Credits</div>
                            <div class="fw-800 fs-5">Rs. {{ number_format($totalCredits, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="small text-muted">Generation Date</div>
                <div class="fw-600">{{ now()->format('d M Y, h:i A') }}</div>
                <div class="small text-muted mt-2">Status</div>
                <div class="fw-800 {{ abs($totalDebits - $totalCredits) < 0.01 ? 'text-success' : 'text-danger' }}">
                    {{ abs($totalDebits - $totalCredits) < 0.01 ? 'BALANCED' : 'OUT OF BALANCE' }}
                </div>
            </div>
        </div>

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th style="width: 15%;">Code</th>
                    <th style="width: 45%;">Account Description</th>
                    <th style="width: 20%;" class="text-end">Debit (Rs.)</th>
                    <th style="width: 20%;" class="text-end">Credit (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trialBalance as $item)
                    <tr>
                        <td><span class="account-code">{{ $item['account']->account_code }}</span></td>
                        <td>
                            <div class="fw-700">{{ $item['account']->account_name }}</div>
                            <div class="small text-muted">{{ $item['account']->account_type }}</div>
                        </td>
                        <td class="text-end fw-700">
                            {{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-700">
                            {{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-end fw-800 py-3">GRAND TOTALS</td>
                    <td class="text-end fw-800 py-3">Rs. {{ number_format($totalDebits, 2) }}</td>
                    <td class="text-end fw-800 py-3">Rs. {{ number_format($totalCredits, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 pt-5">
            <div class="row">
                <div class="col-4">
                    <div class="footer-sign fw-700 text-center">
                        Prepared By
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="footer-sign fw-700 text-center">
                        Verified By
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="footer-sign fw-700 text-center">
                        Authorized Signature
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 text-muted small">
                This is a computer-generated document and does not require a physical signature unless specified.
            </div>
        </div>
    </div>
</body>
</html>
