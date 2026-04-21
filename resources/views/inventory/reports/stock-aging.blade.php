@extends('layouts.app')

@section('title', 'Stock Aging Report - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-calendar-alt me-2" style="color: var(--primary);"></i>
                Stock Aging Report
            </h2>
            <small class="text-muted">Identify slow-moving inventory</small>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 d-block">Fresh Stock</small>
                            <h4 class="mb-0">{{ $ageGroups['recent'] }}</h4>
                        </div>
                        <i class="fas fa-leaf fa-2x opacity-50"></i>
                    </div>
                    <small class="text-white-50">< 30 Days</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 d-block">Medium Age</small>
                            <h4 class="mb-0">{{ $ageGroups['medium'] }}</h4>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                    <small class="text-white-50">30 - 90 Days</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-dark-50 d-block">Old Stock</small>
                            <h4 class="mb-0">{{ $ageGroups['old'] }}</h4>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                    <small class="text-dark-50">90 - 180 Days</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 d-block">Very Old</small>
                            <h4 class="mb-0">{{ $ageGroups['very_old'] }}</h4>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                    </div>
                    <small class="text-white-50">> 180 Days</small>
                </div>
            </div>
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
                        <th>Stock Level</th>
                        <th>Age (Days)</th>
                        <th>Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->sku }}</strong></td>
                            <td>{{ $product->name }}</td>
                            <td><span class="badge bg-secondary">{{ $product->category->name }}</span></td>
                            <td>{{ number_format($product->current_stock, 2) }}</td>
                            <td>
                                <span class="badge {{ $product->age_days > 365 ? 'bg-danger' : ($product->age_days > 180 ? 'bg-warning text-dark' : 'bg-info') }}">
                                    {{ $product->age_days }} days
                                </span>
                            </td>
                            <td>Rs.{{ number_format($product->total_value, 2) }}</td>
                            <td>
                                <span class="badge {{ $product->age_days > 365 ? 'bg-danger' : ($product->age_days > 180 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $product->age_days > 365 ? 'Critical' : ($product->age_days > 180 ? 'Old' : 'Fresh') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No products found
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
@endsection
