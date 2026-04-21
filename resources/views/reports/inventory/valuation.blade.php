@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.inventory.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Inventory Intelligence</h4>
                    <span class="text-muted small">Valuation Report</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
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
                        Inventory Control Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">STOCK VALUATION</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            FISCAL ASSET REVIEW
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- VALUATION SUMMARY -->
            <div class="row mb-5 g-0 border rounded overflow-hidden">
                <div class="col-md-6 border-end p-4 bg-light">
                    <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Total Acquisition Cost</h6>
                    <h2 class="fw-800 mb-0">Rs. {{ number_format($totalValue, 2) }}</h2>
                    <p class="smaller text-muted mt-2 mb-0">Cumulative value based on purchase price</p>
                </div>
                <div class="col-md-6 p-4 bg-premium-dark text-white">
                    <h6 class="text-uppercase smaller fw-800 text-gold mb-2 tracking-wider">Estimated Retail Value</h6>
                    <h2 class="fw-800 mb-0 text-gold">Rs. {{ number_format($totalRetailValue, 2) }}</h2>
                    <p class="smaller text-white-50 mt-2 mb-0">Potential revenue at current tag prices</p>
                </div>
            </div>

            <!-- DETAILED BREAKDOWN -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Product Description</th>
                        <th class="text-center text-uppercase tracking-wider">Stock</th>
                        <th class="text-end text-uppercase tracking-wider">Unit Cost</th>
                        <th class="text-end text-uppercase tracking-wider">Cost Value</th>
                        <th class="text-end text-uppercase tracking-wider">Retail Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $product->name }}</span><br>
                                <small class="text-muted text-uppercase">{{ $product->sku }} | {{ $product->category->name ?? 'Uncategorized' }}</small>
                            </td>
                            <td class="text-center fw-600">{{ $product->current_stock }}</td>
                            <td class="text-end text-muted">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-end fw-600">Rs. {{ number_format($product->current_stock * $product->cost_price, 2) }}</td>
                            <td class="text-end text-dark">Rs. {{ number_format($product->current_stock * $product->selling_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">No inventory records available for valuation.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($products->count() > 0)
                <tfoot>
                    <tr class="highlight-row">
                        <td colspan="3" class="fw-800 text-uppercase py-3">Grand Total</td>
                        <td class="text-end fw-800 py-3 border-top-double">Rs. {{ number_format($totalValue, 2) }}</td>
                        <td class="text-end fw-800 py-3 border-top-double text-gold">Rs. {{ number_format($totalRetailValue, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>

            <!-- Audit & Certification -->
            <div class="row mt-5">
                <div class="col-md-7">
                    <div class="audit-note p-3 border-start border-4 border-dark bg-light">
                        <h6 class="fw-bold text-uppercase small mb-2">Inventory Certification</h6>
                        <p class="small text-muted mb-0">
                            This valuation report is generated based on the current system stock levels and recorded acquisition costs. 
                            The retail value is calculated based on active price tags and is subject to market fluctuations 
                            and discretionary discounting. This document serves as an internal asset assessment for the treasury department.
                        </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="p-3 border rounded text-center">
                        <span class="text-muted smaller text-uppercase fw-700 d-block mb-1">Stock Health Index</span>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                        </div>
                        <span class="fw-800 text-dark">Optimum</span>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Inventory Manager</p>
                        <p class="smaller text-muted">Stock Audit Division</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Finance Officer</p>
                        <p class="smaller text-muted">Treasury Department</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Authorized Approval</p>
                        <p class="smaller text-muted">Operations Director</p>
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
        max-width: 1100px;
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

    .border-top-double {
        border-top: 3px double #1a1a1a;
    }

    .bg-premium-dark { background: #1a1a1a; }
    .text-gold { color: #d4af37 !important; }

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

    .highlight-row td {
        background-color: #fcfcfc;
    }

    .sig-line {
        width: 80%;
        height: 1px;
        background: #1a1a1a;
        margin: 40px auto 0;
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
    }
</style>
@endsection
