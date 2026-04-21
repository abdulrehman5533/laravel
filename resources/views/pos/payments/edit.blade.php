@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit text-warning"></i> Edit Payment - Invoice #{{ $payment->sale->invoice_no }}</h2>
        <a href="{{ route('pos.payments.show', $payment) }}" class="btn btn-secondary">
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
                        <h6>{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->subtotal, 2) }}</h6>
                    </div>
                    @if($payment->sale->making_charges + $payment->sale->wastage_amount > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block">Making & Wastage</small>
                        <h6>{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->making_charges + $payment->sale->wastage_amount, 2) }}</h6>
                    </div>
                    @endif
                    @if($payment->sale->tax_amount > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block">Tax</small>
                        <h6>{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->tax_amount, 2) }}</h6>
                    </div>
                    @endif
                    @if($payment->sale->discount > 0)
                    <div class="mb-3 text-danger">
                        <small class="text-muted d-block">Discount</small>
                        <h6>-{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->discount, 2) }}</h6>
                    </div>
                    @endif
                    <hr>
                    <div class="mb-3 bg-white p-3 rounded">
                        <small class="text-muted d-block">Total Amount Due</small>
                        <h4 class="text-primary mb-0">{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->total, 2) }}</h4>
                    </div>
                    @if($payment->sale->payments()->exists())
                    <div class="mb-3 bg-white p-3 rounded">
                        <small class="text-muted d-block">Already Paid</small>
                        <h6 class="text-success mb-0">{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->payments()->where('id', '!=', $payment->id)->sum('amount'), 2) }}</h6>
                    </div>
                    <div class="mb-3 bg-white p-3 rounded">
                        <small class="text-muted d-block">Outstanding Balance After Change</small>
                        <h6 class="text-warning mb-0">{{ $payment->sale->currency ?? 'Rs' }} <span id="balanceDisplay">{{ number_format($payment->sale->outstanding_balance, 2) }}</span></h6>
                    </div>
                    @endif
                </div>
            </div>

            @if($payment->customer)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-user"></i> Customer</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ $payment->customer->name }}</strong></p>
                    <p class="small text-muted mb-1"><i class="fas fa-phone"></i> {{ $payment->customer->phone ?? 'N/A' }}</p>
                    <p class="small text-muted mb-3"><i class="fas fa-envelope"></i> {{ $payment->customer->email ?? 'N/A' }}</p>
                    @if($payment->customer->loyalty_points > 0)
                    <span class="badge bg-warning">
                        <i class="fas fa-gift"></i> {{ $payment->customer->loyalty_points }} Points
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Payment Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0"><i class="fas fa-money-bill-wave"></i> Payment Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.payments.update', $payment) }}" method="POST" id="paymentForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="pos_sale_id" value="{{ $payment->sale->id }}">
                        <input type="hidden" name="pos_customer_id" value="{{ $payment->pos_customer_id }}">
                        <input type="hidden" name="recorded_by" value="{{ auth()->id() }}">

                        <!-- Payment Status -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Payment Status</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="status" id="statusCompleted" value="completed" {{ $payment->status === 'completed' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success" for="statusCompleted">
                                        <i class="fas fa-check-circle"></i> Completed
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="status" id="statusPending" value="pending" {{ $payment->status === 'pending' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning" for="statusPending">
                                        <i class="fas fa-hourglass"></i> Pending
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" id="paymentMethod" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="cash" {{ $payment->payment_method === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                                    <option value="card" {{ $payment->payment_method === 'card' ? 'selected' : '' }}>💳 Credit/Debit Card</option>
                                    <option value="check" {{ $payment->payment_method === 'check' ? 'selected' : '' }}>🏦 Check/Cheque</option>
                                    <option value="bank_transfer" {{ $payment->payment_method === 'bank_transfer' ? 'selected' : '' }}>🏛️ Bank Transfer</option>
                                    <option value="upi" {{ $payment->payment_method === 'upi' ? 'selected' : '' }}>📱 UPI/Digital Wallet</option>
                                    <option value="crypto" {{ $payment->payment_method === 'crypto' ? 'selected' : '' }}>₿ Cryptocurrency</option>
                                </select>
                                @error('payment_method')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $payment->currency ?? 'Rs' }}</span>
                                    <input type="number" name="amount" id="paymentAmount" class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ $payment->amount }}" step="0.01" min="0.01" required>
                                </div>
                                @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Change Calculation (for cash only) -->
                        <div class="row mb-3" id="changeSection" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount Received (Cash)</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $payment->currency ?? 'Rs' }}</span>
                                    <input type="number" id="amountReceived" class="form-control" step="0.01" min="0.01" placeholder="Enter amount received">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Change Due</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $payment->currency ?? 'Rs' }}</span>
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
                                    <input type="text" name="cheque_number" class="form-control" value="{{ $payment->cheque_number ?? '' }}" placeholder="e.g., CHQ12345">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $payment->bank_name ?? '' }}" placeholder="e.g., Bank of America">
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
                                    <input type="text" name="transaction_id" class="form-control" value="{{ $payment->transaction_id ?? '' }}" placeholder="TXN123456789">
                                </div>
                            </div>

                            <!-- Bank Transfer Fields -->
                            <div id="bankFields" class="row mb-3" style="display:none;">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $payment->bank_name ?? '' }}" placeholder="e.g., Chase Bank">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" name="reference" class="form-control" value="{{ $payment->reference ?? '' }}" placeholder="Reference/UTR">
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label">Notes / Reference</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes about this payment...">{{ $payment->notes ?? '' }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save"></i> Update Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('paymentMethod');
    const changeSection = document.getElementById('changeSection');
    const amountInput = document.getElementById('paymentAmount');
    const amountReceived = document.getElementById('amountReceived');
    const changeDue = document.getElementById('changeDue');
    
    function updateChangeSection() {
        if (paymentMethod.value === 'cash') {
            changeSection.style.display = 'flex';
            calculateChange();
        } else {
            changeSection.style.display = 'none';
        }
    }

    function updateMethodFields() {
        document.getElementById('chequeFields').style.display = 'none';
        document.getElementById('cardFields').style.display = 'none';
        document.getElementById('bankFields').style.display = 'none';

        if (paymentMethod.value === 'check') {
            document.getElementById('chequeFields').style.display = 'flex';
        } else if (paymentMethod.value === 'card') {
            document.getElementById('cardFields').style.display = 'flex';
        } else if (paymentMethod.value === 'bank_transfer') {
            document.getElementById('bankFields').style.display = 'flex';
        }
        
        updateChangeSection();
    }

    function calculateChange() {
        const getVal = (el) => {
            if (!el) return 0;
            let val = el.value.toString().replace(/,/g, '');
            return parseFloat(val) || 0;
        };

        const amount = getVal(amountInput);
        const received = getVal(amountReceived);
        const change = Math.max(0, received - amount);
        changeDue.value = change.toFixed(2);
    }

    paymentMethod.addEventListener('change', updateMethodFields);
    
    // Amount Received Events
    amountReceived.addEventListener('input', calculateChange);
    amountReceived.addEventListener('keyup', calculateChange);
    amountReceived.addEventListener('change', calculateChange);
    
    // Amount Input Events
    amountInput.addEventListener('input', calculateChange);
    amountInput.addEventListener('keyup', calculateChange);
    amountInput.addEventListener('change', calculateChange);

    updateMethodFields();
});
</script>
@endsection
