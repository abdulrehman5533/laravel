@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Sales Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}" class="text-decoration-none text-muted">Reports</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Revenue Analytics</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-premium-dark px-4 py-2 rounded-pill fw-700 shadow-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-file-export me-2"></i>Export Analytics
            </button>
        </div>
    </div>

    <!-- Today's Metrics -->
    @if(isset($todayMetrics))
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-success-soft text-success shadow-sm">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Today</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">Rs. {{ number_format($todayMetrics['totalSales'] ?? 0, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Gross Revenue</p>
                <div class="mt-3 smaller fw-600 text-muted">
                    <i class="fas fa-receipt me-1"></i> {{ $todayMetrics['count'] ?? 0 }} Transactions
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-info-soft text-info shadow-sm">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Liquidity</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">Rs. {{ number_format($todayMetrics['totalPaid'] ?? 0, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Cash Collected</p>
                <div class="mt-3 smaller fw-600 text-warning">
                    <i class="fas fa-clock me-1"></i> Rs. {{ number_format($todayMetrics['totalOutstanding'] ?? 0, 2) }} Pending
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-gold text-dark shadow-sm">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Performance</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">Rs. {{ number_format($todayMetrics['grossProfit'] ?? 0, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Gross Profit</p>
                <div class="mt-3 smaller fw-600 text-success">
                    <i class="fas fa-percentage me-1"></i> {{ $todayMetrics['grossMargin'] ?? 0 }}% Margin
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-premium-dark text-white shadow-sm">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Basket</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">Rs. {{ number_format($todayMetrics['averageOrderValue'] ?? 0, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Avg Order Value</p>
                <div class="mt-3 smaller fw-600 text-muted">
                    <i class="fas fa-boxes me-1"></i> {{ $todayMetrics['totalItems'] ?? 0 }} Items Sold
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Access Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="{{ route('reports.sales.daily') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-center mb-3">
                        <div class="report-icon bg-gold text-dark me-3 shadow-sm">
                            <i class="fas fa-calendar-day fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">Daily Audit</h5>
                            <p class="text-muted mb-0 smaller fw-600">Day-by-day transaction review</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800 smaller">
                        VIEW REPORT <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.sales.weekly') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-center mb-3">
                        <div class="report-icon bg-info-soft text-info me-3 shadow-sm">
                            <i class="fas fa-chart-bar fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">Weekly Trends</h5>
                            <p class="text-muted mb-0 smaller fw-600">Seven-day performance analysis</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-info fw-800 smaller">
                        VIEW REPORT <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.sales.monthly') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-center mb-3">
                        <div class="report-icon bg-premium-dark text-white me-3 shadow-sm">
                            <i class="fas fa-calendar-alt fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">Monthly Review</h5>
                            <p class="text-muted mb-0 smaller fw-600">Complete month-over-month growth</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-dark fw-800 smaller">
                        VIEW REPORT <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.sales.outstanding') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-center mb-3">
                        <div class="report-icon bg-danger-soft text-danger me-3 shadow-sm">
                            <i class="fas fa-file-invoice-dollar fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">Receivables</h5>
                            <p class="text-muted mb-0 smaller fw-600">Bill-wise & Ageing Analysis</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-danger fw-800 smaller">
                        VIEW REPORT <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.sales.customer-profit-loss') }}" class="text-decoration-none group">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-center mb-3">
                        <div class="report-icon bg-success-soft text-success me-3 shadow-sm">
                            <i class="fas fa-user-chart fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">Client Profitability</h5>
                            <p class="text-muted mb-0 smaller fw-600">Analysis of high-value patrons</p>
                        </div>
                    </div>
                    <div class="mt-auto d-flex align-items-center text-success fw-800 smaller">
                        VIEW REPORT <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Top Products & Customers -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-soft rounded-24 h-100">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-800 text-dark mb-0">
                            <i class="fas fa-trophy text-gold me-2"></i>Premier Products
                        </h5>
                        <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">MTD</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 rounded-start smaller fw-800 text-uppercase tracking-wider">Product</th>
                                    <th class="border-0 text-end smaller fw-800 text-uppercase tracking-wider">Qty</th>
                                    <th class="border-0 rounded-end text-end smaller fw-800 text-uppercase tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts ?? [] as $product)
                                <tr>
                                    <td>
                                        <div class="fw-700 text-dark">{{ $product->description }}</div>
                                        <div class="smaller text-muted">{{ $product->sku }}</div>
                                    </td>
                                    <td class="text-end fw-600">{{ number_format($product->total_qty, 1) }}</td>
                                    <td class="text-end fw-800 text-success">Rs. {{ number_format($product->revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No data recorded for this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-soft rounded-24 h-100">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-800 text-dark mb-0">
                            <i class="fas fa-user-tie text-premium-dark me-2"></i>Valued Clients
                        </h5>
                        <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">MTD</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 rounded-start smaller fw-800 text-uppercase tracking-wider">Customer</th>
                                    <th class="border-0 text-end smaller fw-800 text-uppercase tracking-wider">Orders</th>
                                    <th class="border-0 rounded-end text-end smaller fw-800 text-uppercase tracking-wider">Investment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers ?? [] as $customer)
                                <tr>
                                    <td>
                                        <div class="fw-700 text-dark">{{ $customer->customer?->name ?? 'Private Client' }}</div>
                                        <div class="smaller text-muted">{{ $customer->customer?->phone ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-end fw-600">{{ $customer->orders ?? 0 }}</td>
                                    <td class="text-end fw-800 text-dark">Rs. {{ number_format($customer->total_spent ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No data recorded for this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-24 overflow-hidden">
            <div class="modal-header bg-premium-dark text-white border-0 p-4">
                <h5 class="modal-title fw-800"><i class="fas fa-download me-2"></i>Export Sales Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.sales.export') }}" method="GET" target="_blank">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label smaller fw-800 text-uppercase text-muted mb-2 tracking-wider">Report Period</label>
                        <select name="period" class="form-select border-0 bg-light rounded-pill px-4 py-2">
                            <option value="day">Today's Transactions</option>
                            <option value="week">Current Fiscal Week</option>
                            <option value="month" selected>Current Fiscal Month</option>
                            <option value="year">Full Fiscal Year</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label smaller fw-800 text-uppercase text-muted mb-2 tracking-wider">Output Format</label>
                        <select name="format" class="form-select border-0 bg-light rounded-pill px-4 py-2">
                            <option value="pdf">Professional PDF Report</option>
                            <option value="excel">Data Spreadsheet (Excel)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium-dark rounded-pill px-4 py-2 fw-700 shadow-sm">
                        Generate Report
                    </button>
                </div>
            </form>
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
    .bg-danger-soft { background: rgba(239, 68, 68, 0.08); }
    
    .text-success { color: #10b981 !important; }
    .text-info { color: #0dcaf0 !important; }

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
    
    .transition-up { transition: all 0.3s ease; }
    .group:hover .transition-up { transform: translateY(-10px); }
    .transition-right { transition: transform 0.3s ease; }
    .group:hover .transition-right { transform: translateX(5px); }

    .table thead th {
        font-size: 0.65rem;
        padding: 12px;
    }
    .table tbody td {
        padding: 12px;
        font-size: 0.85rem;
    }
</style>
@endsection
