<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Sheet - As of {{ $asOfDate->format('d M Y') }}</title>
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
            padding: 8px 15px;
            font-weight: 800;
            border-left: 5px solid #1a1a1a;
            margin-top: 25px;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .subsection-title {
            font-weight: 700;
            color: #d4af37;
            padding: 5px 15px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .line-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 30px;
            border-bottom: 1px solid #f8f9fa;
            font-size: 0.9rem;
        }
        
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            font-weight: 800;
            background-color: #fcfcfc;
            border-top: 1px solid #eee;
        }
        
        .grand-total-box {
            padding: 20px 15px;
            font-weight: 800;
            background-color: #1a1a1a !important;
            color: white !important;
            margin-top: 20px;
            border-radius: 8px;
            -webkit-print-color-adjust: exact;
        }
        
        .footer-sign {
            margin-top: 40px;
            border-top: 1px solid #1a1a1a;
            padding-top: 10px;
            display: inline-block;
            min-width: 180px;
        }
        
        .balanced-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
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
                <div class="text-muted small">Statement of Financial Position</div>
            </div>
            <div class="text-end">
                <h1 class="fw-800 mb-0">BALANCE SHEET</h1>
                <div class="fw-700 text-uppercase">As of {{ $asOfDate->format('d F Y') }}</div>
            </div>
        </div>

        <div class="row">
            <!-- ASSETS -->
            <div class="col-6 pe-4 border-end">
                <div class="section-title">I. ASSETS</div>
                
                <div class="subsection-title mt-2">Current Assets</div>
                @foreach($currentAssets as $asset)
                    <div class="line-item">
                        <span>{{ $asset['account_name'] }}</span>
                        <span>Rs. {{ number_format($asset['amount'], 2) }}</span>
                    </div>
                @endforeach
                <div class="total-item">
                    <span>Total Current Assets</span>
                    <span>Rs. {{ number_format($totalCurrentAssets, 2) }}</span>
                </div>

                @if(count($fixedAssets) > 0)
                    <div class="subsection-title mt-3">Fixed Assets</div>
                    @foreach($fixedAssets as $asset)
                        <div class="line-item">
                            <span>{{ $asset['account_name'] }}</span>
                            <span>Rs. {{ number_format($asset['amount'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="total-item">
                        <span>Total Fixed Assets</span>
                        <span>Rs. {{ number_format($totalFixedAssets, 2) }}</span>
                    </div>
                @endif

                @if(count($otherAssets) > 0)
                    <div class="subsection-title mt-3">Other Assets</div>
                    @foreach($otherAssets as $asset)
                        <div class="line-item">
                            <span>{{ $asset['account_name'] }}</span>
                            <span>Rs. {{ number_format($asset['amount'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="total-item">
                        <span>Total Other Assets</span>
                        <span>Rs. {{ number_format($totalOtherAssets, 2) }}</span>
                    </div>
                @endif

                <div class="grand-total-box">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-5">TOTAL ASSETS</span>
                        <span class="fs-5 text-gold">Rs. {{ number_format($totalAssets, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- LIABILITIES & EQUITY -->
            <div class="col-6 ps-4">
                <div class="section-title">II. LIABILITIES & EQUITY</div>
                
                <div class="subsection-title mt-2">Current Liabilities</div>
                @foreach($currentLiabilities as $liability)
                    <div class="line-item">
                        <span>{{ $liability['account_name'] }}</span>
                        <span>Rs. {{ number_format($liability['amount'], 2) }}</span>
                    </div>
                @endforeach
                <div class="total-item">
                    <span>Total Current Liabilities</span>
                    <span>Rs. {{ number_format($totalCurrentLiabilities, 2) }}</span>
                </div>

                @if(count($longTermLiabilities) > 0)
                    <div class="subsection-title mt-3">Long-Term Liabilities</div>
                    @foreach($longTermLiabilities as $liability)
                        <div class="line-item">
                            <span>{{ $liability['account_name'] }}</span>
                            <span>Rs. {{ number_format($liability['amount'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="total-item">
                        <span>Total Long-Term Liabilities</span>
                        <span>Rs. {{ number_format($totalLongTermLiabilities, 2) }}</span>
                    </div>
                @endif

                <div class="subsection-title mt-4">Equity & Retained Earnings</div>
                @foreach($equity as $item)
                    <div class="line-item">
                        <span>{{ $item['account_name'] }}</span>
                        <span>Rs. {{ number_format($item['amount'], 2) }}</span>
                    </div>
                @endforeach
                <div class="total-item">
                    <span>Total Equity</span>
                    <span>Rs. {{ number_format($totalEquity, 2) }}</span>
                </div>

                <div class="grand-total-box bg-secondary">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-5">TOTAL LIAB. & EQUITY</span>
                        <span class="fs-5">Rs. {{ number_format($totalLiabilitiesEquity, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            @if(abs($totalAssets - $totalLiabilitiesEquity) < 0.01)
                <div class="balanced-badge bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>Statement Perfectly Balanced
                </div>
            @else
                <div class="balanced-badge bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>Discrepancy: Rs. {{ number_format(abs($totalAssets - $totalLiabilitiesEquity), 2) }}
                </div>
            @endif
        </div>

        <div class="mt-5">
            <div class="row">
                <div class="col-6">
                    <div class="footer-sign fw-700 text-center">
                        Finance Director
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="footer-sign fw-700 text-center">
                        External Auditor
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 text-muted small">
                Certified as a true and fair view of the company's financial state as of {{ $asOfDate->format('d/m/Y') }}.
            </div>
        </div>
    </div>
</body>
</html>
