@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-undo text-danger"></i> Create Return / Repair</h2>
        <a href="{{ route('pos.returns.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0"><i class="fas fa-plus-circle"></i> New Return/Repair Entry</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.returns.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Completed Sale <span class="text-danger">*</span></label>
                            <select name="pos_sale_id" id="pos_sale_id" class="form-select @error('pos_sale_id') is-invalid @enderror" required>
                                <option value="">-- Select Sale --</option>
                                @foreach($sales as $sale)
                                    <option value="{{ $sale->id }}" {{ old('pos_sale_id') == $sale->id ? 'selected' : '' }}>
                                        Invoice #{{ $sale->invoice_no }} - {{ $sale->customer->name ?? 'Walk-in' }} ({{ $sale->created_at->format('Y-m-d') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pos_sale_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div id="sale_details_container" style="display: none;" class="alert alert-info py-2 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Customer:</strong> <span id="display_customer">--</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Invoice Total:</strong> <span id="display_total">--</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Item <span class="text-danger">*</span></label>
                            <select name="pos_sale_item_id" id="pos_sale_item_id" class="form-select @error('pos_sale_item_id') is-invalid @enderror" required disabled>
                                <option value="">-- Select Item --</option>
                            </select>
                            @error('pos_sale_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Quantity Returned <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_returned" id="quantity_returned" class="form-control @error('quantity_returned') is-invalid @enderror" value="{{ old('quantity_returned', 0) }}" step="0.001" min="0" required>
                                <small class="text-muted">Max: <span id="max_quantity">0</span></small>
                                @error('quantity_returned')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Return Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="return" {{ old('type') == 'return' ? 'selected' : '' }}>Return</option>
                                    <option value="exchange" {{ old('type') == 'exchange' ? 'selected' : '' }}>Exchange</option>
                                    <option value="repair" {{ old('type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}" placeholder="e.g. Manufacturing defect, Wrong size" required>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Refund Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" name="refund_amount" class="form-control @error('refund_amount') is-invalid @enderror" value="{{ old('refund_amount', 0) }}" step="0.01" min="0">
                                </div>
                                @error('refund_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Refund Method</label>
                                <select name="refund_method" class="form-select @error('refund_method') is-invalid @enderror">
                                    <option value="original" {{ old('refund_method') == 'original' ? 'selected' : '' }}>Original Payment Method</option>
                                    <option value="cash" {{ old('refund_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('refund_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                                @error('refund_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Additional details...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-save"></i> Save Return Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saleSelect = document.getElementById('pos_sale_id');
    const itemSelect = document.getElementById('pos_sale_item_id');
    const quantityInput = document.getElementById('quantity_returned');
    const maxQtySpan = document.getElementById('max_quantity');
    const detailsContainer = document.getElementById('sale_details_container');
    const displayCustomer = document.getElementById('display_customer');
    const displayTotal = document.getElementById('display_total');

    let saleItems = [];

    saleSelect.addEventListener('change', function() {
        const saleId = this.value;
        if (!saleId) {
            itemSelect.disabled = true;
            itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
            detailsContainer.style.display = 'none';
            return;
        }

        fetch(`/pos/returns/get-sale-items/${saleId}`)
            .then(response => response.json())
            .then(data => {
                saleItems = data.items;
                itemSelect.disabled = false;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                
                saleItems.forEach(item => {
                    const productName = item.product ? item.product.name : 'Unknown Product';
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${productName} (${item.quantity} ${item.unit || 'pcs'}) - Rs ${item.price}`;
                    itemSelect.appendChild(option);
                });

                displayCustomer.textContent = data.customer ? data.customer.name : 'Walk-in';
                displayTotal.textContent = `${data.currency} ${data.total}`;
                detailsContainer.style.display = 'block';
            });
    });

    itemSelect.addEventListener('change', function() {
        const itemId = this.value;
        const selectedItem = saleItems.find(item => item.id == itemId);
        
        if (selectedItem) {
            quantityInput.value = selectedItem.quantity;
            quantityInput.max = selectedItem.quantity;
            maxQtySpan.textContent = selectedItem.quantity;
        } else {
            quantityInput.value = 0;
            maxQtySpan.textContent = 0;
        }
    });
});
</script>
@endpush
