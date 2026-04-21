@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('accounts.bank-payments.index') }}" class="btn btn-white btn-sm rounded-12 shadow-sm me-3 border-0">
                    <i class="fas fa-arrow-left text-danger"></i>
                </a>
                <div>
                    <h1 class="h2 mb-1 text-dark fw-800">Bank Withdrawal</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Withdrawal Voucher</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-700 shadow-sm">
                <i class="fas fa-exclamation-triangle me-1"></i>Liquidity Reduction
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon-box bg-danger text-white me-3 shadow-sm">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-800 text-white">Withdrawal Voucher</h5>
                            <p class="text-white-50 small mb-0 fw-600">Register cash or electronic outflows</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('accounts.bank-payments.withdrawals.store') }}" method="POST" id="withdrawalForm">
                        @csrf

                        <!-- Bank Account Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Source Vault (Bank Account) <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-university text-danger"></i></span>
                                <select name="bank_account_id" class="form-select border-0 py-3 fw-600 @error('bank_account_id') is-invalid @enderror" required>
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->bank_name }} - {{ $account->account_number }} (Balance: Rs.{{ number_format($account->balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('bank_account_id')
                                <div class="text-danger smaller fw-700 mt-2 px-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Withdrawal Type -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Method of Withdrawal <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="withdrawal_type" id="cash_withdrawal" value="cash" required {{ old('withdrawal_type') == 'cash' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger w-100 py-3 rounded-15 fw-700 d-flex flex-column align-items-center gap-2 shadow-sm" for="cash_withdrawal">
                                        <i class="fas fa-money-bill-wave fs-4"></i> Cash
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="withdrawal_type" id="cheque_withdrawal" value="cheque" required {{ old('withdrawal_type') == 'cheque' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger w-100 py-3 rounded-15 fw-700 d-flex flex-column align-items-center gap-2 shadow-sm" for="cheque_withdrawal">
                                        <i class="fas fa-receipt fs-4"></i> Cheque
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="withdrawal_type" id="transfer_withdrawal" value="transfer" required {{ old('withdrawal_type') == 'transfer' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger w-100 py-3 rounded-15 fw-700 d-flex flex-column align-items-center gap-2 shadow-sm" for="transfer_withdrawal">
                                        <i class="fas fa-exchange-alt fs-4"></i> Transfer
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Withdrawal Amount <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 fw-800 text-dark">Rs.</span>
                                <input type="number" name="amount" step="0.01" min="0" class="form-control border-0 py-3 fw-800 fs-4 @error('amount') is-invalid @enderror" placeholder="0.00" value="{{ old('amount') }}" required>
                            </div>
                            @error('amount')
                                <div class="text-danger smaller fw-700 mt-2 px-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Payee -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Payee / Recipient <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-user-tie text-muted"></i></span>
                                <input type="text" name="payee_name" class="form-control border-0 py-3 fw-600" placeholder="Beneficiary name" value="{{ old('payee_name') }}" required>
                            </div>
                        </div>

                        <!-- Cheque Details -->
                        <div id="cheque_details_panel" class="mb-4" style="display: none;">
                            <div class="rounded-20 p-4 bg-light border-0 shadow-inner">
                                <h6 class="fw-800 text-dark mb-3">Cheque Specifications</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-muted">Cheque Number</label>
                                        <input type="text" name="cheque_number" class="form-control border-0 shadow-sm py-2 rounded-10 fw-600" placeholder="XXXXXX" value="{{ old('cheque_number') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-muted">Cheque Date</label>
                                        <input type="date" name="cheque_date" class="form-control border-0 shadow-sm py-2 rounded-10 fw-600" value="{{ old('cheque_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reference -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Internal Reference</label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-hashtag text-muted"></i></span>
                                <input type="text" name="reference_number" class="form-control border-0 py-3 fw-600" placeholder="Journal Ref / Auth ID" value="{{ old('reference_number') }}">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-5">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Purpose & Remarks</label>
                            <textarea name="notes" class="form-control border-0 shadow-sm rounded-15 p-3 fw-600" rows="3" placeholder="Reason for withdrawal...">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-danger w-100 py-3 rounded-15 fw-800 fs-5 shadow-sm border-0">
                                    <i class="fas fa-shield-alt me-2"></i>Authorize Withdrawal
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
            <!-- Summary Card -->
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-danger text-white p-4 border-0">
                    <h6 class="mb-0 fw-800 d-flex align-items-center">
                        <i class="fas fa-calculator me-2"></i>Balance Impact
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4 pb-3 border-bottom">
                        <p class="text-muted small mb-1 fw-700 text-uppercase tracking-wider">Current Liquidity</p>
                        <h4 class="fw-800 text-dark mb-0" id="summary_current">Rs.0.00</h4>
                    </div>
                    <div class="mb-4 pb-3 border-bottom">
                        <p class="text-muted small mb-1 fw-700 text-uppercase tracking-wider">Withdrawal Amount</p>
                        <h4 class="fw-800 text-danger mb-0" id="summary_amount">Rs.0.00</h4>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted small mb-1 fw-700 text-uppercase tracking-wider">Remaining Balance</p>
                        <h4 class="fw-800 text-success mb-0" id="summary_remaining">Rs.0.00</h4>
                    </div>
                    
                    <div id="insufficient_alert" class="alert bg-danger-soft text-danger border-0 rounded-15 p-3 d-none">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle mt-1 me-2"></i>
                            <span class="small fw-700">Insufficient funds in the selected account for this withdrawal.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Checklist -->
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft">
                <h6 class="fw-800 text-dark mb-3">Pre-Auth Checklist</h6>
                <div class="checklist">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-1 bg-danger-soft text-danger rounded-circle me-2">
                            <i class="fas fa-info tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Confirm payee identity and account.</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-1 bg-danger-soft text-danger rounded-circle me-2">
                            <i class="fas fa-info tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Check for dual-signature requirements.</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="p-1 bg-danger-soft text-danger rounded-circle me-2">
                            <i class="fas fa-info tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Verify the purpose matches ledger codes.</span>
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
    .rounded-10 { border-radius: 10px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    .tiny { font-size: 0.6rem; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.08); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }

    .glass-input-group {
        border: 1px solid #eee;
        background: white;
    }
    .glass-input-group:focus-within {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
    }

    .btn-outline-danger {
        border: 2px solid #ef4444;
        color: #ef4444;
    }
    .btn-check:checked + .btn-outline-danger {
        background: #ef4444;
        color: white;
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
    }

    .activity-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }
    .stat-card {
        background: white;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        const accountSelect = document.querySelector('select[name="bank_account_id"]');
        const withdrawalTypeRadios = document.querySelectorAll('input[name="withdrawal_type"]');
        const chequePanel = document.getElementById('cheque_details_panel');
        
        const summaryCurrent = document.getElementById('summary_current');
        const summaryAmount = document.getElementById('summary_amount');
        const summaryRemaining = document.getElementById('summary_remaining');
        const insufficientAlert = document.getElementById('insufficient_alert');

        function updateSummary() {
            const amount = parseFloat(amountInput.value) || 0;
            const selectedOption = accountSelect.options[accountSelect.selectedIndex];
            
            let currentBalance = 0;
            if (selectedOption && selectedOption.value) {
                const text = selectedOption.text;
                const match = text.match(/Balance:\s*Rs.?([\d,.]+)/);
                if (match) {
                    currentBalance = parseFloat(match[1].replace(/,/g, ''));
                }
            }

            const remaining = currentBalance - amount;
            
            summaryCurrent.textContent = 'Rs.' + currentBalance.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            summaryAmount.textContent = 'Rs.' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            summaryRemaining.textContent = 'Rs.' + remaining.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            if (remaining < 0 && amount > 0) {
                insufficientAlert.classList.remove('d-none');
                summaryRemaining.classList.replace('text-success', 'text-danger');
            } else {
                insufficientAlert.classList.add('d-none');
                summaryRemaining.classList.replace('text-danger', 'text-success');
            }
        }

        withdrawalTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                chequePanel.style.display = this.value === 'cheque' ? 'block' : 'none';
            });
        });

        amountInput.addEventListener('input', updateSummary);
        accountSelect.addEventListener('change', updateSummary);

        // Initial trigger
        const checkedType = document.querySelector('input[name="withdrawal_type"]:checked');
        if (checkedType) chequePanel.style.display = checkedType.value === 'cheque' ? 'block' : 'none';
        updateSummary();
    });
</script>
@endsection
