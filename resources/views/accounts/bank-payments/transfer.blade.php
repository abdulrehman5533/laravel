@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('accounts.bank-payments.index') }}" class="btn btn-white btn-sm rounded-12 shadow-sm me-3 border-0">
                    <i class="fas fa-arrow-left text-info"></i>
                </a>
                <div>
                    <h1 class="h2 mb-1 text-dark fw-800">Inter-Bank Transfer</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Internal Transfer</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-info text-white px-3 py-2 rounded-pill fw-700 shadow-sm">
                <i class="fas fa-sync-alt me-1"></i>Internal Reallocation
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon-box bg-info text-white me-3 shadow-sm">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-800 text-white">Transfer Protocol</h5>
                            <p class="text-white-50 small mb-0 fw-600">Move funds between internal vaults</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('accounts.bank-payments.transfers.store') }}" method="POST" id="transferForm">
                        @csrf

                        <div class="row g-4 mb-4">
                            <!-- From Account -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Source Account <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-sign-out-alt text-danger"></i></span>
                                    <select name="from_account_id" id="from_account_id" class="form-select border-0 py-3 fw-600 @error('from_account_id') is-invalid @enderror" required>
                                        <option value="">-- Select Source --</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }} data-balance="{{ $account->current_balance }}">
                                                {{ $account->bank_name }} (Rs.{{ number_format($account->current_balance, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('from_account_id')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- To Account -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Destination Account <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-sign-in-alt text-success"></i></span>
                                    <select name="to_account_id" id="to_account_id" class="form-select border-0 py-3 fw-600 @error('to_account_id') is-invalid @enderror" required>
                                        <option value="">-- Select Destination --</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->bank_name }} - {{ $account->account_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('to_account_id')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="mb-4 text-center py-4 bg-light rounded-20 shadow-inner">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider mb-3">Transfer Amount</label>
                            <div class="d-flex justify-content-center align-items-center">
                                <span class="display-6 fw-800 text-dark me-2">Rs.</span>
                                <input type="number" name="amount" step="0.01" class="form-control bg-transparent border-0 text-center fw-800 display-6 w-50 @error('amount') is-invalid @enderror" 
                                       placeholder="0.00" value="{{ old('amount') }}" required style="outline: none; box-shadow: none;">
                            </div>
                            @error('amount')
                                <div class="text-danger smaller fw-700 mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Reference -->
                            <div class="col-md-12">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Reference Number</label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-barcode text-muted"></i></span>
                                    <input type="text" name="reference_number" class="form-control border-0 py-3 fw-600" placeholder="e.g. TRN-123456" value="{{ old('reference_number') }}">
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Internal Remarks</label>
                                <textarea name="notes" rows="3" class="form-control border-0 shadow-sm rounded-15 p-3 fw-600" placeholder="Purpose of this transfer...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-info text-white w-100 py-3 rounded-15 fw-800 fs-5 shadow-sm border-0">
                                    <i class="fas fa-random me-2"></i>Execute Transfer
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
            <!-- Impact Card -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4 bg-premium-dark text-white">
                <div class="card-body p-4 text-center py-5">
                    <div class="transfer-visual d-flex justify-content-around align-items-center mb-5 position-relative">
                        <div class="account-circle p-3 rounded-circle bg-white-10 border border-white-10">
                            <i class="fas fa-university fs-3 text-white-50"></i>
                        </div>
                        <div class="transfer-arrow px-4">
                            <i class="fas fa-arrow-right fs-2 text-info animated-arrow"></i>
                        </div>
                        <div class="account-circle p-3 rounded-circle bg-white-10 border border-white-10">
                            <i class="fas fa-university fs-3 text-info"></i>
                        </div>
                    </div>
                    <h5 class="fw-800 mb-1">Transaction Impact</h5>
                    <p class="text-white-50 small mb-4">Total Amount: <span class="text-info fw-800" id="impact_amount">Rs.0.00</span></p>
                    
                    <div id="from_status" class="p-3 bg-white-5 rounded-15 mb-2 text-start">
                        <p class="smaller text-white-50 fw-700 mb-1 text-uppercase tracking-wider">Source Change</p>
                        <p class="mb-0 fw-700 small" id="from_impact">-</p>
                    </div>
                    <div id="to_status" class="p-3 bg-white-5 rounded-15 text-start">
                        <p class="smaller text-white-50 fw-700 mb-1 text-uppercase tracking-wider">Dest. Change</p>
                        <p class="mb-0 fw-700 small" id="to_impact">-</p>
                    </div>
                </div>
            </div>

            <!-- Validation Info -->
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft">
                <h6 class="fw-800 text-dark mb-3 d-flex align-items-center">
                    <i class="fas fa-shield-alt text-info me-2"></i>Security Policy
                </h6>
                <p class="text-muted smaller fw-600 mb-3 line-height-relaxed">
                    Internal transfers require immediate reconciliation. Once executed, a dual-entry ledger record will be generated automatically for both accounts.
                </p>
                <div class="p-3 bg-info-soft rounded-15">
                    <div class="d-flex align-items-center text-info fw-700 smaller">
                        <i class="fas fa-check-circle me-2"></i> Real-time Balance Sync
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
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .line-height-relaxed { line-height: 1.6; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-white-10 { background: rgba(255, 255, 255, 0.1); }
    .bg-white-5 { background: rgba(255, 255, 255, 0.05); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.08); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-inner { box-shadow: inset 0 2px 5px rgba(0,0,0,0.05); }

    .glass-input-group { border: 1px solid #eee; background: white; }
    .glass-input-group:focus-within { border-color: #0dcaf0; box-shadow: 0 0 0 4px rgba(13, 202, 240, 0.1) !important; }

    .activity-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }
    .stat-card { background: white; }

    .animated-arrow {
        animation: slideRight 1.5s infinite;
    }
    @keyframes slideRight {
        0% { transform: translateX(-5px); opacity: 0.5; }
        50% { transform: translateX(5px); opacity: 1; }
        100% { transform: translateX(-5px); opacity: 0.5; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        const fromAccount = document.getElementById('from_account_id');
        const toAccount = document.getElementById('to_account_id');
        const impactAmount = document.getElementById('impact_amount');
        const fromImpact = document.getElementById('from_impact');
        const toImpact = document.getElementById('to_impact');

        function updateImpact() {
            const amount = parseFloat(amountInput.value) || 0;
            impactAmount.textContent = 'Rs.' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            
            const fromOption = fromAccount.options[fromAccount.selectedIndex];
            const toOption = toAccount.options[toAccount.selectedIndex];
            
            if (fromOption && fromOption.value) {
                fromImpact.textContent = fromOption.text.split(' (')[0] + ' (-Rs.' + amount.toLocaleString('en-IN') + ')';
                fromImpact.className = 'mb-0 fw-700 small text-danger';
            } else {
                fromImpact.textContent = '-';
                fromImpact.className = 'mb-0 fw-700 small text-white-50';
            }
            
            if (toOption && toOption.value) {
                toImpact.textContent = toOption.text.split(' - ')[0] + ' (+Rs.' + amount.toLocaleString('en-IN') + ')';
                toImpact.className = 'mb-0 fw-700 small text-success';
            } else {
                toImpact.textContent = '-';
                toImpact.className = 'mb-0 fw-700 small text-white-50';
            }
        }

        amountInput.addEventListener('input', updateImpact);
        fromAccount.addEventListener('change', updateImpact);
        toAccount.addEventListener('change', updateImpact);
        updateImpact();
    });
</script>
@endsection
