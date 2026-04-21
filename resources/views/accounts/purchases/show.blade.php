@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Procurement Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.purchases.orders.index') }}" class="text-decoration-none text-muted">Supply Chain</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">PO-{{ $purchaseOrder->po_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.purchases.orders.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Hub
                </a>
                
                @if($purchaseOrder->status === 'Draft')
                    <a href="{{ route('accounts.purchases.orders.edit', $purchaseOrder) }}" class="btn btn-light-warning shadow-sm px-4 py-2 rounded-12 fw-700">
                        <i class="fas fa-edit me-2"></i>Edit Order
                    </a>
                    <form method="POST" action="{{ route('accounts.purchases.orders.confirm', $purchaseOrder) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                            <i class="fas fa-check-double me-2"></i>Confirm Order
                        </button>
                    </form>
                @endif

                @if($purchaseOrder->status !== 'Received' && $purchaseOrder->status !== 'Cancelled')
                    <form method="POST" action="{{ route('accounts.purchases.orders.cancel', $purchaseOrder) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this procurement?');">
                        @csrf
                        <button type="submit" class="btn btn-light-danger shadow-sm px-4 py-2 rounded-12 fw-700 text-danger">
                            <i class="fas fa-times me-2"></i>Cancel Procurement
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Audit Column -->
        <div class="col-lg-8">
            <!-- Strategic Overview -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-gold rounded-10 me-3">
                                <i class="fas fa-shield-alt text-dark"></i>
                            </div>
                            <h5 class="mb-0 text-white fw-800">Procurement Dossier</h5>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-white-10 text-gold border-0 px-3 py-2 rounded-pill fw-700">
                                <i class="fas fa-hashtag me-1"></i> {{ $purchaseOrder->po_number }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <div class="audit-group mb-4">
                                <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Strategic Partner</label>
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-light rounded-15 me-3 text-primary">
                                        <i class="fas fa-handshake fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-800 text-dark mb-0">{{ $purchaseOrder->supplier->name ?? 'Direct Sourcing' }}</h5>
                                        <span class="text-muted small">Partner ID: #{{ str_pad($purchaseOrder->supplier->id ?? 0, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="audit-group">
                                <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Material Classification</label>
                                <div class="p-3 bg-light rounded-15">
                                    <div class="fw-700 text-dark d-flex align-items-center">
                                        <i class="fas fa-gem text-gold me-2"></i>
                                        {{ $purchaseOrder->material_type }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-4">
                                <div class="col-6">
                                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Issuance Date</label>
                                    <div class="fw-700 text-dark">{{ $purchaseOrder->po_date->format('d M, Y') }}</div>
                                    <div class="text-muted small">{{ $purchaseOrder->po_date->diffForHumans() }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Target Logistics</label>
                                    <div class="fw-700 text-primary">{{ $purchaseOrder->expected_delivery_date->format('d M, Y') }}</div>
                                    <div class="text-muted small">Planned Arrival</div>
                                </div>
                                @if($purchaseOrder->actual_delivery_date)
                                    <div class="col-12">
                                        <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Actual Logistics</label>
                                        <div class="fw-700 text-success">
                                            <i class="fas fa-truck-loading me-1"></i>
                                            {{ $purchaseOrder->actual_delivery_date->format('d M, Y') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($purchaseOrder->description)
                        <div class="mt-5 p-4 bg-light-soft rounded-20 border-start border-gold border-4">
                            <label class="text-muted small fw-800 text-uppercase tracking-wider mb-2 d-block">Executive Brief</label>
                            <p class="mb-0 text-dark fw-600 italic">"{{ $purchaseOrder->description }}"</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Itemized Inventory -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-primary-soft rounded-10 me-3">
                            <i class="fas fa-list-check text-primary"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Inventory Manifest</h5>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light-soft">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Inventory Component</th>
                                <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0 text-center">Unit Volume</th>
                                <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0 text-end">Unit Value</th>
                                <th class="pe-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0 text-end">Net Contribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-700 text-dark">{{ $item->item_name }}</div>
                                        <div class="text-muted small">SKU Reference: #SKU-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark fw-800 px-3 py-2 rounded-pill">{{ $item->quantity }} Units</span>
                                    </td>
                                    <td class="text-end fw-600">Rs.{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="pe-4 text-end fw-800 text-dark">Rs.{{ number_format($item->line_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Intelligence -->
            @if($purchaseOrder->payments->count())
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden">
                    <div class="card-header bg-white p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-success-soft rounded-10 me-3">
                                <i class="fas fa-receipt text-success"></i>
                            </div>
                            <h5 class="mb-0 fw-800">Treasury Activity</h5>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light-soft">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Transaction Hub</th>
                                    <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Channel</th>
                                    <th class="py-3 text-uppercase tracking-wider small fw-800 text-muted border-0 text-end">Settlement Value</th>
                                    <th class="pe-4 py-3 text-uppercase tracking-wider small fw-800 text-muted border-0">Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->payments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-700 text-dark">{{ $payment->payment_date->format('d M, Y') }}</div>
                                            <div class="text-muted small">{{ $payment->payment_date->diffForHumans() }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-700">
                                                <i class="fas fa-university me-1"></i> {{ strtoupper($payment->payment_method) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-800 text-dark">Rs.{{ number_format($payment->amount, 2) }}</td>
                                        <td class="pe-4">
                                            <span class="text-muted small fw-600">{{ $payment->reference_number ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Intelligence -->
        <div class="col-lg-4">
            <!-- Financial Projection -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <h5 class="mb-0 text-white fw-800">Financial Liquidation</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-600">Sub-Total Protocol</span>
                        <span class="text-dark fw-800">Rs.{{ number_format($purchaseOrder->sub_total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-600">GST Assessment ({{ $purchaseOrder->gst_percentage }}%)</span>
                        <span class="text-dark fw-800">Rs.{{ number_format($purchaseOrder->gst_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-600">Trade Discount ({{ $purchaseOrder->discount_percentage }}%)</span>
                        <span class="text-danger fw-800">-Rs.{{ number_format($purchaseOrder->discount_amount, 2) }}</span>
                    </div>
                    @if($purchaseOrder->other_charges > 0)
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted fw-600">Auxiliary Charges</span>
                            <span class="text-dark fw-800">Rs.{{ number_format($purchaseOrder->other_charges, 2) }}</span>
                        </div>
                    @endif
                    <hr class="my-4 border-light">
                    <div class="p-3 bg-light-soft rounded-15 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase small fw-800 text-muted">Net Procurement Value</span>
                            <span class="h4 mb-0 fw-900 text-primary">Rs.{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="p-3 bg-success-soft rounded-15 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-700 text-success">Treasury Settled</span>
                                <span class="fw-800 text-success">Rs.{{ number_format($purchaseOrder->amount_paid, 2) }}</span>
                            </div>
                        </div>
                        <div class="p-3 bg-danger-soft rounded-15">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-700 text-danger">Outstanding Exposure</span>
                                <span class="fw-800 text-danger">Rs.{{ number_format($purchaseOrder->amount_due, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lifecycle Status -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-body p-4 text-center">
                    <label class="text-muted small fw-800 text-uppercase tracking-wider mb-3 d-block">Operational Lifecycle</label>
                    <div class="mb-4">
                        @switch($purchaseOrder->status)
                            @case('Draft')
                                <div class="p-3 bg-secondary-soft rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-secondary"></i>
                                </div>
                                <h5 class="fw-800 text-secondary mb-1">PROPOSAL PHASE</h5>
                                <p class="text-muted small">Awaiting internal validation and authorization</p>
                                @break
                            @case('Confirmed')
                                <div class="p-3 bg-info-soft rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-check-circle fa-2x text-info"></i>
                                </div>
                                <h5 class="fw-800 text-info mb-1">LOGISTICS ACTIVE</h5>
                                <p class="text-muted small">Procurement authorized; awaiting partner fulfillment</p>
                                @break
                            @case('Received')
                                <div class="p-3 bg-success-soft rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-warehouse fa-2x text-success"></i>
                                </div>
                                <h5 class="fw-800 text-success mb-1">ASSET SECURED</h5>
                                <p class="text-muted small">Inventory successfully ingested into central reserves</p>
                                @break
                            @default
                                <div class="p-3 bg-danger-soft rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                                <h5 class="fw-800 text-danger mb-1">TERMINATED</h5>
                                <p class="text-muted small">Operational flow has been archived or cancelled</p>
                        @endswitch
                    </div>
                    
                    @if($purchaseOrder->is_overdue)
                        <div class="alert bg-danger text-white rounded-15 border-0 p-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span class="fw-700">Logistics Breach: {{ $purchaseOrder->days_overdue }} Days</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audit Trail -->
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
                                <div class="text-muted small">{{ $purchaseOrder->created_at->format('d M, Y | H:i') }}</div>
                                <div class="text-muted small">Auth: {{ optional($purchaseOrder->createdBy)->name }}</div>
                            </div>
                        </div>
                        @if($purchaseOrder->updated_at != $purchaseOrder->created_at)
                            <div class="d-flex">
                                <div class="timeline-dot bg-gold me-3 mt-1"></div>
                                <div>
                                    <div class="fw-800 text-dark small">LAST INTELLIGENCE UPDATE</div>
                                    <div class="text-muted small">{{ $purchaseOrder->updated_at->format('d M, Y | H:i') }}</div>
                                    <div class="text-muted small">System synchronized</div>
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
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.1); }
    
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    .shadow-gold { box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
    
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    
    .fw-900 { font-weight: 900; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    
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
    
    .btn-light-warning { background: #fff8e1; color: #ffa000; border: 1px solid #ffecb3; }
    .btn-light-danger { background: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2; }
    
    .audit-group label {
        letter-spacing: 1px;
    }
    
    .table thead th {
        font-size: 0.75rem;
        border: none;
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
