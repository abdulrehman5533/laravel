{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

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
        transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
    }
    .quick-action-btn:hover {
        background: #ffffff;
        border-color: var(--secondary);
        transform: translateY(-2px);
    }
    .quick-action-btn i {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }
    .quick-action-btn span {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
    }
    .module-card {
        border-top: 3px solid transparent;
        height: 100%;
    }
    .module-title {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }
    .module-description {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 12px;
    }
    .badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Executive Overview</h1>
        <p class="dashboard-subtitle">Real-time business performance & monitoring</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-download me-1"></i> Report</button>
        <button class="btn btn-sm btn-gold"><i class="fas fa-plus me-1"></i> New Entry</button>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Today's Revenue</p>
                        <h4 class="fw-bold mb-0">Rs. {{ number_format($today_sales, 2) }}</h4>
                        <div class="mt-2 small text-success">
                            <i class="fas fa-arrow-up"></i> 12.5% <span class="text-muted">vs yesterday</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Inventory Value</p>
                        <h4 class="fw-bold mb-0">Rs. {{ number_format($inventory_value, 2) }}</h4>
                        <div class="mt-2 small">
                            <span class="badge bg-light text-dark border">{{ $total_products }} Products</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-gem"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Receivables</p>
                        <h4 class="fw-bold mb-0">Rs. {{ number_format($total_receivables, 2) }}</h4>
                        <div class="mt-2 small text-danger">
                            <i class="fas fa-clock"></i> Action required
                        </div>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Active Customers</p>
                        <h4 class="fw-bold mb-0">{{ number_format($total_customers) }}</h4>
                        <div class="mt-2 small text-primary">
                            <i class="fas fa-user-plus"></i> {{ $new_customers_this_month }} new this month
                        </div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line me-2 text-primary"></i>Sales Performance</span>
                    <div class="btn-group">
                        <button class="btn btn-xs btn-outline-secondary active">7D</button>
                        <button class="btn btn-xs btn-outline-secondary">1M</button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fas fa-chart-pie me-2 text-primary"></i>Stock Distribution</span>
                </div>
                <div class="card-body">
                    <div style="height: 320px;">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <span><i class="fas fa-bolt me-2 text-warning"></i>Operational Quick Links</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('pos.sales.create') }}" class="quick-action-btn">
                        <i class="fas fa-cash-register text-success"></i>
                        <span>New Sales Invoice</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('inventory.products.create') }}" class="quick-action-btn">
                        <i class="fas fa-box-open text-primary"></i>
                        <span>Register Product</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('customers.index') }}" class="quick-action-btn">
                        <i class="fas fa-user-tag text-info"></i>
                        <span>Customer Registry</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('accounts.accounting-dashboard.index') }}" class="quick-action-btn">
                        <i class="fas fa-file-invoice-dollar text-danger"></i>
                        <span>Accounting Ledgers</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('hr.self-service.index') }}" class="quick-action-btn">
                        <i class="fas fa-user-clock text-warning"></i>
                        <span>My Attendance & Leave</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('ai-agent.chat') }}" class="quick-action-btn" title="AI Automation Agent - Chat, Voice, Analytics">
                        <i class="fas fa-brain text-success" style="font-size: 1.8rem;"></i>
                        <span>AI Agent</span>
                        <small class="text-muted" style="font-size: 0.7rem; margin-top: 4px;">Chat • Voice • Analytics</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Integration Section -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-th-large me-2 text-primary"></i>System Modules</span>
                    <span class="badge bg-soft-primary text-primary border">8 Active Modules</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Inventory Module -->
                        <div class="col-xl-3 col-md-6">
                            <div class="module-card card shadow-none border" style="border-top-color: #667eea;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="module-title mb-0">
                                            <i class="fas fa-warehouse me-2" style="color: #667eea;"></i>Inventory
                                        </h6>
                                        <span class="badge bg-primary bg-opacity-10 text-primary small">5 Low</span>
                                    </div>
                                    <p class="module-description">Real-time stock tracking & SKU management</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('inventory.products.index') }}" class="btn btn-xs btn-outline-primary flex-grow-1">Stock</a>
                                        <a href="{{ route('inventory.products.create') }}" class="btn btn-xs btn-primary flex-grow-1">Add</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- POS & Sales -->
                        <div class="col-xl-3 col-md-6">
                            <div class="module-card card shadow-none border" style="border-top-color: #ffc107;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="module-title mb-0">
                                            <i class="fas fa-cash-register me-2" style="color: #ffc107;"></i>POS Sales
                                        </h6>
                                        <span class="badge bg-warning bg-opacity-10 text-dark small">Active</span>
                                    </div>
                                    <p class="module-description">Fast billing, invoice & returns management</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('pos.sales.index') }}" class="btn btn-xs btn-outline-warning flex-grow-1">Ledger</a>
                                        <a href="{{ route('pos.sales.create') }}" class="btn btn-xs btn-warning flex-grow-1">Sell</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CRM -->
                        <div class="col-xl-3 col-md-6">
                            <div class="module-card card shadow-none border" style="border-top-color: #17a2b8;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="module-title mb-0">
                                            <i class="fas fa-users me-2" style="color: #17a2b8;"></i>CRM
                                        </h6>
                                        <span class="badge bg-info bg-opacity-10 text-info small">{{ $total_customers }} Records</span>
                                    </div>
                                    <p class="module-description">Customer loyalty & interaction history</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('customers.index') }}" class="btn btn-xs btn-outline-info flex-grow-1">List</a>
                                        <a href="{{ route('customers.create') }}" class="btn btn-xs btn-info flex-grow-1">New</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reports -->
                        <div class="col-xl-3 col-md-6">
                            <div class="module-card card shadow-none border" style="border-top-color: #6f42c1;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="module-title mb-0">
                                            <i class="fas fa-chart-bar me-2" style="color: #6f42c1;"></i>Reports
                                        </h6>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary small">BI Active</span>
                                    </div>
                                    <p class="module-description">Deep analytics & financial reporting</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('reports.sales.index') }}" class="btn btn-xs btn-outline-secondary flex-grow-1">Sales</a>
                                        <a href="{{ route('reports.financial.index') }}" class="btn btn-xs btn-secondary flex-grow-1">Profit</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-3">
        <!-- Recent Sales -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history me-2 text-primary"></i>Recent Transactions</span>
                    <a href="{{ route('pos.sales.index') }}" class="btn btn-xs btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0 border-0">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_sales as $sale)
                            <tr>
                                <td><span class="fw-bold text-dark">{{ $sale->invoice_number }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2">
                                            <i class="fas fa-user text-muted small"></i>
                                        </div>
                                        <span>{{ $sale->customer->name ?? 'Walk-in' }}</span>
                                    </div>
                                </td>
                                <td>{{ $sale->created_at->format('d M, Y') }}</td>
                                <td class="fw-bold">Rs. {{ number_format($sale->total, 2) }}</td>
                                <td>
                                    @php
                                        $outstanding = (float) $sale->outstanding_balance;
                                        $total = (float) $sale->total;
                                        $hasPaid = $sale->payments->where('status', 'completed')->count() > 0;

                                        if ($sale->status === 'cancelled') {
                                            $statusLabel = 'Cancelled';
                                            $badge = 'bg-secondary text-white';
                                        } elseif ($sale->status === 'held') {
                                            $statusLabel = 'On Hold';
                                            $badge = 'bg-info text-dark';
                                        } elseif ($sale->status === 'draft' || $sale->status === 'opened') {
                                            $statusLabel = 'Draft';
                                            $badge = 'bg-light text-dark border';
                                        } elseif ($outstanding <= 0) {
                                            $statusLabel = 'Paid';
                                            $badge = 'bg-success text-white';
                                        } elseif ($hasPaid && $outstanding > 0) {
                                            $statusLabel = 'Partial';
                                            $badge = 'bg-warning text-dark';
                                        } elseif ($total > 0 && $outstanding >= $total) {
                                            $statusLabel = 'Unpaid';
                                            $badge = 'bg-danger text-white';
                                        } else {
                                            $statusLabel = ucfirst($sale->status ?? 'Unknown');
                                            $badge = 'bg-secondary text-white';
                                        }
                                    @endphp
                                    <span class="badge {{ $badge }} border-0">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('pos.sales.show', $sale->id) }}" class="btn btn-light border btn-xs" title="View"><i class="fas fa-eye text-primary"></i></a>
                                        <a href="{{ route('pos.sales.invoice-a4', $sale->id) }}" target="_blank" class="btn btn-light border btn-xs" title="Print"><i class="fas fa-print text-secondary"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Critical Stock</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border-0">{{ $low_stock }} Alerts</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($low_stock_products as $product)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-dark fw-bold">{{ $product->name }}</h6>
                                <span class="badge bg-danger text-white rounded-pill">{{ $product->stock }} {{ $product->unit ?? 'pcs' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">SKU: {{ $product->sku }}</small>
                                <button class="btn btn-xs btn-outline-primary" onclick="restockProduct('{{ $product->id }}')">
                                    <i class="fas fa-plus me-1"></i> Restock
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="{{ route('inventory.low-stock-alert') }}" class="btn btn-xs btn-link text-decoration-none">Manage All Stock Alerts</a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Price Calculator Modal -->
<div class="modal fade" id="priceCalculatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: none; border-radius: 16px 16px 0 0; padding: 24px;">
                <h5 class="modal-title" style="color: white; font-weight: 800; font-size: 18px;">
                    <i class="fas fa-calculator me-2" style="color: #D4AF37;"></i>Jewellery Price Calculator
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body" style="padding: 32px;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #1a1a2e;">Gold Rate (Rs./g)</label>
                            <input type="number" id="goldRate" class="form-control" value="{{ $gold_rate->rate_22k ?? 5500 }}" step="0.01" style="border-radius: 10px; border: 2px solid #f0f0f0; padding: 12px 14px; transition: all 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #1a1a2e;">Weight (grams)</label>
                            <input type="number" id="weight" class="form-control" value="10" step="0.001" style="border-radius: 10px; border: 2px solid #f0f0f0; padding: 12px 14px; transition: all 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #1a1a2e;">Purity (%)</label>
                            <select id="purity" class="form-control" style="border-radius: 10px; border: 2px solid #f0f0f0; padding: 12px 14px; transition: all 0.3s ease;">
                                <option value="91.6">22K (91.6%)</option>
                                <option value="75">18K (75%)</option>
                                <option value="58.5">14K (58.5%)</option>
                                <option value="99.9">24K (99.9%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #1a1a2e;">Making Charge (Rs.)</label>
                            <input type="number" id="makingCharge" class="form-control" value="1500" step="0.01" style="border-radius: 10px; border: 2px solid #f0f0f0; padding: 12px 14px; transition: all 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #1a1a2e;">Stone Cost (Rs.)</label>
                            <input type="number" id="stoneCost" class="form-control" value="0" step="0.01" style="border-radius: 10px; border: 2px solid #f0f0f0; padding: 12px 14px; transition: all 0.3s ease;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #1a1a2e;">Profit Margin (%)</label>
                            <input type="range" id="profitMargin" class="form-range" min="5" max="50" value="15" step="0.5" style="height: 6px; border-radius: 10px;">
                            <div class="d-flex justify-content-between mt-2">
                                <small style="font-weight: 700; color: #999;">5%</small>
                                <span id="marginDisplay" class="fw-bold" style="color: #D4AF37; font-size: 14px;">15%</span>
                                <small style="font-weight: 700; color: #999;">50%</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Price Breakdown -->
                <div class="card mt-4" style="border: none; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden;">
                    <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; padding: 20px;">
                        <h6 class="mb-0" style="color: white; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-calculator me-2"></i>Price Breakdown
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="p-3 border rounded" style="border: 2px solid #f0f0f0 !important; border-radius: 12px; transition: all 0.3s ease;">
                                    <small class="text-muted" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Gold Value</small>
                                    <h5 id="goldValue" class="mt-3" style="margin-bottom: 0; font-weight: 800; color: #8B4513;">Rs. 0</h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded" style="border: 2px solid #f0f0f0 !important; border-radius: 12px; transition: all 0.3s ease;">
                                    <small class="text-muted" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Making Charge</small>
                                    <h5 id="makingChargeValue" class="mt-3" style="margin-bottom: 0; font-weight: 800; color: #8B4513;">Rs. 0</h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded" style="border: 2px solid #f0f0f0 !important; border-radius: 12px; transition: all 0.3s ease;">
                                    <small class="text-muted" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Stone Cost</small>
                                    <h5 id="stoneCostValue" class="mt-3" style="margin-bottom: 0; font-weight: 800; color: #8B4513;">Rs. 0</h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded" style="border: 2px solid #D4AF37 !important; border-radius: 12px; background: rgba(212, 175, 55, 0.05); transition: all 0.3s ease;">
                                    <small class="text-muted" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Total Cost</small>
                                    <h5 id="totalCost" class="mt-3" style="margin-bottom: 0; font-weight: 800; color: #D4AF37;">Rs. 0</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4 text-center">
                            <div class="col-6">
                                <div class="p-3 border rounded" style="border: 2px solid #ffc107 !important; border-radius: 12px; background: rgba(255, 193, 7, 0.08); transition: all 0.3s ease;">
                                    <small class="text-muted" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Profit (@<span id="marginPercent">15</span>%)</small>
                                    <h5 id="profitAmount" class="mt-3" style="margin-bottom: 0; font-weight: 800; color: #28a745;">Rs. 0</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded" style="border: 2px solid #28a745 !important; border-radius: 12px; background: rgba(40, 167, 69, 0.08); transition: all 0.3s ease;">
                                    <small class="text-muted" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Selling Price</small>
                                    <h3 id="sellingPrice" class="mt-3" style="margin-bottom: 0; font-weight: 800; color: #28a745;">Rs. 0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 20px 32px; background: #f8f9fa; border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 700; padding: 8px 20px; border: 2px solid #ddd;">Close</button>
                <button type="button" class="btn" onclick="saveCalculation()" style="border-radius: 10px; font-weight: 700; padding: 8px 20px; background: linear-gradient(135deg, #D4AF37, #FFD700); color: #2C1810; border: none; box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3); transition: all 0.3s ease;">
                    <i class="fas fa-save me-1"></i> Save as Product
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($sales_chart['labels']) !!},
            datasets: [{
                label: 'Sales (Rs.)',
                data: {!! json_encode($sales_chart['sales']) !!},
                borderColor: '#D4AF37',
                backgroundColor: 'rgba(212, 175, 55, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#1a1a2e',
                pointBorderColor: '#D4AF37',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleColor: '#D4AF37',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { callback: value => 'Rs. ' + value.toLocaleString() }
                },
                x: { grid: { display: false } }
            }
        }
    });

    const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
    new Chart(inventoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($inventory_chart['labels']) !!},
            datasets: [{
                data: {!! json_encode($inventory_chart['values']) !!},
                backgroundColor: [
                    '#D4AF37', '#2C1810', '#8B4513', '#C0C0C0', 
                    '#E5E4E2', '#FFD700', '#B8860B'
                ],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            return ' Value: Rs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Price Calculator Logic
        const goldRateInput = document.getElementById('goldRate');
        const weightInput = document.getElementById('weight');
        const purityInput = document.getElementById('purity');
        const makingChargeInput = document.getElementById('makingCharge');
        const stoneCostInput = document.getElementById('stoneCost');
        const profitMarginInput = document.getElementById('profitMargin');
        
        function updateCalculations() {
            const goldRate = parseFloat(goldRateInput.value) || 0;
            const weight = parseFloat(weightInput.value) || 0;
            const purity = (parseFloat(purityInput.value) || 0) / 100;
            const makingCharge = parseFloat(makingChargeInput.value) || 0;
            const stoneCost = parseFloat(stoneCostInput.value) || 0;
            const margin = (parseFloat(profitMarginInput.value) || 0) / 100;
            
            const goldValue = goldRate * weight * purity;
            const totalCost = goldValue + makingCharge + stoneCost;
            const profit = totalCost * margin;
            const sellingPrice = totalCost + profit;
            
            document.getElementById('goldValue').innerText = 'Rs. ' + goldValue.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('makingChargeValue').innerText = 'Rs. ' + makingCharge.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('stoneCostValue').innerText = 'Rs. ' + stoneCost.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('totalCost').innerText = 'Rs. ' + totalCost.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('profitAmount').innerText = 'Rs. ' + profit.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('sellingPrice').innerText = 'Rs. ' + sellingPrice.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('marginDisplay').innerText = (margin * 100).toFixed(1) + '%';
            document.getElementById('marginPercent').innerText = (margin * 100).toFixed(1);
        }
        
        [goldRateInput, weightInput, purityInput, makingChargeInput, stoneCostInput, profitMarginInput].forEach(el => {
            el.addEventListener('input', updateCalculations);
        });
        
        // Initial calculation
        updateCalculations();

        // Category Sales Chart
        const catSalesCtx = document.getElementById('categorySalesChart');
        if (catSalesCtx) {
            new Chart(catSalesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($category_sales_chart['labels']),
                    datasets: [{
                        label: 'Sales Amount',
                        data: @json($category_sales_chart['values']),
                        backgroundColor: '#4e73df',
                        borderRadius: 5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Purity Sales Chart
        const puritySalesCtx = document.getElementById('puritySalesChart');
        if (puritySalesCtx) {
            new Chart(puritySalesCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($purity_sales_chart['labels']),
                    datasets: [{
                        data: @json($purity_sales_chart['values']),
                        backgroundColor: ['#D4AF37', '#DAA520', '#B87333', '#E5E4E2', '#C0C0C0', '#FFD700']
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Payment Methods Chart
        const payMethodsCtx = document.getElementById('paymentMethodsChart');
        if (payMethodsCtx) {
            new Chart(payMethodsCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: @json($payment_methods_chart['labels']),
                    datasets: [{
                        data: @json($payment_methods_chart['values']),
                        backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });

    function saveCalculation() {
        alert('Product saving functionality would be integrated here.');
    }

    function restockProduct(id) {
        window.location.href = `/inventory/products/${id}/edit`;
    }
</script>
@endpush
@endsection
