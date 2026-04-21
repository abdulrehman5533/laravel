@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Purchase Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Supply Chain Hub</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <button class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-file-export me-2 text-secondary"></i>Export Hub
                </button>
                <a href="{{ route('accounts.purchases.orders.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>New Purchase Order
                </a>
            </div>
        </div>
    </div>

    <!-- Premium Metrics Row -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden group">
                <div class="card-bg-icon">
                    <i class="fas fa-shopping-cart text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-box"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total Procurement</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($purchaseOrders->sum('total_amount'), 2) }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-primary small fw-700"><i class="fas fa-history me-1"></i>Cumulative Volume</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-clock text-warning opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-warning-soft text-warning me-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <span class="text-muted fw-600 small">Pending Fulfillment</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $purchaseOrders->whereIn('status', ['Draft', 'Confirmed'])->count() }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-warning small fw-700"><i class="fas fa-exclamation-circle me-1"></i>Active Orders</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-receipt text-danger opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-danger-soft text-danger me-3">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <span class="text-muted fw-600 small">Accounts Payable</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($purchaseOrders->where('payment_status', '!=', 'paid')->sum('total_amount'), 2) }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-danger small fw-700"><i class="fas fa-file-invoice me-1"></i>Outstanding Debt</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden bg-premium-dark">
                <div class="card-bg-icon">
                    <i class="fas fa-check-double text-gold opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-gold text-dark me-3">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <span class="text-white-50 fw-600 small">Received Inventory</span>
                </div>
                <h2 class="fw-800 mb-2 text-white">{{ $purchaseOrders->where('status', 'Received')->count() }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-gold small fw-700"><i class="fas fa-warehouse me-1"></i>Completed Logistics</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search Hub -->
    <div class="card border-0 shadow-sm rounded-20 mb-5 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-light rounded-10 me-3">
                    <i class="fas fa-search text-primary"></i>
                </div>
                <h5 class="mb-0 fw-800">Procurement Search Hub</h5>
            </div>
        </div>
        <div class="card-body p-4 pt-0">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">PO Identification</label>
                    <div class="glass-input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-hashtag text-primary"></i></span>
                        <input type="text" name="search" class="form-control bg-transparent border-0" placeholder="Search PO Number..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Supplier Partner</label>
                    <div class="glass-input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-handshake text-primary"></i></span>
                        <select name="supplier_id" class="form-select bg-transparent border-0">
                            <option value="">All Partners</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Order Status</label>
                    <div class="glass-input-group">
                        <select name="status" class="form-select bg-transparent border-0 ps-3">
                            <option value="">All Lifecycle</option>
                            <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Confirmed" {{ request('status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                            <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Treasury Status</label>
                    <div class="glass-input-group">
                        <select name="payment_status" class="form-select bg-transparent border-0 ps-3">
                            <option value="">All Treasury</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-12 fw-700 py-2">
                        <i class="fas fa-filter me-2"></i>Apply
                    </button>
                    <a href="{{ route('accounts.purchases.orders.index') }}" class="btn btn-light rounded-12 fw-700 py-2">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchase Orders Intelligence Table -->
    <div class="card border-0 shadow-premium rounded-24 overflow-hidden">
        <div class="card-header bg-premium-dark p-4 border-0">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-gold rounded-10 me-3">
                        <i class="fas fa-list-ul text-dark"></i>
                    </div>
                    <h5 class="mb-0 text-white fw-800">Procurement Registry</h5>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light-soft">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">PO Reference</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Strategic Partner</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Issuance Date</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Procurement Value</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Lifecycle</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Treasury</th>
                        <th class="text-center pe-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Intelligence</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                    @forelse($purchaseOrders as $order)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 bg-light rounded-8 me-3">
                                        <i class="fas fa-file-contract text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">{{ $order->po_number }}</div>
                                        <div class="text-muted small">Ref: #PRO-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-700 text-dark">{{ $order->supplier->name ?? '-' }}</div>
                                <div class="text-muted small">ID: {{ $order->supplier->id ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="fw-600">{{ $order->po_date->format('d M, Y') }}</div>
                                <div class="text-muted small">{{ $order->po_date->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="fw-800 text-dark">Rs.{{ number_format($order->total_amount, 2) }}</div>
                                <div class="text-muted small text-uppercase tracking-tighter">Gross Value</div>
                            </td>
                            <td>
                                @switch($order->status)
                                    @case('Draft')
                                        <span class="badge bg-secondary-soft text-secondary border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-edit me-1"></i> DRAFT
                                        </span>
                                        @break
                                    @case('Confirmed')
                                        <span class="badge bg-info-soft text-info border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-check-circle me-1"></i> CONFIRMED
                                        </span>
                                        @break
                                    @case('Received')
                                        <span class="badge bg-success-soft text-success border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-warehouse me-1"></i> RECEIVED
                                        </span>
                                        @break
                                    @case('Cancelled')
                                        <span class="badge bg-danger-soft text-danger border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-times-circle me-1"></i> CANCELLED
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark border-0 px-3 py-2 rounded-pill fw-700">
                                            {{ strtoupper($order->status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td>
                                @switch($order->payment_status)
                                    @case('pending')
                                        <span class="badge bg-warning-soft text-warning border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-clock me-1"></i> PENDING
                                        </span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-primary-soft text-primary border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-pie-chart me-1"></i> PARTIAL
                                        </span>
                                        @break
                                    @case('paid')
                                        <span class="badge bg-success-soft text-success border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-check-double me-1"></i> SETTLED
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark border-0 px-3 py-2 rounded-pill fw-700">
                                            {{ strtoupper($order->payment_status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('accounts.purchases.orders.show', $order) }}" 
                                       class="btn btn-light btn-sm rounded-8 hover-elevate">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    @if($order->status === 'Draft')
                                        <a href="{{ route('accounts.purchases.orders.edit', $order) }}" 
                                           class="btn btn-light btn-sm rounded-8 hover-elevate">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-inbox text-muted opacity-25 mb-3" style="font-size: 4rem;"></i>
                                    <h6 class="text-muted fw-600">No Procurement Intelligence Found</h6>
                                    <p class="text-muted small mb-0">Adjust your filters or initiate a new purchase order</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer bg-white border-0 p-4">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .bg-premium-dark { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); }
    .bg-light-soft { background-color: #f8f9fa; }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    .shadow-gold { box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
    
    .rounded-12 { border-radius: 12px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 20px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .card-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        transform: rotate(-15deg);
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .glass-input-group {
        background: #f8f9fa;
        border: 1px solid #eee;
        border-radius: 12px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .glass-input-group:focus-within {
        background: white;
        border-color: #d4af37;
        box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
    }
    
    .hover-elevate {
        transition: all 0.2s ease;
    }
    
    .hover-elevate:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .table thead th {
        border-bottom: 2px solid #f0f0f0;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #ccc;
    }
</style>
@endsection
