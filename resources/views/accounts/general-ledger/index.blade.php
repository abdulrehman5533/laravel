@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">General Ledger</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Ledger Records</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.general-ledger.export-pdf', request()->all()) }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                </a>
                <a href="{{ route('accounts.general-ledger.export-excel', request()->all()) }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-file-excel text-success me-2"></i>Excel
                </a>
                <button class="btn btn-gold shadow-gold px-4 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>Manual Journal
                </button>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-12 mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Smart Filters Panel -->
    <div class="card border-0 shadow-premium rounded-24 mb-5 overflow-hidden">
        <div class="card-header bg-premium-dark py-3 px-4">
            <h6 class="text-white mb-0 fw-700 small text-uppercase ls-1">
                <i class="fas fa-search-dollar me-2 text-gold"></i>Audit Intelligence Filter
            </h6>
        </div>
        <div class="card-body p-4 bg-glass">
            <form method="GET" class="row g-4 align-items-end">
                <div class="col-xl-4 col-md-6">
                    <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Financial Account</label>
                    <div class="input-group glass-input-group">
                        <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-book-open text-gold"></i></span>
                        <select name="account_id" class="form-select border-0 bg-transparent py-2 ps-2">
                            <option value="">All Accounts</option>
                            @foreach($chartOfAccounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_code }} — {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Date Period</label>
                    <div class="d-flex gap-2">
                        <div class="input-group glass-input-group">
                            <input type="date" name="from_date" class="form-control border-0 bg-transparent py-2" value="{{ $fromDate }}">
                        </div>
                        <div class="input-group glass-input-group">
                            <input type="date" name="to_date" class="form-control border-0 bg-transparent py-2" value="{{ $toDate }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6">
                    <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Reference</label>
                    <div class="input-group glass-input-group">
                        <input type="text" name="reference" class="form-control border-0 bg-transparent py-2" placeholder="INV-2024..." value="{{ request('reference') }}">
                    </div>
                </div>
                <div class="col-xl-2 col-md-6">
                    <button type="submit" class="btn btn-gold w-100 rounded-12 py-2 fw-700 h-100">
                        <i class="fas fa-magic me-2"></i>Analyze
                    </button>
                </div>
                <div class="col-xl-1 col-md-6">
                    <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-premium-dark w-100 rounded-12 py-2 h-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(request('account_id'))
        <!-- Account Specific Summary -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="stat-card border-0 shadow-soft h-100 position-relative overflow-hidden group">
                    <div class="card-bg-icon"><i class="fas fa-id-card text-gold opacity-10"></i></div>
                    <div class="p-4 position-relative">
                        <p class="text-muted small fw-700 text-uppercase mb-2 ls-1">Account Summary</p>
                        <h4 class="fw-800 text-dark mb-2">{{ optional($selectedAccount)->account_name }}</h4>
                        <span class="badge bg-gold text-dark rounded-pill px-3 py-1 fw-800">{{ optional($selectedAccount)->account_code }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card border-0 shadow-soft h-100 position-relative overflow-hidden group">
                    <div class="card-bg-icon"><i class="fas fa-door-open text-primary opacity-10"></i></div>
                    <div class="p-4 position-relative">
                        <p class="text-muted small fw-700 text-uppercase mb-2 ls-1">Opening Balance</p>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="text-muted small fw-700">Rs.</span>
                            <h3 class="fw-800 text-dark mb-0">{{ number_format($openingBalance, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card border-0 shadow-premium h-100 bg-premium-dark text-white position-relative overflow-hidden group">
                    <div class="card-bg-icon"><i class="fas fa-vault text-gold opacity-10"></i></div>
                    <div class="p-4 position-relative">
                        <p class="text-white-50 small fw-700 text-uppercase mb-2 ls-1">Closing Balance</p>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="text-gold small fw-700">Rs.</span>
                            <h3 class="fw-800 text-gold mb-0">{{ number_format($closingBalance, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Ledger Records Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-5 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-800 text-dark">Journal Ledger</h5>
                <p class="text-muted small mb-0 fw-600">Verification status and transaction history</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark rounded-pill px-3 py-2 fw-700 shadow-sm border">
                    <i class="fas fa-list-ul me-2 text-gold"></i>{{ $ledgerEntries->total() }} Records
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover-premium mb-0">
                    <thead class="bg-premium-dark text-white">
                        <tr>
                            <th class="ps-5 py-3 fw-700 text-uppercase small ls-1">Execution Date</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1">Reference & Narration</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1 text-end">Debit (Rs.)</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1 text-end">Credit (Rs.)</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1 text-end">Running Balance</th>
                            <th class="pe-5 py-3 fw-700 text-uppercase small ls-1 text-center">Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerEntries as $entry)
                            <tr class="align-middle">
                                <td class="ps-5 py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="date-box me-3">
                                            <span class="d-block fw-800 text-dark mb-0">{{ $entry->date->format('d') }}</span>
                                            <span class="d-block text-muted smaller text-uppercase fw-700">{{ $entry->date->format('M Y') }}</span>
                                        </div>
                                        <div class="time-badge bg-light text-muted smaller px-2 py-1 rounded-6 fw-600">
                                            <i class="far fa-clock me-1"></i>{{ $entry->created_at->format('H:i') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="d-flex flex-column">
                                        <div class="fw-800 text-dark mb-1 d-flex align-items-center">
                                            @if($entry->reference_type)
                                                <span class="badge bg-gold-soft text-gold rounded-pill me-2 px-2 smaller fw-700">{{ strtoupper($entry->reference_type) }}</span>
                                            @endif
                                            {{ $entry->journalEntry->reference_number ?? 'N/A' }}
                                        </div>
                                        <div class="text-muted small fw-600" style="max-width: 350px;">
                                            <i class="fas fa-quote-left text-light-gray me-1 small"></i>
                                            {{ $entry->description ?? 'Official transaction record' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 text-end">
                                    @if($entry->debit > 0)
                                        <span class="fw-800 text-primary">Rs. {{ number_format($entry->debit, 2) }}</span>
                                    @else
                                        <span class="text-light-gray">-</span>
                                    @endif
                                </td>
                                <td class="py-4 text-end">
                                    @if($entry->credit > 0)
                                        <span class="fw-800 text-secondary">Rs. {{ number_format($entry->credit, 2) }}</span>
                                    @else
                                        <span class="text-light-gray">-</span>
                                    @endif
                                </td>
                                <td class="py-4 text-end">
                                    <div class="running-balance-pill bg-light rounded-pill px-3 py-1 d-inline-block border">
                                        <span class="fw-800 text-dark">Rs. {{ number_format($entry->running_balance, 2) }}</span>
                                    </div>
                                </td>
                                <td class="pe-5 py-4 text-center">
                                    <a href="{{ route('accounts.general-ledger.show', $entry->account_id) }}" class="btn btn-icon-premium" title="Examine Account Ledger">
                                        <i class="fas fa-fingerprint"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center">
                                    <div class="empty-state p-5">
                                        <div class="icon-circle bg-gold-soft mb-4 mx-auto">
                                            <i class="fas fa-inbox text-gold fs-1"></i>
                                        </div>
                                        <h5 class="fw-800 text-dark">No Ledger Records Found</h5>
                                        <p class="text-muted fw-600">Adjust your filters or timeframe to view entries.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Hub -->
    <div class="d-flex justify-content-center mt-5">
        {{ $ledgerEntries->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    /* Premium Styling */
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-12 { border-radius: 12px; }
    .rounded-6 { border-radius: 6px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .text-secondary { color: #d4af37 !important; }
    .text-gold { color: #d4af37; }
    .text-light-gray { color: #e0e0e0; }
    
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 15px 50px -12px rgba(0,0,0,0.15); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4); color: #1a1a1a; }

    .btn-white { background: #fff; border: 1px solid #f0f0f0; transition: all 0.3s ease; }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

    .btn-premium-dark { background: #1a1a1a; color: #fff; border: none; transition: all 0.3s ease; }
    .btn-premium-dark:hover { background: #000; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-2px); color: #fff; }

    .glass-input-group {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .glass-input-group:focus-within {
        background: #fff;
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1);
        border-color: #d4af37;
    }

    .stat-card { background: white; border-radius: 24px; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-5px); }
    .card-bg-icon { position: absolute; bottom: -20px; right: -10px; font-size: 80px; pointer-events: none; transition: all 0.5s ease; }
    .stat-card:hover .card-bg-icon { transform: scale(1.1) rotate(-10deg); opacity: 0.15 !important; }

    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; cursor: pointer; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.002); box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
    
    .date-box { text-align: center; min-width: 60px; border-right: 2px solid #f0f0f0; padding-right: 15px; }
    
    .btn-icon-premium {
        width: 38px; height: 38px; border-radius: 12px; display: inline-flex;
        align-items: center; justify-content: center; background: #f8f9fa;
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-icon-premium:hover { background: #1a1a1a; color: #fff; transform: rotate(10deg); }

    .empty-state .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    
    .bg-glass { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); }
    
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
</style>
@endsection
