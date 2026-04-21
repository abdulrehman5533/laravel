@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-credit-card text-primary"></i> Process Payment - Invoice #{{ $sale->invoice_no }}</h2>
        <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <!-- Left: Sale Summary -->
        <div class="col-lg-4">
            <div class="card bg-light mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-receipt"></i> Sale Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Subtotal</small>
                        <h6>{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->subtotal, 2) }}</h6>
                    </div>
                    @if($sale->making_charges + $sale->wastage_amount > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block">Making & Wastage</small>
                        <h6>{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->making_charges + $sale->wastage_amount, 2) }}</h6>
                    </div>
                    @endif
                    @if($sale->tax_amount > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block">Tax</small>
                        <h6>{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->tax_amount, 2) }}</h6>
                    </div>
                    @endif
                    @if($sale->discount > 0)
                    <div class="mb-3 text-danger">
                        <small class="text-muted d-block">Discount</small>
                        <h6>-{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->discount, 2) }}</h6>
                    </div>
                    @endif
                    <hr>
                    <div class="mb-3 bg-white p-3 rounded">
                        <small class="text-muted d-block">Total Amount Due</small>
                        <h4 class="text-primary mb-0">{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->total, 2) }}</h4>
                    </div>
                    @if($sale->payments()->exists())
                    <div class="mb-3 bg-white p-3 rounded">
                        <small class="text-muted d-block">Already Paid</small>
                        <h6 class="text-success mb-0">{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->payments()->sum('amount'), 2) }}</h6>
                    </div>
                    <div class="mb-3 bg-white p-3 rounded">
                        <small class="text-muted d-block">Outstanding Balance</small>
                        <h6 class="text-warning mb-0">{{ $sale->currency ?? 'Rs' }} {{ number_format($sale->outstanding_balance, 2) }}</h6>
                    </div>
                    @endif
                </div>
            </div>

            @if($sale->customer)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-user"></i> Customer</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ $sale->customer->name }}</strong></p>
                    <p class="small text-muted mb-1"><i class="fas fa-phone"></i> {{ $sale->customer->phone ?? 'N/A' }}</p>
                    <p class="small text-muted mb-3"><i class="fas fa-envelope"></i> {{ $sale->customer->email ?? 'N/A' }}</p>
                    @if($sale->customer->loyalty_points > 0)
                    <span class="badge bg-warning">
                        <i class="fas fa-gift"></i> {{ $sale->customer->loyalty_points }} Points
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Payment Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-money-bill-wave"></i> Payment Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.payments.store') }}" method="POST" id="paymentForm">
                        @csrf
                        <input type="hidden" name="pos_sale_id" value="{{ $sale->id }}">
                        <input type="hidden" name="pos_customer_id" value="{{ $sale->pos_customer_id }}">

                        <!-- Payment Type Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Payment Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_type" id="fullPayment" value="full" checked>
                                    <label class="btn btn-outline-success" for="fullPayment">
                                        <i class="fas fa-check-circle"></i> Full Payment
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="payment_type" id="partialPayment" value="partial">
                                    <label class="btn btn-outline-warning" for="partialPayment">
                                        <i class="fas fa-percentage"></i> Partial Payment
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="payment_type" id="installment" value="installment">
                                    <label class="btn btn-outline-info" for="installment">
                                        <i class="fas fa-calendar"></i> Installment Plan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Currency and Exchange Rate -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Currency</label>
                                <select name="currency" class="form-select" id="paymentCurrency">
                                    <option value="PKR" {{ ($sale->currency == 'PKR' || !$sale->currency) ? 'selected' : '' }}>🇵🇰 PKR - Pakistani Rupee</option>
                                    <option value="INR" {{ $sale->currency == 'INR' ? 'selected' : '' }}>🇮🇳 INR - Indian Rupee</option>
                                    <option value="USD" {{ $sale->currency == 'USD' ? 'selected' : '' }}>🇺🇸 USD - US Dollar</option>
                                    <option value="AED" {{ $sale->currency == 'AED' ? 'selected' : '' }}>🇦🇪 AED - UAE Dirham</option>
                                    <option value="GBP" {{ $sale->currency == 'GBP' ? 'selected' : '' }}>🇬🇧 GBP - British Pound</option>
                                    <option value="EUR" {{ $sale->currency == 'EUR' ? 'selected' : '' }}>🇪🇺 EUR - Euro</option>
                                    <option value="SAR" {{ $sale->currency == 'SAR' ? 'selected' : '' }}>🇸🇦 SAR - Saudi Riyal</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Exchange Rate (1 <span class="selected-currency">PKR</span> = ? Base)</label>
                                <div class="input-group">
                                    <input type="number" name="exchange_rate" id="exchangeRate" class="form-control" value="1.000000" step="0.000001" min="0.000001">
                                    <span class="input-group-text">Base: {{ $sale->currency ?? 'PKR' }}</span>
                                </div>
                                <small class="text-muted">Enter how many base currency units equal 1 unit of payment currency.</small>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" id="paymentMethod" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="cash" selected>💵 Cash</option>
                                    <option value="card">💳 Credit/Debit Card</option>
                                    <option value="jazzcash">📱 JazzCash</option>
                                    <option value="easypaisa">📱 EasyPaisa</option>
                                    <option value="bank_js">🏦 JS Bank</option>
                                    <option value="check">🏦 Check/Cheque</option>
                                    <option value="bank_transfer">🏛️ Bank Transfer</option>
                                    <option value="upi">📱 UPI/Digital Wallet</option>
                                    <option value="crypto">₿ Cryptocurrency</option>
                                </select>
                                @error('payment_method')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount to Pay (In <span class="selected-currency">PKR</span>) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text currency-symbol">{{ $sale->currency ?? 'Rs' }}</span>
                                    <input type="number" name="amount" id="paymentAmount" class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ $sale->outstanding_balance }}" step="0.01" min="0.01" required>
                                </div>
                                @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div class="mt-1">
                                    <small class="text-primary fw-bold" id="baseEquivalentDisplay"></small>
                                </div>
                                <small class="text-muted d-block mt-1" id="amountHint"></small>
                            </div>
                        </div>

                        <!-- Change Calculation (for cash only) -->
                        <div class="row mb-3" id="changeSection" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount Received (Cash)</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $sale->currency ?? 'Rs' }}</span>
                                    <input type="number" id="amountReceived" class="form-control" step="0.01" min="0.01" placeholder="Enter amount received">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Change Due</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $sale->currency ?? 'Rs' }}</span>
                                    <input type="text" id="changeDue" class="form-control bg-light" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Payment Method Fields -->
                        <div id="methodFields">
                            <!-- Cheque Fields -->
                            <div id="chequeFields" class="row mb-3" style="display:none;">
                                <div class="col-md-6">
                                    <label class="form-label">Cheque Number</label>
                                    <input type="text" name="cheque_number" class="form-control" placeholder="e.g., CHQ12345">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="e.g., Bank of America">
                                </div>
                            </div>

                            <!-- Card Fields -->
                            <div id="cardFields" class="row mb-3" style="display:none;">
                                <div class="col-md-6">
                                    <label class="form-label">Card Last 4 Digits</label>
                                    <input type="text" name="card_last4" class="form-control" placeholder="1234">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Transaction ID</label>
                                    <input type="text" name="transaction_id" class="form-control" placeholder="TXN123456789">
                                </div>
                            </div>

                            <!-- Bank Transfer Fields -->
                            <div id="bankFields" class="row mb-3" style="display:none;">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="e.g., Chase Bank">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" name="reference" class="form-control" placeholder="Reference/UTR">
                                </div>
                            </div>
                        </div>

                        <!-- Payment Accounts Info Box -->
                        <div id="paymentAccountsInfo" class="alert alert-info border-info mb-3" style="display:none;">
                            <h6 class="alert-heading fw-bold mb-2"><i class="fas fa-university me-2"></i> Payment Account Details</h6>
                            <div id="jazzEaisaInfo" style="display:none;">
                                <p class="mb-1"><strong>JazzCash / EasyPaisa:</strong> <span class="text-primary">03272117821</span></p>
                                <p class="mb-0"><strong>Account Name:</strong> {{ config('app.name', 'MAGIA LUPOS') }}</p>
                            </div>
                            <div id="jsBankInfo" style="display:none;">
                                <p class="mb-1"><strong>JS Bank Account Number:</strong> <span class="text-primary">0002502508</span></p>
                                <p class="mb-0"><strong>IBAN:</strong> <span class="text-primary">PK92JSBL9001000002502508</span></p>
                            </div>
                            <hr class="my-2">
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Please provide the transaction ID or reference number in the notes field below after payment.</small>
                        </div>

                        <!-- Installment Plan Options -->
                        <div id="installmentOptions" class="row mb-3" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Number of Installments</label>
                                <input type="number" name="num_installments" class="form-control" value="3" min="2" max="12">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Frequency</label>
                                <select name="installment_frequency" class="form-select">
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="fortnightly">Fortnightly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label">Notes / Reference</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes about this payment..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Complete Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment History -->
            @if($sale->payments()->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0"><i class="fas fa-history"></i> Payment History</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->payments as $payment)
                            <tr>
                                <td><small>{{ $payment->created_at->format('Y-m-d H:i') }}</small></td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                                <td>
                                    <strong>{{ $payment->currency ?? $sale->currency ?? 'Rs' }} {{ number_format($payment->amount, 2) }}</strong>
                                    @if(($payment->currency ?? $sale->currency) !== ($sale->currency ?? 'PKR'))
                                        <br>
                                        <small class="text-muted">
                                            Rate: {{ number_format($payment->exchange_rate, 2) }} 
                                            ({{ $sale->currency ?? 'PKR' }} {{ number_format($payment->amount * $payment->exchange_rate, 2) }})
                                        </small>
                                    @endif
                                </td>
                                <td><small>{{ $payment->reference ?? '-' }}</small></td>
                                <td><span class="badge bg-success">{{ ucfirst($payment->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');
    const paymentMethod = document.getElementById('paymentMethod');
    const changeSection = document.getElementById('changeSection');
    const amountInput = document.getElementById('paymentAmount');
    const amountReceived = document.getElementById('amountReceived');
    const changeDue = document.getElementById('changeDue');
    const totalDue = {{ $sale->outstanding_balance ?? 0 }};
    const paymentCurrency = document.getElementById('paymentCurrency');
    const exchangeRateInput = document.getElementById('exchangeRate');
    const baseEquivalentDisplay = document.getElementById('baseEquivalentDisplay');
    const baseCurrency = "{{ $sale->currency ?? 'PKR' }}";
    const baseSymbol = "{{ $sale->currency == 'USD' ? '$' : 'Rs' }}";

    const currencySymbols = {
        'PKR': 'Rs',
        'INR': '₹',
        'USD': '$',
        'AED': 'DH',
        'GBP': '£',
        'EUR': '€',
        'SAR': 'SR'
    };

    const defaultRates = {
        'PKR': 1.0,
        'INR': 3.30, 
        'USD': 280.0,
        'AED': 76.0,
        'GBP': 350.0,
        'EUR': 300.0,
        'SAR': 74.0
    };

    function updateCurrencyDisplay() {
        const currency = paymentCurrency.value;
        const symbol = currencySymbols[currency] || currency;
        
        document.querySelectorAll('.selected-currency').forEach(el => el.textContent = currency);
        document.querySelectorAll('.currency-symbol').forEach(el => el.textContent = symbol);
        
        // Update exchange rate
        if (defaultRates[currency]) {
            exchangeRateInput.value = defaultRates[currency].toFixed(6);
        }

        // If it was full payment, recalculate the foreign amount
        const type = document.querySelector('input[name="payment_type"]:checked').value;
        if (type === 'full') {
            const rate = parseFloat(exchangeRateInput.value) || 1;
            amountInput.value = (totalDue / rate).toFixed(2);
        }
        
        calculateBaseEquivalent();
    }

    function calculateBaseEquivalent() {
        const amount = parseFloat(amountInput.value) || 0;
        const rate = parseFloat(exchangeRateInput.value) || 1;
        const currency = paymentCurrency.value;

        const equivalent = amount * rate;
        if (currency !== baseCurrency) {
            baseEquivalentDisplay.textContent = `Equivalent to ${baseCurrency}: ${baseSymbol} ${equivalent.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            baseEquivalentDisplay.textContent = '';
        }
        
        updateChangeCalculation();
    }

    function updatePaymentType() {
        const type = document.querySelector('input[name="payment_type"]:checked').value;
        document.getElementById('installmentOptions').style.display = type === 'installment' ? 'flex' : 'none';
        
        if (type === 'full') {
            const rate = parseFloat(exchangeRateInput.value) || 1;
            amountInput.value = (totalDue / rate).toFixed(2);
        }
        calculateBaseEquivalent();
    }

    function updatePaymentMethod() {
        const method = paymentMethod.value;
        document.getElementById('chequeFields').style.display = method === 'check' ? 'flex' : 'none';
        document.getElementById('cardFields').style.display = method === 'card' ? 'flex' : 'none';
        document.getElementById('bankFields').style.display = (method === 'bank_transfer' || method === 'bank_js') ? 'flex' : 'none';
        changeSection.style.display = method === 'cash' ? 'flex' : 'none';

        if (method === 'cash') {
            updateChangeCalculation();
        }

        // Online/Bank Account Info
        const infoBox = document.getElementById('paymentAccountsInfo');
        const jazzEaisa = document.getElementById('jazzEaisaInfo');
        const jsBank = document.getElementById('jsBankInfo');

        if (method === 'jazzcash' || method === 'easypaisa' || method === 'bank_js') {
            infoBox.style.display = 'block';
            jazzEaisa.style.display = (method === 'jazzcash' || method === 'easypaisa') ? 'block' : 'none';
            jsBank.style.display = method === 'bank_js' ? 'block' : 'none';
        } else {
            infoBox.style.display = 'none';
        }
    }

    function updateChangeCalculation() {
        const getVal = (el) => {
            if (!el) return 0;
            let val = el.value.toString().replace(/,/g, '');
            return parseFloat(val) || 0;
        };

        const paid = getVal(amountReceived);
        const amount = getVal(amountInput);
        const change = paid - amount;
        changeDue.value = change >= 0 ? change.toFixed(2) : '0.00';
    }

    paymentTypeRadios.forEach(radio => radio.addEventListener('change', updatePaymentType));
    paymentMethod.addEventListener('change', updatePaymentMethod);
    
    // Amount Received Events
    amountReceived.addEventListener('input', updateChangeCalculation);
    amountReceived.addEventListener('keyup', updateChangeCalculation);
    amountReceived.addEventListener('change', updateChangeCalculation);
    
    // Payment Amount Events
    amountInput.addEventListener('input', calculateBaseEquivalent);
    amountInput.addEventListener('keyup', calculateBaseEquivalent);
    amountInput.addEventListener('change', calculateBaseEquivalent);
    
    paymentCurrency.addEventListener('change', updateCurrencyDisplay);
    exchangeRateInput.addEventListener('input', calculateBaseEquivalent);

    updatePaymentType();
    updatePaymentMethod();
    updateCurrencyDisplay();
});
</script>
@endsection
