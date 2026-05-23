@extends('layouts.app')
@section('title', 'Reorder List')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Reorder List</h1>
            <small class="text-muted">Products that need to be restocked</small>
        </div>
        <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3 border-danger">
                <h4 class="text-danger mb-0">{{ $outOfStock->count() }}</h4>
                <small class="text-muted">Out of Stock</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3 border-warning">
                <h4 class="text-warning mb-0">{{ $lowStock->count() }}</h4>
                <small class="text-muted">Low Stock</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-primary mb-0">Rs. {{ number_format($totalReorderValue, 0) }}</h4>
                <small class="text-muted">Estimated Reorder Value</small>
            </div>
        </div>
    </div>

    @if($outOfStock->count())
    <div class="card shadow-sm mb-4 border-danger">
        <div class="card-header bg-danger text-white fw-semibold"><i class="fas fa-exclamation-circle me-2"></i>Out of Stock ({{ $outOfStock->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>SKU</th><th>Product</th><th>Category</th><th>Purity</th><th class="text-end">Reorder Qty</th><th class="text-end">Est. Cost</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($outOfStock as $p)
                    <tr class="table-danger">
                        <td><code>{{ $p->sku }}</code></td>
                        <td><a href="{{ route('inventory.products.show', $p) }}" class="text-decoration-none">{{ $p->name }}</a></td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td>{{ $p->purity->name ?? '—' }}</td>
                        <td class="text-end">{{ $p->reorder_quantity }}</td>
                        <td class="text-end">Rs. {{ number_format($p->reorder_quantity * $p->cost_price, 0) }}</td>
                        <td><a href="{{ route('inventory.products.show', $p) }}" class="btn btn-sm btn-danger">Add Stock</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($lowStock->count())
    <div class="card shadow-sm border-warning">
        <div class="card-header bg-warning text-dark fw-semibold"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock ({{ $lowStock->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>SKU</th><th>Product</th><th>Category</th><th class="text-end">Current</th><th class="text-end">Reorder Level</th><th class="text-end">Reorder Qty</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($lowStock as $p)
                    <tr class="table-warning">
                        <td><code>{{ $p->sku }}</code></td>
                        <td><a href="{{ route('inventory.products.show', $p) }}" class="text-decoration-none">{{ $p->name }}</a></td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td class="text-end text-warning fw-semibold">{{ $p->current_stock }}</td>
                        <td class="text-end">{{ $p->reorder_level }}</td>
                        <td class="text-end">{{ $p->reorder_quantity }}</td>
                        <td><a href="{{ route('inventory.products.show', $p) }}" class="btn btn-sm btn-warning">Add Stock</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($products->isEmpty())
    <div class="card shadow-sm"><div class="card-body text-center py-5 text-success"><i class="fas fa-check-circle fa-3x mb-3 d-block"></i><h5>All products are well stocked!</h5></div></div>
    @endif
</div>
@endsection
