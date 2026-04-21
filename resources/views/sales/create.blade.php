@extends('layouts.app')

@section('title', 'Create Sale')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Create Sale</h3>
        <a href="{{ route('pos.sales.index') }}" class="btn btn-secondary">Back to list</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                <input type="hidden" name="invoice_type" id="invoice_type" value="">
                @include('sales.partials.invoice_type_modal')
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Customer (optional)</label>
                        <select name="customer_id" class="form-select">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 text-end align-self-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">+ Add Item</button>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- rows injected by JS -->
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Subtotal</label>
                        <input type="text" id="subtotal" class="form-control" readonly value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax Amount</label>
                        <input type="number" step="0.01" name="tax_amount" id="tax_amount" class="form-control" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Discount</label>
                        <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="0">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="UPI">UPI</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                            <option value="Due">Due</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input type="text" id="totalAmount" class="form-control" readonly value="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Create Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@stack('styles')
@stack('scripts')
<script>
// ...existing code...
// Invoice type modal logic is included via partial
const products = [
    @foreach($products as $p)
    {
        id: {{ $p->id }},
        name: "{{ addslashes($p->name) }}",
        price: {{ $p->selling_price ?? 0 }},
        stock: {{ $p->stock ?? 0 }}
    },
    @endforeach
];

let itemIndex = 0;

function addItemRow(selectedId = null, qty = 1) {
    const tbody = document.querySelector('#itemsTable tbody');
    const tr = document.createElement('tr');
    tr.dataset.index = itemIndex;

    const productOptions = products.map(p => `<option value="${p.id}" ${p.id==selectedId? 'selected': ''}>${p.name} (Rs.${p.price})</option>`).join('');

    tr.innerHTML = `
        <td>
            <select name="items[${itemIndex}][product_id]" class="form-select product-select">${productOptions}</select>
        </td>
        <td>
            <input type="text" name="items[${itemIndex}][unit_price]" class="form-control unit-price" readonly value="0">
        </td>
        <td>
            <input type="number" min="1" name="items[${itemIndex}][quantity]" class="form-control item-qty" value="${qty}">
        </td>
        <td>
            <input type="text" class="form-control item-total" readonly value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
        </td>
    `;

    tbody.appendChild(tr);

    // initialize values
    const select = tr.querySelector('.product-select');
    const unitInput = tr.querySelector('.unit-price');
    const qtyInput = tr.querySelector('.item-qty');
    const totalInput = tr.querySelector('.item-total');

    function updateRow() {
        const pid = parseInt(select.value);
        const prod = products.find(x => x.id === pid) || {price:0, stock:0};
        unitInput.value = prod.price.toFixed(2);
        const q = parseFloat(qtyInput.value) || 0;
        totalInput.value = (prod.price * q).toFixed(2);
        calculateTotals();
    }

    select.addEventListener('change', updateRow);
    qtyInput.addEventListener('input', updateRow);
    tr.querySelector('.remove-item').addEventListener('click', function() { tr.remove(); calculateTotals(); });

    // trigger initial update
    updateRow();
    itemIndex++;
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
        const total = parseFloat(tr.querySelector('.item-total').value) || 0;
        subtotal += total;
    });

    const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const total = subtotal + tax - discount;

    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('totalAmount').value = total.toFixed(2);
}

document.getElementById('addItemBtn').addEventListener('click', function() { addItemRow(); });
document.getElementById('tax_amount').addEventListener('input', calculateTotals);
document.getElementById('discount').addEventListener('input', calculateTotals);

// Add an initial row
addItemRow();

// Before submit: ensure quantities and product ids are present as items[*][...]
document.getElementById('saleForm').addEventListener('submit', function(e) {
    // basic check: at least one item
    if (document.querySelectorAll('#itemsTable tbody tr').length === 0) {
        e.preventDefault();
        alert('Please add at least one item.');
    }
});
</script>
@endpush

@endsection
