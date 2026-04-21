@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">New Expense Entry</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.expense.index') }}" class="text-decoration-none text-muted">Expenses</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Create Record</li>
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
            <form action="{{ route('accounts.expense.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Main Form Card -->
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-gold text-dark me-3">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <h5 class="text-white mb-0 fw-800">Transaction Details</h5>
                                <p class="text-white-50 small mb-0">All fields marked with <span class="text-gold">*</span> are required</p>
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
                                           value="{{ old('date', date('Y-m-d')) }}" required>
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
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                           value="{{ old('vendor_name') }}" maxlength="100" placeholder="e.g. Acme Services">
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
                                           value="{{ old('amount') }}" required placeholder="0.00">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Payment Instrument <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select border-light bg-light rounded-12 py-2 @error('payment_method') is-invalid @enderror" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method') == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Recurring -->
                            <div class="col-md-6">
                                <div class="glass-input-wrapper p-3 rounded-15 border">
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" name="is_recurring" id="is_recurring" class="form-check-input custom-switch"
                                               {{ old('is_recurring') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-700 text-dark ms-2" for="is_recurring">
                                            Recurring Transaction
                                        </label>
                                    </div>
                                    <div id="recurrence_type_div" class="mt-3 d-none">
                                        <select name="recurrence_type" class="form-select form-select-sm border-light bg-white rounded-8 @error('recurrence_type') is-invalid @enderror">
                                            <option value="">-- Frequency --</option>
                                            <option value="daily" {{ old('recurrence_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ old('recurrence_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ old('recurrence_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="yearly" {{ old('recurrence_type') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Transaction Memo / Description</label>
                                <textarea name="description" class="form-control border-light bg-light rounded-12 p-3 @error('description') is-invalid @enderror"
                                          rows="4" maxlength="500" placeholder="Provide additional context for this expense...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="text-end mt-1">
                                    <small class="text-muted fw-600"><span id="charCount">0</span> / 500 characters</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attachments Card -->
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-5">
                    <div class="card-header bg-white p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info-soft text-info rounded-10 me-3">
                                <i class="fas fa-paperclip"></i>
                            </div>
                            <h5 class="mb-0 fw-800">Support Documentation</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5 pt-0">
                        <div class="upload-zone p-5 rounded-20 border-2 border-dashed border-light text-center position-relative mb-3">
                            <input type="file" name="attachments[]" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" 
                                   multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" id="fileInput">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h6 class="fw-800 text-dark">Click or Drag Files Here</h6>
                            <p class="text-muted small mb-0">Upload receipts, invoices, or delivery notes (PDF, JPG, PNG, DOC, XLS)</p>
                            <div id="fileList" class="mt-3 row g-2"></div>
                        </div>
                        @error('attachments')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-3 justify-content-end mb-5">
                    <a href="{{ route('accounts.expense.index') }}" class="btn btn-white shadow-sm px-5 py-3 rounded-15 fw-700">
                        Discard Changes
                    </a>
                    <button type="submit" class="btn btn-gold shadow-gold px-5 py-3 rounded-15 fw-800">
                        Complete Registration <i class="fas fa-chevron-right ms-2 small"></i>
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

    .custom-switch {
        width: 3.5rem;
        height: 1.75rem;
        cursor: pointer;
    }
    .custom-switch:checked {
        background-color: #d4af37;
        border-color: #d4af37;
    }

    .upload-zone {
        transition: all 0.3s ease;
        background: #fcfcfc;
    }
    .upload-zone:hover {
        border-color: #d4af37 !important;
        background: #fff;
    }
    .cursor-pointer { cursor: pointer; }

    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurrenceTypeDiv = document.getElementById('recurrence_type_div');
    const descriptionArea = document.querySelector('textarea[name="description"]');
    const charCount = document.getElementById('charCount');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    // Character counter
    descriptionArea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    // File preview
    fileInput.addEventListener('change', function() {
        fileList.innerHTML = '';
        if (this.files) {
            Array.from(this.files).forEach(file => {
                const col = document.createElement('div');
                col.className = 'col-md-4';
                col.innerHTML = `
                    <div class="p-2 bg-light rounded-10 border d-flex align-items-center">
                        <i class="fas fa-file-alt text-muted me-2"></i>
                        <span class="small text-truncate fw-600">${file.name}</span>
                    </div>
                `;
                fileList.appendChild(col);
            });
        }
    });

    // Load subcategories on category change
    categorySelect.addEventListener('change', function() {
        if (this.value) {
            fetch(`/accounts/expense/subcategories?category_id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        if ('{{ old("subcategory_id") }}' == subcategory.id) {
                            option.selected = true;
                        }
                        subcategorySelect.appendChild(option);
                    });
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
        }
    });

    // Show/hide recurrence type
    isRecurringCheckbox.addEventListener('change', function() {
        if (this.checked) {
            recurrenceTypeDiv.classList.remove('d-none');
            recurrenceTypeDiv.classList.add('animate__animated', 'animate__fadeInDown');
        } else {
            recurrenceTypeDiv.classList.add('d-none');
        }
    });

    // Trigger initial state
    if (categorySelect.value) categorySelect.dispatchEvent(new Event('change'));
    if (isRecurringCheckbox.checked) recurrenceTypeDiv.classList.remove('d-none');
    charCount.textContent = descriptionArea.value.length;
});
</script>
@endsection
