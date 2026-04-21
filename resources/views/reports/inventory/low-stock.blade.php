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
                    <span class="text-muted small">Low Stock Analysis</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Alert List
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
                        Supply Chain & Procurement Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0 text-danger">STOCK REPLENISHMENT</h4>
                        <div class="report-period mt-1 text-muted fw-bold small">
                            CRITICAL LEVEL ALERTS
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- ALERT SUMMARY -->
            <div class="alert alert-custom d-flex align-items-center p-4 mb-5 border-0 rounded-16">
                <div class="alert-icon bg-danger text-white rounded-circle me-4 shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h5 class="fw-800 mb-1">Critical Stock Warning</h5>
                    <p class="mb-0 smaller fw-600 text-muted">The following {{ $products->count() }} items have fallen below their designated safety thresholds and require immediate procurement action.</p>
                </div>
            </div>

            <!-- DETAILED BREAKDOWN -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Product Description</th>
                        <th class="text-center text-uppercase tracking-wider">Current Stock</th>
                        <th class="text-center text-uppercase tracking-wider">Threshold</th>
                        <th class="text-center text-uppercase tracking-wider">Deficit</th>
                        <th class="text-end text-uppercase tracking-wider">Priority Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $product->name }}</span><br>
                                <small class="text-muted text-uppercase">{{ $product->sku }} | {{ $product->category->name ?? 'Uncategorized' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="fw-800 text-danger fs-5">{{ $product->current_stock }}</span>
                            </td>
                            <td class="text-center text-muted fw-600">
                                {{ $product->reorder_level }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-soft-danger text-danger px-3 py-2 rounded-pill">
                                    -{{ $product->reorder_level - $product->current_stock }} Units
                                </span>
                            </td>
                            <td class="text-end">
                                @if($product->current_stock == 0)
                                    <span class="text-danger fw-800 text-uppercase smaller tracking-wider">
                                        <i class="fas fa-circle me-1 animate-pulse"></i> Out of Stock
                                    </span>
                                @else
                                    <span class="text-warning fw-800 text-uppercase smaller tracking-wider">
                                        <i class="fas fa-arrow-down me-1"></i> Critical
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
                                    <h5 class="fw-800 text-dark">Optimal Inventory Levels</h5>
                                    <p class="text-muted small">No items currently require replenishment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Procurement Advisory -->
            <div class="row mt-5">
                <div class="col-md-8">
                    <div class="audit-note p-3 border rounded bg-light">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Procurement Directive</h6>
                        <p class="small text-muted mb-0">
                            Purchasing department is advised to initiate reorder requests for the items listed above. 
                            Prioritize items with a deficit greater than 50% of the reorder level. 
                            Ensure supplier lead times are considered to prevent complete stock-outs.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="p-3 border rounded">
                        <span class="text-muted smaller text-uppercase fw-700 d-block mb-1">Last Audit Date</span>
                        <span class="fw-800 text-dark">{{ now()->subDays(1)->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-6">
                        <div class="sig-line mb-2 w-75"></div>
                        <p class="small fw-bold text-uppercase mb-0">Warehouse Supervisor</p>
                        <p class="smaller text-muted">Logistics Department</p>
                    </div>
                    <div class="col-6 text-end">
                        <div class="sig-line mb-2 w-75 ms-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Purchase Manager</p>
                        <p class="smaller text-muted">Procurement Division</p>
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
        border-right: 3px solid #dc3545;
        padding-right: 20px;
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

    .alert-custom {
        background-color: #fff5f5;
        border: 1px solid #ffe3e3 !important;
    }

    .alert-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
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

    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .rounded-16 { border-radius: 16px; }

    .sig-line {
        height: 1px;
        background: #1a1a1a;
        margin-top: 40px;
    }

    .smaller { font-size: 0.65rem; }
    .tracking-wider { letter-spacing: 0.08em; }
    .tracking-tighter { letter-spacing: -0.01em; }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }

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
