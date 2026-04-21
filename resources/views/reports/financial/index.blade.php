@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Financial Reports</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}" class="text-decoration-none text-muted">Reports</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Financial Analysis</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold">
                <i class="fas fa-chart-pie me-1"></i>Performance Metrics
            </span>
        </div>
    </div>

    <!-- Monthly Overview Cards -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-gold text-dark shadow-sm">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">MTD Sales</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">Rs.{{ number_format($monthlySales, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Gross Revenue</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-premium-dark text-white shadow-sm">
                        <i class="fas fa-tags"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">MTD Margin</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">{{ $monthlyMargin }}%</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Operating Margin</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-success-soft text-success shadow-sm">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">MTD Profit</span>
                </div>
                <h3 class="fw-800 text-success mb-1">Rs.{{ number_format($monthlyProfit, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Net Earnings</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-info-soft text-info shadow-sm">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Today</span>
                </div>
                <h3 class="fw-800 text-info mb-1">Rs.{{ number_format($todaySales, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Daily Revenue</p>
            </div>
        </div>
    </div>

    <!-- Report Access Cards -->
    <div class="row g-4">
        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.financial.profit-loss') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-gold text-dark me-4 shadow-gold">
                            <i class="fas fa-file-invoice-dollar fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Profit & Loss</h4>
                            <p class="text-muted mb-0 fw-600">Income, expenses, and net performance analysis</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800">
                        View Detailed Report <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.financial.cash-flow') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-premium-dark text-white me-4 shadow-premium">
                            <i class="fas fa-exchange-alt fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Cash Flow</h4>
                            <p class="text-muted mb-0 fw-600">Tracking inflows and outflows of liquidity</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800">
                        View Detailed Report <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.financial.metal-book') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-warning text-dark me-4 shadow-sm">
                            <i class="fas fa-weight fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Metal Book</h4>
                            <p class="text-muted mb-0 fw-600">Pure gold (Fine Weight) movement tracking</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800">
                        View Detailed Report <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-4">
            <a href="{{ route('reports.financial.trading-p-l') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-5 h-100 transition-up">
                    <div class="d-flex align-items-center mb-4">
                        <div class="report-icon bg-info text-white me-4 shadow-sm">
                            <i class="fas fa-balance-scale fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-800 text-dark mb-1">Trading Account</h4>
                            <p class="text-muted mb-0 fw-600">Gross profit analysis from trading activities</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800">
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
    
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.08); }
    
    .text-success { color: #10b981 !important; }
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
