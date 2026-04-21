@extends('layouts.app')

@section('title', 'Consolidated Inventory | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold">Consolidated Multi-Branch Stock</h2>
            <p class="text-muted">Head-office view of inventory across all branches.</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Export PDF</button>
        </div>
    </div>

    <div class="row g-4">
        @foreach($branchStock as $item)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-primary">{{ $item['branch']->name }}</h5>
                        <span class="badge bg-light text-dark border">{{ $item['branch']->location }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded shadow-sm">
                                <h4 class="fw-bold mb-0 text-dark">{{ $item['total_items'] }}</h4>
                                <small class="text-muted text-uppercase fw-semibold">Products</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded shadow-sm">
                                <h4 class="fw-bold mb-0 text-dark">{{ number_format($item['total_stock'], 2) }}</h4>
                                <small class="text-muted text-uppercase fw-semibold">In Stock</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded shadow-sm">
                                <h4 class="fw-bold mb-0 text-success">Rs. {{ number_format($item['total_value'], 2) }}</h4>
                                <small class="text-muted text-uppercase fw-semibold">Inventory Value</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold">Recent Movements</span>
                            <a href="#" class="small text-decoration-none">View Details</a>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-grid">
                        <a href="{{ route('inventory.products.index', ['branch_id' => $item['branch']->id]) }}" class="btn btn-outline-primary btn-sm">
                            Manage Branch Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
