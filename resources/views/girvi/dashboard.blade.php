@extends('layouts.app')

@section('title', 'Girvi Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .dashboard-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .dashboard-subtitle {
        color: #64748b;
        font-size: 0.875rem;
    }
    .stat-card {
        padding: 20px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        box-shadow: var(--card-shadow);
        height: 100%;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Girvi Management</h1>
        <p class="dashboard-subtitle">Monitor pawned items, loans, interest and aging analysis</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('girvi.dashboard.sync') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary" title="Recalculate interest and update loan statuses">
                <i class="fas fa-sync-alt me-1"></i> Force Sync
            </button>
        </form>
        <a href="{{ route('girvi.loans.index') }}" class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-list me-1"></i> View All</a>
        <a href="{{ route('girvi.loans.create') }}" class="btn btn-sm btn-gold"><i class="fas fa-plus me-1"></i> New Girvi</a>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Active Girvis</p>
                        <h4 class="fw-bold mb-0">{{ $stats['active_count'] }}</h4>
                        <div class="mt-2 small text-muted">Currently in possession</div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Principal</p>
                        <h4 class="fw-bold mb-0">Rs.{{ number_format($stats['total_principal'], 0) }}</h4>
                        <div class="mt-2 small text-muted">Total capital loaned</div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Outstanding</p>
                        <h4 class="fw-bold mb-0 text-danger">Rs.{{ number_format($stats['total_outstanding'], 0) }}</h4>
                        <div class="mt-2 small text-danger fw-bold">Incl. Accrued Interest</div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Interest Collected</p>
                        <h4 class="fw-bold mb-0 text-primary">Rs.{{ number_format($stats['total_interest_earned'], 0) }}</h4>
                        <div class="mt-2 small text-success fw-bold">Realized Profit</div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Aging Analysis -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2 text-primary"></i>Girvi Aging Analysis</span>
                </div>
                <div class="card-body">
                    <div style="height: 250px; position: relative;" class="mb-4">
                        <canvas id="agingChart"></canvas>
                    </div>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <div class="small text-muted fw-bold">0-30 Days</div>
                            <div class="fw-bold text-success">{{ $agingData['0-30'] }}</div>
                        </div>
                        <div class="col-4 border-end">
                            <div class="small text-muted fw-bold">31-90 Days</div>
                            <div class="fw-bold text-warning">{{ $agingData['31-90'] }}</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted fw-bold">90+ Days</div>
                            <div class="fw-bold text-danger">{{ $agingData['90+'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history me-2 text-primary"></i>Recent Transactions</span>
                    <a href="{{ route('girvi.loans.index') }}" class="btn btn-xs btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 small fw-bold text-muted text-uppercase">ID</th>
                                <th class="small fw-bold text-muted text-uppercase">Customer</th>
                                <th class="small fw-bold text-muted text-uppercase">Amount</th>
                                <th class="text-end pe-3 small fw-bold text-muted text-uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentGirvis as $girvi)
                            <tr>
                                <td class="ps-3"><a href="{{ route('girvi.loans.show', $girvi) }}" class="fw-bold text-primary">{{ $girvi->girvi_number }}</a></td>
                                <td><div class="fw-bold small">{{ $girvi->customer->name }}</div></td>
                                <td class="fw-bold">Rs.{{ number_format($girvi->loan_amount, 0) }}</td>
                                <td class="text-end pe-3 small text-muted">{{ $girvi->girvi_date->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted small">No recent girvi activity.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <!-- Collateral Distribution -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fas fa-gem me-2 text-primary"></i>Collateral Distribution</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <tbody>
                                @foreach($collateralData as $data)
                                <tr>
                                    <td>
                                        <div class="fw-bold small">{{ $data->item_type }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $data->count }} Items</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold">{{ number_format($data->total_weight, 2) }}g</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- High Risk Loans -->
        <div class="col-lg-8">
            <div class="card h-100 border-danger border-opacity-25">
                <div class="card-header d-flex justify-content-between align-items-center bg-danger bg-opacity-10">
                    <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>High Risk Loans (LTV > 85%)</span>
                    <span class="badge bg-danger">Alert</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 small fw-bold text-muted text-uppercase">Loan ID</th>
                                <th class="small fw-bold text-muted text-uppercase">Customer</th>
                                <th class="small fw-bold text-muted text-uppercase text-center">LTV %</th>
                                <th class="text-end pe-3 small fw-bold text-muted text-uppercase">O/S Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($highRiskGirvis as $hg)
                            <tr>
                                <td class="ps-3"><a href="{{ route('girvi.loans.show', $hg) }}" class="fw-bold text-danger">{{ $hg->girvi_number }}</a></td>
                                <td><div class="fw-bold small">{{ $hg->customer->name }}</div></td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ number_format($hg->ltv_ratio, 1) }}%</span>
                                </td>
                                <td class="text-end pe-3 fw-bold">Rs.{{ number_format($hg->outstanding_amount, 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted small">No high risk loans detected.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('agingChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['0-30 Days', '31-90 Days', '90+ Days'],
                datasets: [{
                    data: [{{ $agingData['0-30'] }}, {{ $agingData['31-90'] }}, {{ $agingData['90+'] }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20, font: { size: 11, weight: 'bold' } } }
                }
            }
        });
    });
</script>
@endpush
@endsection
