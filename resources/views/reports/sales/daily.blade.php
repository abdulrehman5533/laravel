@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.sales.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Sales Intelligence</h4>
                    <span class="text-muted small">Daily Revenue Audit</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-inline-flex gap-2">
                <input type="date" name="date" class="form-control form-control-sm border-0 shadow-sm px-3 rounded-pill" value="{{ $date }}">
                <button type="submit" class="btn btn-dark btn-sm px-3 rounded-pill shadow-sm">Filter</button>
                <a href="{{ route('reports.sales.daily.print', ['date' => $date]) }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm">
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
                        Retail Operations & Audit Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">DAILY SALES REPORT</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- REVENUE SUMMARY CARDS -->
            <div class="row mb-5 g-3">
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Total Revenue</h6>
                        <h4 class="fw-800 mb-0 text-dark">Rs. {{ number_format($totalSales, 2) }}</h4>
                        <p class="smaller text-success fw-bold mb-0 mt-1">{{ $count }} Transactions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Net Collection</h6>
                        <h4 class="fw-800 mb-0 text-primary">Rs. {{ number_format($totalPaid, 2) }}</h4>
                        <p class="smaller text-warning fw-bold mb-0 mt-1">Rs. {{ number_format($totalOutstanding, 2) }} Credit</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-premium-dark text-white text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-gold mb-2 tracking-wider">Gross Profit</h6>
                        <h4 class="fw-800 mb-0 text-gold">Rs. {{ number_format($grossProfit, 2) }}</h4>
                        <p class="smaller text-white-50 fw-bold mb-0 mt-1">{{ $grossMargin }}% Margin</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Items Sold</h6>
                        <h4 class="fw-800 mb-0 text-dark">{{ $totalItems }} Units</h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">Avg. {{ number_format($averageOrderValue, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- DETAILED TRANSACTIONS -->
            <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Transaction Audit Trail</h6>
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Invoice / Time</th>
                        <th class="text-uppercase tracking-wider">Customer / Client</th>
                        <th class="text-center text-uppercase tracking-wider">Items</th>
                        <th class="text-end text-uppercase tracking-wider">Bill Amount</th>
                        <th class="text-end text-uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="smaller fw-600">
                                <span class="text-dark">#{{ $sale->id }}</span><br>
                                <span class="text-muted">{{ $sale->created_at->format('h:i A') }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</span>
                                @if($sale->customer?->phone)
                                    <br><small class="text-muted">{{ $sale->customer->phone }}</small>
                                @endif
                            </td>
                            <td class="text-center fw-600">{{ $sale->items->count() }}</td>
                            <td class="text-end fw-800">
                                Rs. {{ number_format($sale->total, 2) }}
                            </td>
                            <td class="text-end">
                                @if($sale->outstanding_balance > 0)
                                    <span class="badge bg-soft-warning text-warning px-2 py-1 rounded-pill smaller fw-800">PARTIAL</span>
                                @else
                                    <span class="badge bg-soft-success text-success px-2 py-1 rounded-pill smaller fw-800">PAID</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">No sales transactions recorded for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- REVENUE COMPONENTS -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="p-4 border rounded-24 bg-light">
                        <h6 class="fw-800 text-uppercase smaller text-muted mb-4 tracking-wider text-center">Revenue Composition</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Cost of Goods (COGS)</small>
                                <span class="fw-700 text-dark">Rs. {{ number_format($costOfGoodsSold, 2) }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Total Discount</small>
                                <span class="fw-700 text-danger">Rs. {{ number_format($totalDiscount, 2) }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Making Charges</small>
                                <span class="fw-700 text-dark">Rs. {{ number_format($totalMakingCharges, 2) }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Tax Collected</small>
                                <span class="fw-700 text-dark">Rs. {{ number_format($totalTax, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="audit-note p-4 border rounded-24 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Auditor's Daily Note</h6>
                        <p class="small text-muted mb-0">
                            This report represents the finalized sales figures for the specified calendar day. 
                            All revenue figures are reconciled with Point of Sale (POS) records and payment gateway confirmations. 
                            Gross profit calculations account for material costs, labor (making charges), and tax obligations.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Cashier</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Store Manager</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Accounting Dept</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8f9fa;
        font-family: 'Inter', sans-serif;
    }

    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    
    .report-paper {
        width: 100%;
        max-width: 1000px;
        min-height: 29.7cm;
        padding: 50px 70px;
        color: #1a1a1a;
    }

    .company-name {
        font-weight: 800;
        letter-spacing: -0.02em;
        font-size: 1.5rem;
    }

    .company-details {
        font-size: 0.7rem;
        line-height: 1.5;
        color: #6c757d;
        font-weight: 500;
    }

    .report-title-box {
        border-left: 3px solid #d4af37;
        padding-left: 20px;
    }

    .report-type-title {
        font-weight: 800;
        letter-spacing: 0.1em;
        font-size: 1.25rem;
    }

    .border-bottom-double {
        border-bottom: 3px double #dee2e6;
        padding-bottom: 25px;
    }

    .table-report thead th {
        border-top: none;
        border-bottom: 2px solid #1a1a1a;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 15px 10px;
    }

    .table-report tbody td {
        padding: 15px 10px;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .bg-premium-dark { background: #1a1a1a; }
    .text-gold { color: #d4af37 !important; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .rounded-16 { border-radius: 16px; }
    .rounded-24 { border-radius: 24px; }

    .sig-line {
        height: 1px;
        background: #1a1a1a;
        margin-top: 40px;
    }

    .smaller { font-size: 0.65rem; }
    .tracking-wider { letter-spacing: 0.08em; }
    .tracking-tighter { letter-spacing: -0.01em; }

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
        .d-print-none { display: none !important; }
        @page { size: A4; margin: 1.5cm; }
        .bg-premium-dark { background: #1a1a1a !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection
