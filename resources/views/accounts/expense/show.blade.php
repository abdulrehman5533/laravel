@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Expense Audit Detail</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.expense.index') }}" class="text-decoration-none text-muted">Expenses</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">View #EXP-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <button onclick="window.print()" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-print me-2 text-secondary"></i>Print Voucher
                </button>
                @if($expense->status === 'pending')
                    <a href="{{ route('accounts.expense.edit', $expense->id) }}" class="btn btn-warning shadow-sm px-4 py-2 rounded-12 fw-700">
                        <i class="fas fa-edit me-2"></i>Edit Record
                    </a>
                @endif
                <a href="{{ route('accounts.expense.index') }}" class="btn btn-premium-dark shadow-premium px-4 py-2 rounded-12 fw-700 text-white">
                    <i class="fas fa-arrow-left me-2 text-gold"></i>Back to Registry
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-15 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 print-none">
        <!-- Main Audit Column -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-0 border-0">
                    <div class="px-4 py-3 d-flex justify-content-between align-items-center border-bottom border-white-10">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('assets/images/logo-gold.png') }}" alt="Logo" height="30" class="me-3 opacity-75">
                            <span class="text-white-50 fw-700 small tracking-wider">OFFICIAL EXPENSE VOUCHER</span>
                        </div>
                        <span class="badge bg-gold text-dark fw-800 rounded-pill px-3 py-2">
                            REF: #EXP-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <!-- Voucher Header -->
                    <div class="row mb-5 align-items-center">
                        <div class="col-md-6">
                            <h2 class="fw-800 text-dark mb-1">Rs.{{ number_format($expense->amount, 2) }}</h2>
                            <p class="text-muted fw-600 text-uppercase tracking-wider mb-0">Total Reimbursable Amount</p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="d-inline-block p-3 rounded-20 {{ $expense->status === 'approved' ? 'bg-success-soft text-success' : ($expense->status === 'pending' ? 'bg-warning-soft text-warning' : 'bg-danger-soft text-danger') }}">
                                <div class="d-flex align-items-center">
                                    <i class="fas {{ $expense->status === 'approved' ? 'fa-check-double' : ($expense->status === 'pending' ? 'fa-clock' : 'fa-times-circle') }} fs-3 me-3"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-800 text-uppercase small tracking-wider">{{ $expense->status }}</h6>
                                        <span class="small fw-600 opacity-75">{{ $expense->approved_at ? $expense->approved_at->format('M d, Y') : 'Pending Verification' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="p-4 rounded-20 bg-light border border-white">
                                <label class="text-muted small fw-700 text-uppercase tracking-wider mb-2 d-block">Transaction Identity</label>
                                <div class="mb-3">
                                    <span class="text-muted small">Category:</span>
                                    <span class="d-block fw-800 text-dark fs-5">{{ optional($expense->category)->name }}</span>
                                    <span class="text-primary small fw-600">{{ optional($expense->subcategory)->name ?? 'General' }}</span>
                                </div>
                                <div>
                                    <span class="text-muted small">Merchant / Payee:</span>
                                    <span class="d-block fw-700 text-dark">{{ $expense->vendor_name ?? 'Internal Operation' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-20 bg-light border border-white h-100">
                                <label class="text-muted small fw-700 text-uppercase tracking-wider mb-2 d-block">Origin & Method</label>
                                <div class="mb-3">
                                    <span class="text-muted small">Business Unit:</span>
                                    <span class="d-block fw-700 text-dark"><i class="fas fa-map-marker-alt text-gold me-2"></i>{{ optional($expense->branch)->name }}</span>
                                </div>
                                <div>
                                    <span class="text-muted small">Payment Instrument:</span>
                                    <span class="d-block fw-700 text-dark text-capitalize"><i class="fas fa-credit-card text-gold me-2"></i>{{ str_replace('_', ' ', $expense->payment_method) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-5">
                        <label class="text-muted small fw-700 text-uppercase tracking-wider mb-3 d-block">Expenditure Justification</label>
                        <div class="p-4 rounded-20 bg-white border">
                            <p class="mb-0 fw-600 text-secondary lh-lg">
                                {{ $expense->description ?: 'No additional description provided for this transaction.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div>
                        <label class="text-muted small fw-700 text-uppercase tracking-wider mb-4 d-block">Audit Timeline</label>
                        <div class="timeline-premium">
                            <div class="timeline-step active">
                                <div class="timeline-icon bg-primary shadow-sm">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="fw-800 mb-0">Record Created</h6>
                                    <p class="text-muted small mb-0">By {{ optional($expense->createdBy)->name }} on {{ $expense->created_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                            
                            @if($expense->status !== 'pending')
                                <div class="timeline-step active">
                                    <div class="timeline-icon bg-{{ $expense->status === 'approved' ? 'success' : 'danger' }} shadow-sm">
                                        <i class="fas {{ $expense->status === 'approved' ? 'fa-check' : 'fa-times' }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-800 mb-0">Verification {{ ucfirst($expense->status) }}</h6>
                                        <p class="text-muted small mb-0">By {{ optional($expense->approvedBy)->name }} on {{ $expense->approved_at?->format('M d, Y \a\t h:i A') }}</p>
                                        @if($expense->rejection_reason)
                                            <div class="mt-2 p-3 bg-danger-soft text-danger rounded-12 border border-danger-10">
                                                <i class="fas fa-exclamation-triangle me-2"></i><strong>Reason:</strong> {{ $expense->rejection_reason }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="timeline-step">
                                    <div class="timeline-icon bg-light text-muted">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-800 text-muted mb-0">Awaiting Final Review</h6>
                                        <p class="text-muted small mb-0">Currently in the approval queue</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Support Documentation -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-5">
                <div class="card-header bg-white p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-info-soft text-info rounded-10 me-3">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Support Documentation</h5>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5 pt-0">
                    @if($expense->attachments->count())
                        <div class="row g-4">
                            @foreach($expense->attachments as $attachment)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border rounded-20 bg-light-hover transition-all group overflow-hidden">
                                        @if(str_contains($attachment->file_type, 'image'))
                                            <div class="position-relative">
                                                <img src="{{ asset('storage/' . $attachment->file_path) }}" 
                                                     class="card-img-top img-fluid" 
                                                     style="height: 160px; object-fit: cover;" 
                                                     alt="{{ $attachment->original_name }}">
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <span class="badge bg-dark-soft backdrop-blur text-white small">Image</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-white" style="height: 160px;">
                                                <i class="fas {{ str_contains($attachment->file_type, 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-alt text-primary' }} fa-4x opacity-25"></i>
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <span class="badge bg-dark-soft backdrop-blur text-white small">Document</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="overflow-hidden me-2">
                                                    <p class="mb-0 fw-800 text-dark text-truncate small" title="{{ $attachment->original_name }}">
                                                        {{ $attachment->original_name }}
                                                    </p>
                                                    <span class="text-muted smaller text-uppercase fw-700">{{ explode('/', $attachment->file_type)[1] ?? $attachment->file_type }}</span>
                                                </div>
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                                   class="btn btn-icon btn-white shadow-sm flex-shrink-0" target="_blank">
                                                    <i class="fas fa-external-link-alt text-gold"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 bg-light rounded-20 border-2 border-dashed">
                            <i class="fas fa-file-invoice fa-3x text-light mb-3"></i>
                            <p class="text-muted fw-600 mb-0">No supporting documents attached to this record.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-xl-4 print-none">
            <!-- Summary Stats Card -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-800 text-muted text-uppercase tracking-wider small mb-4">Financial Overview</h6>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-600">Base Amount</span>
                        <span class="fw-800 text-dark">Rs.{{ number_format($expense->amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-600">Tax Component</span>
                        <span class="fw-800 text-success">Rs.0.00</span>
                    </div>
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-dark fw-800 fs-5">Total Liability</span>
                        <span class="fw-800 text-gold fs-4">Rs.{{ number_format($expense->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Approval Controls -->
            @if($expense->status === 'pending')
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <h5 class="text-white mb-0 fw-800">Review Actions</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('accounts.expense.approve', $expense->id) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-gold w-100 py-3 rounded-15 fw-800 shadow-gold">
                                <i class="fas fa-check-circle me-2"></i>Confirm Approval
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger w-100 py-3 rounded-15 fw-700" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times-circle me-2"></i>Reject Transaction
                        </button>
                    </div>
                </div>
            @endif

            <!-- Quick Meta Card -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden">
                <div class="card-body p-4">
                    <h6 class="fw-800 text-muted text-uppercase tracking-wider small mb-4">System Metadata</h6>
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <div class="p-2 bg-light rounded-10 me-3">
                                <i class="fas fa-fingerprint text-muted"></i>
                            </div>
                            <div>
                                <span class="text-muted smaller d-block fw-700 text-uppercase tracking-wider">Internal UUID</span>
                                <span class="fw-800 text-dark small">{{ $expense->id }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <div class="p-2 bg-light rounded-10 me-3">
                                <i class="fas fa-history text-muted"></i>
                            </div>
                            <div>
                                <span class="text-muted smaller d-block fw-700 text-uppercase tracking-wider">Registry Date</span>
                                <span class="fw-800 text-dark small">{{ $expense->created_at->format('M d, Y, h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex align-items-start">
                            <div class="p-2 bg-light rounded-10 me-3">
                                <i class="fas fa-user-shield text-muted"></i>
                            </div>
                            <div>
                                <span class="text-muted smaller d-block fw-700 text-uppercase tracking-wider">Custodian</span>
                                <span class="fw-800 text-dark small">{{ optional($expense->createdBy)->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-24">
            <form action="{{ route('accounts.expense.reject', $expense->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-800 text-dark">Audit Rejection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <p class="text-muted fw-600 mb-4">Please specify the reason for rejecting this transaction. This will be recorded in the audit trail.</p>
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control border-light bg-light rounded-15 p-3 @error('rejection_reason') is-invalid @enderror" 
                              rows="4" required maxlength="500" placeholder="e.g. Incomplete documentation, incorrect amount..."></textarea>
                    @error('rejection_reason')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-12 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 py-2 rounded-12 fw-700 shadow-sm">Reject Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    .border-white-10 { border-color: rgba(255,255,255,0.1) !important; }
    
    .shadow-premium { box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .shadow-gold { box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3); }
    
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    
    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(212, 175, 55, 0.5);
    }

    .btn-premium-dark {
        background: #1a1a1a;
        color: #fff;
        border: none;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.1rem;
    }

    .timeline-premium {
        position: relative;
        padding-left: 32px;
    }
    .timeline-premium::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #f0f0f0;
    }
    .timeline-step {
        position: relative;
        padding-bottom: 30px;
    }
    .timeline-icon {
        position: absolute;
        left: -32px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: #fff;
        z-index: 1;
    }
    .timeline-content {
        padding-top: 5px;
    }

    .bg-light-hover:hover {
        background: #f8f9fa !important;
        transform: translateY(-2px);
    }
    .group:hover .group-hover-gold {
        background: #d4af37 !important;
    }
    .group:hover .group-hover-gold i {
        color: #1a1a1a !important;
    }

    .btn-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 12px;
    }

    @media print {
        .print-none, .btn, nav, .sidebar, .navbar, .modal, .card-header .btn-group {
            display: none !important;
        }
        body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .container-fluid {
            padding: 0 !important;
            width: 100% !important;
        }
        .printable-voucher {
            display: block !important;
        }
    }

    /* Ultra-Professional Market-Ready Expense Voucher Styles */
    .printable-voucher {
        display: none;
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        padding: 0;
        color: #000;
        background: #fff;
        font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    }
    
    .voucher-copy {
        position: relative;
        padding: 40px;
        border: 1px solid #eee;
        page-break-after: always;
        min-height: 500px;
    }

    .voucher-copy:last-child {
        page-break-after: auto;
        border-top: 2px dashed #ccc;
        margin-top: 20px;
        padding-top: 60px;
    }

    .copy-indicator {
        position: absolute;
        top: 20px;
        right: 40px;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        color: #999;
        letter-spacing: 2px;
        border: 1px solid #eee;
        padding: 4px 12px;
        border-radius: 4px;
    }

    .voucher-header-top {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .brand-section h1 {
        font-size: 24px;
        font-weight: 900;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .brand-section p {
        font-size: 11px;
        color: #555;
        margin: 0;
        line-height: 1.4;
    }

    .voucher-title-section {
        text-align: right;
    }

    .voucher-title-section h2 {
        font-size: 32px;
        font-weight: 900;
        color: #d4af37;
        margin: 0;
        line-height: 1;
    }

    .voucher-title-section .ref-no {
        font-size: 14px;
        font-weight: 700;
        margin-top: 5px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        border: 2px solid #000;
        margin-bottom: 30px;
    }

    .summary-item {
        padding: 15px;
        border-right: 1px solid #000;
    }

    .summary-item:last-child {
        border-right: none;
        background: #f8f9fa;
    }

    .summary-item label {
        display: block;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 5px;
    }

    .summary-item span {
        font-size: 14px;
        font-weight: 700;
    }

    .main-details-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    .main-details-table th, .main-details-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .main-details-table th {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #666;
        background: #fafafa;
        width: 180px;
    }

    .main-details-table td {
        font-size: 14px;
        font-weight: 600;
    }

    .amount-in-words {
        font-style: italic;
        font-size: 12px;
        color: #555;
        margin-bottom: 30px;
        padding: 10px 15px;
        background: #fffcf0;
        border: 1px solid #f1e5ac;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: 60px;
    }

    .signature-block {
        text-align: center;
    }

    .signature-line {
        border-top: 1.5px solid #000;
        margin-bottom: 8px;
    }

    .signature-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .signature-name {
        font-size: 10px;
        color: #666;
    }

    .verification-qr {
        position: absolute;
        bottom: 150px;
        right: 40px;
        text-align: center;
    }

    .qr-placeholder {
        width: 80px;
        height: 80px;
        border: 1px solid #eee;
        padding: 5px;
        margin-bottom: 5px;
        background: #fff;
    }

    .watermark-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 120px;
        font-weight: 900;
        color: rgba(0,0,0,0.03);
        pointer-events: none;
        white-space: nowrap;
        z-index: 0;
    }

    .cut-line {
        text-align: center;
        margin: 20px 0;
        position: relative;
    }

    .cut-line i {
        background: #fff;
        padding: 0 10px;
        position: relative;
        z-index: 1;
        color: #ccc;
    }

    .status-stamp {
        position: absolute;
        top: 150px;
        left: 50%;
        transform: translateX(-50%) rotate(-15deg);
        border: 4px solid;
        padding: 10px 30px;
        font-size: 40px;
        font-weight: 900;
        text-transform: uppercase;
        border-radius: 8px;
        opacity: 0.15;
    }
</style>

<!-- Dedicated Printable Voucher (Market Ready Best) -->
<div class="printable-voucher">
    @foreach(['Office Copy', 'Accountant Copy'] as $copyTitle)
    <div class="voucher-copy">
        <div class="watermark-text">JEWELLERY PRO</div>
        <div class="copy-indicator">{{ $copyTitle }}</div>
        
        @if($expense->status === 'approved')
            <div class="status-stamp" style="color: #10b981; border-color: #10b981;">PAID</div>
        @elseif($expense->status === 'rejected')
            <div class="status-stamp" style="color: #ef4444; border-color: #ef4444;">VOID</div>
        @endif

        <div class="voucher-header-top">
            <div class="brand-section">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ asset('Favicon.jpg') }}" alt="Logo" height="40" class="me-2">
                    <h1>JEWELLERY PRO</h1>
                </div>
                <p><strong>Branch:</strong> {{ optional($expense->branch)->name }}</p>
                <p>{{ optional($expense->branch)->address }}</p>
                <p><strong>Contact:</strong> {{ optional($expense->branch)->phone }} | {{ optional($expense->branch)->email }}</p>
            </div>
            <div class="voucher-title-section">
                <h2>CASH VOUCHER</h2>
                <div class="ref-no">No: #EXP-{{ str_pad($expense->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="small fw-bold text-muted mt-1">Date: {{ $expense->date->format('d-M-Y') }}</div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <label>Category</label>
                <span>{{ optional($expense->category)->name }}</span>
            </div>
            <div class="summary-item">
                <label>Payment Method</label>
                <span class="text-capitalize">{{ str_replace('_', ' ', $expense->payment_method) }}</span>
            </div>
            <div class="summary-item" style="border-right: none;">
                <label>Amount (PKR)</label>
                <span class="fs-4">{{ number_format($expense->amount, 2) }}</span>
            </div>
        </div>

        <table class="main-details-table">
            <tr>
                <th>Paid To / Vendor</th>
                <td>{{ $expense->vendor_name ?? 'Internal Operation' }}</td>
            </tr>
            <tr>
                <th>Sub-Classification</th>
                <td>{{ optional($expense->subcategory)->name ?? 'General Expenditure' }}</td>
            </tr>
            <tr>
                <th>Narrative / Purpose</th>
                <td style="line-height: 1.6;">{{ $expense->description ?: 'Operational expenditure for the jewelry management workflow, verified and approved according to financial guidelines.' }}</td>
            </tr>
        </table>

        <div class="amount-in-words">
            <strong>Amount in Words:</strong> {{ \App\Helpers\CurrencyHelper::spellOut($expense->amount) }} Pakistani Rupees Only
        </div>

        <div class="verification-qr">
            <div class="qr-placeholder">
                <i class="fas fa-qrcode fa-4x" style="color: #eee;"></i>
            </div>
            <div style="font-size: 8px; font-weight: 800; color: #999;">SCAN TO VERIFY</div>
        </div>

        <div class="signature-grid">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Prepared By</div>
                <div class="signature-name">{{ optional($expense->createdBy)->name }}</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Authorized By</div>
                <div class="signature-name">{{ optional($expense->approvedBy)->name ?? 'Internal Auditor' }}</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Receiver Signature</div>
                <div class="signature-name">&nbsp;</div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center; font-size: 9px; color: #bbb; letter-spacing: 1px;">
            COMPUTER GENERATED DOCUMENT | JEWELLERY PRO FINANCIAL CONTROL SYSTEM
        </div>
    </div>
    
    @if($loop->first)
    <div class="cut-line">
        <i class="fas fa-cut"></i>
    </div>
    @endif
    @endforeach
</div>
@endsection
