@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Bank & Treasury</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Liquidity Management</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.bank-payments.deposits.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus-circle me-2"></i>Deposit
                </a>
                <a href="{{ route('accounts.bank-payments.withdrawals.create') }}" class="btn btn-premium-dark text-white shadow-premium px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-minus-circle me-2"></i>Withdraw
                </a>
                <a href="{{ route('accounts.bank-payments.transfers.create') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-exchange-alt me-2 text-secondary"></i>Transfer
                </a>
            </div>
        </div>
    </div>

    <!-- Bank Accounts Interactive Cards -->
    <div class="row mb-5">
        @foreach($bankAccounts as $account)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card border-0 overflow-hidden group">
                    <div class="card-bg-icon">
                        <i class="fas fa-university text-gold opacity-10"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="stat-icon bg-gold text-dark shadow-sm">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-8 border-0" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-12">
                                <li><a class="dropdown-item py-2 fw-600" href="{{ route('accounts.bank-payments.accounts.show', $account->id) }}"><i class="fas fa-file-invoice me-2 text-gold"></i>Statement</a></li>
                                <li><a class="dropdown-item py-2 fw-600" href="{{ route('accounts.bank-payments.accounts.edit', $account->id) }}"><i class="fas fa-edit me-2 text-gold"></i>Edit Account</a></li>
                                <li>
                                    <form action="{{ route('accounts.bank-payments.accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item py-2 fw-600 text-danger"><i class="fas fa-trash-alt me-2"></i>Delete Vault</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <p class="text-muted small fw-800 text-uppercase mb-1 tracking-wider">{{ $account->bank_name }}</p>
                    <h5 class="fw-800 text-dark mb-1">{{ $account->account_number }}</h5>
                    <p class="text-muted small mb-4 fw-600">{{ $account->account_type }}</p>
                    
                    <div class="mt-auto pt-3 border-top">
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="text-muted small fw-700">Rs.</span>
                            <h3 class="fw-800 text-dark mb-0">{{ number_format($account->current_balance, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Add New Bank Account Action -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('accounts.bank-payments.accounts.create') }}" class="add-stat-card border-0 shadow-dashed rounded-24 d-flex flex-column align-items-center justify-content-center text-decoration-none h-100 p-4">
                <div class="plus-icon-circle mb-3 bg-light text-muted">
                    <i class="fas fa-plus"></i>
                </div>
                <h6 class="fw-800 text-muted mb-0">New Vault</h6>
                <p class="text-muted smaller fw-600 mt-1">Add Account</p>
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions Panel -->
        <div class="col-xl-8 mb-5">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden h-100">
                <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-800 text-dark">Treasury Audit Trail</h5>
                        <p class="text-muted small mb-0 fw-600">Latest bank movements and clearing status</p>
                    </div>
                    <a href="#" class="btn btn-white btn-sm px-3 rounded-8 fw-700 text-secondary border">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-premium-dark text-white">
                            <tr>
                                <th class="ps-4 border-0 py-3 small text-uppercase tracking-wider">Date</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider">Activity</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider">Reference</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider text-end">Amount</th>
                                <th class="pe-4 border-0 py-3 small text-uppercase tracking-wider text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-700 text-dark">{{ $transaction->transaction_date->format('d M, Y') }}</div>
                                        <small class="text-muted fw-600">{{ $transaction->created_at->format('H:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="activity-icon-box bg-{{ $transaction->transaction_type === 'deposit' ? 'success' : ($transaction->transaction_type === 'withdrawal' ? 'danger' : 'info') }}-soft text-{{ $transaction->transaction_type === 'deposit' ? 'success' : ($transaction->transaction_type === 'withdrawal' ? 'danger' : 'info') }} me-3">
                                                <i class="fas fa-{{ $transaction->transaction_type === 'deposit' ? 'arrow-down' : ($transaction->transaction_type === 'withdrawal' ? 'arrow-up' : 'exchange-alt') }} small"></i>
                                            </div>
                                            <span class="fw-700 text-dark">{{ ucfirst($transaction->transaction_type) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-700 text-dark small mb-0">{{ $transaction->reference_number ?? 'REF-N/A' }}</div>
                                        <div class="text-gold smaller fw-700">{{ optional($transaction->bankAccount)->bank_name }}</div>
                                    </td>
                                    <td class="text-end fw-800">
                                        <span class="text-{{ $transaction->transaction_type === 'deposit' ? 'success' : 'danger' }}">
                                            {{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}Rs.{{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        @if($transaction->status === 'completed')
                                            <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 small fw-700"><i class="fas fa-check-circle me-1"></i>CLEARED</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 small fw-700"><i class="fas fa-clock me-1"></i>PENDING</span>
                                        @else
                                            <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 small fw-700"><i class="fas fa-times-circle me-1"></i>VOID</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center">
                                        <div class="text-muted">
                                            <i class="fas fa-university fs-1 opacity-20 mb-3 d-block"></i>
                                            No treasury activity recorded
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Reconciliation & Cheques -->
        <div class="col-xl-4 mb-5">
            <!-- Reconciliation Progress -->
            <div class="card border-0 shadow-premium rounded-24 p-4 mb-4 bg-premium-dark text-white overflow-hidden position-relative">
                <div class="position-relative z-2">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-800 mb-0">Bank Recon</h5>
                        <div class="p-2 bg-gold text-dark rounded-8">
                            <i class="fas fa-shield-alt small"></i>
                        </div>
                    </div>
                    
                    <div class="reconcile-stats mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 fw-600 small">Book Balance</span>
                            <span class="fw-800">Rs.{{ number_format($totalBookBalance, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-white-50 fw-600 small">Verified</span>
                            <span class="fw-800 text-gold">Rs.{{ number_format($totalBankBalance, 2) }}</span>
                        </div>
                        
                        <!-- stylelint-disable -->
                        @php 
                            $reconciledPercent = $totalBookBalance > 0 ? ($totalBankBalance / $totalBookBalance) * 100 : 100;
                            $displayPercent = min(100, $reconciledPercent);
                        @endphp
                        <div class="progress bg-white-10 mb-2" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar bg-gold" role="progressbar" style="width: {{ $displayPercent }}%;"></div>
                        </div>
                        <!-- stylelint-enable -->
                        <div class="text-end">
                            <span class="smaller fw-700 text-white-50">{{ number_format(min(100, $reconciledPercent), 1) }}% Match Rate</span>
                        </div>
                    </div>
                    
                    <div class="glass-alert rounded-15 p-3 mb-4">
                        <p class="text-white-50 mb-1 smaller fw-700 text-uppercase tracking-wider">Variance Analysis</p>
                        <p class="text-white mb-0 fw-800">{{ abs($totalBookBalance - $totalBankBalance) < 0.01 ? 'Perfect Match' : 'Gap: Rs.'.number_format(abs($totalBookBalance - $totalBankBalance), 2) }}</p>
                    </div>
                    
                    <a href="{{ route('accounts.bank-payments.reconcile') }}" class="btn btn-gold w-100 rounded-12 fw-800 py-2">
                        <i class="fas fa-sync-alt me-2"></i>Run Audit
                    </a>
                </div>
                <div class="card-bg-icon">
                    <i class="fas fa-fingerprint text-white opacity-5"></i>
                </div>
            </div>

            <!-- Issued Cheques Tracker -->
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-800 text-dark">Issued Cheques</h6>
                        <p class="text-muted smaller fw-600 mb-0">Post-dated & Clearing</p>
                    </div>
                    <span class="badge bg-gold text-dark rounded-pill px-3 py-2 fw-700">{{ $issuedCheques->count() }} Active</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($issuedCheques as $cheque)
                            <div class="list-group-item px-4 py-3 border-light group">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="cheque-icon bg-light text-muted rounded-8 p-2 me-3">
                                            <i class="fas fa-money-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-800 text-dark small mb-0">#{{ $cheque->cheque_number }}</div>
                                            <div class="text-muted smaller fw-600">Due: {{ $cheque->cheque_date->format('d M, Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-800 text-dark small">Rs.{{ number_format($cheque->amount, 2) }}</div>
                                        @if($cheque->status === 'pending')
                                            <span class="text-warning fw-800 smaller text-uppercase">Pending</span>
                                        @elseif($cheque->status === 'cleared')
                                            <span class="text-success fw-800 smaller text-uppercase">Cleared</span>
                                        @else
                                            <span class="text-danger fw-800 smaller text-uppercase">Bounced</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-5 text-center">
                                <i class="fas fa-receipt fs-2 text-muted opacity-20 mb-3 d-block"></i>
                                <p class="text-muted small fw-600">No active cheques found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light p-3 text-center border-0">
                    <a href="{{ route('accounts.bank-payments.cheques.issue') }}" class="text-secondary fw-800 small text-decoration-none d-flex align-items-center justify-content-center">
                        Issue New Cheque <i class="fas fa-arrow-right ms-2 tiny"></i>
                    </a>
                </div>
            </div>
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
    .rounded-8 { border-radius: 8px; }
    .tiny { font-size: 0.6rem; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .text-secondary { color: #d4af37 !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.08); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.08); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.08); }
    .bg-white-10 { background: rgba(255, 255, 255, 0.1); }

    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-dashed { border: 2px dashed #e0e0e0 !important; transition: all 0.3s ease; }
    .shadow-dashed:hover { border-color: #d4af37 !important; background: #fffdf7 !important; }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        color: #1a1a1a;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px -15px rgba(0,0,0,0.12);
    }
    .card-bg-icon {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 5rem;
        transform: rotate(-15deg);
    }
    .stat-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1rem;
    }
    .activity-icon-box {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    .plus-icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .glass-alert {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }
</style>
@endsection
