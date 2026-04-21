@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Inventory Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}" class="text-decoration-none text-muted">Reports</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Stock & Assets</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold">
                <i class="fas fa-box-open me-1"></i>Warehouse Analytics
            </span>
        </div>
    </div>

    <!-- Inventory Overview Cards -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-gold text-dark shadow-sm">
                        <i class="fas fa-gem"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Total Assets</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">{{ number_format($totalItems) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Unique SKUs</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-danger-soft text-danger shadow-sm">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Alerts</span>
                </div>
                <h3 class="fw-800 text-danger mb-1">{{ number_format($lowStockItems) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Low Stock Items</p>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100 bg-premium-dark text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-gold text-dark shadow-sm">
                        <i class="fas fa-vault"></i>
                    </div>
                    <span class="badge bg-dark text-white-50 rounded-pill px-2 py-1 smaller fw-700 border border-secondary">Financial Value</span>
                </div>
                <h2 class="fw-800 text-gold mb-1">Rs. {{ number_format($totalValuation, 2) }}</h2>
                <p class="text-white-50 smaller fw-700 text-uppercase mb-0 tracking-wider">Total Inventory Cost Valuation</p>
            </div>
        </div>
    </div>

    <!-- Inventory Access Cards -->
    <div class="row g-4">
        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.inventory.valuation') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-gold text-dark me-4 shadow-gold">
                            <i class="fas fa-calculator fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Stock Valuation</h4>
                            <p class="text-muted mb-0 fw-600">Cost vs Retail value analysis of all products</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800">
                        View Detailed Report <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.inventory.low-stock') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-danger-soft text-danger me-4 shadow-sm">
                            <i class="fas fa-level-down-alt fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Low Stock Alerts</h4>
                            <p class="text-muted mb-0 fw-600">Identify items falling below reorder thresholds</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-danger fw-800">
                        View Detailed Report <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.inventory.stock-ledger') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-premium-dark text-white me-4 shadow-premium">
                            <i class="fas fa-history fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Stock Ledger</h4>
                            <p class="text-muted mb-0 fw-600">Complete audit trail of inventory movements</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-dark fw-800">
                        View Audit Trail <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.inventory.weight-trial') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-info-soft text-info me-4 shadow-sm">
                            <i class="fas fa-weight fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Weight Trial</h4>
                            <p class="text-muted mb-0 fw-600">Technical breakdown of metal weights and purities</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-info fw-800">
                        View Detailed Report <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-24 { border-radius: 24px; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    
    .bg-danger-soft { background: rgba(220, 53, 69, 0.08); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.08); }
    
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #0dcaf0 !important; }

    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

    .stat-card { background: white; transition: transform 0.3s ease; }
    .stat-icon, .report-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }
    
    .report-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
    }
    
    .transition-up { transition: all 0.3s ease; }
    .group:hover .transition-up { transform: translateY(-10px); }
    .transition-right { transition: transform 0.3s ease; }
    .group:hover .transition-right { transform: translateX(5px); }
</style>
@endsection
