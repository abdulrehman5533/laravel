@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Procurement Revision</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.purchases.orders.index') }}" class="text-decoration-none text-muted">Supply Chain</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Revise PO-{{ $purchaseOrder->po_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('accounts.purchases.orders.show', $purchaseOrder) }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Intelligence
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('accounts.purchases.orders.update', $purchaseOrder) }}">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <!-- Form Body -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-gold rounded-10 me-3">
                                <i class="fas fa-edit text-dark"></i>
                            </div>
                            <h5 class="mb-0 text-white fw-800">Operational Amendment</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <!-- Primary Intelligence -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Strategic Partner <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-handshake text-primary"></i></span>
                                    <select name="supplier_id" class="form-select bg-transparent border-0 @error('supplier_id') is-invalid @enderror" required>
                                        <option value="">Select Partner</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('supplier_id')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Issuance Date <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" name="po_date" class="form-control bg-transparent border-0 @error('po_date') is-invalid @enderror"
                                           value="{{ old('po_date', $purchaseOrder->po_date->format('Y-m-d')) }}" required>
                                </div>
                                @error('po_date')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Logistics & Classification -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Material Classification <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-gem text-primary"></i></span>
                                    <select name="material_type" class="form-select bg-transparent border-0 @error('material_type') is-invalid @enderror" required>
                                        <option value="">Select Classification</option>
                                        <option value="Gold" {{ old('material_type', $purchaseOrder->material_type) == 'Gold' ? 'selected' : '' }}>Gold</option>
                                        <option value="Silver" {{ old('material_type', $purchaseOrder->material_type) == 'Silver' ? 'selected' : '' }}>Silver</option>
                                        <option value="Diamond" {{ old('material_type', $purchaseOrder->material_type) == 'Diamond' ? 'selected' : '' }}>Diamond</option>
                                        <option value="Gems" {{ old('material_type', $purchaseOrder->material_type) == 'Gems' ? 'selected' : '' }}>Gems</option>
                                        <option value="Other" {{ old('material_type', $purchaseOrder->material_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                @error('material_type')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Target Logistics Date <span class="text-danger">*</span></label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-truck-loading text-primary"></i></span>
                                    <input type="date" name="expected_delivery_date" class="form-control bg-transparent border-0 @error('expected_delivery_date') is-invalid @enderror"
                                           value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date->format('Y-m-d')) }}" required>
                                </div>
                                @error('expected_delivery_date')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Technical Specs -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Reference Protocol</label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-hashtag text-primary"></i></span>
                                    <input type="text" name="reference_number" class="form-control bg-transparent border-0 @error('reference_number') is-invalid @enderror"
                                           value="{{ old('reference_number', $purchaseOrder->reference_number) }}" placeholder="Internal Reference ID">
                                </div>
                                @error('reference_number')
                                    <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Other Charges Description</label>
                                <div class="glass-input-group">
                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-info-circle text-primary"></i></span>
                                    <input type="text" name="other_charges_description" class="form-control bg-transparent border-0 @error('other_charges_description') is-invalid @enderror"
                                           value="{{ old('other_charges_description', $purchaseOrder->other_charges_description) }}" placeholder="Logistics, Insurance, etc.">
                                </div>
                            </div>
                        </div>

                        <!-- Descriptive Brief -->
                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Operational Executive Brief</label>
                            <textarea name="description" class="form-control rounded-20 bg-light border-0 p-4 @error('description') is-invalid @enderror" 
                                      rows="4" placeholder="Detailed scope of procurement...">{{ old('description', $purchaseOrder->description) }}</textarea>
                            @error('description')
                                <div class="text-danger small mt-1 fw-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Terms & Conditions</label>
                                <textarea name="terms_conditions" class="form-control rounded-20 bg-light border-0 p-4 @error('terms_conditions') is-invalid @enderror" 
                                          rows="3" placeholder="Legal and operational constraints...">{{ old('terms_conditions', $purchaseOrder->terms_conditions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Internal Audit Notes</label>
                                <textarea name="notes" class="form-control rounded-20 bg-light border-0 p-4 @error('notes') is-invalid @enderror" 
                                          rows="3" placeholder="Restricted audit observations...">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Intelligence Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4 sticky-top" style="top: 2rem;">
                    <div class="card-header bg-premium-dark p-4 border-0">
                        <h5 class="mb-0 text-white fw-800">Financial Revision</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Sub-Total Protocol</label>
                            <div class="glass-input-group">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-coins text-gold"></i></span>
                                <input type="number" name="sub_total" id="subTotal" class="form-control bg-transparent border-0 fw-800 text-dark @error('sub_total') is-invalid @enderror"
                                       value="{{ old('sub_total', $purchaseOrder->sub_total) }}" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">GST %</label>
                                <div class="glass-input-group">
                                    <input type="number" name="gst_percentage" id="gstPercentage" class="form-control bg-transparent border-0 fw-700 text-dark @error('gst_percentage') is-invalid @enderror"
                                           value="{{ old('gst_percentage', $purchaseOrder->gst_percentage) }}" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Disc %</label>
                                <div class="glass-input-group">
                                    <input type="number" name="discount_percentage" id="discountPercentage" class="form-control bg-transparent border-0 fw-700 text-dark @error('discount_percentage') is-invalid @enderror"
                                           value="{{ old('discount_percentage', $purchaseOrder->discount_percentage) }}" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase tracking-wider text-muted mb-2">Auxiliary Charges</label>
                            <div class="glass-input-group">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-plus-circle text-primary"></i></span>
                                <input type="number" name="other_charges" id="otherCharges" class="form-control bg-transparent border-0 fw-700 text-dark @error('other_charges') is-invalid @enderror"
                                       value="{{ old('other_charges', $purchaseOrder->other_charges) }}" step="0.01" min="0">
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        <div class="p-4 bg-light-soft rounded-24 border border-light mb-4 text-center">
                            <span class="text-uppercase small fw-800 text-muted tracking-widest d-block mb-1">Revised Net Value</span>
                            <div class="h3 mb-0 fw-900 text-primary">Rs. <span id="totalAmountDisplay">0.00</span></div>
                            <input type="hidden" name="total_amount" id="totalAmount" value="{{ old('total_amount', $purchaseOrder->total_amount) }}">
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-premium-dark shadow-premium py-3 rounded-15 fw-800 text-white">
                                <i class="fas fa-save me-2 text-gold"></i>Commit Amendments
                            </button>
                            <a href="{{ route('accounts.purchases.orders.show', $purchaseOrder) }}" class="btn btn-light py-3 rounded-15 fw-700 text-muted">
                                Discard Changes
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
    .fw-600 { font-weight: 600; }
    
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
        const subTotal = document.getElementById('subTotal');
        const gstPercentage = document.getElementById('gstPercentage');
        const discountPercentage = document.getElementById('discountPercentage');
        const otherCharges = document.getElementById('otherCharges');
        const totalAmount = document.getElementById('totalAmount');
        const totalAmountDisplay = document.getElementById('totalAmountDisplay');

        function calculateTotal() {
            const sub = parseFloat(subTotal.value) || 0;
            const gst = sub * (parseFloat(gstPercentage.value) || 0) / 100;
            const discount = sub * (parseFloat(discountPercentage.value) || 0) / 100;
            const other = parseFloat(otherCharges.value) || 0;
            
            const total = sub + gst + other - discount;
            const formattedTotal = total.toFixed(2);
            totalAmount.value = formattedTotal;
            totalAmountDisplay.innerText = new Intl.NumberFormat('en-IN').format(formattedTotal);
        }

        [subTotal, gstPercentage, discountPercentage, otherCharges].forEach(el => {
            el.addEventListener('input', calculateTotal);
        });
        
        calculateTotal();
    });
</script>
@endsection
