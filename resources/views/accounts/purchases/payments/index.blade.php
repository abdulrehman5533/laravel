@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Treasury Disbursement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.purchases.orders.index') }}" class="text-decoration-none text-muted">Supply Chain</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Payment Registry</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.purchases.payments.export') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-file-export me-2 text-secondary"></i>Export Treasury Log
                </a>
                <a href="{{ route('accounts.purchases.payments.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>Record Settlement
                </a>
            </div>
        </div>
    </div>

    <!-- Premium Metrics Row -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden group">
                <div class="card-bg-icon">
                    <i class="fas fa-money-bill-wave text-success opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-success-soft text-success me-3">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <span class="text-muted fw-600 small">Approved Disbursements</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($payments->where('status', 'Approved')->sum('amount_paid'), 2) }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-success small fw-700"><i class="fas fa-shield-alt me-1"></i>Verified Assets</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-hourglass-half text-warning opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-warning-soft text-warning me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="text-muted fw-600 small">Pending Verification</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($payments->where('status', 'Pending')->sum('amount_paid'), 2) }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-warning small fw-700"><i class="fas fa-exclamation-circle me-1"></i>Awaiting Audit</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-university text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span class="text-muted fw-600 small">Volume (30 Days)</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($payments->sum('amount_paid'), 2) }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-primary small fw-700"><i class="fas fa-chart-line me-1"></i>Settlement Velocity</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden bg-premium-dark">
                <div class="card-bg-icon">
                    <i class="fas fa-shield-alt text-gold opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-gold text-dark me-3">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <span class="text-white-50 fw-600 small">Secured Protocols</span>
                </div>
                <h2 class="fw-800 mb-2 text-white">{{ $payments->count() }}</h2>
                <div class="d-flex align-items-center">
                    <span class="text-gold small fw-700"><i class="fas fa-lock me-1"></i>Audit-Ready Log</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Audit Filters -->
    <div class="card border-0 shadow-sm rounded-20 mb-5 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-light rounded-10 me-3">
                    <i class="fas fa-sliders-h text-primary"></i>
                </div>
                <h5 class="mb-0 fw-800">Treasury Audit Filters</h5>
            </div>
        </div>
        <div class="card-body p-4 pt-0">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Verification Status</label>
                    <div class="glass-input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-shield-alt text-primary"></i></span>
                        <select name="status" class="form-select bg-transparent border-0 ps-0">
                            <option value="">All Verification States</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved Disbursement</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected Request</option>
                            <option value="Reversed" {{ request('status') == 'Reversed' ? 'selected' : '' }}>Reversed Transaction</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Settlement Channel</label>
                    <div class="glass-input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-credit-card text-primary"></i></span>
                        <select name="payment_method" class="form-select bg-transparent border-0 ps-0">
                            <option value="">All Strategic Channels</option>
                            <option value="Bank_Transfer" {{ request('payment_method') == 'Bank_Transfer' ? 'selected' : '' }}>Institutional Transfer</option>
                            <option value="Cash" {{ request('payment_method') == 'Cash' ? 'selected' : '' }}>Liquidity Settlement</option>
                            <option value="Cheque" {{ request('payment_method') == 'Cheque' ? 'selected' : '' }}>Instrumental Draft</option>
                            <option value="UPI" {{ request('payment_method') == 'UPI' ? 'selected' : '' }}>Digital Expressway</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-12 fw-700 py-2">
                        <i class="fas fa-search me-2"></i>Execute Search
                    </button>
                    <a href="{{ route('accounts.purchases.payments.index') }}" class="btn btn-light rounded-12 fw-700 py-2">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Treasury Intelligence Table -->
    <div class="card border-0 shadow-premium rounded-24 overflow-hidden">
        <div class="card-header bg-premium-dark p-4 border-0">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-gold rounded-10 me-3">
                        <i class="fas fa-file-invoice-dollar text-dark"></i>
                    </div>
                    <h5 class="mb-0 text-white fw-800">Settlement Registry</h5>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light-soft">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Audit Reference</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Strategic Partner</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Procurement Ref</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Settlement Date</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0 text-end">Disbursement</th>
                        <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0 text-center">Protocol</th>
                        <th class="text-center pe-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Intelligence</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 bg-light rounded-8 me-3">
                                        <i class="fas fa-fingerprint text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">{{ $payment->payment_reference }}</div>
                                        <div class="text-muted small">System ID: #TR-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-700 text-dark">{{ $payment->supplier->name ?? '-' }}</div>
                                <div class="text-muted small text-uppercase tracking-tighter">Verified Supplier</div>
                            </td>
                            <td>
                                @if($payment->purchaseOrder)
                                    <a href="{{ route('accounts.purchases.orders.show', $payment->purchaseOrder) }}" class="text-decoration-none d-block">
                                        <span class="badge bg-primary-soft text-primary rounded-pill fw-800 border-0 px-3">
                                            <i class="fas fa-shopping-cart me-1"></i> {{ $payment->purchaseOrder->po_number }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-muted small italic">Direct Settlement</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-600">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</div>
                                <div class="text-muted small">{{ \Carbon\Carbon::parse($payment->payment_date)->diffForHumans() }}</div>
                            </td>
                            <td class="text-end fw-800 text-dark">
                                Rs.{{ number_format($payment->amount_paid, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border-0 px-3 py-2 rounded-pill fw-700">
                                    {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @switch($payment->status)
                                    @case('Pending')
                                        <span class="badge bg-warning-soft text-warning border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-clock me-1"></i> PENDING
                                        </span>
                                        @break
                                    @case('Approved')
                                        <span class="badge bg-success-soft text-success border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-check-circle me-1"></i> APPROVED
                                        </span>
                                        @break
                                    @case('Rejected')
                                        <span class="badge bg-danger-soft text-danger border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-times-circle me-1"></i> REJECTED
                                        </span>
                                        @break
                                    @case('Reversed')
                                        <span class="badge bg-secondary-soft text-secondary border-0 px-3 py-2 rounded-pill fw-700">
                                            <i class="fas fa-undo me-1"></i> REVERSED
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-info-soft text-info border-0 px-3 py-2 rounded-pill fw-700">
                                            {{ strtoupper($payment->status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('accounts.purchases.payments.show', $payment) }}" 
                                   class="btn btn-light btn-sm rounded-8 hover-elevate shadow-sm">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-receipt text-muted opacity-25 mb-3" style="font-size: 4rem;"></i>
                                    <h6 class="text-muted fw-600">No Treasury Intelligence Found</h6>
                                    <p class="text-muted small mb-0">Initiate a new settlement to populate the registry</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-white border-0 p-4">
                {{ $payments->links() }}
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
    
    .stat-card:hover { transform: translateY(-5px); }
    
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

    .breadcrumb-item + .breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #ccc;
    }
</style>
@endsection
