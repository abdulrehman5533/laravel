@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.financial.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Financial Reporting</h4>
                    <span class="text-muted small">Trading Profit & Loss Account</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="{{ route('reports.financial.trading-p-l') }}" method="GET" class="d-inline-flex gap-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                <a href="{{ route('reports.financial.trading-p-l.print', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                    <i class="fas fa-print me-1"></i> Print Proper Report
                </a>
            </form>
        </div>
    </div>

    <!-- Professional Report Container -->
    <div class="report-paper shadow-sm mx-auto bg-white" id="printableReport">
        <!-- Corporate Letterhead -->
        <div class="report-header border-bottom-double mb-4">
            <div class="row align-items-center">
                <div class="col-7">
                    <h2 class="company-name mb-1">{{ config('app.name', 'MAGIA LUPOS Jewellery Management System (JMS)') }}</h2>
                    <p class="company-details mb-0 text-uppercase tracking-tighter">
                        Premium Jewelry Solutions & ERP<br>
                        Financial Audit & Trading Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">TRADING ACCOUNT</h4>
                        <div class="report-period mt-1">
                            {{ $startDate->format('M d, Y') }} — {{ $endDate->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <div class="row g-0 border">
                <!-- LEFT SIDE (DEBITS) -->
                <div class="col-md-6 border-end">
                    <table class="table table-report mb-0 h-100">
                        <thead>
                            <tr>
                                <th class="text-uppercase tracking-wider ps-3">Particulars (Debit)</th>
                                <th class="text-end text-uppercase tracking-wider pe-3" style="width: 150px;">Amount (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3">To Opening Stock</td>
                                <td class="text-end pe-3 text-muted">{{ number_format($openingStock, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3">To Purchases</td>
                                <td class="text-end pe-3 text-muted">{{ number_format($purchases, 2) }}</td>
                            </tr>
                            @if($grossProfit > 0)
                            <tr class="table-active-row">
                                <td class="ps-3 fw-bold text-success">To Gross Profit c/o</td>
                                <td class="text-end pe-3 fw-bold text-success">{{ number_format($grossProfit, 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td class="ps-3">&nbsp;</td>
                                <td class="text-end pe-3">&nbsp;</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-top-double">
                            <tr>
                                <th class="ps-3 py-3 fw-800">TOTAL DEBIT</th>
                                <th class="text-end pe-3 py-3 fw-800">Rs. {{ number_format(max($sales + $closingStock, $openingStock + $purchases), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- RIGHT SIDE (CREDITS) -->
                <div class="col-md-6">
                    <table class="table table-report mb-0 h-100">
                        <thead>
                            <tr>
                                <th class="text-uppercase tracking-wider ps-3">Particulars (Credit)</th>
                                <th class="text-end text-uppercase tracking-wider pe-3" style="width: 150px;">Amount (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3">By Sales</td>
                                <td class="text-end pe-3 text-muted">{{ number_format($sales, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3">By Closing Stock</td>
                                <td class="text-end pe-3 text-muted">{{ number_format($closingStock, 2) }}</td>
                            </tr>
                            @if($grossProfit < 0)
                            <tr class="table-active-row">
                                <td class="ps-3 fw-bold text-danger">By Gross Loss c/o</td>
                                <td class="text-end pe-3 fw-bold text-danger">{{ number_format(abs($grossProfit), 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td class="ps-3">&nbsp;</td>
                                <td class="text-end pe-3">&nbsp;</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-top-double">
                            <tr>
                                <th class="ps-3 py-3 fw-800">TOTAL CREDIT</th>
                                <th class="text-end pe-3 py-3 fw-800">Rs. {{ number_format(max($sales + $closingStock, $openingStock + $purchases), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- SUMMARY PERFORMANCE -->
            <div class="row mt-5">
                <div class="col-md-7">
                    <div class="audit-note p-3 border rounded h-100">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Accounting Definition</h6>
                        <p class="small text-muted mb-0">
                            The Trading Account shows the result of buying and selling of goods. 
                            It is prepared to determine the Gross Profit or Gross Loss of the business 
                            activities. This excludes indirect expenses which are handled in the 
                            Income Statement (P&L).
                        </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="final-summary bg-light p-4 rounded border h-100 d-flex flex-column justify-content-center text-center">
                        <span class="text-muted text-uppercase smaller fw-bold tracking-wider mb-2">Resultant Performance</span>
                        <h2 class="fw-bold mb-0 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $grossProfit >= 0 ? 'GROSS PROFIT' : 'GROSS LOSS' }}
                        </h2>
                        <div class="fs-3 fw-800 mt-2">
                            Rs. {{ number_format(abs($grossProfit), 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Bookkeeper</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Head Accountant</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Authorized Signatory</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Professional Typography & Layout */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f4f7f6;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .report-paper {
        width: 100%;
        max-width: 1000px;
        min-height: 29.7cm; /* A4 Ratio */
        padding: 60px;
        color: #2c3e50;
    }

    .company-name {
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #1a1a1a;
        font-size: 1.75rem;
    }

    .company-details {
        font-size: 0.75rem;
        line-height: 1.6;
        color: #6c757d;
        font-weight: 500;
    }

    .report-title-box {
        border-left: 4px solid #1a1a1a;
        padding-left: 20px;
        text-align: right;
    }

    .report-type-title {
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #1a1a1a;
    }

    .report-period {
        font-size: 0.85rem;
        font-weight: 600;
        color: #d4af37;
    }

    .border-bottom-double {
        border-bottom: 3px double #dee2e6;
        padding-bottom: 30px;
    }

    .border-top-double {
        border-top: 3px double #1a1a1a;
    }

    .border-bottom-single {
        border-bottom: 1px solid #1a1a1a;
    }

    /* Table Styling */
    .table-report {
        margin-bottom: 0;
    }

    .table-report thead th {
        border-top: none;
        border-bottom: 2px solid #1a1a1a;
        font-size: 0.7rem;
        font-weight: 700;
        color: #1a1a1a;
        padding: 12px 8px;
        background-color: #f8f9fa;
    }

    .table-report tbody td {
        padding: 12px 8px;
        font-size: 0.9rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .table-active-row td {
        background-color: #fafafa;
    }

    .audit-note {
        background-color: #f8f9fa;
        border-color: #eee !important;
    }

    .sig-line {
        width: 80%;
        height: 1px;
        background: #333;
        margin: 50px auto 0;
    }

    .fw-800 { font-weight: 800; }
    .smaller { font-size: 0.7rem; }
    .tracking-tighter { letter-spacing: -0.01em; }

    /* Print Specifics */
    @media print {
        body { background-color: white !important; }
        .no-print-padding { padding: 0 !important; }
        .report-paper {
            width: 100%;
            max-width: none;
            box-shadow: none !important;
            padding: 0;
            margin: 0;
        }
        .container-fluid { width: 100%; padding: 0; margin: 0; }
        .d-print-none { display: none !important; }
        
        @page {
            size: A4;
            margin: 1.5cm;
        }
    }
</style>
@endsection
