@extends('layouts.app')

@section('title', 'Request Transfer | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>New Stock Transfer Request</h2>
    </div>

    <form action="{{ route('inventory.transfers.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Transfer Details</h5>
                        <div class="mb-3">
                            <label class="form-label">Source Branch</label>
                            <select name="from_branch_id" class="form-select" required>
                                <option value="">Select Source</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Destination Branch</label>
                            <select name="to_branch_id" class="form-select" required>
                                <option value="">Select Destination</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Reason for transfer..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>SUBMIT TRANSFER REQUEST
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Select Items</h5>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="addItem">
                            <i class="fas fa-plus me-1"></i>Add Item
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div id="itemsContainer">
                            <div class="row g-3 mb-3 pb-3 border-bottom item-row">
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Product</label>
                                    <select name="items[0][product_id]" class="form-select select2" required>
                                        <option value="">Search by SKU or Name</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }} (Available: {{ $product->current_stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Quantity</label>
                                    <input type="number" name="items[0][quantity]" class="form-control" step="0.001" min="0.001" required>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item" style="height: 38px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let itemIndex = 1;
    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset inputs and update names
        newRow.querySelectorAll('input, select').forEach(input => {
            input.value = '';
            input.name = input.name.replace('[0]', `[${itemIndex}]`);
        });
        
        container.appendChild(newRow);
        itemIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
            } else {
                alert('At least one item is required.');
            }
        }
    });
</script>
@endpush
@endsection
