{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Products - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Products Management</h1>
        <div>
            <a href="{{ route('products.create') }}" class="btn btn-gold">
                <i class="fas fa-plus me-1"></i> Add New Product
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="Ring" {{ request('category') == 'Ring' ? 'selected' : '' }}>Rings</option>
                        <option value="Necklace" {{ request('category') == 'Necklace' ? 'selected' : '' }}>Necklaces</option>
                        <option value="Earring" {{ request('category') == 'Earring' ? 'selected' : '' }}>Earrings</option>
                        <option value="Bracelet" {{ request('category') == 'Bracelet' ? 'selected' : '' }}>Bracelets</option>
                        <option value="Bangle" {{ request('category') == 'Bangle' ? 'selected' : '' }}>Bangles</option>
                        <option value="Pendant" {{ request('category') == 'Pendant' ? 'selected' : '' }}>Pendants</option>
                        <option value="Chain" {{ request('category') == 'Chain' ? 'selected' : '' }}>Chains</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="material" class="form-control">
                        <option value="">All Materials</option>
                        <option value="Gold" {{ request('material') == 'Gold' ? 'selected' : '' }}>Gold</option>
                        <option value="Silver" {{ request('material') == 'Silver' ? 'selected' : '' }}>Silver</option>
                        <option value="Platinum" {{ request('material') == 'Platinum' ? 'selected' : '' }}>Platinum</option>
                        <option value="Diamond" {{ request('material') == 'Diamond' ? 'selected' : '' }}>Diamond</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Material</th>
                            <th>Weight</th>
                            <th>Stock</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->sku }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-image me-3">
                                        @if($product->images && is_array(json_decode($product->images)) && count(json_decode($product->images)) > 0)
                                            <img src="{{ asset('storage/' . json_decode($product->images)[0]) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded" 
                                                 width="50" 
                                                 height="50"
                                                 style="object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-gem text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->certificate_no)
                                            <small class="d-block text-success">
                                                <i class="fas fa-certificate"></i> {{ $product->certificate_no }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $product->category }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-{{ $product->material == 'Gold' ? 'crown' : 'gem' }} me-1"></i>
                                    {{ $product->material }}
                                </span>
                            </td>
                            <td>{{ $product->weight }}g</td>
                            <td>
                                @if($product->stock < 5)
                                    <span class="badge bg-danger">{{ $product->stock }}</span>
                                @elseif($product->stock < 10)
                                    <span class="badge bg-warning text-dark">{{ $product->stock }}</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td class="text-danger">Rs.{{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-success fw-bold">Rs.{{ number_format($product->selling_price, 2) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-success" onclick="updateStock('{{ $product->id }}')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-gem fa-3x text-muted mb-3"></i>
                                <h5>No products found</h5>
                                <p class="text-muted">Add your first product to get started</p>
                                <a href="{{ route('products.create') }}" class="btn btn-gold">
                                    <i class="fas fa-plus me-1"></i> Add Product
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($products->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} entries
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function updateStock(productId) {
    const quantity = prompt('Enter quantity to add:');
    if (quantity && !isNaN(quantity) && quantity > 0) {
        fetch(`/products/update-stock/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ quantity: parseInt(quantity) })
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
