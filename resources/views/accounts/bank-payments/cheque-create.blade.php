@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('accounts.bank-payments.cheques.index') }}" class="btn btn-white btn-sm rounded-12 shadow-sm me-3 border-0">
                    <i class="fas fa-arrow-left text-gold"></i>
                </a>
                <div>
                    <h1 class="h2 mb-1 text-dark fw-800">Issue Cheque</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('accounts.bank-payments.index') }}" class="text-decoration-none text-muted">Bank</a></li>
                            <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">New Instrument</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold">
                <i class="fas fa-stamp me-1"></i>Official Instrument
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon-box bg-gold text-dark me-3 shadow-gold">
                            <i class="fas fa-money-check"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-800 text-white">Cheque Issuance Voucher</h5>
                            <p class="text-white-50 small mb-0 fw-600">Secure registration of bank instruments</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('accounts.bank-payments.cheques.store') }}" method="POST" id="chequeForm">
                        @csrf

                        <!-- Bank Account Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Originating Bank <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-university text-gold"></i></span>
                                <select name="bank_account_id" class="form-select border-0 py-3 fw-600 @error('bank_account_id') is-invalid @enderror" required>
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->bank_name }} - {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('bank_account_id')
                                <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Cheque Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Cheque Number <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-hashtag text-muted"></i></span>
                                    <input type="text" name="cheque_number" class="form-control border-0 py-3 fw-600 @error('cheque_number') is-invalid @enderror" 
                                           placeholder="000XXX" value="{{ old('cheque_number') }}" required>
                                </div>
                                @error('cheque_number')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cheque Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Date on Cheque <span class="text-danger">*</span></label>
                                <div class="input-group glass-input-group shadow-sm rounded-15">
                                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="date" name="cheque_date" class="form-control border-0 py-3 fw-600 @error('cheque_date') is-invalid @enderror" 
                                           value="{{ old('cheque_date', date('Y-m-d')) }}" required>
                                </div>
                                @error('cheque_date')
                                    <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Payee Name -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Payee (Beneficiary) <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-user-tie text-gold"></i></span>
                                <input type="text" name="payee_name" class="form-control border-0 py-3 fw-600 @error('payee_name') is-invalid @enderror" 
                                       placeholder="Enter recipient's full name" value="{{ old('payee_name') }}" required>
                            </div>
                            @error('payee_name')
                                <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div class="mb-4">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Cheque Amount <span class="text-danger">*</span></label>
                            <div class="input-group glass-input-group shadow-sm rounded-15 overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-3 fw-800 text-dark">Rs.</span>
                                <input type="number" name="amount" step="0.01" class="form-control border-0 py-3 fw-800 fs-4 @error('amount') is-invalid @enderror" 
                                       placeholder="0.00" value="{{ old('amount') }}" required>
                            </div>
                            @error('amount')
                                <div class="text-danger smaller fw-700 mt-2 px-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-5">
                            <label class="form-label fw-800 text-dark small text-uppercase tracking-wider">Issuance Purpose</label>
                            <textarea name="memo" rows="3" class="form-control border-0 shadow-sm rounded-15 p-3 fw-600" 
                                      placeholder="Explain the reason for this payment...">{{ old('memo') }}</textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-gold w-100 py-3 rounded-15 fw-800 fs-5 shadow-gold border-0">
                                    <i class="fas fa-check-circle me-2"></i>Authorize & Issue Cheque
                                </button>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('accounts.bank-payments.cheques.index') }}" class="btn btn-white w-100 py-3 rounded-15 fw-700 border text-muted">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Cheque Visualizer -->
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4 bg-premium-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-800 text-gold smaller tracking-widest text-uppercase">Instrument Preview</span>
                        <i class="fas fa-shield-check text-success fs-5"></i>
                    </div>
                    
                    <div class="cheque-preview bg-white text-dark rounded-12 p-3 mb-4 shadow-sm position-relative overflow-hidden" style="min-height: 180px;">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2 border-dark border-opacity-10">
                            <div class="fw-800 small text-uppercase" id="prev_bank">BANK NAME</div>
                            <div class="fw-700 small" id="prev_date">DD / MM / YYYY</div>
                        </div>
                        
                        <div class="mb-3">
                            <span class="smaller text-muted fw-700">PAY TO:</span>
                            <div class="fw-800 fs-6 border-bottom border-dark border-opacity-10" id="prev_payee" style="min-height: 24px;">Recipient Name</div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="grow me-3">
                                <span class="smaller text-muted fw-700">THE SUM OF:</span>
                                <div class="fw-700 smaller text-muted italic" id="prev_words">Words will appear here</div>
                            </div>
                            <div class="bg-light p-2 rounded-8 border border-dark border-opacity-10 fw-800" style="min-width: 100px;">
                                Rs. <span id="prev_amount">0.00</span>
                            </div>
                        </div>
                        
                        <div class="position-absolute bottom-0 start-0 w-100 p-2 text-center text-muted smaller opacity-50 fw-600 ls-2">
                            ⑈ 000000 ⑈ 000000000 ⑈ 00
                        </div>
                    </div>
                    
                    <div class="glass-alert rounded-15 p-3">
                        <p class="smaller fw-700 mb-0 text-white"><i class="fas fa-lock text-gold me-2"></i>Security Hash will be generated upon final authorization.</p>
                    </div>
                </div>
            </div>

            <!-- Audit Checklist -->
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft">
                <h6 class="fw-800 text-dark mb-3">Audit Protocol</h6>
                <div class="checklist">
                    <div class="d-flex align-items-start mb-3">
                        <div class="p-1 bg-success-soft text-success rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Ensure cheque leaves are sequential in the stock.</span>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <div class="p-1 bg-success-soft text-success rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Verify payee name matches authorized ledger.</span>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="p-1 bg-success-soft text-success rounded-circle me-2 mt-1">
                            <i class="fas fa-check tiny"></i>
                        </div>
                        <span class="small fw-600 text-muted">Confirm dual authorization if amount > Rs.50,000.</span>
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
    .tiny { font-size: 0.6rem; }
    .smaller { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .ls-2 { letter-spacing: 2px; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

    .glass-input-group { border: 1px solid #eee; background: white; }
    .glass-input-group:focus-within { border-color: #d4af37; box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1) !important; }

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

    .activity-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }
    .stat-card { background: white; }
    .italic { font-style: italic; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        const payeeInput = document.querySelector('input[name="payee_name"]');
        const dateInput = document.querySelector('input[name="cheque_date"]');
        const bankSelect = document.querySelector('select[name="bank_account_id"]');
        
        const prevBank = document.getElementById('prev_bank');
        const prevDate = document.getElementById('prev_date');
        const prevPayee = document.getElementById('prev_payee');
        const prevAmount = document.getElementById('prev_amount');

        function updatePreview() {
            prevAmount.textContent = (parseFloat(amountInput.value) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
            prevPayee.textContent = payeeInput.value || 'Recipient Name';
            prevDate.textContent = dateInput.value ? new Date(dateInput.value).toLocaleDateString('en-GB') : 'DD / MM / YYYY';
            
            const selectedBank = bankSelect.options[bankSelect.selectedIndex];
            prevBank.textContent = (selectedBank && selectedBank.value) ? selectedBank.text.split(' - ')[0] : 'BANK NAME';
        }

        [amountInput, payeeInput, dateInput, bankSelect].forEach(el => {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });

        updatePreview();
    });
</script>
@endsection
