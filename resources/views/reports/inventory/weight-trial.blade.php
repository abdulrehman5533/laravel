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
                    <span class="text-muted small">Technical Weight Trial</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print technical breakdown
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
                        Technical Weights & Purity Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">WEIGHT TRIAL REPORT</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            FINE WEIGHT CALCULATION
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- DETAILED BREAKDOWN -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Asset Description</th>
                        <th class="text-uppercase tracking-wider">Purity</th>
                        <th class="text-center text-uppercase tracking-wider">Stock</th>
                        <th class="text-end text-uppercase tracking-wider">Gross (g)</th>
                        <th class="text-end text-uppercase tracking-wider">Net (g)</th>
                        <th class="text-end text-uppercase tracking-wider">Wastage</th>
                        <th class="text-end text-uppercase tracking-wider bg-light">Fine (g)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalGross = 0;
                        $totalNet = 0;
                        $totalFine = 0;
                        $totalStock = 0;
                    @endphp
                    @forelse($products as $product)
                        @php
                            $totalGross += $product->gross_weight;
                            $totalNet += $product->net_weight;
                            $totalFine += $product->fine_weight;
                            $totalStock += $product->current_stock;
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $product->name }}</span><br>
                                <small class="text-muted text-uppercase tracking-tighter">{{ $product->sku }} | {{ $product->category->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="smaller fw-700 text-uppercase text-muted border-bottom border-gold">
                                    {{ $product->purity->name ?? 'N/A' }}
                                </span><br>
                                <small class="smaller text-muted">({{ $product->purity->percentage ?? 100 }}%)</small>
                            </td>
                            <td class="text-center fw-600">{{ number_format($product->current_stock, 2) }}</td>
                            <td class="text-end text-muted">{{ number_format($product->gross_weight, 3) }}</td>
                            <td class="text-end fw-600">{{ number_format($product->net_weight, 3) }}</td>
                            <td class="text-end text-muted small">{{ $product->wastage_percentage ?? 0 }}%</td>
                            <td class="text-end fw-800 text-dark bg-light">{{ number_format($product->fine_weight, 3) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted italic">No technical weight data available.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($products->count() > 0)
                <tfoot>
                    <tr class="highlight-row bg-premium-dark text-white">
                        <td colspan="2" class="fw-800 text-uppercase py-3">Consolidated Totals</td>
                        <td class="text-center fw-800 py-3">{{ number_format($totalStock, 2) }}</td>
                        <td class="text-end py-3 text-white-50 small">{{ number_format($totalGross, 3) }}</td>
                        <td class="text-end fw-800 py-3 border-top-double">{{ number_format($totalNet, 3) }}</td>
                        <td class="text-end py-3">-</td>
                        <td class="text-end fw-800 py-3 border-top-double text-gold fs-5">{{ number_format($totalFine, 3) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>

            <!-- Technical Disclosure -->
            <div class="row mt-5">
                <div class="col-md-12">
                    <div class="audit-note p-3 border rounded bg-light">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Technical Disclaimer</h6>
                        <p class="small text-muted mb-0">
                            The "Fine Weight" is a technical calculation representing the pure metal content based on the net weight 
                            and purity percentage. This calculation is critical for inventory reconciliation and metal market valuation. 
                            Wastage allowances are applied according to manufacturing standards. All weights are recorded using 
                            calibrated high-precision laboratory scales.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Lab Technician</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Stock Controller</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Authorized Signatory</p>
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
        border-top: 2px solid #fff;
    }

    .bg-premium-dark { background: #1a1a1a; }
    .text-gold { color: #d4af37 !important; }
    .border-gold { border-color: #d4af37 !important; }

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
