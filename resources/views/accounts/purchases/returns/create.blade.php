@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-plus-circle text-warning me-2"></i>Initiate Purchase Return
            </h1>
            <p class="text-muted mt-1">Create a new return for a received purchase order</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('accounts.purchases.returns.store') }}">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Purchase Order <span class="text-danger">*</span></label>
                                <select name="purchase_order_id" id="purchase_order_id" class="form-select @error('purchase_order_id') is-invalid @enderror" required>
                                    <option value="">Select Received Purchase Order</option>
                                    @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" 
                                                data-total="{{ $po->total_amount }}"
                                                data-supplier="{{ $po->supplier->name ?? 'Unknown' }}"
                                                {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                            {{ $po->po_number }} - {{ $po->supplier->name ?? 'N/A' }} (Total: Rs.{{ number_format($po->total_amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('purchase_order_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Return Reason <span class="text-danger">*</span></label>
                                <select name="return_reason" class="form-select @error('return_reason') is-invalid @enderror" required>
                                    <option value="">Select Reason</option>
                                    <option value="Quality_Defect" {{ old('return_reason') == 'Quality_Defect' ? 'selected' : '' }}>Quality Defect</option>
                                    <option value="Quantity_Variance" {{ old('return_reason') == 'Quantity_Variance' ? 'selected' : '' }}>Quantity Variance</option>
                                    <option value="Purity_Issue" {{ old('return_reason') == 'Purity_Issue' ? 'selected' : '' }}>Purity Issue</option>
                                    <option value="Damaged" {{ old('return_reason') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                                    <option value="Not_As_Ordered" {{ old('return_reason') == 'Not_As_Ordered' ? 'selected' : '' }}>Not As Ordered</option>
                                    <option value="Other" {{ old('return_reason') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('return_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Subtotal (Return Value) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" name="subtotal" id="subtotal" class="form-control @error('subtotal') is-invalid @enderror"
                                           value="{{ old('subtotal') }}" step="0.01" min="0" required>
                                </div>
                                @error('subtotal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">GST Percentage (%) <span class="text-danger">*</span></label>
                                <input type="number" name="gst_percentage" id="gst_percentage" class="form-control @error('gst_percentage') is-invalid @enderror"
                                       value="{{ old('gst_percentage', 0) }}" step="0.01" min="0" max="28" required>
                                @error('gst_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Total Return Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rs.</span>
                                    <input type="text" id="total_amount" class="form-control bg-light" readonly value="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Reason Details</label>
                                <textarea name="return_reason_details" class="form-control @error('return_reason_details') is-invalid @enderror" 
                                          rows="3" placeholder="Provide more details about the return">{{ old('return_reason_details') }}</textarea>
                                @error('return_reason_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-end">
                                <a href="{{ route('accounts.purchases.returns.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Initiate Return
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Return Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Supplier:</span>
                        <span id="summary_supplier" class="fw-semibold">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Original PO Total:</span>
                        <span id="summary_po_total" class="fw-semibold">Rs.0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span id="summary_subtotal" class="fw-semibold">Rs.0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">GST Amount:</span>
                        <span id="summary_gst" class="fw-semibold">Rs.0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="h6 mb-0">Total Return:</span>
                        <span id="summary_total" class="h6 mb-0 fw-bold text-danger">Rs.0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const poSelect = document.getElementById('purchase_order_id');
        const subtotalInput = document.getElementById('subtotal');
        const gstInput = document.getElementById('gst_percentage');
        const totalInput = document.getElementById('total_amount');
        
        const summarySupplier = document.getElementById('summary_supplier');
        const summaryPoTotal = document.getElementById('summary_po_total');
        const summarySubtotal = document.getElementById('summary_subtotal');
        const summaryGst = document.getElementById('summary_gst');
        const summaryTotal = document.getElementById('summary_total');

        function calculateReturn() {
            const subtotal = parseFloat(subtotalInput.value) || 0;
            const gstPercent = parseFloat(gstInput.value) || 0;
            const gstAmount = subtotal * (gstPercent / 100);
            const total = subtotal + gstAmount;

            totalInput.value = total.toFixed(2);
            
            summarySubtotal.textContent = 'Rs.' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
            summaryGst.textContent = 'Rs.' + gstAmount.toLocaleString('en-IN', {minimumFractionDigits: 2});
            summaryTotal.textContent = 'Rs.' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});

            const selectedOption = poSelect.options[poSelect.selectedIndex];
            if (selectedOption) {
                summarySupplier.textContent = selectedOption.dataset.supplier || '-';
                summaryPoTotal.textContent = 'Rs.' + (parseFloat(selectedOption.dataset.total) || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
            }
        }

        poSelect.addEventListener('change', calculateReturn);
        subtotalInput.addEventListener('input', calculateReturn);
        gstInput.addEventListener('input', calculateReturn);
        
        calculateReturn();
    });
</script>
@endsection
