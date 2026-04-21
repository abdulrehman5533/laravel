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
                    <h4 class="mb-0 fw-bold">Customer Intelligence</h4>
                    <span class="text-muted small">Profitability Analysis by Client</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('reports.sales.customer-profit-loss.print') }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm">
                <i class="fas fa-print me-1"></i> Print Proper Report
            </a>
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
                        Business Intelligence & Analytics Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">CUSTOMER PROFITABILITY</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            LIFETIME VALUATION REPORT
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- OVERALL METRICS -->
            <div class="row mb-5 g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Cumulative Revenue</h6>
                        <h4 class="fw-800 mb-0 text-dark">Rs. {{ number_format($totalSales, 2) }}</h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">{{ count($report) }} Active Clients</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-16 bg-premium-dark text-white text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-gold mb-2 tracking-wider">Total Gross Profit</h6>
                        <h4 class="fw-800 mb-0 text-gold">Rs. {{ number_format($totalProfit, 2) }}</h4>
                        <p class="smaller text-white-50 fw-bold mb-0 mt-1">Net Margin: {{ round($overallMargin, 2) }}%</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Avg. Profit / Client</h6>
                        <h4 class="fw-800 mb-0 text-dark">Rs. {{ count($report) > 0 ? number_format($totalProfit / count($report), 2) : 0 }}</h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">Client Value Index</p>
                    </div>
                </div>
            </div>

            <!-- PROFITABILITY TABLE -->
            <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Client Profitability Matrix</h6>
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Client Details</th>
                        <th class="text-end text-uppercase tracking-wider">Total Revenue</th>
                        <th class="text-end text-uppercase tracking-wider">Cost (COGS)</th>
                        <th class="text-end text-uppercase tracking-wider">Gross Profit</th>
                        <th class="text-end text-uppercase tracking-wider">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $item)
                        <tr>
                            <td>
                                <div class="fw-800 text-dark">{{ $item['customer']->name }}</div>
                                <div class="smaller text-muted">{{ $item['customer']->phone }}</div>
                            </td>
                            <td class="text-end fw-600">Rs. {{ number_format($item['sales'], 2) }}</td>
                            <td class="text-end text-muted small">Rs. {{ number_format($item['cogs'], 2) }}</td>
                            <td class="text-end fw-800 text-success">Rs. {{ number_format($item['profit'], 2) }}</td>
                            <td class="text-end">
                                <span class="badge {{ $item['margin'] > 20 ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }} px-2 py-1 rounded-pill smaller fw-800">
                                    {{ round($item['margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">No customer sales data available for analysis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- ANALYSIS NOTE -->
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="audit-note p-4 border rounded-24 bg-light shadow-sm">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Profitability Analysis Disclosure</h6>
                        <p class="small text-muted mb-0">
                            This report evaluates client value based on direct gross profit. 
                            Calculations are derived from total sales revenue minus the Cost of Goods Sold (COGS) recorded at the time of transaction. 
                            High-margin clients (green) represent premium value segments, while lower-margin clients may indicate high-volume or discounted sales patterns.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Business Analyst</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Marketing Head</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Chief Financial Officer</p>
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
