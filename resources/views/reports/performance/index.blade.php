@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Operational Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}" class="text-decoration-none text-muted">Reports</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Employee Performance</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-outline-dark px-4 py-2 rounded-pill fw-700 shadow-sm disabled">
                <i class="fas fa-file-export me-1"></i> Export Metrics
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-gold text-dark shadow-sm">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Workforce</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">0</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Active Team Members</p>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-premium-dark text-white shadow-sm">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Productivity</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">0</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Total Sales Contributions</p>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-info-soft text-info shadow-sm">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Efficiency</span>
                </div>
                <h3 class="fw-800 text-info mb-1">0</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Avg. Sales Per Person</p>
            </div>
        </div>
    </div>

    <!-- Detailed Performance Matrix -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden">
        <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-800 text-dark">Employee Efficiency Matrix</h5>
            <div class="text-muted smaller fw-600">Performance Audit Benchmarks</div>
        </div>
        <div class="card-body p-5 text-center bg-light">
            <div class="py-5">
                <div class="mb-4">
                    <i class="fas fa-chart-bar text-muted-light" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-800 text-dark mb-2">Metrics Under Synchronization</h4>
                <p class="text-muted mx-auto" style="max-width: 500px;">
                    The performance analytics engine is currently synchronizing historical transaction data. 
                    Individual employee contribution benchmarks and sales velocity metrics will be available shortly.
                </p>
                <div class="mt-4">
                    <span class="badge bg-soft-dark text-dark px-3 py-2 rounded-pill smaller fw-700">
                        <i class="fas fa-sync fa-spin me-2"></i> DATA ENGINE STATUS: INITIALIZING
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-24 { border-radius: 24px; }
    .smaller { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .text-muted-light { color: #e0e0e0; }
    
    .bg-info-soft { background: rgba(13, 202, 240, 0.08); }
    .bg-soft-dark { background: rgba(0, 0, 0, 0.05); }
    
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    
    .stat-card { background: white; transition: all 0.3s ease; }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }
</style>
@endsection
