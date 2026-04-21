@extends('layouts.app')

@section('title', 'Inventory Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

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
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .inventory-section-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        height: 100%;
    }
    .inventory-section-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-top-left-radius: var(--radius-md);
        border-top-right-radius: var(--radius-md);
    }
    .inventory-list-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }
    .inventory-list-item:hover {
        background: #f8fafc;
    }
    .inventory-list-item:last-child {
        border-bottom: none;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Inventory Control</h1>
        <p class="dashboard-subtitle">Real-time stock monitoring, valuations and movement analytics</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.products.index') }}" class="btn btn-sm btn-gold"><i class="fas fa-warehouse me-1"></i> Manage Products</a>
        <div class="dropdown">
            <button class="btn btn-sm btn-white border shadow-sm dropdown-toggle" data-bs-toggle="dropdown">Operations</button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item" href="{{ route('inventory.transfers.index') }}">Stock Transfers</a></li>
                <li><a class="dropdown-item" href="{{ route('inventory.alerts.index') }}">Stock Alerts</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('inventory.analytics') }}">Full Analytics</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Key Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Inventory Value</p>
                        <h4 class="fw-bold mb-0">Rs.{{ number_format($inventory['metrics']['total_inventory_value'], 0) }}</h4>
                        <div class="mt-2 small text-muted">{{ $inventory['metrics']['total_products'] }} SKUs registered</div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Retail Value</p>
                        <h4 class="fw-bold mb-0">Rs.{{ number_format($inventory['metrics']['total_retail_value'], 0) }}</h4>
                        <div class="mt-2 small text-success fw-bold">Potential Revenue</div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Profit Potential</p>
                        <h4 class="fw-bold mb-0">Rs.{{ number_format($inventory['metrics']['profit_potential'], 0) }}</h4>
                        <div class="mt-2 small text-primary fw-bold">{{ number_format($inventory['metrics']['profit_margin_percent'], 1) }}% Margin</div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card border-danger border-opacity-25 bg-danger bg-opacity-10">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-danger small fw-bold text-uppercase mb-1">Stock Alerts</p>
                        <h4 class="fw-bold mb-0 text-danger">{{ $inventory['metrics']['low_stock_count'] }}</h4>
                        <div class="mt-2 small text-danger fw-bold">{{ $inventory['metrics']['out_of_stock_count'] }} Out of Stock</div>
                    </div>
                    <div class="stat-icon bg-danger text-white">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Low Stock Products -->
        <div class="col-lg-6">
            <div class="inventory-section-card">
                <div class="inventory-section-header">
                    <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-bell me-2 text-warning"></i>Stock Replenishment Required</span>
                    <a href="{{ route('inventory.low-stock-alert') }}" class="btn btn-xs btn-outline-primary">View Registry</a>
                </div>
                <div class="card-body p-0">
                    @forelse($inventory['low_stock']->take(5) as $product)
                        <div class="inventory-list-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark small">{{ $product->name }}</div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar {{ $product->current_stock > 0 ? 'bg-warning' : 'bg-danger' }}" 
                                             style="width: {{ min($product->current_stock / max(1, $product->reorder_level) * 100, 100) }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted" style="font-size: 0.7rem;">Stock: {{ number_format($product->current_stock, 1) }}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">Reorder at: {{ number_format($product->reorder_level, 1) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-xs btn-light border ms-3">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-check-circle text-success mb-2"></i>
                            <p class="small mb-0">Inventory levels are within optimal range.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Fast Moving -->
        <div class="col-lg-6">
            <div class="inventory-section-card">
                <div class="inventory-section-header">
                    <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-bolt me-2 text-danger"></i>High Velocity Assets</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10">Last 30 Days</span>
                </div>
                <div class="card-body p-0">
                    @forelse($inventory['fast_moving']->take(5) as $item)
                        <div class="inventory-list-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $item['product']->name }}</div>
                                    <span class="text-muted" style="font-size: 0.7rem;">Movements: {{ number_format($item['movements'], 0) }} | Turnover: {{ number_format($item['turnover_rate'], 2) }}x</span>
                                </div>
                                <span class="badge bg-success-soft text-success">{{ number_format($item['movements'], 0) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small">No velocity data available for this period.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Slow Moving -->
        <div class="col-lg-6">
            <div class="inventory-section-card">
                <div class="inventory-section-header">
                    <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-history me-2 text-info"></i>Dormant Inventory (Aging)</span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10">90+ Days</span>
                </div>
                <div class="card-body p-0">
                    @forelse($inventory['slow_moving']->take(5) as $item)
                        <div class="inventory-list-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $item['product']->name }}</div>
                                    <span class="text-muted" style="font-size: 0.7rem;">Value: Rs.{{ number_format($item['stock_value'], 0) }} | Shelf Life: {{ $item['age_days'] }} days</span>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $item['age_days'] }}d</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small">No stagnant inventory detected.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-lg-6">
            <div class="inventory-section-card">
                <div class="inventory-section-header">
                    <span class="fw-bold small text-uppercase text-muted"><i class="fas fa-layer-group me-2 text-primary"></i>Stock Distribution by Category</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <tbody>
                                @forelse($inventory['metrics']['by_category'] as $category)
                                    <tr>
                                        <td class="ps-3"><span class="fw-bold small">{{ $category->name }}</span></td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-light text-primary border">
                                                {{ $category->products()->count() }} SKUs
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center py-4 text-muted small">No categories defined.</td></tr>
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
