@extends('layouts.app')

@section('title', 'Multi-Location Stock Report - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-map-marker-alt me-2" style="color: var(--primary);"></i>
                Multi-Location Stock Report
            </h2>
        </div>
    </div>

    <div class="row mb-4">
        @foreach($locations as $location)
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">{{ $location->name }}</h6>
                        <h3 class="mb-2">{{ $locationStats[$location->id] ?? 0 }} Items</h3>
                        <small class="text-muted">{{ ucfirst($location->type) }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        @foreach($locations as $location)
                            <th>{{ $location->name }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                        @php $product = $item['product']; @endphp
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->sku }}</td>
                            @foreach($locations as $location)
                                <td>
                                    {{ number_format($item['locations'][$location->id]['quantity'] ?? 0, 2) }}
                                </td>
                            @endforeach
                            <td><strong>{{ number_format($product->current_stock, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($locations) + 3 }}" class="text-center text-muted py-4">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
