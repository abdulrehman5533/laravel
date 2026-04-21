@extends('layouts.app')

@section('title', 'Accounting Dashboard - ' . config('app.name', 'MAGIA LUPOS'))

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
        transition: transform 0.15s ease-in-out;
    }
    .stat-card:hover {
        transform: translateY(-2px);
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
    .module-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 20px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .module-card:hover {
        border-color: var(--secondary);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        color: inherit;
    }
    .module-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 16px;
    }
    .progress-compact {
        height: 6px;
        border-radius: 3px;
        background: #f1f5f9;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Accounting & Financials</h1>
        <p class="dashboard-subtitle">Monitor cash flow, ledgers and financial performance</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-file-export me-1"></i> Export Data</button>
        <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-sm btn-gold"><i class="fas fa-plus me-1"></i> New Transaction</a>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Primary KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Income</p>
                        <h4 class="fw-bold mb-0" id="stat-total-income">Rs. {{ number_format($totalIncome, 0) }}</h4>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center small mt-2">
                    <span class="text-success fw-bold me-1"><i class="fas fa-caret-up"></i> 5.2%</span>
                    <span class="text-muted">vs prev. month</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Expenses</p>
                        <h4 class="fw-bold mb-0" id="stat-total-expenses">Rs. {{ number_format($totalExpenses, 0) }}</h4>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center small mt-2">
                    <span class="text-danger fw-bold me-1"><i class="fas fa-caret-up"></i> 2.1%</span>
                    <span class="text-muted">vs prev. month</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card" id="profit-card" style="{{ $netProfit >= 0 ? 'border: 1px solid rgba(13, 110, 253, 0.25); background-color: rgba(13, 110, 253, 0.1);' : 'background-color: rgba(220, 53, 69, 0.1);' }}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Net Profit</p>
                        <h4 class="fw-bold mb-0" id="stat-net-profit" style="{{ $netProfit >= 0 ? 'color: #0d6efd;' : 'color: #dc3545;' }}">Rs. {{ number_format($netProfit, 0) }}</h4>
                    </div>
                    <div class="stat-icon" id="profit-icon" style="{{ $netProfit >= 0 ? 'background-color: #0d6efd; color: white;' : 'background-color: #dc3545; color: white;' }}">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center small mt-2">
                    <span class="fw-bold me-1" id="stat-profit-margin">{{ $totalIncome > 0 ? number_format(($netProfit / $totalIncome) * 100, 1) : 0 }}%</span>
                    <span class="text-muted">Margin</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Cash On Hand</p>
                        <h4 class="fw-bold mb-0" id="stat-cash-balance">Rs. {{ number_format($cashBalance, 0) }}</h4>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-vault"></i>
                    </div>
                </div>
                <div class="progress progress-compact mt-2">
                    <div class="progress-bar bg-info" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 shadow-sm border">
                <div class="d-flex align-items-center">
                    <div class="bg-light p-2 rounded me-3">
                        <i class="fas fa-hand-holding-usd text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Receivables</p>
                        <h6 class="fw-bold mb-0 text-primary" id="stat-receivables">Rs. {{ number_format($outstandingReceivables, 0) }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 shadow-sm border">
                <div class="d-flex align-items-center">
                    <div class="bg-light p-2 rounded me-3">
                        <i class="fas fa-file-invoice-dollar text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Payables</p>
                        <h6 class="fw-bold mb-0 text-danger" id="stat-payables">Rs. {{ number_format($outstandingPayables, 0) }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 shadow-sm border">
                <div class="d-flex align-items-center">
                    <div class="bg-light p-2 rounded me-3">
                        <i class="fas fa-boxes text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Stock Value</p>
                        <h6 class="fw-bold mb-0 text-warning" id="stat-inventory">Rs. {{ number_format($inventoryValue, 0) }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-3 shadow-sm border">
                <div class="d-flex align-items-center">
                    <div class="bg-light p-2 rounded me-3">
                        <i class="fas fa-university text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Bank Balance</p>
                        <h6 class="fw-bold mb-0 text-success" id="stat-bank">Rs. {{ number_format($bankBalance, 0) }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line me-2 text-primary"></i>Cash Flow Trend (30 Days)</span>
                    <div class="btn-group">
                        <button class="btn btn-xs btn-outline-secondary active">Monthly</button>
                        <button class="btn btn-xs btn-outline-secondary">Yearly</button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="fas fa-chart-pie me-2 text-primary"></i>Asset Allocation</span>
                </div>
                <div class="card-body">
                    <div style="height: 200px; position: relative;" class="mb-4">
                        <canvas id="distributionChart"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <p class="text-muted small mb-0 fw-bold">Total</p>
                            <h6 class="fw-bold mb-0" id="stat-total-assets-k">Rs. {{ number_format(($cashBalance + $bankBalance + $inventoryValue) / 1000, 1) }}k</h6>
                        </div>
                    </div>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span><i class="fas fa-circle text-info me-2 small"></i>Cash</span>
                            <span class="fw-bold" id="stat-cash-percent">{{ number_format(($cashBalance / max(1, $cashBalance + $bankBalance + $inventoryValue)) * 100, 1) }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span><i class="fas fa-circle text-success me-2 small"></i>Bank</span>
                            <span class="fw-bold" id="stat-bank-percent">{{ number_format(($bankBalance / max(1, $cashBalance + $bankBalance + $inventoryValue)) * 100, 1) }}%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-warning me-2 small"></i>Inventory</span>
                            <span class="fw-bold" id="stat-inventory-percent">{{ number_format(($inventoryValue / max(1, $cashBalance + $bankBalance + $inventoryValue)) * 100, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions & Sales Status -->
    <div class="row g-3 mb-4">
        <!-- Sales Tracking -->
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                    <span class="fw-bold text-dark"><i class="fas fa-shopping-cart me-2 text-primary"></i>Real-time Sales Tracking</span>
                    <a href="{{ route('pos.sales.index') }}" class="btn btn-xs btn-outline-primary px-2">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-3">Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Time</th>
                            </tr>
                        </thead>
                        <tbody id="recent-sales-table">
                            @forelse($recentSales as $sale)
                            <tr>
                                <td class="ps-3"><span class="fw-bold text-primary">{{ $sale->invoice_no }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user text-muted small" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <span>{{ $sale->customer->name ?? 'Walk-in' }}</span>
                                    </div>
                                </td>
                                <td class="fw-bold">Rs. {{ number_format($sale->total, 0) }}</td>
                                <td>
                                    @php
                                        $statusColor = match($sale->payment_status) {
                                            'paid' => 'success',
                                            'partial' => 'info',
                                            'unpaid' => 'danger',
                                            'pending' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} border-0 px-2">
                                        <i class="fas fa-circle me-1 small" style="font-size: 0.5rem;"></i>
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 text-muted small">{{ $sale->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No recent sales found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Ledger -->
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm text-dark">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>Recent Ledger Activity</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="recent-ledger-list">
                        @forelse($recentTransactions as $tx)
                        <div class="list-group-item px-3 py-2 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 small fw-bold">{{ $tx->account->account_name }}</h6>
                                <span class="{{ $tx->debit > 0 ? 'text-danger' : 'text-success' }} fw-bold small">
                                    {{ $tx->debit > 0 ? '-' : '+' }} Rs. {{ number_format($tx->debit > 0 ? $tx->debit : $tx->credit, 0) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">{{ Str::limit($tx->description, 35) }}</span>
                                <span class="text-muted" style="font-size: 0.7rem;">{{ $tx->date->format('d M') }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">No ledger activity recorded</div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-transparent text-center border-0 py-2">
                    <a href="{{ route('accounts.general-ledger.index') }}" class="btn btn-xs btn-link text-decoration-none">Open Full Ledger <i class="fas fa-chevron-right ms-1 small"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Hub -->
    <div class="row g-3">
        <div class="col-12">
            <h6 class="fw-bold mb-2">Accounting Modules</h6>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('accounts.bank-payments.index') }}" class="module-card">
                <div class="module-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-university"></i>
                </div>
                <h6 class="fw-bold mb-1">Bank Treasury</h6>
                <p class="text-muted small mb-0">Manage deposits, bank transfers and reconciliation reports.</p>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('accounts.general-ledger.index') }}" class="module-card">
                <div class="module-icon bg-dark bg-opacity-10 text-dark">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h6 class="fw-bold mb-1">General Ledger</h6>
                <p class="text-muted small mb-0">Complete transaction history with detailed auditing capability.</p>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('accounts.financial-reports.trial-balance') }}" class="module-card">
                <div class="module-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h6 class="fw-bold mb-1">Financial Statements</h6>
                <p class="text-muted small mb-0">P&L accounts, balance sheets and trial balances.</p>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('accounts.suppliers.index') }}" class="module-card">
                <div class="module-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h6 class="fw-bold mb-1">Supplier Relations</h6>
                <p class="text-muted small mb-0">Track payables, purchase ledgers and supplier age analysis.</p>
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: 'Income',
                    data: {!! json_encode($chartData['incomes']) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Expenses',
                    data: {!! json_encode($chartData['expenses']) !!},
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Distribution Chart
        const distCtx = document.getElementById('distributionChart').getContext('2d');
        const distChart = new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'Bank', 'Inventory'],
                datasets: [{
                    data: [{{ $cashBalance }}, {{ $bankBalance }}, {{ $inventoryValue }}],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Real-time Update Polling
        function updateDashboard() {
            fetch("{{ route('accounts.accounting-dashboard.data') }}")
                .then(response => response.json())
                .then(data => {
                    // Update Stats
                    document.getElementById('stat-total-income').innerText = 'Rs. ' + data.totalIncome;
                    document.getElementById('stat-total-expenses').innerText = 'Rs. ' + data.totalExpenses;
                    
                    const netProfitEl = document.getElementById('stat-net-profit');
                    netProfitEl.innerText = 'Rs. ' + data.netProfit;
                    
                    const profitCard = document.getElementById('profit-card');
                    const profitIcon = document.getElementById('profit-icon');
                    const marginEl = document.getElementById('stat-profit-margin');
                    
                    const margin = data.totalIncomeRaw > 0 ? ((data.netProfitRaw / data.totalIncomeRaw) * 100).toFixed(1) : 0;
                    marginEl.innerText = margin + '%';

                    if (data.netProfitRaw >= 0) {
                        netProfitEl.style.color = '#0d6efd';
                        profitCard.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
                        profitCard.style.border = '1px solid rgba(13, 110, 253, 0.25)';
                        profitIcon.style.backgroundColor = '#0d6efd';
                    } else {
                        netProfitEl.style.color = '#dc3545';
                        profitCard.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
                        profitCard.style.border = '1px solid rgba(220, 53, 69, 0.25)';
                        profitIcon.style.backgroundColor = '#dc3545';
                    }

                    document.getElementById('stat-cash-balance').innerText = 'Rs. ' + data.cashBalance;
                    document.getElementById('stat-receivables').innerText = 'Rs. ' + data.outstandingReceivables;
                    document.getElementById('stat-payables').innerText = 'Rs. ' + data.outstandingPayables;
                    document.getElementById('stat-inventory').innerText = 'Rs. ' + data.inventoryValue;
                    document.getElementById('stat-bank').innerText = 'Rs. ' + data.bankBalance;

                    // Update Asset Distribution
                    const totalAssets = data.cashBalanceRaw + data.bankBalanceRaw + data.inventoryValueRaw;
                    document.getElementById('stat-total-assets-k').innerText = 'Rs. ' + (totalAssets / 1000).toFixed(1) + 'k';
                    
                    const cashPct = totalAssets > 0 ? ((data.cashBalanceRaw / totalAssets) * 100).toFixed(1) : 0;
                    const bankPct = totalAssets > 0 ? ((data.bankBalanceRaw / totalAssets) * 100).toFixed(1) : 0;
                    const invPct = totalAssets > 0 ? ((data.inventoryValueRaw / totalAssets) * 100).toFixed(1) : 0;
                    
                    document.getElementById('stat-cash-percent').innerText = cashPct + '%';
                    document.getElementById('stat-bank-percent').innerText = bankPct + '%';
                    document.getElementById('stat-inventory-percent').innerText = invPct + '%';

                    distChart.data.datasets[0].data = [data.cashBalanceRaw, data.bankBalanceRaw, data.inventoryValueRaw];
                    distChart.update();

                    // Update Trend Chart
                    trendChart.data.labels = data.chartData.labels;
                    trendChart.data.datasets[0].data = data.chartData.incomes;
                    trendChart.data.datasets[1].data = data.chartData.expenses;
                    trendChart.update();

                    // Update Recent Sales
                    const salesTbody = document.getElementById('recent-sales-table');
                    let salesHtml = '';
                    if (data.recentSales.length > 0) {
                        data.recentSales.forEach(sale => {
                            const statusColors = {
                                'paid': 'success',
                                'partial': 'info',
                                'unpaid': 'danger',
                                'pending': 'warning'
                            };
                            const color = statusColors[sale.payment_status] || 'secondary';
                            
                            salesHtml += `
                                <tr>
                                    <td class="ps-3"><span class="fw-bold text-primary">${sale.invoice_no}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-muted small" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <span>${sale.customer_name}</span>
                                        </div>
                                    </td>
                                    <td class="fw-bold">Rs. ${sale.total}</td>
                                    <td>
                                        <span class="badge bg-${color} bg-opacity-10 text-${color} border-0 px-2">
                                            <i class="fas fa-circle me-1 small" style="font-size: 0.5rem;"></i>
                                            ${sale.payment_status.charAt(0).toUpperCase() + sale.payment_status.slice(1)}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 text-muted small">${sale.time_ago}</td>
                                </tr>
                            `;
                        });
                    } else {
                        salesHtml = '<tr><td colspan="5" class="text-center py-4 text-muted">No recent sales found</td></tr>';
                    }
                    salesTbody.innerHTML = salesHtml;

                    // Update Recent Ledger
                    const ledgerList = document.getElementById('recent-ledger-list');
                    let ledgerHtml = '';
                    if (data.recentTransactions.length > 0) {
                        data.recentTransactions.forEach(tx => {
                            const colorClass = tx.type === 'debit' ? 'text-danger' : 'text-success';
                            const prefix = tx.type === 'debit' ? '-' : '+';
                            
                            ledgerHtml += `
                                <div class="list-group-item px-3 py-2 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 small fw-bold">${tx.account_name}</h6>
                                        <span class="${colorClass} fw-bold small">
                                            ${prefix} Rs. ${tx.amount}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">${tx.description}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">${tx.date}</span>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        ledgerHtml = '<div class="text-center py-4 text-muted small">No ledger activity recorded</div>';
                    }
                    ledgerList.innerHTML = ledgerHtml;
                })
                .catch(error => console.error('Error polling dashboard data:', error));
        }

        // Poll every 30 seconds
        setInterval(updateDashboard, 30000);
    });
</script>
@endsection
