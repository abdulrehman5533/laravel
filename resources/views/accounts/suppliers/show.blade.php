@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Partner Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.suppliers.index') }}" class="text-decoration-none text-muted">Suppliers</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">{{ $supplier->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.suppliers.ledger', $supplier) }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-book text-info me-2"></i>Audit Ledger
                </a>
                <a href="{{ route('accounts.suppliers.edit', $supplier) }}" class="btn btn-premium-dark shadow-premium px-4 rounded-12 fw-700">
                    <i class="fas fa-edit me-2"></i>Configure
                </a>
                <a href="{{ route('accounts.suppliers.index') }}" class="btn btn-gold shadow-gold px-4 rounded-12 fw-700">
                    <i class="fas fa-arrow-left me-2"></i>Back to Hub
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Partner Profile -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark py-4 px-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-gold-soft text-gold rounded-12 p-3 me-3">
                            <i class="fas fa-id-badge fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-0 fw-800">Operational Profile</h5>
                            <p class="text-white-50 small mb-0 fw-600">Verified institutional stakeholder data</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="profile-item p-3 bg-light rounded-16">
                                <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Full Legal Name</label>
                                <span class="fw-800 text-dark fs-5">{{ $supplier->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-item p-3 bg-light rounded-16">
                                <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Corporate Identity</label>
                                <span class="fw-800 text-dark fs-5">{{ $supplier->company_name ?? 'Individual' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="profile-item p-3 bg-light rounded-16">
                                <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Communication</label>
                                <span class="fw-700 text-dark small d-block"><i class="fas fa-phone-alt text-gold me-2"></i>{{ $supplier->phone_primary }}</span>
                                <span class="text-muted smaller fw-600">{{ $supplier->email }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="profile-item p-3 bg-light rounded-16">
                                <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Classification</label>
                                <span class="badge bg-gold-soft text-gold rounded-pill px-3 py-2 fw-800 smaller">{{ strtoupper($supplier->supplier_type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="profile-item p-3 bg-light rounded-16">
                                <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Operational Status</label>
                                <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-800 smaller">ACTIVE PARTNER</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax & Treasury Data -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-soft rounded-24 h-100">
                        <div class="card-header bg-white py-4 px-4 border-0">
                            <h6 class="mb-0 fw-800 text-dark">Tax Compliance</h6>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between">
                                    <span class="text-muted small fw-700 uppercase">GSTIN / TAX ID</span>
                                    <span class="fw-800 text-dark">{{ $supplier->gstin ?? 'Not Registered' }}</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between">
                                    <span class="text-muted small fw-700 uppercase">PAN IDENTIFIER</span>
                                    <span class="fw-800 text-dark">{{ $supplier->pan ?? 'N/A' }}</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between">
                                    <span class="text-muted small fw-700 uppercase">REGISTRATION</span>
                                    <span class="fw-800 text-dark">{{ $supplier->registration_number ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-soft rounded-24 h-100">
                        <div class="card-header bg-white py-4 px-4 border-0">
                            <h6 class="mb-0 fw-800 text-dark">Treasury Details</h6>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between">
                                    <span class="text-muted small fw-700 uppercase">BANK ENTITY</span>
                                    <span class="fw-800 text-dark">{{ $supplier->bank_name ?? 'N/A' }}</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between">
                                    <span class="text-muted small fw-700 uppercase">ACC NUMBER</span>
                                    <span class="fw-800 text-dark">{{ $supplier->bank_account_number ?? 'N/A' }}</span>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between">
                                    <span class="text-muted small fw-700 uppercase">SORT / IFSC</span>
                                    <span class="fw-800 text-dark">{{ $supplier->ifsc_code ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary Sidebar -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-premium rounded-24 bg-premium-dark text-white mb-4 overflow-hidden position-relative">
                <div class="card-bg-icon"><i class="fas fa-vault text-gold opacity-10"></i></div>
                <div class="card-body p-4 position-relative z-1">
                    <h5 class="fw-800 mb-4 text-gold">Liquidity Snapshot</h5>
                    
                    <div class="balance-item mb-4 p-3 rounded-16 bg-white-5 border-white-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-white-50 small fw-700 text-uppercase mb-1">Lifetime Volume</p>
                                <h4 class="fw-800 mb-0 text-white">Rs. {{ number_format($totalPurchases, 2) }}</h4>
                            </div>
                            <div class="stat-icon bg-gold text-dark rounded-12 p-2">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>

                    <div class="balance-item p-3 rounded-16 bg-danger-soft border-danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-danger-soft-text small fw-700 text-uppercase mb-1">Net Exposure</p>
                                <h2 class="fw-900 text-danger mb-0">Rs. {{ number_format($outstandingBalance, 2) }}</h2>
                            </div>
                            <i class="fas fa-exclamation-triangle text-danger opacity-50 fs-2"></i>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 smaller fw-600">Credit Utilization</span>
                            <span class="text-gold smaller fw-800">
                                @if($supplier->credit_limit)
                                    {{ number_format(($outstandingBalance / $supplier->credit_limit) * 100, 1) }}%
                                @else
                                    Unlimited
                                @endif
                            </span>
                        </div>
                        <div class="progress bg-white-10" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar bg-gold" style="width: {{ $supplier->credit_limit ? min(100, ($outstandingBalance / $supplier->credit_limit) * 100) : 0 }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Radar -->
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-dark">Strategic Intelligence</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted smaller fw-700 uppercase">Quality Score</span>
                            <span class="fw-800 text-dark">{{ $supplier->quality_score ?? 0 }}%</span>
                        </div>
                        <div class="progress bg-light" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $supplier->quality_score ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted smaller fw-700 uppercase">Lead Time Efficiency</span>
                            <span class="fw-800 text-dark">{{ $averageLeadTime }} Days</span>
                        </div>
                        <div class="progress bg-light" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="rating-box p-3 bg-gold-soft rounded-16 text-center">
                        <p class="text-muted smaller fw-800 text-uppercase mb-2">Partner Reliability Rating</p>
                        <div class="text-gold fs-4">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= ($supplier->rating ?? 0) ? '' : 'opacity-20' }}"></i>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Ledger Tabs -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden">
        <div class="card-header bg-white border-0 p-0">
            <ul class="nav nav-tabs-premium" id="ledgerTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-800 text-uppercase small ls-1 px-5 py-4" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                        <i class="fas fa-receipt me-2"></i>Purchase Orders
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-800 text-uppercase small ls-1 px-5 py-4" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                        <i class="fas fa-money-bill-wave me-2"></i>Treasury Payments
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="ledgerTabsContent">
            <!-- Purchase Orders Tab -->
            <div class="tab-pane fade show active" id="orders" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover-premium align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-5 py-3 small text-uppercase fw-700">Reference</th>
                                <th class="py-3 small text-uppercase fw-700">Execution Date</th>
                                <th class="py-3 small text-uppercase fw-700">Valuation</th>
                                <th class="py-3 small text-uppercase fw-700">Status</th>
                                <th class="pe-5 py-3 small text-uppercase fw-700 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplier->purchaseOrders->take(10) as $order)
                                <tr>
                                    <td class="ps-5 py-4">
                                        <div class="fw-800 text-dark">{{ $order->po_number }}</div>
                                        <div class="text-muted smaller fw-600">Contractual Ref</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="fw-700 text-dark">{{ $order->po_date->format('d M, Y') }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="fw-800 text-dark">Rs. {{ number_format($order->total_amount, 2) }}</div>
                                    </td>
                                    <td class="py-4">
                                        <span class="badge bg-info-soft text-info rounded-pill px-3 py-2 fw-800 smaller">{{ strtoupper($order->status) }}</span>
                                    </td>
                                    <td class="pe-5 py-4 text-center">
                                        <a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-icon-premium">
                                            <i class="fas fa-search-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center">
                                        <p class="text-muted fw-600 mb-0">No purchase records found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Treasury Payments Tab -->
            <div class="tab-pane fade" id="payments" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover-premium align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-5 py-3 small text-uppercase fw-700">Clearance Date</th>
                                <th class="py-3 small text-uppercase fw-700">Protocol</th>
                                <th class="py-3 small text-uppercase fw-700">Disbursement</th>
                                <th class="py-3 small text-uppercase fw-700">Reference</th>
                                <th class="pe-5 py-3 small text-uppercase fw-700 text-center">Audit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplier->payments->take(10) as $payment)
                                <tr>
                                    <td class="ps-5 py-4">
                                        <div class="fw-800 text-dark">{{ $payment->payment_date->format('d M, Y') }}</div>
                                    </td>
                                    <td class="py-4">
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-800 smaller">{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    </td>
                                    <td class="py-4">
                                        <div class="fw-900 text-success">Rs. {{ number_format($payment->amount_paid, 2) }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="fw-700 text-dark small">{{ $payment->reference_number ?? 'Internal Transfer' }}</div>
                                    </td>
                                    <td class="pe-5 py-4 text-center">
                                        <button class="btn btn-icon-premium">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center">
                                        <p class="text-muted fw-600 mb-0">No treasury movements found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Styling */
    .fw-900 { font-weight: 900; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-16 { border-radius: 16px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-white-5 { background: rgba(255, 255, 255, 0.05); }
    .border-white-10 { border: 1px solid rgba(255, 255, 255, 0.1); }
    
    .text-gold { color: #d4af37; }
    .text-danger-soft-text { color: #dc3545; }
    .text-white-50 { color: rgba(255,255,255,0.5); }
    .text-light-gray { color: #e0e0e0; }
    
    .shadow-premium { box-shadow: 0 15px 50px -12px rgba(0,0,0,0.15); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4); color: #1a1a1a; }

    .btn-white { background: #fff; border: 1px solid #f0f0f0; transition: all 0.3s ease; }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

    .btn-premium-dark { background: #1a1a1a; color: #fff; border: none; transition: all 0.3s ease; }
    .btn-premium-dark:hover { background: #000; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-2px); color: #fff; }

    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.001); }
    
    .btn-icon-premium {
        width: 38px; height: 38px; border-radius: 12px; display: inline-flex;
        align-items: center; justify-content: center; background: #f8f9fa;
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-icon-premium:hover { background: #1a1a1a; color: #fff; transform: rotate(10deg); }

    .nav-tabs-premium { border-bottom: 1px solid #f0f0f0; display: flex; }
    .nav-tabs-premium .nav-link { 
        border: none; color: #666; position: relative; 
        transition: all 0.3s ease; border-right: 1px solid #f8f9fa;
    }
    .nav-tabs-premium .nav-link.active { 
        color: #1a1a1a; background: #fff;
    }
    .nav-tabs-premium .nav-link.active::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0;
        height: 3px; background: #d4af37;
    }

    .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
    .card-bg-icon { position: absolute; bottom: -20px; right: -10px; font-size: 80px; pointer-events: none; transition: all 0.5s ease; }
    
    .smaller { font-size: 0.75rem; }
</style>
@endsection
