@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit text-primary"></i> Edit Sale #{{ $sale->id }}</h2>
        <div>
            <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-outline-info me-2">
                <i class="fas fa-eye"></i> View Sale
            </a>
            <a href="{{ route('pos.sales.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Sales List
            </a>
        </div>
    </div>

    <form action="{{ route('pos.sales.update', $sale) }}" method="POST" id="editSaleForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Sale Details -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Sale Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('sale_date') is-invalid @enderror"
                                       id="sale_date" name="sale_date"
                                       value="{{ old('sale_date', $sale->sale_date ? $sale->sale_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                @error('sale_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->full_name }} ({{ $customer->customer_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="discount_percentage" class="form-label">Discount Percentage (%)</label>
                                <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror"
                                       id="discount_percentage" name="discount_percentage"
                                       value="{{ old('discount_percentage', $sale->discount_percentage ?? 0) }}"
                                       min="0" max="100" step="0.01">
                                @error('discount_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="discount_amount" class="form-label">Discount Amount (Rs.)</label>
                                <input type="number" class="form-control @error('discount_amount') is-invalid @enderror"
                                       id="discount_amount" name="discount_amount"
                                       value="{{ old('discount_amount', $sale->discount_amount ?? 0) }}"
                                       min="0" step="0.01">
                                @error('discount_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $sale->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sale Items -->
                <div class="card mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0"><i class="fas fa-shopping-cart"></i> Sale Items</h6>
                        <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            @foreach($sale->items as $index => $item)
                            <div class="item-row border rounded p-3 mb-3" data-index="{{ $index }}">
                                <div class="row">
                                    <div class="col-md-4 mb-2 position-relative">
                                        <label class="form-label">Product</label>
                                        <input type="text" class="form-control product-search"
                                               placeholder="Search product by name..."
                                               value="{{ $item->inventoryProduct->name ?? 'Product not found' }}" required autocomplete="off">
                                        <div class="product-suggestions list-group shadow-sm position-absolute" style="display:none; z-index: 1000; width: 100%;"></div>
                                        <input type="hidden" name="items[{{ $index }}][inventory_product_id]"
                                               value="{{ $item->inventory_product_id }}">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Qty</label>
                                        <input type="number" class="form-control quantity"
                                               name="items[{{ $index }}][quantity]"
                                               value="{{ $item->quantity }}" min="1" step="1" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Unit Price</label>
                                        <input type="number" class="form-control unit-price"
                                               name="items[{{ $index }}][unit_price]"
                                               value="{{ $item->unit_price }}" min="0" step="0.01" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Total</label>
                                        <input type="number" class="form-control item-total" readonly
                                               value="{{ number_format($item->quantity * $item->unit_price, 2) }}">
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-danger remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Sale Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-calculator"></i> Sale Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">Rs. {{ number_format($sale->subtotal ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span id="tax">Rs. {{ number_format($sale->tax_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span id="discount">Rs. {{ number_format($sale->discount_amount ?? 0, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Grand Total:</strong>
                            <strong id="grandTotal" class="text-primary">Rs. {{ number_format($sale->grand_total ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Sale Info -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0"><i class="fas fa-info"></i> Sale Information</h6>
                    </div>
                    <div class="card-body small">
                        <p><strong>Sale ID:</strong> {{ $sale->id }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </p>
                        <p><strong>Created:</strong> {{ $sale->created_at->format('M d, Y H:i') }}</p>
                        <p><strong>Items:</strong> {{ $sale->items->count() }}</p>
                        @if($sale->customer)
                            <p><strong>Customer:</strong> {{ $sale->customer->full_name }}</p>
                        @else
                            <p><strong>Customer:</strong> Walk-in</p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="m-0"><i class="fas fa-cogs"></i> Actions</h6>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Update Sale
                        </button>
                        <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function(){
    let itemIndex = {{ $sale->items->count() }};

    // Add new item row
    document.getElementById('addItemBtn').addEventListener('click', function(){
        addItemRow();
    });

    function addItemRow(productId = '', productName = '', quantity = 1, unitPrice = 0) {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('div');
        row.className = 'item-row border rounded p-3 mb-3';
        row.setAttribute('data-index', itemIndex);

        row.innerHTML = `
            <div class="row">
                <div class="col-md-4 mb-2 position-relative">
                    <label class="form-label">Product</label>
                    <input type="text" class="form-control product-search"
                           placeholder="Search product by name..." value="${productName}" required autocomplete="off">
                    <div class="product-suggestions list-group shadow-sm position-absolute" style="display:none; z-index: 1000; width: 100%;"></div>
                    <input type="hidden" name="items[${itemIndex}][inventory_product_id]" value="${productId}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Qty</label>
                    <input type="number" class="form-control quantity"
                           name="items[${itemIndex}][quantity]" value="${quantity}" min="1" step="1" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" class="form-control unit-price"
                           name="items[${itemIndex}][unit_price]" value="${unitPrice}" min="0" step="0.01" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Total</label>
                    <input type="number" class="form-control item-total" readonly value="${(quantity * unitPrice).toFixed(2)}">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(row);
        attachEventListeners(row);
        itemIndex++;
        updateTotals();
    }

    function attachEventListeners(row) {
        // Remove item
        row.querySelector('.remove-item').addEventListener('click', function(){
            row.remove();
            updateTotals();
        });

        // Update totals on quantity/price change
        row.querySelector('.quantity').addEventListener('input', updateItemTotal);
        row.querySelector('.unit-price').addEventListener('input', updateItemTotal);

        // Product search
        const searchInput = row.querySelector('.product-search');
        const suggestions = row.querySelector('.product-suggestions');
        const idInput = row.querySelector('input[type="hidden"]');
        const priceInput = row.querySelector('.unit-price');
        let debounceTimer;

        searchInput.addEventListener('input', function(){
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                suggestions.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(async () => {
                const resp = await fetch(`{{ route('pos.products.lookup') }}?q=${encodeURIComponent(q)}`);
                const items = await resp.json();
                
                suggestions.innerHTML = '';
                items.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action py-2';
                    btn.innerHTML = `<div class="d-flex justify-content-between">
                        <span class="fw-bold text-dark">${item.sku}</span>
                        <span class="text-muted small">Rs.${item.unit_price}</span>
                    </div><div class="small text-muted">${item.description}</div>`;
                    btn.onclick = () => {
                        searchInput.value = item.description;
                        idInput.value = item.id;
                        priceInput.value = item.unit_price;
                        suggestions.style.display = 'none';
                        updateItemTotal.call(priceInput);
                    };
                    suggestions.appendChild(btn);
                });
                suggestions.style.display = items.length ? 'block' : 'none';
            }, 250);
        });

        // Close suggestions on click outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.style.display = 'none';
            }
        });
    }

    function updateItemTotal() {
        const row = this.closest('.item-row');
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const total = quantity * unitPrice;
        row.querySelector('.item-total').value = total.toFixed(2);
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-total').forEach(function(el){
            subtotal += parseFloat(el.value) || 0;
        });

        const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
        const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;

        let discount = discountAmount;
        if (discountPercentage > 0) {
            discount += (subtotal * discountPercentage / 100);
        }

        const tax = (subtotal - discount) * 0.1; // Assuming 10% tax
        const grandTotal = subtotal - discount + tax;

        document.getElementById('subtotal').textContent = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('tax').textContent = 'Rs. ' + tax.toFixed(2);
        document.getElementById('discount').textContent = 'Rs. ' + discount.toFixed(2);
        document.getElementById('grandTotal').textContent = 'Rs. ' + grandTotal.toFixed(2);
    }

    // Attach event listeners to existing items
    document.querySelectorAll('.item-row').forEach(function(row){
        attachEventListeners(row);
    });

    // Update totals on discount change
    document.getElementById('discount_percentage').addEventListener('input', updateTotals);
    document.getElementById('discount_amount').addEventListener('input', updateTotals);

    // Initialize totals
    updateTotals();
})();
</script>
@endsection
