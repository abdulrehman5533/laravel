@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Edit Expense Record</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.expense.index') }}" class="text-decoration-none text-muted">Expenses</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Modify #EXP-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('accounts.expense.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Registry
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <!-- Status Alert -->
            <div class="card border-0 shadow-sm rounded-20 mb-4 overflow-hidden">
                <div class="d-flex align-items-center p-3 {{ $expense->status === 'approved' ? 'bg-success-soft text-success' : ($expense->status === 'pending' ? 'bg-warning-soft text-warning' : 'bg-danger-soft text-danger') }}">
                    <div class="stat-icon bg-white text-dark me-3 shadow-sm">
                        <i class="fas {{ $expense->status === 'approved' ? 'fa-check' : ($expense->status === 'pending' ? 'fa-clock' : 'fa-times') }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-800 text-uppercase small tracking-wider">Current Status: {{ ucfirst($expense->status) }}</h6>
                        @if($expense->status === 'rejected')
                            <p class="mb-0 small fw-600 mt-1">Reason: {{ $expense->rejection_reason }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <form action="{{ route('accounts.expense.update', $expense->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Main Form Card -->
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-5">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-gold text-dark me-3">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <h5 class="text-white mb-0 fw-800">Update Transaction Details</h5>
                                <p class="text-white-50 small mb-0">Record Reference: <strong>#EXP-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }}</strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <!-- Date & Branch Section -->
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Transaction Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="date" name="date" class="form-control border-light bg-light rounded-end-12 py-2 @error('date') is-invalid @enderror" 
                                           value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
                                </div>
                                @error('date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Origin Branch <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <select name="branch_id" class="form-select border-light bg-light rounded-end-12 py-2 @error('branch_id') is-invalid @enderror" required>
                                        <option value="">-- Select Branch --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id', $expense->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Classification -->
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Primary Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select border-light bg-light rounded-12 py-2 @error('category_id') is-invalid @enderror" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Sub-Category</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-select border-light bg-light rounded-12 py-2 @error('subcategory_id') is-invalid @enderror">
                                    <option value="">-- Select Subcategory --</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $expense->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 border-top my-4"></div>

                            <!-- Financials -->
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Vendor / Payee Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light rounded-start-12"><i class="fas fa-user-tie text-muted"></i></span>
                                    <input type="text" name="vendor_name" class="form-control border-light bg-light rounded-end-12 py-2 @error('vendor_name') is-invalid @enderror"
                                           value="{{ old('vendor_name', $expense->vendor_name) }}" maxlength="100" placeholder="e.g. Acme Services">
                                </div>
                                @error('vendor_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-gold border-gold text-dark fw-bold rounded-start-12">Rs.</span>
                                    <input type="number" name="amount" step="0.01" min="0.01" class="form-control border-gold bg-light rounded-end-12 py-2 fw-800 fs-5 @error('amount') is-invalid @enderror"
                                           value="{{ old('amount', $expense->amount) }}" required placeholder="0.00">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Payment Instrument <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select border-light bg-light rounded-12 py-2 @error('payment_method') is-invalid @enderror" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="cheque" {{ old('payment_method', $expense->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_card" {{ old('payment_method', $expense->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method', $expense->payment_method) == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="upi" {{ old('payment_method', $expense->payment_method) == 'upi' ? 'selected' : '' }}>UPI</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Transaction Memo / Description</label>
                                <textarea name="description" class="form-control border-light bg-light rounded-12 p-3 @error('description') is-invalid @enderror"
                                          rows="4" maxlength="500" placeholder="Provide additional context for this expense...">{{ old('description', $expense->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-3 justify-content-end mb-5">
                    <a href="{{ route('accounts.expense.index') }}" class="btn btn-white shadow-sm px-5 py-3 rounded-15 fw-700">
                        Cancel Changes
                    </a>
                    <button type="submit" class="btn btn-gold shadow-gold px-5 py-3 rounded-15 fw-800">
                        Save Modifications <i class="fas fa-save ms-2 small"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-24 { border-radius: 24px; }
    
    .tracking-wider { letter-spacing: 0.05em; }
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .border-gold { border-color: #d4af37; }
    .text-gold { color: #d4af37; }
    
    .shadow-premium { box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .shadow-gold { box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3); }
    
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(212, 175, 55, 0.5);
        color: #1a1a1a;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.1rem;
    }

    .input-group-text {
        border-right: none;
    }
    .form-control:focus, .form-select:focus {
        border-color: #d4af37;
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.1);
        background-color: #fff;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');

    // Load subcategories on category change
    categorySelect.addEventListener('change', function() {
        if (this.value) {
            fetch(`/accounts/expense/subcategories?category_id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    const currentValue = '{{ old("subcategory_id", $expense->subcategory_id) }}';
                    subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        if (currentValue == subcategory.id) {
                            option.selected = true;
                        }
                        subcategorySelect.appendChild(option);
                    });
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
        }
    });

    // Trigger initial state
    if (categorySelect.value) {
        // Only dispatch if it's a fresh load, otherwise use populated options from blade
        if (subcategorySelect.options.length <= 1) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endsection
