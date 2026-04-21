@extends('layouts.app')

@section('title', 'Inventory Products - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold text-slate-800 mb-1">Inventory Registry</h1>
            <p class="text-muted small mb-0">Manage master product list and stock levels</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="bulkPrintBtn" class="btn btn-sm btn-white border d-none">
                <i class="fas fa-print me-1"></i> Print Tags (<span id="selectedCount">0</span>)
            </button>
            <a href="{{ route('inventory.products.create') }}" class="btn btn-sm btn-gold">
                <i class="fas fa-plus me-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded me-3">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <p class="text-muted text-xs fw-bold text-uppercase mb-0">Total SKU</p>
                            <h5 class="fw-bold mb-0">{{ $metrics['total_products'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded me-3">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                            <p class="text-muted text-xs fw-bold text-uppercase mb-0">Low Stock</p>
                            <h5 class="fw-bold mb-0">{{ $metrics['low_stock_count'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded me-3">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div>
                            <p class="text-muted text-xs fw-bold text-uppercase mb-0">Stock Out</p>
                            <h5 class="fw-bold mb-0">{{ $metrics['out_of_stock_count'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded me-3">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <p class="text-muted text-xs fw-bold text-uppercase mb-0">Asset Value</p>
                            <h5 class="fw-bold mb-0">Rs. {{ number_format($metrics['total_inventory_value'], 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-xs fw-bold text-muted">Search Query</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Product name, SKU or Serial..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-xs fw-bold text-muted">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-xs fw-bold text-muted">Inventory Status</label>
                    <select name="stock_status" class="form-select form-select-sm">
                        <option value="">Show All</option>
                        <option value="high" {{ request('stock_status') == 'high' ? 'selected' : '' }}>Healthy Stock</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Alert</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-xs fw-bold text-muted">Active Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-dark flex-grow-1">Apply</button>
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-sm btn-white border"><i class="fas fa-redo"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Products ({{ $products->total() }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>SKU / Serial</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Prices</th>
                        <th>Stock</th>
                        <th>Inventory Value</th>
                        <th>Margin</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="form-check-input product-checkbox">
                            </td>
                            <td>
                                <strong class="d-block" style="font-size: 0.9rem;">{{ $product->sku }}</strong>
                                @if($product->serial_number)
                                    <small class="text-muted">SN: {{ substr($product->serial_number, 0, 12) }}...</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" class="rounded shadow-sm" style="width: 45px; height: 45px; object-fit: cover; border: 1px solid #eee;">
                                        @else
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; border: 1px solid #eee;">
                                                <i class="fas fa-image text-muted opacity-50"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div style="max-width: 180px;">
                                        <strong style="font-size: 0.9rem;">{{ $product->name }}</strong>
                                        @if($product->weight)
                                        <br><small class="text-muted">W: {{ number_format($product->weight, 2) }}g</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->category->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <span class="d-block">Cost: Rs.{{ number_format($product->cost_price, 0) }}</span>
                                    <span class="d-block text-success fw-bold">Sale: Rs.{{ number_format($product->selling_price, 0) }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge {{ $product->current_stock <= 0 ? 'bg-danger' : ($product->current_stock <= $product->reorder_level ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ number_format($product->current_stock, 1) }}
                                    </span>
                                    @if($product->current_stock <= $product->reorder_level)
                                    <br><small class="text-danger fw-bold">Low Stock</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <strong>Rs.{{ number_format($product->current_stock * $product->cost_price, 0) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->profit_margin_percent >= 30 ? 'success' : 'warning' }} text-white">
                                    {{ number_format($product->profit_margin_percent, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->status === 'active' ? 'success' : ($product->status === 'pledged' ? 'info' : ($product->status === 'inactive' ? 'warning text-dark' : 'danger')) }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-outline-primary" title="View Details" data-bs-toggle="tooltip">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-outline-warning" title="Edit" data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('inventory.products.print-tag', $product) }}" target="_blank" class="btn btn-outline-secondary" title="Print Jewelry Tag" data-bs-toggle="tooltip">
                                        <i class="fas fa-tag"></i>
                                    </a>
                                    <a href="{{ route('inventory.products.download-barcode', $product) }}" class="btn btn-outline-info" title="Download Barcode" data-bs-toggle="tooltip">
                                        <i class="fas fa-barcode"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-box fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No products found matching your criteria</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>

<form id="bulkPrintForm" action="{{ route('inventory.products.tags-preview') }}" method="GET" class="d-none">
    <input type="hidden" name="product_ids" id="bulkPrintIds">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const bulkPrintBtn = document.getElementById('bulkPrintBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkPrintForm = document.getElementById('bulkPrintForm');
    const bulkPrintIdsInput = document.getElementById('bulkPrintIds');

    function updateBulkPrintVisibility() {
        const selectedCount = document.querySelectorAll('.product-checkbox:checked').length;
        if (selectedCount > 0) {
            bulkPrintBtn.classList.remove('d-none');
            selectedCountSpan.textContent = selectedCount;
        } else {
            bulkPrintBtn.classList.add('d-none');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            productCheckboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkPrintVisibility();
        });
    }

    productCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkPrintVisibility);
    });

    if (bulkPrintBtn) {
        bulkPrintBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked'))
                .map(cb => cb.value);
            
            bulkPrintIdsInput.value = selectedIds.join(',');
            bulkPrintForm.submit();
        });
    }
});
</script>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(139, 69, 19, 0.05);
    }
</style>
@endsection
