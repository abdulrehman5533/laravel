@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-eye text-warning me-2"></i>Purchase Return Details
            </h1>
            <p class="text-muted mt-1">View details for return {{ $purchaseReturn->return_number }}</p>
        </div>
        <div class="btn-group">
            @if($purchaseReturn->status === 'Draft' || $purchaseReturn->status === 'Pending')
                @if(Auth::user()->can('manage-accounts'))
                    <form action="{{ route('accounts.purchases.returns.approve', $purchaseReturn) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this return?')">
                            <i class="fas fa-check"></i> Approve Return
                        </button>
                    </form>
                @endif
                <a href="{{ route('accounts.purchases.returns.edit', $purchaseReturn) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
            
            @if($purchaseReturn->status === 'Approved' && Auth::user()->can('manage-accounts'))
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="fas fa-money-bill-wave"></i> Process Refund
                </button>
            @endif

            <a href="{{ route('accounts.purchases.returns.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Return Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Return Number</label>
                            <span class="h5 fw-bold text-dark">{{ $purchaseReturn->return_number }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Status</label>
                            @switch($purchaseReturn->status)
                                @case('Draft')
                                    <span class="badge bg-secondary p-2">Draft</span>
                                    @break
                                @case('Pending')
                                    <span class="badge bg-warning text-dark p-2">Pending Approval</span>
                                    @break
                                @case('Approved')
                                    <span class="badge bg-info p-2">Approved</span>
                                    @break
                                @case('Completed')
                                    <span class="badge bg-success p-2">Completed / Refunded</span>
                                    @break
                                @case('Cancelled')
                                    <span class="badge bg-danger p-2">Cancelled</span>
                                    @break
                            @endswitch
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Supplier</label>
                            <h6 class="fw-bold">{{ $purchaseReturn->supplier->name ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Purchase Order</label>
                            @if($purchaseReturn->purchaseOrder)
                                <a href="{{ route('accounts.purchases.orders.show', $purchaseReturn->purchaseOrder) }}" class="h6 fw-bold text-decoration-none">
                                    {{ $purchaseReturn->purchaseOrder->po_number }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Return Date</label>
                            <h6 class="fw-bold">{{ $purchaseReturn->return_date->format('d M Y') }}</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Return Reason</label>
                            <h6 class="fw-bold">{{ str_replace('_', ' ', $purchaseReturn->return_reason) }}</h6>
                        </div>
                        
                        <div class="col-12 border-top pt-4 mt-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Subtotal</label>
                                    <h6 class="fw-bold">Rs.{{ number_format($purchaseReturn->subtotal, 2) }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">GST ({{ $purchaseReturn->gst_percentage }}%)</label>
                                    <h6 class="fw-bold">Rs.{{ number_format($purchaseReturn->gst_amount, 2) }}</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Total Return Amount</label>
                                    <h5 class="fw-bold text-danger">Rs.{{ number_format($purchaseReturn->return_amount, 2) }}</h5>
                                </div>
                            </div>
                        </div>

                        @if($purchaseReturn->refunded_amount > 0)
                            <div class="col-12">
                                <div class="alert alert-success d-flex justify-content-between align-items-center">
                                    <span><strong>Refunded Amount:</strong> Rs.{{ number_format($purchaseReturn->refunded_amount, 2) }}</span>
                                    <span><strong>Status:</strong> {{ $purchaseReturn->refund_status }}</span>
                                </div>
                            </div>
                        @endif

                        @if($purchaseReturn->return_reason_details)
                            <div class="col-12">
                                <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Reason Details</label>
                                <div class="bg-light p-3 rounded text-muted">
                                    {{ $purchaseReturn->return_reason_details }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Audit Trail</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-0 text-uppercase fw-bold">Initiated By</p>
                                    <h6 class="mb-0 fw-bold">{{ $purchaseReturn->creator->name ?? 'System' }}</h6>
                                </div>
                                <span class="text-muted small">{{ $purchaseReturn->created_at->format('d M, H:i') }}</span>
                            </div>
                        </li>
                        @if($purchaseReturn->approved_by)
                            <li class="list-group-item px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted small mb-0 text-uppercase fw-bold">Approved By</p>
                                        <h6 class="mb-0 fw-bold text-success">{{ $purchaseReturn->approver->name ?? 'N/A' }}</h6>
                                    </div>
                                    <span class="text-muted small">{{ $purchaseReturn->approved_at ? $purchaseReturn->approved_at->format('d M, H:i') : '' }}</span>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
@if($purchaseReturn->status === 'Approved')
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('accounts.purchases.returns.refund', $purchaseReturn) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" name="refund_amount" class="form-control" 
                                   value="{{ $purchaseReturn->return_amount }}" step="0.01" min="0" 
                                   max="{{ $purchaseReturn->return_amount }}" required>
                        </div>
                        <div class="form-text">Maximum refundable amount is Rs.{{ number_format($purchaseReturn->return_amount, 2) }}</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
