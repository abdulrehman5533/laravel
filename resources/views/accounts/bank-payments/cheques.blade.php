@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('accounts.bank-payments.index') }}" class="btn btn-white btn-sm rounded-12 shadow-sm me-3 border-0">
                    <i class="fas fa-arrow-left text-gold"></i>
                </a>
                <div>
                    <h1 class="h2 mb-1 text-dark fw-800">Cheque Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Cheque Tracking</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('accounts.bank-payments.cheques.issue') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                <i class="fas fa-plus me-2"></i>Issue New Cheque
            </a>
        </div>
    </div>

    <!-- Status Filter & Metrics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-soft rounded-20 bg-white">
                <div class="card-body p-3">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-md-auto">
                            <label class="form-label mb-0 fw-700 text-muted small ps-2">FILTER BY STATUS</label>
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select border-0 bg-light rounded-12 fw-600 shadow-none">
                                <option value="">-- All Instruments --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                                <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                                <option value="bounced" {{ request('status') == 'bounced' ? 'selected' : '' }}>Bounced</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-premium-dark text-white px-4 rounded-12 fw-700 shadow-sm">
                                <i class="fas fa-filter me-2 tiny"></i>Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-3 mt-lg-0">
            <div class="card border-0 shadow-soft rounded-20 bg-premium-dark text-white overflow-hidden position-relative h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between position-relative z-2">
                    <div>
                        <p class="text-white-50 smaller fw-700 mb-0 tracking-wider">TOTAL OUTSTANDING</p>
                        <h4 class="fw-800 text-gold mb-0">Rs.{{ number_format($cheques->where('status', 'issued')->sum('amount'), 2) }}</h4>
                    </div>
                    <div class="activity-icon-box bg-white-10 text-gold">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="card-bg-icon"><i class="fas fa-money-check text-white opacity-5"></i></div>
            </div>
        </div>
    </div>

    <!-- Cheques Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-premium-dark text-white">
                    <tr>
                        <th class="ps-4 border-0 py-3 small text-uppercase tracking-wider">Cheque Details</th>
                        <th class="border-0 py-3 small text-uppercase tracking-wider">Bank Account</th>
                        <th class="border-0 py-3 small text-uppercase tracking-wider">Payee</th>
                        <th class="border-0 py-3 small text-uppercase tracking-wider">Amount</th>
                        <th class="border-0 py-3 small text-uppercase tracking-wider text-center">Status</th>
                        <th class="pe-4 border-0 py-3 small text-uppercase tracking-wider text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $cheque)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="cheque-icon bg-light text-muted rounded-8 p-2 me-3 shadow-inner">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">#{{ $cheque->cheque_number }}</div>
                                        <small class="text-muted fw-600">{{ $cheque->cheque_date ? \Carbon\Carbon::parse($cheque->cheque_date)->format('d M, Y') : 'No Date' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-700 text-dark small">{{ $cheque->bankAccount->bank_name ?? 'N/A' }}</div>
                                <div class="text-gold smaller fw-700 text-uppercase">{{ $cheque->bankAccount->account_type ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-700 text-dark">{{ $cheque->payee_name }}</div>
                            </td>
                            <td>
                                <div class="fw-800 text-dark fs-6">Rs.{{ number_format($cheque->amount, 2) }}</div>
                            </td>
                            <td class="text-center">
                                @if($cheque->status == 'issued')
                                    <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 small fw-700">ISSUED</span>
                                @elseif($cheque->status == 'cleared')
                                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 small fw-700">CLEARED</span>
                                @elseif($cheque->status == 'bounced')
                                    <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 small fw-700">BOUNCED</span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary rounded-pill px-3 py-2 small fw-700">{{ strtoupper($cheque->status) }}</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded-10 overflow-hidden">
                                    <a href="javascript:void(0)" class="btn btn-white btn-sm px-3" title="Audit View">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    @if($cheque->status == 'issued')
                                        <a href="javascript:void(0)" class="btn btn-white btn-sm px-3 border-start" title="Reconcile">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center">
                                <div class="text-muted">
                                    <i class="fas fa-receipt fs-1 opacity-20 mb-3 d-block"></i>
                                    No cheques found matching the current filters
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($cheques->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $cheques->links() }}
        </div>
    @endif
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-12 { border-radius: 12px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-10 { border-radius: 10px; }
    .rounded-8 { border-radius: 8px; }
    .tiny { font-size: 0.6rem; }
    .smaller { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.08); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.08); }
    .bg-secondary-soft { background: rgba(108, 117, 125, 0.08); }
    .bg-white-10 { background: rgba(255, 255, 255, 0.1); }

    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        color: #1a1a1a;
    }

    .activity-icon-box {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .cheque-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-bg-icon {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 5rem;
        transform: rotate(-15deg);
        pointer-events: none;
    }
</style>
@endsection
