{{-- resources/views/viewer/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Viewer Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')

<!-- Dashboard Header -->
<div class="dashboard-header mb-4">
    <div class="dashboard-header-content">
        <h1 class="dashboard-title">
            <i class="fas fa-eye me-2"></i>Viewer Dashboard
        </h1>
        <p class="dashboard-subtitle">Read-only overview of business activity</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h6 class="text-muted mb-3">Today's Sales Count</h6>
                        <h3 class="fw-bold mb-3">{{ $today_sales_count }}</h3>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50, #8BC34A);">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h6 class="text-muted mb-3">Total Products</h6>
                        <h3 class="fw-bold mb-3">{{ number_format($total_products) }}</h3>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #03A9F4);">
                        <i class="fas fa-gem"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h6 class="text-muted mb-3">Current Gold Rate (24K)</h6>
                        <h3 class="fw-bold mb-3">Rs. {{ number_format($gold_rate ? $gold_rate->rate_24k : 0, 2) }}</h3>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #FFD700, #DAA520);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="mb-4">Recent Sales Activity</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_sales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td>Rs. {{ number_format($sale->total, 2) }}</td>
                                <td><span class="badge bg-success">{{ $sale->status }}</span></td>
                                <td>{{ $sale->sale_time->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No recent sales found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
