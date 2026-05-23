@extends('layouts.app')
@section('title', 'Stock Valuation')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Stock Valuation Report</h1>
            <small class="text-muted">Metal-wise & category-wise inventory value</small>
        </div>
        <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Metal Type</label>
                    <select name="metal_type" class="form-select">
                        <option value="">All Metals</option>
                        <option value="gold" {{ $metalType == 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="silver" {{ $metalType == 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="diamond" {{ $metalType == 'diamond' ? 'selected' : '' }}>Diamond</option>
                        <option value="platinum" {{ $metalType == 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-primary mb-0">Rs. {{ number_format($summary['total_cost_value'], 0) }}</h4>
                <small class="text-muted">Total Cost Value</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-success mb-0">Rs. {{ number_format($summary['total_retail_value'], 0) }}</h4>
                <small class="text-muted">Total Retail Value</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-warning mb-0">Rs. {{ number_format($summary['total_retail_value'] - $summary['total_cost_value'], 0) }}</h4>
                <small class="text-muted">Profit Potential</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-info mb-0">{{ number_format($summary['total_weight'], 3) }}g</h4>
                <small class="text-muted">Total Weight</small>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- By Category --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">By Category</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Category</th><th>Items</th><th class="text-end">Cost Value</th><th class="text-end">Retail Value</th></tr></thead>
                        <tbody>
                            @foreach($summary['by_category'] as $catName => $data)
                            <tr>
                                <td>{{ $catName ?: 'Uncategorized' }}</td>
                                <td>{{ $data['count'] }}</td>
                                <td class="text-end">{{ number_format($data['cost_value'], 0) }}</td>
                                <td class="text-end text-success">{{ number_format($data['retail_value'], 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- By Purity --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">By Purity</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Purity</th><th>Items</th><th class="text-end">Cost Value</th><th class="text-end">Weight (g)</th></tr></thead>
                        <tbody>
                            @foreach($summary['by_purity'] as $purityName => $data)
                            <tr>
                                <td><span class="badge bg-warning text-dark">{{ $purityName ?: 'N/A' }}</span></td>
                                <td>{{ $data['count'] }}</td>
                                <td class="text-end">{{ number_format($data['cost_value'], 0) }}</td>
                                <td class="text-end">{{ number_format($data['weight'], 3) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Detail --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Product-wise Valuation ({{ $products->count() }} items)</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>SKU</th><th>Product</th><th>Category</th><th>Purity</th><th class="text-end">Stock</th><th class="text-end">Weight</th><th class="text-end">Cost Value</th><th class="text-end">Retail Value</th><th class="text-end">Margin</th></tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td><code>{{ $p->sku }}</code></td>
                        <td><a href="{{ route('inventory.products.show', $p) }}" class="text-decoration-none">{{ $p->name }}</a></td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $p->purity->name ?? '—' }}</span></td>
                        <td class="text-end">{{ $p->current_stock }}</td>
                        <td class="text-end">{{ number_format($p->current_stock * $p->net_weight, 3) }}g</td>
                        <td class="text-end">{{ number_format($p->current_stock * $p->cost_price, 0) }}</td>
                        <td class="text-end text-success">{{ number_format($p->current_stock * $p->selling_price, 0) }}</td>
                        <td class="text-end {{ ($p->selling_price - $p->cost_price) > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $p->cost_price > 0 ? number_format((($p->selling_price - $p->cost_price) / $p->cost_price) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
