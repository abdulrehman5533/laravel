@extends('layouts.app')

@section('title', 'Inventory Analytics')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-pie me-2"></i>Inventory Analytics</h2>
        <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Profit Margin</h6>
                    <h4 class="mb-0">{{ number_format($metrics['profit_margin_percent'], 1) }}%</h4>
                    <small class="text-success">Overall margin across all products</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Profit Per Unit (Avg)</h6>
                    <h4 class="mb-0">Rs.{{ $metrics['total_products'] > 0 ? number_format($metrics['profit_potential'] / $metrics['total_products'], 0) : 0 }}</h4>
                    <small class="text-success">Average profit per product</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Recent Wastage (30 days)</h6>
                    <h4 class="mb-0">{{ number_format($metrics['recent_wastage'], 1) }}</h4>
                    <small class="text-warning">Units lost to wastage</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Fast Moving Products -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-fire text-danger me-2"></i>Top 10 Fast Moving Products</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Movements</th>
                                    <th>Turnover</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fastMoving as $item)
                                    <tr>
                                        <td><strong>{{ substr($item['product']->name, 0, 25) }}</strong></td>
                                        <td>{{ number_format($item['movements'], 0) }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ number_format($item['turnover_rate'], 2) }}x</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slow Moving Products -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-snail text-warning me-2"></i>Top 10 Slow Moving Products</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Stock Value</th>
                                    <th>Age (days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($slowMoving as $item)
                                    <tr>
                                        <td><strong>{{ substr($item['product']->name, 0, 25) }}</strong></td>
                                        <td>Rs.{{ number_format($item['stock_value'], 0) }}</td>
                                        <td>
                                            <span class="badge bg-warning">{{ $item['age_days'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No slow-moving products</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Inventory Turnover -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Inventory Turnover Ratio (Last 30 Days)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Sales Value</th>
                                    <th>Inventory Value</th>
                                    <th>Turnover Ratio</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($turnover->take(15) as $item)
                                    <tr>
                                        <td><strong>{{ substr($item['product']->name, 0, 30) }}</strong></td>
                                        <td>Rs.{{ number_format($item['sales_value'], 0) }}</td>
                                        <td>Rs.{{ number_format($item['inventory_value'], 0) }}</td>
                                        <td>
                                            <strong class="text-{{ $item['turnover_ratio'] >= 1 ? 'success' : 'warning' }}">
                                                {{ number_format($item['turnover_ratio'], 2) }}x
                                            </strong>
                                        </td>
                                        <td>
                                            @if($item['turnover_ratio'] >= 2)
                                                <span class="badge bg-success">High Turnover</span>
                                            @elseif($item['turnover_ratio'] >= 1)
                                                <span class="badge bg-info">Good</span>
                                            @else
                                                <span class="badge bg-warning">Low Turnover</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No turnover data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- By Category Distribution -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Inventory Distribution by Category</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Products</th>
                                    <th>Inventory Value</th>
                                    <th>Retail Value</th>
                                    <th>Profit Potential</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalCatValue = $categoryMetrics->sum(fn($c) => $c['inventory_value']);
                                @endphp
                                @forelse($categoryMetrics as $cat)
                                    <tr>
                                        <td><strong>{{ $cat['category']->name }}</strong></td>
                                        <td>{{ $cat['product_count'] }}</td>
                                        <td>Rs.{{ number_format($cat['inventory_value'], 0) }}</td>
                                        <td>Rs.{{ number_format($cat['retail_value'], 0) }}</td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                Rs.{{ number_format($cat['retail_value'] - $cat['inventory_value'], 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $totalCatValue > 0 ? number_format(($cat['inventory_value'] / $totalCatValue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No category data</td>
                                    </tr>
                                @endforelse
                                <tr class="table-light fw-bold">
                                    <td colspan="2">TOTAL</td>
                                    <td>Rs.{{ number_format($metrics['total_inventory_value'], 0) }}</td>
                                    <td>Rs.{{ number_format($metrics['total_retail_value'], 0) }}</td>
                                    <td class="text-success">Rs.{{ number_format($metrics['profit_potential'], 0) }}</td>
                                    <td>100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
