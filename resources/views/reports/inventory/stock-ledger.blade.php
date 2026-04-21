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
                    <span class="text-muted small">Comprehensive Stock Ledger</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Audit Trail
            </button>
        </div>
    </div>

    <!-- Filter Section (Web Only) -->
    <div class="card border-0 shadow-soft rounded-24 mb-5 d-print-none">
        <div class="card-body p-4">
            <form action="{{ route('reports.inventory.stock-ledger') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label smaller fw-800 text-uppercase text-muted mb-2 tracking-wider">Asset Selection</label>
                    <select name="product_id" class="form-select border-0 bg-light rounded-pill px-4">
                        <option value="">All Inventory Items</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label smaller fw-800 text-uppercase text-muted mb-2 tracking-wider">Start Date</label>
                    <input type="date" name="from_date" class="form-control border-0 bg-light rounded-pill px-4" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label smaller fw-800 text-uppercase text-muted mb-2 tracking-wider">End Date</label>
                    <input type="date" name="to_date" class="form-control border-0 bg-light rounded-pill px-4" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-premium-dark w-100 rounded-pill py-2 fw-700">
                        <i class="fas fa-filter me-2"></i> Refine
                    </button>
                </div>
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
                        Internal Audit & Stock Control<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">STOCK LEDGER</h4>
                        <div class="report-period mt-1 text-muted fw-bold small">
                            @if(request('from_date') || request('to_date'))
                                {{ request('from_date', 'Start') }} — {{ request('to_date', 'Present') }}
                            @else
                                FULL MOVEMENT HISTORY
                            @endif
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
                        <th class="text-uppercase tracking-wider">Timestamp</th>
                        <th class="text-uppercase tracking-wider">Asset Details</th>
                        <th class="text-center text-uppercase tracking-wider">Flow</th>
                        <th class="text-end text-uppercase tracking-wider">Change</th>
                        <th class="text-end text-uppercase tracking-wider">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="text-muted smaller fw-600">
                                {{ $movement->created_at->format('d M Y') }}<br>
                                {{ $movement->created_at->format('H:i:s') }}
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $movement->product->name }}</span><br>
                                <small class="text-muted text-uppercase tracking-tighter">{{ $movement->location->name ?? 'Vault / Main Stock' }}</small>
                                @if($movement->description)
                                    <div class="smaller text-muted mt-1 font-italic">{{ $movement->description }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($movement->type == 'in')
                                    <span class="badge bg-soft-success text-success px-2 py-1 rounded-pill smaller fw-800 text-uppercase">Acquisition</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger px-2 py-1 rounded-pill smaller fw-800 text-uppercase">Disbursement</span>
                                @endif
                            </td>
                            <td class="text-end fw-800 {{ $movement->type == 'in' ? 'text-success' : 'text-danger' }}">
                                {{ $movement->type == 'in' ? '+' : '-' }}{{ $movement->quantity }}
                            </td>
                            <td class="text-end fw-800 text-dark">
                                {{ $movement->new_balance }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-search text-muted fs-1 mb-3"></i>
                                    <h5 class="fw-800 text-muted">No Movements Recorded</h5>
                                    <p class="text-muted small mb-0">No inventory transitions found for the selected criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Audit Trail Notes -->
            <div class="row mt-5">
                <div class="col-md-12">
                    <div class="audit-note p-3 border rounded bg-light">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Audit Compliance Statement</h6>
                        <p class="small text-muted mb-0">
                            This ledger represents an immutable record of stock transitions including sales disbursements, 
                            purchase acquisitions, and manual inventory adjustments. Every entry is cross-referenced with 
                            a unique transaction identifier and authenticated by the system audit trail. 
                            Unauthorized adjustments are strictly prohibited under financial compliance policies.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pagination (Web Only) -->
            <div class="d-print-none mt-4">
                {{ $movements->links() }}
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Logistics Officer</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Inventory Auditor</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">System Administrator</p>
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
        border-left: 3px solid #1a1a1a;
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

    .btn-premium-dark {
        background-color: #1a1a1a;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-premium-dark:hover {
        background-color: #000;
        color: #d4af37;
    }

    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .rounded-24 { border-radius: 24px; }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

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
    }
</style>
@endsection
