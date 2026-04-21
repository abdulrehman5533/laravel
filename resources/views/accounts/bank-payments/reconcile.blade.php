@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('accounts.bank-payments.index') }}" class="btn btn-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="fas fa-arrow-left text-muted"></i>
                </a>
                <div>
                    <h1 class="h2 mb-1 text-dark fw-800">Bank Reconciliation</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Treasury</a></li>
                            <li class="breadcrumb-item active fw-600" aria-current="page">Reconciliation</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-premium-dark text-gold px-3 py-2 rounded-pill fw-700 shadow-sm">
                <i class="fas fa-shield-alt me-2"></i>Secure Audit Mode
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Input Column -->
        <div class="col-xl-7">
            <!-- Bank Identity Card -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-gold text-dark me-3">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-0 fw-800">Bank Account Identity</h5>
                            <p class="text-white-50 small mb-0">Reconciling ledger with external statement</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($bankAccount)
                    <div class="row g-4">
                        <div class="col-md-4">
                            <span class="text-muted smaller d-block fw-700 text-uppercase tracking-wider">Institution</span>
                            <span class="fw-800 text-dark">{{ $bankAccount->bank_name }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted smaller d-block fw-700 text-uppercase tracking-wider">Account Number</span>
                            <span class="fw-800 text-dark">{{ $bankAccount->account_number }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted smaller d-block fw-700 text-uppercase tracking-wider">Reference Name</span>
                            <span class="fw-800 text-dark">{{ $bankAccount->account_holder_name }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Reconciliation Workpad -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="mb-0 fw-800 text-dark">Statement Data Entry</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <form action="{{ route('accounts.bank-payments.reconcile') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Statement Closing Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="date" name="statement_date" class="form-control border-light bg-light rounded-end-12 py-2 @error('statement_date') is-invalid @enderror" 
                                           value="{{ old('statement_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Opening Balance <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12">Rs.</span>
                                    <input type="number" name="opening_balance" step="0.01" class="form-control border-light bg-light rounded-end-12 py-2 fw-700 @error('opening_balance') is-invalid @enderror" 
                                           placeholder="0.00" value="{{ old('opening_balance') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Statement Closing Balance <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-gold border-gold text-dark rounded-start-12">Rs.</span>
                                    <input type="number" name="closing_balance" step="0.01" class="form-control border-gold bg-light rounded-end-12 py-2 fw-800 @error('closing_balance') is-invalid @enderror" 
                                           placeholder="0.00" value="{{ old('closing_balance') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Deposits in Transit</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-plus text-success"></i></span>
                                    <input type="number" name="deposits_in_transit" step="0.01" class="form-control border-light bg-light rounded-end-12 py-2 @error('deposits_in_transit') is-invalid @enderror" 
                                           placeholder="0.00" value="{{ old('deposits_in_transit', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Outstanding Cheques</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-minus text-danger"></i></span>
                                    <input type="number" name="outstanding_cheques" step="0.01" class="form-control border-light bg-light rounded-end-12 py-2 @error('outstanding_cheques') is-invalid @enderror" 
                                           placeholder="0.00" value="{{ old('outstanding_cheques', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Interest / Dividends Earned</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-chart-line text-info"></i></span>
                                    <input type="number" name="interest_earned" step="0.01" class="form-control border-light bg-light rounded-end-12 py-2 @error('interest_earned') is-invalid @enderror" 
                                           placeholder="0.00" value="{{ old('interest_earned', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Bank Service Charges & Fees</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-file-invoice-dollar text-danger"></i></span>
                                    <input type="number" name="bank_charges" step="0.01" class="form-control border-light bg-light rounded-end-12 py-2 @error('bank_charges') is-invalid @enderror" 
                                           placeholder="0.00" value="{{ old('bank_charges', 0) }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Audit Notes</label>
                                <textarea name="notes" rows="3" class="form-control border-light bg-light rounded-12 p-3 @error('notes') is-invalid @enderror" 
                                          placeholder="Enter any discrepancies or remarks for the audit trail...">{{ old('notes') }}</textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-gold shadow-gold w-100 py-3 rounded-15 fw-800">
                                    <i class="fas fa-sync-alt me-2"></i>Execute Reconciliation Audit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result Column -->
        <div class="col-xl-5">
            @if(isset($reconciliation))
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4 animate__animated animate__fadeInRight">
                <div class="card-header bg-success p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-white rounded-10 me-3">
                            <i class="fas fa-check-double text-success"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-0 fw-800">Audit Result</h5>
                            <p class="text-white-50 small mb-0">Reconciliation Analysis Report</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush rounded-20 border">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light-soft">
                            <span class="text-muted fw-600">Bank Statement Closing</span>
                            <span class="fw-800 text-dark">Rs.{{ number_format($reconciliation['bank_statement_balance'] ?? 0, 2) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted fw-600">Deposits in Transit (+)</span>
                            <span class="fw-700 text-success">Rs.{{ number_format($reconciliation['deposits_in_transit'] ?? 0, 2) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted fw-600">Outstanding Cheques (-)</span>
                            <span class="fw-700 text-danger">- Rs.{{ number_format($reconciliation['outstanding_cheques'] ?? 0, 2) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted fw-600">Account Adjustments (Net)</span>
                            @php $netAdj = ($reconciliation['interest_earned'] ?? 0) - ($reconciliation['bank_charges'] ?? 0); @endphp
                            <span class="fw-700 {{ $netAdj >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $netAdj >= 0 ? '+' : '' }} Rs.{{ number_format($netAdj, 2) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-4 bg-premium-dark text-white">
                            <span class="fw-800 text-gold tracking-wider">ADJUSTED BANK BALANCE</span>
                            <span class="fw-800 fs-4">Rs.{{ number_format($reconciliation['reconciled_balance'] ?? 0, 2) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted fw-600">Company Ledger Balance</span>
                            <span class="fw-800 text-dark">Rs.{{ number_format($reconciliation['book_balance'] ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 p-4 rounded-24 {{ ($reconciliation['difference'] ?? 0) == 0 ? 'bg-success-soft text-success border-success-soft' : 'bg-danger-soft text-danger border-danger-soft' }} border text-center">
                        <h2 class="fw-800 mb-1">
                            {{ ($reconciliation['difference'] ?? 0) == 0 ? 'MATCHED' : 'DISCREPANCY' }}
                        </h2>
                        @if(($reconciliation['difference'] ?? 0) == 0)
                            <p class="mb-0 fw-700 small text-uppercase tracking-widest">Perfect Reconciliation ✓</p>
                        @else
                            <p class="mb-1 fw-700 small text-uppercase tracking-widest">Variance Detected</p>
                            <span class="fs-3 fw-800">Rs.{{ number_format(abs($reconciliation['difference'] ?? 0), 2) }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Help & Guidance Card -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-800 text-dark text-uppercase tracking-widest small mb-4">Reconciliation Guide</h6>
                    <div class="d-flex mb-4">
                        <div class="p-2 bg-white rounded-10 me-3 shadow-sm d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                            <span class="fw-800 text-gold">01</span>
                        </div>
                        <p class="small text-secondary fw-600 mb-0 mt-1">Extract the closing balance directly from your official bank statement.</p>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="p-2 bg-white rounded-10 me-3 shadow-sm d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                            <span class="fw-800 text-gold">02</span>
                        </div>
                        <p class="small text-secondary fw-600 mb-0 mt-1">Identify deposits recorded in ledger but not yet cleared by the bank.</p>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="p-2 bg-white rounded-10 me-3 shadow-sm d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                            <span class="fw-800 text-gold">03</span>
                        </div>
                        <p class="small text-secondary fw-600 mb-0 mt-1">List all cheques issued but not yet presented for payment.</p>
                    </div>
                    <div class="d-flex">
                        <div class="p-2 bg-white rounded-10 me-3 shadow-sm d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                            <span class="fw-800 text-gold">04</span>
                        </div>
                        <p class="small text-secondary fw-600 mb-0 mt-1">Adjust for bank fees, service charges, or interest credited during the period.</p>
                    </div>
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
    .rounded-24 { border-radius: 24px; }
    
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.1em; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    
    .bg-light-soft { background: rgba(0,0,0,0.02); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.05); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.05); }
    
    .border-success-soft { border-color: rgba(25, 135, 84, 0.2) !important; }
    .border-danger-soft { border-color: rgba(220, 53, 69, 0.2) !important; }
    
    .shadow-premium { box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .shadow-gold { box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3); }
    
    .btn-white {
        background: #fff;
        border: 1px solid #eee;
        transition: all 0.2s;
    }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.1rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #d4af37;
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.1);
        background-color: #fff;
    }
</style>
@endsection
