{{-- resources/views/manager/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Manager Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')

<!-- Dashboard Header -->
<div class="dashboard-header mb-4">
    <div class="dashboard-header-content">
        <h1 class="dashboard-title">
            <i class="fas fa-gauge-high me-2"></i>Manager Dashboard
        </h1>
        <p class="dashboard-subtitle">Welcome back! Here's your business overview</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h6 class="text-muted mb-3">Today's Sales</h6>
                        <h3 class="fw-bold mb-3">Rs. {{ number_format($today_sales, 2) }}</h3>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50, #8BC34A);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h6 class="text-muted mb-3">Customers</h6>
                        <h3 class="fw-bold mb-3">{{ number_format($total_customers) }}</h3>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #9C27B0, #673AB7);">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h6 class="text-muted mb-3">Low Stock</h6>
                        <h3 class="fw-bold mb-3" style="color: #dc3545;">{{ $low_stock }}</h3>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800, #FFC107);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-xl-12">
            <div class="stat-card">
                <h5 class="mb-4" style="font-size: 18px; font-weight: 800; color: #1a1a2e;">
                    <i class="fas fa-chart-line me-2" style="color: #8B4513;"></i>Sales Performance (Last 7 Days)
                </h5>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Access -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="stat-card">
                <div class="module-section-header mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-th-large me-2"></i>Authorized Modules
                    </h5>
                </div>
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-primary w-100 p-3 mb-2">
                            <i class="fas fa-warehouse mb-2 d-block"></i> Inventory
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('pos.sales.index') }}" class="btn btn-outline-success w-100 p-3 mb-2">
                            <i class="fas fa-cash-register mb-2 d-block"></i> POS & Sales
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-info w-100 p-3 mb-2">
                            <i class="fas fa-handshake mb-2 d-block"></i> CRM
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('reports.dashboard') }}" class="btn btn-outline-secondary w-100 p-3 mb-2">
                            <i class="fas fa-chart-bar mb-2 d-block"></i> Reports
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('hr.self-service.index') }}" class="btn btn-outline-warning w-100 p-3 mb-2">
                            <i class="fas fa-user-clock mb-2 d-block"></i> My Attendance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // @ts-nocheck
    /* eslint-disable */
    document.addEventListener('DOMContentLoaded', function() {
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($sales_chart['labels']) !!},
                datasets: [{
                    label: 'Sales (Rs.)',
                    data: {!! json_encode($sales_chart['sales']) !!},
                    borderColor: '#D4AF37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>
@endpush
