@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.sales.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Sales Intelligence</h4>
                    <span class="text-muted small">Monthly Revenue Performance</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-inline-flex gap-2">
                <select name="month" class="form-select form-select-sm border-0 shadow-sm px-3 rounded-pill" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2024, $m, 1)->format('F') }}
                    </option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm border-0 shadow-sm px-3 rounded-pill" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-dark btn-sm px-3 rounded-pill shadow-sm">Filter</button>
                <a href="{{ route('reports.sales.monthly.print', ['month' => $month, 'year' => $year]) }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm">
                    <i class="fas fa-print me-1"></i> Print Proper Report
                </a>
            </form>
        </div>
    </div>

    <!-- Professional Report Container -->
    <div class="report-paper shadow-sm mx-auto bg-white" id="printableReport">
        <!-- Corporate Letterhead -->
        <div class="report-header border-bottom-double mb-4">
            <div class="row align-items-center">
                <div class="col-7">
                    <h2 class="company-name mb-1">{{ config('app.name') }}</h2>
                    <p class="company-details mb-0 text-uppercase tracking-tighter">
                        Premium Jewelry Solutions & ERP<br>
                        Commercial Performance Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">MONTHLY SALES PERFORMANCE</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- REVENUE SUMMARY CARDS -->
            <div class="row mb-5 g-3">
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Monthly Revenue</h6>
                        <h4 class="fw-800 mb-0 text-dark">Rs. {{ number_format($totalSales, 2) }}</h4>
                        <p class="smaller text-success fw-bold mb-0 mt-1">{{ $count }} Transactions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Gross Profit</h6>
                        <h4 class="fw-800 mb-0 text-primary">Rs. {{ number_format($grossProfit, 2) }}</h4>
                        <p class="smaller text-info fw-bold mb-0 mt-1">{{ $grossMargin }}% Margin</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-premium-dark text-white text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-gold mb-2 tracking-wider">Total Collection</h6>
                        <h4 class="fw-800 mb-0 text-gold">Rs. {{ number_format($totalPaid, 2) }}</h4>
                        <p class="smaller text-white-50 fw-bold mb-0 mt-1">Rs. {{ number_format($totalOutstanding, 2) }} Pending</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Items Sold</h6>
                        <h4 class="fw-800 mb-0 text-dark">{{ $totalItems }} Units</h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">Avg. {{ number_format($totalSales / max($count, 1), 2) }} / Order</p>
                    </div>
                </div>
            </div>

            <!-- ANALYTICS CHARTS (Web Only) -->
            <div class="row mb-5 d-print-none">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-24">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-800 text-uppercase smaller text-muted mb-0 tracking-wider">Daily Sales Trend</h6>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-24">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-800 text-uppercase smaller text-muted mb-0 tracking-wider">Revenue Mix</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueMixChart" height="280"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOP PERFORMERS -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Premier Products (Top 5)</h6>
                    @php
                        $topProducts = \App\Models\PosSaleItem::whereIn('pos_sale_id', $sales->pluck('id'))
                            ->select('description', 'sku', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'), \Illuminate\Support\Facades\DB::raw('SUM(quantity * unit_price) as revenue'))
                            ->groupBy('sku', 'description')
                            ->orderByDesc('revenue')
                            ->limit(5)
                            ->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-report table-sm">
                            <thead>
                                <tr>
                                    <th class="text-uppercase tracking-wider">Product</th>
                                    <th class="text-end text-uppercase tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $product)
                                <tr>
                                    <td>
                                        <span class="fw-600 text-dark">{{ substr($product->description, 0, 30) }}</span><br>
                                        <span class="smaller text-muted">{{ $product->sku }} | {{ number_format($product->total_qty, 0) }} Sold</span>
                                    </td>
                                    <td class="text-end fw-800">Rs. {{ number_format($product->revenue, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Top Clients (Top 5)</h6>
                    @php
                        $topCustomers = $sales->groupBy('pos_customer_id')
                            ->map(function($group) {
                                return [
                                    'customer' => $group->first()->customer,
                                    'orders' => $group->count(),
                                    'spent' => $group->sum('total')
                                ];
                            })
                            ->sortByDesc('spent')
                            ->take(5);
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-report table-sm">
                            <thead>
                                <tr>
                                    <th class="text-uppercase tracking-wider">Customer</th>
                                    <th class="text-end text-uppercase tracking-wider">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $item)
                                <tr>
                                    <td>
                                        <span class="fw-600 text-dark">{{ $item['customer']?->name ?? 'Walk-in' }}</span><br>
                                        <span class="smaller text-muted">{{ $item['orders'] }} Orders</span>
                                    </td>
                                    <td class="text-end fw-800">Rs. {{ number_format($item['spent'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- REVENUE COMPONENTS & AUDIT NOTE -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="p-4 border rounded-24 bg-light">
                        <h6 class="fw-800 text-uppercase smaller text-muted mb-4 tracking-wider text-center">Revenue Composition</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Cost of Goods (COGS)</small>
                                <span class="fw-700 text-dark">Rs. {{ number_format($costOfGoodsSold, 2) }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Total Discount</small>
                                <span class="fw-700 text-danger">Rs. {{ number_format($totalDiscount, 2) }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Making Charges</small>
                                <span class="fw-700 text-dark">Rs. {{ number_format($totalMakingCharges, 2) }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Tax Collected</small>
                                <span class="fw-700 text-dark">Rs. {{ number_format($totalTax, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="audit-note p-4 border rounded-24 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Auditor's Monthly Note</h6>
                        <p class="small text-muted mb-0">
                            This monthly performance audit consolidates all retail transactions for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}. 
                            Revenue figures account for all product categories, making charges, and applicable taxes. 
                            Gross profit is calculated after deducting the cost of goods sold (COGS) and realized discounts.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Sales Director</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Finance Manager</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Chief Auditor</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8f9fa;
        font-family: 'Inter', sans-serif;
    }

    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    
    .report-paper {
        width: 100%;
        max-width: 1100px;
        min-height: 29.7cm;
        padding: 50px 70px;
        color: #1a1a1a;
    }

    .company-name {
        font-weight: 800;
        letter-spacing: -0.02em;
        font-size: 1.5rem;
    }

    .company-details {
        font-size: 0.7rem;
        line-height: 1.5;
        color: #6c757d;
        font-weight: 500;
    }

    .report-title-box {
        border-left: 3px solid #d4af37;
        padding-left: 20px;
    }

    .report-type-title {
        font-weight: 800;
        letter-spacing: 0.1em;
        font-size: 1.25rem;
    }

    .border-bottom-double {
        border-bottom: 3px double #dee2e6;
        padding-bottom: 25px;
    }

    .table-report thead th {
        border-top: none;
        border-bottom: 2px solid #1a1a1a;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 15px 10px;
    }

    .table-report tbody td {
        padding: 12px 10px;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .bg-premium-dark { background: #1a1a1a; }
    .text-gold { color: #d4af37 !important; }
    .rounded-16 { border-radius: 16px; }
    .rounded-24 { border-radius: 24px; }

    .sig-line {
        height: 1px;
        background: #1a1a1a;
        margin-top: 40px;
    }

    .smaller { font-size: 0.65rem; }
    .tracking-wider { letter-spacing: 0.08em; }
    .tracking-tighter { letter-spacing: -0.01em; }

    @media print {
        body { background-color: white !important; }
        .no-print-padding { padding: 0 !important; }
        .report-paper {
            width: 100%;
            max-width: none;
            box-shadow: none !important;
            padding: 0;
            margin: 0;
        }
        .d-print-none { display: none !important; }
        @page { size: A4 landscape; margin: 1cm; }
        .bg-premium-dark { background: #1a1a1a !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyTrend->pluck('date')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($dailyTrend->pluck('total')) !!},
                    borderColor: '#d4af37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#1a1a1a',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Revenue Mix Chart
        const mixCtx = document.getElementById('revenueMixChart').getContext('2d');
        new Chart(mixCtx, {
            type: 'doughnut',
            data: {
                labels: ['Product Value', 'Making Charges', 'Tax'],
                datasets: [{
                    data: [
                        {{ $totalSales - $totalMakingCharges - $totalTax }},
                        {{ $totalMakingCharges }},
                        {{ $totalTax }}
                    ],
                    backgroundColor: ['#1a1a1a', '#d4af37', '#6c757d'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endpush
@endsection
