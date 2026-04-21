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
                    <h1 class="h2 mb-1 text-dark fw-800">Record Deposit</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">New Entry</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold">
                <i class="fas fa-shield-alt me-1"></i>Secure Audit Entry
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
                            <h5 class="mb-0 fw-800 text-white">Deposit Voucher</h5>
                            <p class="text-white-50 small mb-0 fw-600">Register cash or cheque inflows</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('accounts.bank-payments.deposits.store') }}" method="POST" id="depositForm">
                        @csrf

                        <!-- Bank Account Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Target Vault (Bank Account) <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-university text-gold"></i></span>
                                <select name="bank_account_id" class="form-select border-0 py-3 fw-600 @error('bank_account_id') is-invalid @enderror" required>
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->bank_name }} - {{ $account->account_number }} (Current: Rs.{{ number_format($account->balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('bank_account_id')
                                <div class="text-danger smaller fw-700 mt-2 px-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deposit Type -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Transaction Nature <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="deposit_type" id="cash_deposit" value="cash" required {{ old('deposit_type') == 'cash' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-gold w-100 py-3 rounded-15 fw-700 d-flex align-items-center justify-content-center gap-2 shadow-sm" for="cash_deposit">
                                        <i class="fas fa-money-bill-wave"></i> Cash Deposit
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="deposit_type" id="cheque_deposit" value="cheque" required {{ old('deposit_type') == 'cheque' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-gold w-100 py-3 rounded-15 fw-700 d-flex align-items-center justify-content-center gap-2 shadow-sm" for="cheque_deposit">
                                        <i class="fas fa-receipt"></i> Cheque Deposit
                                    </label>
                                </div>
                            </div>
                            @error('deposit_type')
                                <div class="text-danger smaller fw-700 mt-2 px-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Deposit Amount <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 fw-800 text-dark">Rs.</span>
                                <input type="number" name="amount" step="0.01" min="0" class="form-control border-0 py-3 fw-800 fs-4 @error('amount') is-invalid @enderror" placeholder="0.00" value="{{ old('amount') }}" required>
                            </div>
                            @error('amount')
                                <div class="text-danger smaller fw-700 mt-2 px-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cheque Specific Fields -->
                        <div id="cheque_details_panel" class="mb-4" style="display: none;">
                            <div class="rounded-20 p-4 bg-light border-0 shadow-inner">
                                <h6 class="fw-800 text-dark mb-4 d-flex align-items-center">
                                    <i class="fas fa-pen-nib text-gold me-2"></i>Cheque Specifications
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-muted">Cheque Number</label>
                                        <input type="text" name="cheque_number" class="form-control border-0 shadow-sm py-2 rounded-10 fw-600" placeholder="XXXXXX" value="{{ old('cheque_number') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-muted">Cheque Date</label>
                                        <input type="date" name="cheque_date" class="form-control border-0 shadow-sm py-2 rounded-10 fw-600" value="{{ old('cheque_date') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-muted">Issuer Name</label>
                                        <input type="text" name="cheque_issuer" class="form-control border-0 shadow-sm py-2 rounded-10 fw-600" placeholder="Full name" value="{{ old('cheque_issuer') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-muted">Origin Bank</label>
                                        <input type="text" name="issuer_bank" class="form-control border-0 shadow-sm py-2 rounded-10 fw-600" placeholder="Issuer bank name" value="{{ old('issuer_bank') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reference -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Audit Reference</label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-hashtag text-muted"></i></span>
                                <input type="text" name="reference_number" class="form-control border-0 py-3 fw-600" placeholder="Receipt / UTR / Auth ID" value="{{ old('reference_number') }}">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-5">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Journal Remarks</label>
                            <textarea name="notes" class="form-control border-0 shadow-sm rounded-15 p-3 fw-600" rows="3" placeholder="Describe the purpose of this deposit...">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-gold w-100 py-3 rounded-15 fw-800 fs-5 shadow-gold border-0">
                                    <i class="fas fa-check-double me-2"></i>Verify & Record Deposit
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
            <!-- Information Card -->
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4 bg-premium-dark text-white">
                <div class="card-body p-4">
                    <h6 class="fw-800 mb-4 d-flex align-items-center">
                        <i class="fas fa-info-circle text-gold me-2"></i>Liquidity Insight
                    </h6>
                    <div class="mb-4">
                        <p class="text-white-50 small mb-1 fw-600">Projected Vault Balance</p>
                        <h4 class="fw-800 text-gold mb-0" id="summary_projected">Rs.0.00</h4>
                    </div>
                    <div class="mb-4">
                        <p class="text-white-50 small mb-1 fw-600">Selected Target</p>
                        <p class="fw-700 small mb-0" id="summary_bank_name">No account selected</p>
                    </div>
                    <hr class="bg-white opacity-10">
                    <div class="glass-alert rounded-15 p-3">
                        <p class="smaller fw-700 mb-0 text-white"><i class="fas fa-shield-check text-success me-2"></i>Bank transactions are immutable once recorded in the general ledger.</p>
                    </div>
                </div>
            </div>

            <!-- Audit Tips -->
            <div class="stat-card border-0 p-4 rounded-24">
                <h6 class="fw-800 text-dark mb-3">Audit Guidelines</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-start mb-3">
                        <div class="p-1 bg-success-soft text-success rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Verify the cheque date is not older than 3 months.</span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <div class="p-1 bg-success-soft text-success rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Ensure the deposit slip matches the system amount.</span>
                    </li>
                    <li class="d-flex align-items-start">
                        <div class="p-1 bg-success-soft text-success rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Reference number should be the Bank's UTR or receipt ID.</span>
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
    .rounded-10 { border-radius: 10px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    .tiny { font-size: 0.6rem; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        color: #1a1a1a;
    }

    .btn-outline-gold {
        border: 2px solid #d4af37;
        color: #d4af37;
        background: transparent;
    }
    .btn-check:checked + .btn-outline-gold {
        background: #d4af37;
        color: #1a1a1a;
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
    }

    .glass-input-group {
        border: 1px solid #eee;
        background: white;
    }
    .glass-input-group:focus-within {
        border-color: #d4af37;
        box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1) !important;
    }

    .activity-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.2rem;
    }
    .glass-alert {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }
    .stat-card {
        background: white;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        const accountSelect = document.querySelector('select[name="bank_account_id"]');
        const depositTypeRadios = document.querySelectorAll('input[name="deposit_type"]');
        const chequePanel = document.getElementById('cheque_details_panel');
        
        const summaryProjected = document.getElementById('summary_projected');
        const summaryBankName = document.getElementById('summary_bank_name');

        function updateSummary() {
            const amount = parseFloat(amountInput.value) || 0;
            const selectedOption = accountSelect.options[accountSelect.selectedIndex];
            
            let currentBalance = 0;
            if (selectedOption && selectedOption.value) {
                const text = selectedOption.text;
                summaryBankName.textContent = text.split(' (')[0];
                const match = text.match(/Rs.([\d,.]+)/);
                if (match) {
                    currentBalance = parseFloat(match[1].replace(/,/g, ''));
                }
            } else {
                summaryBankName.textContent = 'No account selected';
            }

            const projected = currentBalance + amount;
            summaryProjected.textContent = 'Rs.' + projected.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        depositTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                chequePanel.style.display = this.value === 'cheque' ? 'block' : 'none';
            });
        });

        amountInput.addEventListener('input', updateSummary);
        accountSelect.addEventListener('change', updateSummary);

        // Initial trigger
        const checkedType = document.querySelector('input[name="deposit_type"]:checked');
        if (checkedType) chequePanel.style.display = checkedType.value === 'cheque' ? 'block' : 'none';
        updateSummary();
    });
</script>
@endsection
