@extends('layouts.app')

@section('title', 'Low Stock Alert - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-exclamation-triangle me-2" style="color: #FF6B6B;"></i>
                Low Stock Alert
            </h2>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Shortfall</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="table-danger table-opacity">
                            <td><strong>{{ $product->sku }}</strong></td>
                            <td>{{ $product->name }}</td>
                            <td><span class="badge bg-secondary">{{ $product->category->name }}</span></td>
                            <td><strong>{{ number_format($product->current_stock, 2) }}</strong></td>
                            <td>{{ number_format($product->reorder_level, 2) }}</td>
                            <td>
                                <strong class="text-danger">
                                    -{{ number_format($product->reorder_level - $product->current_stock, 2) }}
                                </strong>
                            </td>
                            <td>
                                <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle"></i> All products are in stock!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>

<style>
    .table-opacity {
        opacity: 0.9;
    }
</style>
@endsection
