@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Purchase Analytics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Insights & Analytics</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-flex justify-content-md-end gap-2">
                <div class="glass-input-group d-flex bg-white rounded-12 shadow-sm p-1">
                    <select name="month" class="form-select form-select-sm border-0 bg-transparent fw-600" onchange="this.form.submit()" style="min-width: 120px;">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                    <div class="vr my-2 opacity-10"></div>
                    <select name="year" class="form-select form-select-sm border-0 bg-transparent fw-600" onchange="this.form.submit()" style="min-width: 100px;">
                        @for ($y = now()->year - 3; $y <= now()->year; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Premium Metrics Row -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden group">
                <div class="card-bg-icon">
                    <i class="fas fa-shopping-cart text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-cart-plus"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total Orders</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $totalOrders }}</h2>
                <span class="text-muted small fw-600">Total volume for period</span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-money-bill-wave text-success opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-success-soft text-success me-3">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total Purchase</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">Rs.{{ number_format($totalAmount, 2) }}</h2>
                <span class="text-muted small fw-600">Gross procurement value</span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-exclamation-triangle text-danger opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-danger-soft text-danger me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="text-muted fw-600 small">Overdue Orders</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $overdueOrders }}</h2>
                <div class="progress w-100" style="height: 6px; border-radius: 10px; background: #f0f0f0;">
                    <div class="progress-bar bg-danger" style="width: {{ $totalOrders > 0 ? ($overdueOrders / $totalOrders) * 100 : 0 }}%; border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden bg-premium-dark text-white">
                <div class="card-bg-icon">
                    <i class="fas fa-crown text-gold opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-gold text-dark me-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <span class="text-white-50 fw-600 small">Pending Payments</span>
                </div>
                <h2 class="fw-800 mb-2 text-white">Rs.{{ number_format($pendingPayments, 2) }}</h2>
                <span class="text-white-50 small fw-600">Current liability</span>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Quick Actions Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-20 mb-4 overflow-hidden">
                <div class="card-header bg-premium-dark p-4 border-0">
                    <h5 class="mb-0 fw-800 text-white small text-uppercase tracking-wider">Control Center</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('accounts.purchases.orders.index') }}" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center group">
                            <div class="icon-box-sm bg-primary-soft text-primary rounded-8 me-3">
                                <i class="fas fa-list small"></i>
                            </div>
                            <span class="fw-600 text-dark">All Orders</span>
                            <i class="fas fa-chevron-right ms-auto tiny text-muted group-hover-gold"></i>
                        </a>
                        <a href="{{ route('accounts.purchases.orders.index', ['status' => 'Draft']) }}" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center group border-top">
                            <div class="icon-box-sm bg-info-soft text-info rounded-8 me-3">
                                <i class="fas fa-file-alt small"></i>
                            </div>
                            <span class="fw-600 text-dark">Drafts</span>
                            <i class="fas fa-chevron-right ms-auto tiny text-muted group-hover-gold"></i>
                        </a>
                        <a href="{{ route('accounts.purchases.orders.overdue') }}" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center group border-top">
                            <div class="icon-box-sm bg-danger-soft text-danger rounded-8 me-3">
                                <i class="fas fa-exclamation-circle small"></i>
                            </div>
                            <span class="fw-600 text-dark">Overdue</span>
                            <i class="fas fa-chevron-right ms-auto tiny text-muted group-hover-gold"></i>
                        </a>
                        <a href="{{ route('accounts.purchases.orders.pending-payments') }}" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center group border-top">
                            <div class="icon-box-sm bg-warning-soft text-warning rounded-8 me-3">
                                <i class="fas fa-clock small"></i>
                            </div>
                            <span class="fw-600 text-dark">Unpaid</span>
                            <i class="fas fa-chevron-right ms-auto tiny text-muted group-hover-gold"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Audit Summary -->
            <div class="stat-card border-0 p-4">
                <h6 class="fw-800 text-dark mb-4 d-flex align-items-center">
                    <i class="fas fa-shield-alt text-gold me-2"></i>Period Summary
                </h6>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small fw-600">Reporting Period:</span>
                    <span class="text-dark fw-700 small">{{ \Carbon\Carbon::create(null, $month)->format('F Y') }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small fw-600">Total Transactions:</span>
                    <span class="text-dark fw-700 small">{{ $totalOrders }}</span>
                </div>
                <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small fw-600">Avg Order Value:</span>
                    <span class="text-primary fw-800">Rs.{{ $totalOrders > 0 ? number_format($totalAmount / $totalOrders, 2) : '0.00' }}</span>
                </div>
            </div>
        </div>

        <!-- Analytics Charts -->
        <div class="col-lg-9">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="chart-container-premium p-4 shadow-sm bg-white rounded-20 h-100">
                        <h6 class="fw-800 mb-4 text-dark d-flex align-items-center">
                            <div class="p-2 bg-primary-soft text-primary rounded-8 me-2">
                                <i class="fas fa-chart-pie small"></i>
                            </div>
                            Order Status Distribution
                        </h6>
                        <div style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="chart-container-premium p-4 shadow-sm bg-white rounded-20 h-100">
                        <h6 class="fw-800 mb-4 text-dark d-flex align-items-center">
                            <div class="p-2 bg-success-soft text-success rounded-8 me-2">
                                <i class="fas fa-chart-bar small"></i>
                            </div>
                            Payment Status Overview
                        </h6>
                        <div style="height: 250px;">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="card border-0 shadow-sm rounded-20 overflow-hidden">
                <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-800 text-dark">Audit Trail: Period Breakdown</h5>
                    <button class="btn btn-light btn-sm rounded-8 border-0">
                        <i class="fas fa-file-export text-muted"></i>
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-premium-dark text-white">
                            <tr>
                                <th class="ps-4 border-0 py-3 small text-uppercase tracking-wider">Period</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider text-center">Orders</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider">Purchase Amt</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider">Amt Paid</th>
                                <th class="border-0 py-3 small text-uppercase tracking-wider pe-4">Avg Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics as $analytic)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="p-2 bg-light rounded-8 me-3">
                                                <i class="fas fa-calendar-alt text-muted"></i>
                                            </div>
                                            <span class="fw-700 text-dark">{{ $analytic->getPeriodLabel() }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark fw-600 px-3 py-2 rounded-8 border">{{ $analytic->total_orders }}</span>
                                    </td>
                                    <td><span class="fw-700 text-dark">Rs.{{ number_format($analytic->total_purchase_amount, 2) }}</span></td>
                                    <td><span class="fw-700 text-success">Rs.{{ number_format($analytic->amount_paid, 2) }}</span></td>
                                    <td class="pe-4"><span class="fw-800 text-primary">Rs.{{ number_format($analytic->average_order_value, 2) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-folder-open fs-1 opacity-20 mb-3 d-block"></i>
                                            No analytics data available for this period
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-12 { border-radius: 12px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-8 { border-radius: 8px; }
    .tiny { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    .bg-primary-soft { background: rgba(13, 110, 253, 0.08); }
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.08); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.08); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.08); }

    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        color: #1a1a1a;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px -15px rgba(0,0,0,0.12);
    }
    .card-bg-icon {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 5rem;
        transform: rotate(-15deg);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.25rem;
    }
    .icon-box-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .group:hover .group-hover-gold { color: #d4af37 !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
$statusLabels = $statusCounts->keys()->toArray();
$statusData = $statusCounts->values()->toArray();
$paymentLabels = $paymentStatusCounts->keys()->toArray();
$paymentData = $paymentStatusCounts->values()->toArray();
@endphp

<script>
    // @ts-nocheck
    /* eslint-disable */
    // eslint-disable-next-line
    const chartStatusLabels = @json($statusLabels);
    // eslint-disable-next-line
    const chartStatusData = @json($statusData);
    // eslint-disable-next-line
    const chartPaymentLabels = @json($paymentLabels);
    // eslint-disable-next-line
    const chartPaymentData = @json($paymentData);

    document.addEventListener('DOMContentLoaded', function() {
        // Shared Chart Config
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#8898aa';

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: chartStatusLabels,
                    datasets: [{
                        data: chartStatusData,
                        backgroundColor: ['#1a1a1a', '#d4af37', '#10b981', '#ef4444', '#f59e0b', '#3b82f6'],
                        borderWidth: 0,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        }
                    }
                }
            });
        }

        // Payment Status Chart
        const paymentCtx = document.getElementById('paymentChart');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'bar',
                data: {
                    labels: chartPaymentLabels,
                    datasets: [{
                        label: 'Orders',
                        data: chartPaymentData,
                        backgroundColor: '#d4af37',
                        borderRadius: 8,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: false },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
        }
    });
    /* eslint-enable */
</script>
@endsection
