@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="fw-800 mb-0">{{ isset($cashbook) ? 'Edit Cash Entry' : 'New Cash Entry' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.cashbook.index') }}" class="text-decoration-none text-muted">Cashbook</a></li>
                    <li class="breadcrumb-item active">{{ isset($cashbook) ? 'Edit' : 'Create' }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('accounts.cashbook.index') }}" class="btn btn-light border rounded-10 px-4">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <form method="POST" action="{{ isset($cashbook) ? route('accounts.cashbook.update', $cashbook->id) : route('accounts.cashbook.store') }}">
                @csrf
                @if(isset($cashbook)) @method('PUT') @endif

                <div class="card border-0 shadow-sm rounded-16 mb-4">
                    <div class="card-header bg-dark text-white p-4 border-0 rounded-top-16">
                        <h6 class="fw-800 mb-0 text-white"><i class="fas fa-cash-register me-2 text-warning"></i>Transaction Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">

                            {{-- Transaction Type --}}
                            <div class="col-12">
                                <label class="form-label small fw-700 text-uppercase text-muted">Transaction Type <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="entry_type" id="type_in" value="cash_in"
                                            {{ old('entry_type', $cashbook->entry_type ?? 'cash_in') == 'cash_in' ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-success w-100 py-3 rounded-12 fw-700" for="type_in">
                                            <i class="fas fa-arrow-down me-2"></i>Cash In (Received)
                                        </label>
                                    </div>
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="entry_type" id="type_out" value="cash_out"
                                            {{ old('entry_type', $cashbook->entry_type ?? '') == 'cash_out' ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-danger w-100 py-3 rounded-12 fw-700" for="type_out">
                                            <i class="fas fa-arrow-up me-2"></i>Cash Out (Paid)
                                        </label>
                                    </div>
                                </div>
                                @error('entry_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Date & Branch --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control rounded-10 @error('date') is-invalid @enderror"
                                    value="{{ old('date', isset($cashbook) ? $cashbook->date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select rounded-10 @error('branch_id') is-invalid @enderror" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $cashbook->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Category & Subcategory --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Category <span class="text-danger">*</span></label>
                                <select name="category" id="categorySelect" class="form-select rounded-10 @error('category') is-invalid @enderror" required>
                                    <option value="">-- Select Category --</option>
                                    <optgroup label="💰 Cash In Categories">
                                        <option value="Sales Collection" {{ old('category', $cashbook->category ?? '') == 'Sales Collection' ? 'selected' : '' }}>Sales Collection</option>
                                        <option value="Loan Recovery" {{ old('category', $cashbook->category ?? '') == 'Loan Recovery' ? 'selected' : '' }}>Loan Recovery</option>
                                        <option value="Customer Advance" {{ old('category', $cashbook->category ?? '') == 'Customer Advance' ? 'selected' : '' }}>Customer Advance</option>
                                        <option value="Bank Withdrawal" {{ old('category', $cashbook->category ?? '') == 'Bank Withdrawal' ? 'selected' : '' }}>Bank Withdrawal</option>
                                        <option value="Investment Received" {{ old('category', $cashbook->category ?? '') == 'Investment Received' ? 'selected' : '' }}>Investment Received</option>
                                        <option value="Refund Received" {{ old('category', $cashbook->category ?? '') == 'Refund Received' ? 'selected' : '' }}>Refund Received</option>
                                        <option value="Opening Balance" {{ old('category', $cashbook->category ?? '') == 'Opening Balance' ? 'selected' : '' }}>Opening Balance</option>
                                        <option value="Other Income" {{ old('category', $cashbook->category ?? '') == 'Other Income' ? 'selected' : '' }}>Other Income</option>
                                    </optgroup>
                                    <optgroup label="💸 Cash Out Categories">
                                        <option value="Supplier Payment" {{ old('category', $cashbook->category ?? '') == 'Supplier Payment' ? 'selected' : '' }}>Supplier Payment</option>
                                        <option value="Staff Salary" {{ old('category', $cashbook->category ?? '') == 'Staff Salary' ? 'selected' : '' }}>Staff Salary</option>
                                        <option value="Staff Advance" {{ old('category', $cashbook->category ?? '') == 'Staff Advance' ? 'selected' : '' }}>Staff Advance</option>
                                        <option value="Rent" {{ old('category', $cashbook->category ?? '') == 'Rent' ? 'selected' : '' }}>Rent</option>
                                        <option value="Utilities" {{ old('category', $cashbook->category ?? '') == 'Utilities' ? 'selected' : '' }}>Utilities (Electricity/Water)</option>
                                        <option value="Bank Deposit" {{ old('category', $cashbook->category ?? '') == 'Bank Deposit' ? 'selected' : '' }}>Bank Deposit</option>
                                        <option value="Petty Cash" {{ old('category', $cashbook->category ?? '') == 'Petty Cash' ? 'selected' : '' }}>Petty Cash</option>
                                        <option value="Purchase Payment" {{ old('category', $cashbook->category ?? '') == 'Purchase Payment' ? 'selected' : '' }}>Purchase Payment</option>
                                        <option value="Repair & Maintenance" {{ old('category', $cashbook->category ?? '') == 'Repair & Maintenance' ? 'selected' : '' }}>Repair & Maintenance</option>
                                        <option value="Transport" {{ old('category', $cashbook->category ?? '') == 'Transport' ? 'selected' : '' }}>Transport</option>
                                        <option value="Tax Payment" {{ old('category', $cashbook->category ?? '') == 'Tax Payment' ? 'selected' : '' }}>Tax Payment</option>
                                        <option value="Loan Given" {{ old('category', $cashbook->category ?? '') == 'Loan Given' ? 'selected' : '' }}>Loan Given</option>
                                        <option value="Refund Given" {{ old('category', $cashbook->category ?? '') == 'Refund Given' ? 'selected' : '' }}>Refund Given</option>
                                        <option value="Other Expense" {{ old('category', $cashbook->category ?? '') == 'Other Expense' ? 'selected' : '' }}>Other Expense</option>
                                    </optgroup>
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Sub-Category <span class="text-muted fw-400">(optional)</span></label>
                                <input type="text" name="subcategory" class="form-control rounded-10"
                                    value="{{ old('subcategory', $cashbook->subcategory ?? '') }}"
                                    placeholder="e.g., Electricity Bill, Karigar Payment...">
                            </div>

                            {{-- Amount & Payment Method --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Amount (Rs.) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-warning text-dark fw-700 border-warning">Rs.</span>
                                    <input type="number" name="amount" step="0.01" min="0.01"
                                        class="form-control rounded-end-10 fw-800 fs-5 @error('amount') is-invalid @enderror"
                                        value="{{ old('amount', $cashbook->amount ?? '') }}"
                                        placeholder="0.00" required>
                                </div>
                                @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select rounded-10 @error('payment_method') is-invalid @enderror" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="Cash" {{ old('payment_method', $cashbook->payment_method ?? '') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="UPI" {{ old('payment_method', $cashbook->payment_method ?? '') == 'UPI' ? 'selected' : '' }}>UPI</option>
                                    <option value="Bank Transfer" {{ old('payment_method', $cashbook->payment_method ?? '') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Cheque" {{ old('payment_method', $cashbook->payment_method ?? '') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="Card" {{ old('payment_method', $cashbook->payment_method ?? '') == 'Card' ? 'selected' : '' }}>Card</option>
                                    <option value="NEFT/RTGS" {{ old('payment_method', $cashbook->payment_method ?? '') == 'NEFT/RTGS' ? 'selected' : '' }}>NEFT/RTGS</option>
                                </select>
                                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label small fw-700 text-uppercase text-muted">Description / Narration</label>
                                <textarea name="description" class="form-control rounded-10" rows="3"
                                    placeholder="Describe this transaction in detail...">{{ old('description', $cashbook->description ?? '') }}</textarea>
                            </div>

                            {{-- Reference --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Reference Type <span class="text-muted fw-400">(optional)</span></label>
                                <select name="reference_type" class="form-select rounded-10">
                                    <option value="">-- None --</option>
                                    <option value="Sale" {{ old('reference_type', $cashbook->reference_type ?? '') == 'Sale' ? 'selected' : '' }}>Sale Invoice</option>
                                    <option value="Purchase" {{ old('reference_type', $cashbook->reference_type ?? '') == 'Purchase' ? 'selected' : '' }}>Purchase Order</option>
                                    <option value="Expense" {{ old('reference_type', $cashbook->reference_type ?? '') == 'Expense' ? 'selected' : '' }}>Expense</option>
                                    <option value="Girvi" {{ old('reference_type', $cashbook->reference_type ?? '') == 'Girvi' ? 'selected' : '' }}>Girvi Loan</option>
                                    <option value="Payroll" {{ old('reference_type', $cashbook->reference_type ?? '') == 'Payroll' ? 'selected' : '' }}>Payroll</option>
                                    <option value="Other" {{ old('reference_type', $cashbook->reference_type ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-700 text-uppercase text-muted">Reference ID / Number <span class="text-muted fw-400">(optional)</span></label>
                                <input type="text" name="reference_id" class="form-control rounded-10"
                                    value="{{ old('reference_id', $cashbook->reference_id ?? '') }}"
                                    placeholder="e.g., INV-001, PO-123">
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 p-4 d-flex justify-content-end gap-3">
                        <a href="{{ route('accounts.cashbook.index') }}" class="btn btn-light border rounded-10 px-4 fw-600">Cancel</a>
                        <button type="submit" class="btn btn-gold rounded-10 px-5 fw-700">
                            <i class="fas fa-save me-2"></i>{{ isset($cashbook) ? 'Update Entry' : 'Save Entry' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sidebar Guide --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-16 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-800 mb-0"><i class="fas fa-info-circle text-primary me-2"></i>What to Enter Here</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-success me-2 mt-1">Cash In</span>
                            <div class="small text-muted">
                                Customer payment received, bank withdrawal, loan recovery, advance received
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <span class="badge bg-danger me-2 mt-1">Cash Out</span>
                            <div class="small text-muted">
                                Supplier paid, salary given, rent paid, bank deposit, petty cash issued
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning border-0 rounded-10 small mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> Cashbook = physical cash only. For bank/card transactions use Expenses module.
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-16 bg-dark text-white">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-shield-alt fa-2x text-warning mb-3"></i>
                    <h6 class="fw-800 mb-2">Audit Protected</h6>
                    <p class="text-white-50 small mb-0">Once verified by admin, entries are locked and cannot be modified.</p>
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
    .rounded-12 { border-radius: 12px; }
    .rounded-16 { border-radius: 16px; }
    .rounded-top-16 { border-radius: 16px 16px 0 0; }
    .btn-gold { background: linear-gradient(135deg, #d4af37, #c5a02e); color: #1a1a1a; border: none; }
    .btn-gold:hover { opacity: 0.9; color: #1a1a1a; }
    .btn-check:checked + .btn-outline-success { background: #198754; color: white; }
    .btn-check:checked + .btn-outline-danger { background: #dc3545; color: white; }
</style>
@endsection
