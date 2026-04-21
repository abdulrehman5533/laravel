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
                    <span class="text-muted small">Profit & Loss Statement</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="{{ route('reports.financial.profit-loss') }}" method="GET" class="d-inline-flex gap-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}">
                <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                <a href="{{ route('reports.financial.profit-loss.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
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
                    <h2 class="company-name mb-1">{{ config('app.name') }}</h2>
                    <p class="company-details mb-0 text-uppercase tracking-tighter">
                        Premium Jewelry Solutions & ERP<br>
                        Financial Audit Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">INCOME STATEMENT</h4>
                        <div class="report-period mt-1">
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- REVENUE -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Revenue & Income</th>
                        <th class="text-end text-uppercase tracking-wider">Amount (Rs.)</th>
                        <th class="text-end text-uppercase tracking-wider" style="width: 150px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gross Sales Revenue</td>
                        <td class="text-end text-muted">{{ number_format($revenue + $discount, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Service & Making Charges</td>
                        <td class="text-end text-muted">{{ number_format($makingCharges, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Wastage Recovery</td>
                        <td class="text-end text-muted">{{ number_format($wastage, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr class="text-danger">
                        <td>Less: Sales Discounts & Allowances</td>
                        <td class="text-end">({{ number_format($discount, 2) }})</td>
                        <td></td>
                    </tr>
                    <tr class="table-active-row">
                        <td class="fw-bold">Total Net Revenue</td>
                        <td></td>
                        <td class="text-end fw-bold border-bottom-single">{{ number_format($revenue, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- COGS -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Cost of Goods Sold</th>
                        <th class="text-end" style="width: 150px;"></th>
                        <th class="text-end" style="width: 150px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Inventory Acquisition Cost (Material + Stones)</td>
                        <td class="text-end text-muted">{{ number_format($costOfGoodsSold, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr class="text-danger">
                        <td class="fw-bold text-dark">Total Cost of Sales</td>
                        <td></td>
                        <td class="text-end fw-bold">({{ number_format($costOfGoodsSold, 2) }})</td>
                    </tr>
                    <tr class="highlight-row bg-light">
                        <td class="fw-bold fs-5 py-3">GROSS PROFIT</td>
                        <td></td>
                        <td class="text-end fw-bold fs-5 py-3 border-bottom-double">Rs. {{ number_format($grossProfit, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="pt-1"><small class="text-muted fw-bold">Gross Margin: {{ $grossMargin }}%</small></td>
                    </tr>
                </tbody>
            </table>

            <!-- EXPENSES -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Operating Expenditures</th>
                        <th class="text-end text-uppercase tracking-wider">Breakdown</th>
                        <th class="text-end text-uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td>{{ ucfirst($expense->category) }}</td>
                            <td class="text-end text-muted">{{ number_format($expense->total, 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr class="text-danger">
                        <td class="fw-bold text-dark">Total Operating Expenses</td>
                        <td></td>
                        <td class="text-end fw-bold">({{ number_format($totalExpenses, 2) }})</td>
                    </tr>
                </tbody>
            </table>

            <!-- SUMMARY PERFORMANCE -->
            <div class="row mt-5">
                <div class="col-md-7">
                    <div class="audit-note p-3 border rounded">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Internal Audit Notes</h6>
                        <p class="small text-muted mb-0">
                            This Profit and Loss statement is generated based on real-time transaction data including Point of Sale (POS) records, 
                            automated inventory cost tracking (FIFO/Weighted Average), and direct expense vouchers. 
                            This report represents the operational performance of the business for the specified period.
                        </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="final-summary">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Operating Margin:</span>
                            <span class="fw-bold text-dark">{{ $operatingMargin }}%</span>
                        </div>
                        <div class="d-flex justify-content-between py-3 border-top-double">
                            <span class="fw-bold fs-4">NET PROFIT</span>
                            <span class="fw-bold fs-4 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                Rs. {{ number_format($netProfit, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Prepared By</p>
                        <p class="smaller text-muted">Finance Department</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Reviewed By</p>
                        <p class="smaller text-muted">Internal Auditor</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Approved By</p>
                        <p class="smaller text-muted">Managing Director</p>
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
        margin-bottom: 2rem;
    }

    .table-report thead th {
        border-top: none;
        border-bottom: 2px solid #1a1a1a;
        font-size: 0.7rem;
        font-weight: 700;
        color: #1a1a1a;
        padding: 12px 8px;
    }

    .table-report tbody td {
        padding: 10px 8px;
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

    .smaller {
        font-size: 0.65rem;
    }

    .tracking-tighter {
        letter-spacing: -0.01em;
    }

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
        
        .table-report thead th {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
        
        @page {
            size: A4;
            margin: 1.5cm;
        }
    }
</style>
@endsection
