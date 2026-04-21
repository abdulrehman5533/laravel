<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit & Loss - {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}</title>
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
        
        .section-title {
            background-color: #f8f9fa;
            padding: 10px 15px;
            font-weight: 800;
            border-left: 5px solid #d4af37;
            margin-top: 30px;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .line-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            font-weight: 800;
            background-color: #fcfcfc;
        }
        
        .grand-total {
            display: flex;
            justify-content: space-between;
            padding: 20px 15px;
            font-weight: 800;
            background-color: #1a1a1a !important;
            color: white !important;
            margin-top: 30px;
            border-radius: 8px;
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
            .grand-total {
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
                <h1 class="fw-800 mb-0">PROFIT & LOSS</h1>
                <div class="fw-700 text-uppercase">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</div>
            </div>
        </div>

        <!-- REVENUE -->
        <div class="section-title">Operating Revenue</div>
        @foreach($revenues as $revenue)
            <div class="line-item">
                <span>{{ $revenue['account_name'] }}</span>
                <span class="fw-700">Rs. {{ number_format($revenue['amount'], 2) }}</span>
            </div>
        @endforeach
        <div class="total-item">
            <span>TOTAL OPERATING REVENUE</span>
            <span class="text-success">Rs. {{ number_format($totalRevenue, 2) }}</span>
        </div>

        <!-- COGS -->
        <div class="section-title">Cost of Goods Sold</div>
        @foreach($costOfSales as $cost)
            <div class="line-item">
                <span>{{ $cost['account_name'] }}</span>
                <span class="fw-700">Rs. {{ number_format($cost['amount'], 2) }}</span>
            </div>
        @endforeach
        <div class="total-item">
            <span>TOTAL COST OF GOODS SOLD</span>
            <span class="text-danger">Rs. {{ number_format($totalCogs, 2) }}</span>
        </div>

        <div class="total-item mt-3 border-top border-bottom py-3">
            <span class="fs-5">GROSS PROFIT</span>
            <span class="fs-5 text-primary">Rs. {{ number_format($grossProfit, 2) }}</span>
        </div>

        <!-- EXPENSES -->
        <div class="section-title">Operating Expenditure</div>
        @foreach($operatingExpenses as $expense)
            <div class="line-item">
                <span>{{ $expense['account_name'] }}</span>
                <span class="fw-700">Rs. {{ number_format($expense['amount'], 2) }}</span>
            </div>
        @endforeach
        <div class="total-item">
            <span>TOTAL OPERATING EXPENDITURE</span>
            <span class="text-danger">Rs. {{ number_format($totalOperatingExpenses, 2) }}</span>
        </div>

        <!-- OTHER -->
        @if(count($otherIncomeExpense) > 0)
            <div class="section-title">Non-Operating Items</div>
            @foreach($otherIncomeExpense as $other)
                <div class="line-item">
                    <span>{{ $other['account_name'] }}</span>
                    <span class="fw-700">Rs. {{ number_format($other['amount'], 2) }}</span>
                </div>
            @endforeach
        @endif

        <div class="grand-total">
            <span class="fs-4">NET PERFORMANCE / PROFIT</span>
            <span class="fs-4 {{ $netProfit >= 0 ? 'text-gold' : 'text-danger' }}">Rs. {{ number_format($netProfit, 2) }}</span>
        </div>

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
        </div>
    </div>
</body>
</html>
