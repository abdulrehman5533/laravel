@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Treasury Settlement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.purchases.payments.index') }}" class="text-decoration-none text-muted">Treasury</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Record Settlement</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('accounts.purchases.payments.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Registry Hub
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('accounts.purchases.payments.store') }}">
        @csrf
        <div class="row g-4">
            <!-- Form Body -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-gold rounded-10 me-3">
                                <i class="fas fa-hand-holding-usd text-dark"></i>
                            </div>
                            <h5 class="mb-0 text-white fw-800">Settlement Protocol</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <!-- Procurement Link -->
                        <div class="mb-5">
                            <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Target Procurement Order <span class="text-danger">*</span></label>
                            <div class="glass-input-group p-1">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-shopping-cart text-primary"></i></span>
                                <select name="purchase_order_id" id="purchase_order_id" class="form-select bg-transparent border-0 fw-600 @error('purchase_order_id') is-invalid @enderror" required>
                                    <option value="">Select Validated Purchase Order</option>
                                    @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" 
                                                data-balance="{{ $po->total_amount - $po->paid_amount }}"
                                                data-supplier="{{ $po->supplier->name ?? 'Unknown' }}"
                                                {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                            {{ $po->po_number }} - {{ $po->supplier->name ?? 'N/A' }} (Exposure: Rs.{{ number_format($po->total_amount - $po->paid_amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('purchase_order_id')
                                <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Disbursement Date <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-calendar-check text-primary"></i></span>
                                    <input type="date" name="payment_date" class="form-control bg-transparent border-0 @error('payment_date') is-invalid @enderror"
                                           value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                                </div>
                                @error('payment_date')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Settlement Value <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0 text-primary fw-800">Rs.</span>
                                    <input type="number" name="amount_paid" id="amount_paid" class="form-control bg-transparent border-0 fw-800 text-dark @error('amount_paid') is-invalid @enderror"
                                           value="{{ old('amount_paid') }}" step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                                @error('amount_paid')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Settlement Channel <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-university text-primary"></i></span>
                                    <select name="payment_method" id="payment_method" class="form-select bg-transparent border-0 @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Protocol</option>
                                        <option value="Bank_Transfer" {{ old('payment_method') == 'Bank_Transfer' ? 'selected' : '' }}>Institutional Transfer</option>
                                        <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Liquidity Settlement</option>
                                        <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Instrumental Draft (Cheque)</option>
                                        <option value="Credit_Card" {{ old('payment_method') == 'Credit_Card' ? 'selected' : '' }}>Credit Facility</option>
                                        <option value="Online_Payment" {{ old('payment_method') == 'Online_Payment' ? 'selected' : '' }}>Gateway Transaction</option>
                                        <option value="UPI" {{ old('payment_method') == 'UPI' ? 'selected' : '' }}>Digital Unified Protocol</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Audit Reference / Transaction ID</label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-fingerprint text-primary"></i></span>
                                    <input type="text" name="transaction_id" class="form-control bg-transparent border-0 @error('transaction_id') is-invalid @enderror"
                                           value="{{ old('transaction_id') }}" placeholder="External Reference ID">
                                </div>
                            </div>
                        </div>

                        <div id="cheque_details" style="display: none;" class="mb-5">
                            <div class="p-4 bg-warning-soft rounded-24 border border-warning border-opacity-10">
                                <h6 class="fw-800 text-dark mb-3"><i class="fas fa-money-check me-2"></i>Instrument Intel</h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Instrument Number</label>
                                        <div class="glass-input-group bg-white">
                                            <input type="text" name="cheque_number" class="form-control bg-transparent border-0"
                                                   value="{{ old('cheque_number') }}" placeholder="Enter Cheque #">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Maturity Date</label>
                                        <div class="glass-input-group bg-white">
                                            <input type="date" name="cheque_date" class="form-control bg-transparent border-0"
                                                   value="{{ old('cheque_date') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Treasury Observations</label>
                            <textarea name="notes" class="form-control rounded-20 bg-light border-0 p-4 @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Restricted audit notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intelligence Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4 sticky-top" style="top: 2rem;">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <h5 class="mb-0 text-white fw-800">Settlement Snapshot</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="audit-item mb-4">
                            <label class="text-muted small fw-800 text-uppercase tracking-wider mb-1 d-block">Strategic Partner</label>
                            <div id="summary_supplier" class="fw-800 text-dark h6 mb-0">-</div>
                        </div>
                        
                        <div class="audit-item mb-4">
                            <label class="text-muted small fw-800 text-uppercase tracking-wider mb-1 d-block">Outstanding Exposure</label>
                            <div id="summary_balance" class="fw-800 text-danger h6 mb-0">Rs.0.00</div>
                        </div>

                        <hr class="my-4 border-light">

                        <div class="p-4 bg-light-soft rounded-24 border border-light mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-uppercase small fw-800 text-muted">Disbursement</span>
                                <span id="summary_paying" class="fw-900 text-success">Rs.0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-uppercase small fw-800 text-muted">Post-Settlement</span>
                                <span id="summary_remaining" class="fw-900 text-primary">Rs.0.00</span>
                            </div>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-premium-dark shadow-premium py-3 rounded-15 fw-800 text-white">
                                <i class="fas fa-shield-check me-2 text-gold"></i>Authorize Settlement
                            </button>
                            <a href="{{ route('accounts.purchases.payments.index') }}" class="btn btn-light py-3 rounded-15 fw-700 text-muted">
                                Abort Transaction
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .bg-premium-dark { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); }
    .bg-light-soft { background-color: #f8f9fa; }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    .shadow-gold { box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
    
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    
    .fw-900 { font-weight: 900; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    
    .glass-input-group {
        background: #f8f9fa;
        border: 1px solid #eee;
        border-radius: 12px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .glass-input-group:focus-within {
        background: white;
        border-color: #d4af37;
        box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: none;
        background: transparent;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #ccc;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const poSelect = document.getElementById('purchase_order_id');
        const amountInput = document.getElementById('amount_paid');
        const methodSelect = document.getElementById('payment_method');
        const chequeDetails = document.getElementById('cheque_details');
        
        const summarySupplier = document.getElementById('summary_supplier');
        const summaryBalance = document.getElementById('summary_balance');
        const summaryPaying = document.getElementById('summary_paying');
        const summaryRemaining = document.getElementById('summary_remaining');

        function updateSummary() {
            const selectedOption = poSelect.options[poSelect.selectedIndex];
            const balance = parseFloat(selectedOption?.dataset?.balance) || 0;
            const supplier = selectedOption?.dataset?.supplier || 'No Partner Selected';
            const paying = parseFloat(amountInput.value) || 0;
            const remaining = Math.max(0, balance - paying);

            const formatter = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'PKR',
                minimumFractionDigits: 2
            });

            summarySupplier.textContent = supplier;
            summaryBalance.textContent = formatter.format(balance);
            summaryPaying.textContent = formatter.format(paying);
            summaryRemaining.textContent = formatter.format(remaining);
        }

        poSelect.addEventListener('change', updateSummary);
        amountInput.addEventListener('input', updateSummary);
        
        methodSelect.addEventListener('change', function() {
            if (this.value === 'Cheque') {
                chequeDetails.style.display = 'block';
            } else {
                chequeDetails.style.display = 'none';
            }
        });

        if (methodSelect.value === 'Cheque') {
            chequeDetails.style.display = 'block';
        }
        updateSummary();
    });
</script>
@endsection
