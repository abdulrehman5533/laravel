@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Settlement Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.purchases.payments.index') }}" class="text-decoration-none text-muted">Treasury</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">{{ $supplierPayment->payment_reference }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.purchases.payments.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2 text-secondary"></i>Registry
                </a>
                
                @if($supplierPayment->status === 'Pending' && Auth::user()->can('manage-accounts'))
                    <form action="{{ route('accounts.purchases.payments.approve', $supplierPayment) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700" onclick="return confirm('Authorize this disbursement?')">
                            <i class="fas fa-check-double me-2"></i>Authorize
                        </button>
                    </form>
                @endif

                @if($supplierPayment->status === 'Approved' && Auth::user()->can('manage-accounts'))
                    <form action="{{ route('accounts.purchases.payments.reverse', $supplierPayment) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-light-danger shadow-sm px-4 py-2 rounded-12 fw-700 text-danger" onclick="return confirm('Reverse this transaction?')">
                            <i class="fas fa-undo me-2"></i>Reverse Transaction
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Disbursement Dossier -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-gold rounded-10 me-3">
                                <i class="fas fa-receipt text-dark"></i>
                            </div>
                            <h5 class="mb-0 text-white fw-800">Settlement Voucher</h5>
                        </div>
                        <span class="badge bg-white-10 text-gold border-0 px-3 py-2 rounded-pill fw-700">
                            REF: {{ $supplierPayment->payment_reference }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <div class="audit-group mb-4">
                                <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Strategic Partner</label>
                                <div class="d-flex align-items-center p-3 bg-light rounded-20">
                                    <div class="p-3 bg-white rounded-circle shadow-sm me-3 text-primary">
                                        <i class="fas fa-handshake fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-800 text-dark mb-0">{{ $supplierPayment->supplier->name ?? 'N/A' }}</h6>
                                        <span class="text-muted small">{{ $supplierPayment->supplier->phone ?? 'Verified Partner' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($supplierPayment->purchaseOrder)
                                <div class="audit-group mb-4">
                                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Procurement Link</label>
                                    <a href="{{ route('accounts.purchases.orders.show', $supplierPayment->purchaseOrder) }}" class="text-decoration-none">
                                        <div class="p-3 bg-primary-soft rounded-20 d-flex align-items-center">
                                            <i class="fas fa-shopping-bag text-primary me-2"></i>
                                            <span class="fw-700 text-primary">PO-{{ $supplierPayment->purchaseOrder->po_number }}</span>
                                            <i class="fas fa-external-link-alt ms-auto small opacity-50 text-primary"></i>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="row g-4">
                                <div class="col-6">
                                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Settlement Date</label>
                                    <div class="fw-700 text-dark">{{ \Carbon\Carbon::parse($supplierPayment->payment_date)->format('d M, Y') }}</div>
                                    <div class="text-muted small">Transaction Cycle</div>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Protocol</label>
                                    <span class="badge bg-light text-dark fw-800 px-3 py-2 rounded-pill">
                                        {{ strtoupper(str_replace('_', ' ', $supplierPayment->payment_method)) }}
                                    </span>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Transaction Identifier</label>
                                    <div class="p-3 border-dashed rounded-15 bg-light-soft">
                                        <code class="text-primary fw-800">{{ $supplierPayment->transaction_id ?? 'INT-SEQ-'.str_pad($supplierPayment->id, 6, '0', STR_PAD_LEFT) }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($supplierPayment->payment_method === 'Cheque')
                        <div class="mt-5 p-4 bg-warning-soft rounded-24 border border-warning border-opacity-10">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-money-check fa-2x text-warning"></i>
                                </div>
                                <div class="col-md-11">
                                    <h6 class="fw-800 text-dark mb-1">Financial Instrument Details</h6>
                                    <div class="d-flex gap-4">
                                        <span class="small fw-700">Cheque #: <span class="text-dark">{{ $supplierPayment->cheque_number }}</span></span>
                                        <span class="small fw-700">Maturity Date: <span class="text-dark">{{ \Carbon\Carbon::parse($supplierPayment->cheque_date)->format('d M, Y') }}</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($supplierPayment->notes)
                        <div class="mt-5">
                            <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Audit Observations</label>
                            <div class="p-4 bg-light-soft rounded-20 italic text-dark fw-600">
                                "{{ $supplierPayment->notes }}"
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Intelligence -->
        <div class="col-lg-4">
            <!-- Financial Value -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-body p-5 text-center bg-premium-dark">
                    <label class="text-white-50 small fw-800 text-uppercase tracking-widest mb-3 d-block">Net Disbursement</label>
                    <h2 class="text-gold fw-900 mb-0">Rs.{{ number_format($supplierPayment->amount_paid, 2) }}</h2>
                    <div class="mt-3">
                        <span class="badge bg-white-10 text-white border-0 px-3 py-2 rounded-pill fw-600">
                            <i class="fas fa-shield-check me-1"></i> Treasury Authorized
                        </span>
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-body p-4 text-center">
                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-3 d-block">Verification Lifecycle</label>
                    <div class="mb-2">
                        @switch($supplierPayment->status)
                            @case('Pending')
                                <div class="p-3 bg-warning-soft rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-clock fa-xl text-warning"></i>
                                </div>
                                <h5 class="fw-800 text-warning mb-1">PENDING AUDIT</h5>
                                <p class="text-muted small">Disbursement awaiting treasury clearance</p>
                                @break
                            @case('Approved')
                                <div class="p-3 bg-success-soft rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-check-double fa-xl text-success"></i>
                                </div>
                                <h5 class="fw-800 text-success mb-1">SETTLED</h5>
                                <p class="text-muted small">Transaction finalized and assets transferred</p>
                                @break
                            @case('Reversed')
                                <div class="p-3 bg-secondary-soft rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-undo fa-xl text-secondary"></i>
                                </div>
                                <h5 class="fw-800 text-secondary mb-1">REVERSED</h5>
                                <p class="text-muted small">Transaction neutralized in general ledger</p>
                                @break
                            @default
                                <div class="p-3 bg-danger-soft rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-times-circle fa-xl text-danger"></i>
                                </div>
                                <h5 class="fw-800 text-danger mb-1">{{ strtoupper($supplierPayment->status) }}</h5>
                        @endswitch
                    </div>
                </div>
            </div>

            <!-- Audit footprint -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="mb-0 fw-800">Audit Footprint</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="timeline-intelligence">
                        <div class="d-flex mb-4">
                            <div class="timeline-dot bg-primary me-3 mt-1"></div>
                            <div>
                                <div class="fw-800 text-dark small">ORIGINATION</div>
                                <div class="text-muted small">{{ $supplierPayment->created_at->format('d M, Y | H:i') }}</div>
                                <div class="text-muted small">Auth: {{ $supplierPayment->creator->name ?? 'System' }}</div>
                            </div>
                        </div>
                        @if($supplierPayment->approved_by)
                            <div class="d-flex">
                                <div class="timeline-dot bg-success me-3 mt-1"></div>
                                <div>
                                    <div class="fw-800 text-success small">TREASURY AUTHORIZATION</div>
                                    <div class="text-muted small">{{ $supplierPayment->approved_at ? \Carbon\Carbon::parse($supplierPayment->approved_at)->format('d M, Y | H:i') : '' }}</div>
                                    <div class="text-muted small">Auth: {{ $supplierPayment->approver->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.1); }
    
    .text-gold { color: #d4af37 !important; }
    .shadow-gold { box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
    
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    
    .fw-900 { font-weight: 900; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    
    .timeline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        position: relative;
    }
    
    .timeline-dot::after {
        content: '';
        position: absolute;
        left: 4px;
        top: 10px;
        width: 2px;
        height: 30px;
        background: #eee;
    }
    
    .timeline-intelligence div:last-child .timeline-dot::after {
        display: none;
    }
    
    .border-dashed { border: 2px dashed #eee; }
    
    .btn-light-danger { background: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2; }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #ccc;
    }
</style>
@endsection
