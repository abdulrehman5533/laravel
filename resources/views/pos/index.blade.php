@extends('layouts.app')

@section('title', 'POS Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .dashboard-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .dashboard-subtitle {
        color: #64748b;
        font-size: 0.875rem;
    }
    .stat-card {
        padding: 20px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        box-shadow: var(--card-shadow);
        height: 100%;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all 0.15s ease;
        height: 100%;
        color: #334155;
    }
    .quick-action-btn:hover {
        background: #ffffff;
        border-color: var(--secondary);
        transform: translateY(-2px);
        color: var(--primary);
    }
    .quick-action-btn i {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }
    .quick-action-btn span {
        font-size: 0.8125rem;
        font-weight: 600;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">POS Terminal</h1>
        <p class="dashboard-subtitle">Manage retail sales, held bills and customer transactions</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pos.sales.index') }}" class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-history me-1"></i> Sales History</a>
        <a href="{{ route('pos.sales.create') }}" class="btn btn-sm btn-gold"><i class="fas fa-plus me-1"></i> New Sale</a>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Today's Sales</p>
                        <h4 class="fw-bold mb-0">Rs. {{ number_format($stats['today_sales'], 2) }}</h4>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Today's Transactions</p>
                        <h4 class="fw-bold mb-0">{{ $stats['today_transactions'] }}</h4>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Held Bills</p>
                        <h4 class="fw-bold mb-0">{{ $stats['pending_holds'] }}</h4>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Pricing Tiers</p>
                        <h4 class="fw-bold mb-0">{{ count($pricingTiers) }}</h4>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pos.sales.create') }}" class="quick-action-btn">
                <i class="fas fa-plus-circle text-primary"></i>
                <span>New Sale</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pos.sales.index') }}" class="quick-action-btn">
                <i class="fas fa-list text-info"></i>
                <span>View Sales</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pos.customers.create') }}" class="quick-action-btn">
                <i class="fas fa-user-plus text-success"></i>
                <span>Add Customer</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pos.payments.index') }}" class="quick-action-btn">
                <i class="fas fa-credit-card text-warning"></i>
                <span>Payments</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pos.returns.index') }}" class="quick-action-btn">
                <i class="fas fa-exchange-alt text-danger"></i>
                <span>Returns</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pos.invoices.index') }}" class="quick-action-btn">
                <i class="fas fa-file-invoice text-secondary"></i>
                <span>Invoices</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('hr.self-service.index') }}" class="quick-action-btn">
                <i class="fas fa-user-clock text-warning"></i>
                <span>My Attendance</span>
            </a>
        </div>
    </div>

    <!-- Recent & Held Sales -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2 text-primary"></i>Active & Held Sales</span>
                    <a href="{{ route('pos.sales.index') }}" class="btn btn-xs btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Invoice</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                            <tr>
                                <td class="ps-3"><span class="fw-bold">{{ $sale->invoice_no }}</span></td>
                                <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                <td>Rs. {{ number_format($sale->total, 2) }}</td>
                                <td>
                                    @if($sale->status === 'held')
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-2 py-1 small fw-bold">HELD</span>
                                    @else
                                        <span class="badge bg-info-soft text-info rounded-pill px-2 py-1 small fw-bold">{{ strtoupper($sale->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($sale->status === 'held')
                                        <form action="{{ route('pos.sales.resume', $sale) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success">
                                                <i class="fas fa-play me-1"></i> Resume
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> Open
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active or held sales found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Pricing Tiers -->
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fas fa-percent me-2 text-primary"></i>Active Pricing Tiers</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Tier Name</th>
                                    <th class="text-end pe-3">Discount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pricingTiers as $tier)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">{{ $tier->name }}</td>
                                    <td class="text-end pe-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">
                                            {{ number_format($tier->discount_percent, 2) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No active tiers</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
