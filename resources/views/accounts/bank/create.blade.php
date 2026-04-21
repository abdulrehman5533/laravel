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
                    <h1 class="h2 mb-1 text-dark fw-800">New Account</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Register Vault</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold">
                <i class="fas fa-university me-1"></i>New Financial Entity
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon-box bg-gold text-dark me-3 shadow-gold">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-800 text-white">Account Registration</h5>
                            <p class="text-white-50 small mb-0 fw-600">Establish a new bank connection</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('accounts.bank-payments.accounts.store') }}" method="POST">
                        @csrf

                        <div class="row g-4 mb-4">
                            <!-- Branch -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Assigned Branch <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-map-marker-alt text-gold"></i></span>
                                    <select name="branch_id" class="form-select border-0 py-3 fw-600 @error('branch_id') is-invalid @enderror" required>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('branch_id')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Account Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Account Category <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-tags text-gold"></i></span>
                                    <select name="account_type" class="form-select border-0 py-3 fw-600 @error('account_type') is-invalid @enderror" required>
                                        <option value="Current" {{ old('account_type') == 'Current' ? 'selected' : '' }}>Current Account</option>
                                        <option value="Savings" {{ old('account_type') == 'Savings' ? 'selected' : '' }}>Savings Account</option>
                                        <option value="Business" {{ old('account_type') == 'Business' ? 'selected' : '' }}>Business Account</option>
                                    </select>
                                </div>
                                @error('account_type')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Bank Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Financial Institution <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-university text-gold"></i></span>
                                    <input type="text" name="bank_name" class="form-control border-0 py-3 fw-600 @error('bank_name') is-invalid @enderror" 
                                           placeholder="e.g. HBL Bank" value="{{ old('bank_name') }}" required>
                                </div>
                                @error('bank_name')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- IFSC/Swift -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">IFSC / SWIFT Code</label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-barcode text-gold"></i></span>
                                    <input type="text" name="ifsc_code" class="form-control border-0 py-3 fw-600" 
                                           placeholder="e.g. HBL0001" value="{{ old('ifsc_code') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Account Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Account Title <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-user-tag text-gold"></i></span>
                                    <input type="text" name="account_name" class="form-control border-0 py-3 fw-600 @error('account_name') is-invalid @enderror" 
                                           placeholder="e.g. Main Business A/C" value="{{ old('account_name') }}" required>
                                </div>
                                @error('account_name')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Account Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Account Number <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-hashtag text-gold"></i></span>
                                    <input type="text" name="account_number" class="form-control border-0 py-3 fw-600 @error('account_number') is-invalid @enderror" 
                                           placeholder="XXXX-XXXX-XXXX" value="{{ old('account_number') }}" required>
                                </div>
                                @error('account_number')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Opening Balance -->
                        <div class="mb-5">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Initial Capital (Opening Balance) <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 fw-800 text-dark">Rs.</span>
                                <input type="number" name="opening_balance" step="0.01" class="form-control border-0 py-3 fw-800 fs-4 @error('opening_balance') is-invalid @enderror" 
                                       placeholder="0.00" value="{{ old('opening_balance', '0.00') }}" required>
                            </div>
                            @error('opening_balance')
                                <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-gold w-100 py-3 rounded-15 fw-800 fs-5 shadow-gold border-0">
                                    <i class="fas fa-plus-circle me-2"></i>Register New Account
                                </button>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('accounts.bank-payments.index') }}" class="btn btn-white w-100 py-3 rounded-15 fw-700 border text-muted">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft mb-4">
                <h6 class="fw-800 text-dark mb-3 d-flex align-items-center">
                    <i class="fas fa-shield-alt text-gold me-2"></i>Security Protocol
                </h6>
                <p class="text-muted smaller fw-600 line-height-relaxed mb-0">
                    Registering a new account establishes a permanent record in the financial infrastructure. Ensure all details match the official bank statement to prevent reconciliation discrepancies.
                </p>
            </div>

            <div class="card border-0 shadow-premium rounded-24 overflow-hidden bg-premium-dark text-white p-4">
                <h6 class="fw-800 mb-4 text-gold">Accounting Impact</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-start mb-3">
                        <div class="p-1 bg-white-10 text-gold rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-white-50">Adds new vault to Treasury dashboard.</span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <div class="p-1 bg-white-10 text-gold rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-white-50">Enables deposits and withdrawals.</span>
                    </li>
                    <li class="d-flex align-items-start">
                        <div class="p-1 bg-white-10 text-gold rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-white-50">Creates initial ledger entry.</span>
                    </li>
                </ul>
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
    .tiny { font-size: 0.6rem; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .line-height-relaxed { line-height: 1.6; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-white-10 { background: rgba(255, 255, 255, 0.1); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

    .glass-input-group { border: 1px solid #eee; background: white; }
    .glass-input-group:focus-within { border-color: #d4af37; box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1) !important; }

    .activity-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.2rem;
    }
    .stat-card { background: white; }
</style>
@endsection
