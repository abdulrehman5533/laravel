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
                    <h1 class="h2 mb-1 text-dark fw-800">Account Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Ledger</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.bank-payments.accounts.edit', $account->id) }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-edit me-2 text-gold"></i>Edit Vault
                </a>
                <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold d-flex align-items-center">
                    <i class="fas fa-university me-1"></i>Official Statement
                </span>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Account Summary -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100 bg-premium-dark text-white overflow-hidden position-relative">
                <div class="position-relative z-2">
                    <p class="text-white-50 small fw-800 text-uppercase mb-1 tracking-wider">{{ $account->bank_name }}</p>
                    <h3 class="fw-800 text-white mb-1">{{ $account->account_number }}</h3>
                    <p class="text-gold small mb-4 fw-700">{{ $account->account_type }} Account</p>
                    
                    <div class="mt-5">
                        <span class="text-white-50 small fw-700 d-block mb-1">Current Liquid Balance</span>
                        <h2 class="fw-800 text-white mb-0">Rs.{{ number_format($account->current_balance, 2) }}</h2>
                    </div>
                </div>
                <div class="card-bg-icon">
                    <i class="fas fa-vault text-white opacity-5"></i>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-xl-8 col-md-6 mb-4">
            <div class="card border-0 shadow-soft rounded-24 p-4 h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-md-4 text-center border-end border-light">
                        <div class="stat-icon bg-success-soft text-success mx-auto mb-3">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h5 class="fw-800 text-dark mb-1">Rs.{{ number_format($account->transactions()->where('transaction_type', 'deposit')->sum('amount'), 2) }}</h5>
                        <p class="text-muted smaller fw-700 text-uppercase mb-0">Total Inflow</p>
                    </div>
                    <div class="col-md-4 text-center border-end border-light">
                        <div class="stat-icon bg-danger-soft text-danger mx-auto mb-3">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h5 class="fw-800 text-dark mb-1">Rs.{{ number_format($account->transactions()->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</h5>
                        <p class="text-muted smaller fw-700 text-uppercase mb-0">Total Outflow</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="stat-icon bg-info-soft text-info mx-auto mb-3">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="fw-800 text-dark mb-1">{{ $account->transactions()->count() }}</h5>
                        <p class="text-muted smaller fw-700 text-uppercase mb-0">Transactions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-800 text-dark">Statement Audit Trail</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-light btn-sm rounded-8 fw-700 px-3"><i class="fas fa-download me-2"></i>PDF</button>
                <button class="btn btn-light btn-sm rounded-8 fw-700 px-3"><i class="fas fa-file-excel me-2"></i>Excel</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0 py-3 small text-uppercase fw-800 text-muted tracking-wider">Date</th>
                        <th class="border-0 py-3 small text-uppercase fw-800 text-muted tracking-wider">Description</th>
                        <th class="border-0 py-3 small text-uppercase fw-800 text-muted tracking-wider">Type</th>
                        <th class="border-0 py-3 small text-uppercase fw-800 text-muted tracking-wider text-end">Amount</th>
                        <th class="pe-4 border-0 py-3 small text-uppercase fw-800 text-muted tracking-wider text-end">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($account->transactions as $transaction)
                        <tr>
                            <td class="ps-4 fw-700 text-dark">{{ $transaction->transaction_date->format('d M, Y') }}</td>
                            <td class="fw-600 text-muted">{{ $transaction->description }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->transaction_type == 'deposit' ? 'success' : 'danger' }}-soft text-{{ $transaction->transaction_type == 'deposit' ? 'success' : 'danger' }} rounded-pill px-3 py-1 small fw-700">
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td class="text-end fw-800 text-{{ $transaction->transaction_type == 'deposit' ? 'success' : 'danger' }}">
                                {{ $transaction->transaction_type == 'deposit' ? '+' : '-' }}Rs.{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="pe-4 text-end fw-800 text-dark">Rs.{{ number_format($transaction->balance_after, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center">
                                <i class="fas fa-receipt fs-2 text-muted opacity-20 mb-3 d-block"></i>
                                <p class="text-muted fw-600">No transaction history found for this vault.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-12 { border-radius: 12px; }
    .rounded-24 { border-radius: 24px; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    
    .text-success { color: #10b981 !important; }
    .text-danger { color: #ef4444 !important; }
    .text-info { color: #0dcaf0 !important; }

    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.2rem;
    }
    .stat-card { background: white; }
    .card-bg-icon {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 8rem;
        transform: rotate(-15deg);
    }
</style>
@endsection
