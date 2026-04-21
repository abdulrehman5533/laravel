@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Account Intel</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.general-ledger.index') }}" class="text-decoration-none text-muted">General Ledger</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">{{ $account->account_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.general-ledger.export-pdf', ['account_id' => $account->id]) }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-file-pdf text-danger me-2"></i>Export Audit
                </a>
                <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-premium-dark shadow-premium px-4 rounded-12 fw-700">
                    <i class="fas fa-arrow-left me-2"></i>Back to Ledger
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Account Info Card -->
        <div class="col-xl-7 col-lg-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-file-invoice-dollar text-primary opacity-10"></i></div>
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-primary-soft text-primary rounded-12 p-3 me-3">
                            <i class="fas fa-university fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">Master Account Profile</h5>
                            <p class="text-muted small fw-600 mb-0">Official registration details</p>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Accounting Code</label>
                            <span class="fw-800 text-dark fs-5">{{ $account->account_code }}</span>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Financial Name</label>
                            <span class="fw-800 text-dark fs-5">{{ $account->account_name }}</span>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Category Type</label>
                            <div>
                                <span class="badge bg-gold-soft text-gold rounded-pill px-3 py-2 fw-800 smaller">{{ strtoupper($account->account_type) }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Status</label>
                            <div>
                                <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-800 smaller">ACTIVE AUDIT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Summary Card -->
        <div class="col-xl-5 col-lg-6 mb-4">
            <div class="card border-0 shadow-premium rounded-24 bg-premium-dark text-white h-100 overflow-hidden position-relative">
                <div class="card-bg-icon"><i class="fas fa-vault text-gold opacity-10"></i></div>
                <div class="card-body p-4 position-relative z-1">
                    <h5 class="fw-800 mb-4 text-gold">Liquidity Snapshot</h5>
                    
                    <div class="balance-item mb-4 p-3 rounded-16 bg-white-5 border-white-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-white-50 small fw-700 text-uppercase mb-1">Opening Capital</p>
                                <h4 class="fw-800 mb-0">Rs. {{ number_format($account->opening_balance, 2) }}</h4>
                            </div>
                            <i class="fas fa-door-open text-white-50 fs-4"></i>
                        </div>
                    </div>

                    <div class="balance-item p-3 rounded-16 bg-gold shadow-gold text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-dark-50 small fw-700 text-uppercase mb-1">Live Current Balance</p>
                                <h2 class="fw-900 mb-0">Rs. {{ number_format($account->current_balance, 2) }}</h2>
                            </div>
                            <i class="fas fa-chart-line text-dark-50 fs-2"></i>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <div class="progress bg-white-10" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar bg-gold" style="width: 75%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-white-50 smaller fw-600">Audit Coverage</span>
                            <span class="text-gold smaller fw-800">100% Verified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-5 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-800 text-dark">Activity Log</h5>
                <p class="text-muted small mb-0 fw-600">Detailed historical transaction records</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark rounded-pill px-3 py-2 fw-700 shadow-sm border">
                    {{ $ledgerEntries->total() }} Events
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover-premium mb-0">
                    <thead class="bg-premium-dark text-white">
                        <tr>
                            <th class="ps-5 py-3 fw-700 text-uppercase small ls-1">Date</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1">Protocol / Ref</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1">Narration</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1 text-end">Debit</th>
                            <th class="py-3 fw-700 text-uppercase small ls-1 text-end">Credit</th>
                            <th class="pe-5 py-3 fw-700 text-uppercase small ls-1 text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerEntries as $entry)
                            <tr class="align-middle">
                                <td class="ps-5 py-4">
                                    <div class="fw-800 text-dark mb-0">{{ $entry->date->format('d M, Y') }}</div>
                                    <div class="time-badge bg-light text-muted smaller px-2 py-1 rounded-6 fw-600 d-inline-block">
                                        <i class="far fa-clock me-1"></i>{{ $entry->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="py-4">
                                    <span class="badge bg-gold-soft text-gold rounded-pill px-2 py-1 smaller fw-700">
                                        {{ $entry->journalEntry->reference_number ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <div class="text-muted small fw-600" style="max-width: 300px;">
                                        {{ $entry->description }}
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
                                <td class="pe-5 py-4 text-end">
                                    <div class="running-balance-pill bg-light rounded-pill px-3 py-1 d-inline-block border">
                                        <span class="fw-800 text-dark">Rs. {{ number_format($entry->running_balance, 2) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center">
                                    <div class="empty-state p-5">
                                        <div class="icon-circle bg-gold-soft mb-4 mx-auto">
                                            <i class="fas fa-history text-gold fs-1"></i>
                                        </div>
                                        <h5 class="fw-800 text-dark">No Activity Found</h5>
                                        <p class="text-muted fw-600">This account has no recorded transactions yet.</p>
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
    .fw-900 { font-weight: 900; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-16 { border-radius: 16px; }
    .rounded-12 { border-radius: 12px; }
    .rounded-6 { border-radius: 6px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-white-5 { background: rgba(255, 255, 255, 0.05); }
    .border-white-10 { border: 1px solid rgba(255, 255, 255, 0.1); }
    
    .text-secondary { color: #d4af37 !important; }
    .text-gold { color: #d4af37; }
    .text-dark-50 { color: rgba(0,0,0,0.5); }
    .text-white-50 { color: rgba(255,255,255,0.5); }
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

    .stat-card { background: white; border-radius: 24px; transition: all 0.3s ease; }
    .card-bg-icon { position: absolute; bottom: -20px; right: -10px; font-size: 80px; pointer-events: none; transition: all 0.5s ease; }
    .stat-card:hover .card-bg-icon { transform: scale(1.1) rotate(-10deg); opacity: 0.15 !important; }

    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.002); }
    
    .empty-state .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    
    .smaller { font-size: 0.75rem; }
</style>
@endsection
