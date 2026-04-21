<!-- Inventory Widget -->
<div class="row mb-4">
    <div class="col-md-12">
        <h5 class="mb-3"><i class="fas fa-warehouse me-2"></i>Inventory Overview</h5>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="opacity-75 d-block">Total Products</small>
                        <h4 class="mb-0">{{ $inventory['metrics']['total_products'] }}</h4>
                    </div>
                    <i class="fas fa-boxes fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="opacity-75 d-block">Low Stock</small>
                        <h4 class="mb-0">{{ $inventory['metrics']['low_stock_count'] }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="opacity-75 d-block">Out of Stock</small>
                        <h4 class="mb-0">{{ $inventory['metrics']['out_of_stock_count'] }}</h4>
                    </div>
                    <i class="fas fa-ban fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="opacity-75 d-block">Profit Potential</small>
                        <h4 class="mb-0">Rs.{{ number_format($inventory['metrics']['profit_potential'], 0) }}</h4>
                        <small class="opacity-75">{{ number_format($inventory['metrics']['profit_margin_percent'], 1) }}%</small>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Low Stock Items -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-bell text-warning me-2"></i>Low Stock Alert
                </h6>
            </div>
            <div class="card-body p-0">
                @if($inventory['low_stock']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($inventory['low_stock']->take(5) as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <br>
                                    <small class="text-muted">Stock: {{ number_format($product->current_stock, 1) }} | Reorder: {{ number_format($product->reorder_quantity, 1) }}</small>
                                </div>
                                <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-sm btn-warning">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                    @if($inventory['low_stock']->count() > 5)
                        <div class="card-footer">
                            <a href="{{ route('inventory.low-stock-alert') }}" class="text-decoration-none">View all low stock items →</a>
                        </div>
                    @endif
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x opacity-50 mb-2"></i>
                        <p>All stock levels are healthy</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top 5 Products by Value -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-crown text-success me-2"></i>Top Products by Value
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @php
                        $topProducts = $inventory['by_category']->flatMap(fn($cat) => $cat->products)
                            ->sortByDesc(fn($p) => $p->current_stock * $p->cost_price)
                            ->take(5);
                    @endphp
                    @foreach($topProducts as $product)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <strong>{{ substr($product->name, 0, 30) }}</strong>
                                <br>
                                <small class="text-muted">Rs.{{ number_format($product->current_stock * $product->cost_price, 0) }}</small>
                            </div>
                            <span class="badge bg-success">{{ number_format($product->current_stock, 1) }} units</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
</style>
