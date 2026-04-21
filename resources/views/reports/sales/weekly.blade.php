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
                    <span class="text-muted small">Weekly Revenue Performance</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-inline-flex gap-2 align-items-center">
                <div class="input-group input-group-sm rounded-pill overflow-hidden shadow-sm border-0">
                    <input type="date" name="start_date" class="form-control border-0 px-3" value="{{ $startDate }}" onchange="this.form.submit()">
                    <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-arrow-right small"></i></span>
                    <input type="date" name="end_date" class="form-control border-0 px-3" value="{{ $endDate }}" onchange="this.form.submit()">
                </div>
                <button type="submit" class="btn btn-dark btn-sm px-3 rounded-pill shadow-sm ms-2">Filter</button>
                <a href="{{ route('reports.sales.weekly.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm ms-2">
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
                        <h4 class="report-type-title mb-0 text-info">WEEKLY PERFORMANCE</h4>
                        <div class="report-period mt-1 text-muted fw-bold small">
                            {{ \Carbon\Carbon::parse($startDate)->format('M d') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- PERFORMANCE METRICS -->
            <div class="row mb-5 g-3">
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Weekly Revenue</h6>
                        <h4 class="fw-800 mb-0 text-dark">Rs. {{ number_format($totalSales, 2) }}</h4>
                        <p class="smaller text-success fw-bold mb-0 mt-1">Avg: Rs. {{ number_format($totalSales / max(\Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1, 1), 0) }}/day</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Transaction Count</h6>
                        <h4 class="fw-800 mb-0 text-info">{{ $count }}</h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">{{ number_format($count / max(\Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1, 1), 1) }} Daily Volume</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-premium-dark text-white text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-gold mb-2 tracking-wider">Weekly Profit</h6>
                        <h4 class="fw-800 mb-0 text-gold">Rs. {{ number_format($grossProfit, 2) }}</h4>
                        <p class="smaller text-white-50 fw-bold mb-0 mt-1">{{ $grossMargin }}% Average Margin</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Net Liquidity</h6>
                        <h4 class="fw-800 mb-0 text-dark">Rs. {{ number_format($totalPaid, 2) }}</h4>
                        <p class="smaller text-warning fw-bold mb-0 mt-1">Rs. {{ number_format($totalOutstanding, 2) }} Receivables</p>
                    </div>
                </div>
            </div>

            <!-- Charts (Web Only) -->
            <div class="row mb-5 d-print-none">
                <div class="col-md-8">
                    <div class="p-4 border rounded-24 shadow-sm">
                        <h6 class="fw-800 text-uppercase smaller text-muted mb-4 tracking-wider">Revenue Trend Line</h6>
                        <div style="height: 300px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded-24 h-100 shadow-sm bg-light">
                        <h6 class="fw-800 text-uppercase smaller text-muted mb-4 tracking-wider text-center">Revenue Pillars</h6>
                        <div class="mb-3 border-bottom pb-2">
                            <small class="text-muted d-block smaller text-uppercase fw-700">Total Asset Count</small>
                            <span class="fw-700 text-dark">{{ $totalItems }} Units Sold</span>
                        </div>
                        <div class="mb-3 border-bottom pb-2">
                            <small class="text-muted d-block smaller text-uppercase fw-700">Avg Basket Value</small>
                            <span class="fw-700 text-dark">Rs. {{ number_format($averageOrderValue, 2) }}</span>
                        </div>
                        <div class="mb-3 border-bottom pb-2">
                            <small class="text-muted d-block smaller text-uppercase fw-700">Tax Liabilities</small>
                            <span class="fw-700 text-dark">Rs. {{ number_format($totalTax, 2) }}</span>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block smaller text-uppercase fw-700">COGS Performance</small>
                            <span class="fw-700 text-dark">Rs. {{ number_format($costOfGoodsSold, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETAILED TRANSACTIONS -->
            <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Consolidated Transaction Log</h6>
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Date / Time</th>
                        <th class="text-uppercase tracking-wider">Invoice / Client</th>
                        <th class="text-end text-uppercase tracking-wider">Revenue</th>
                        <th class="text-end text-uppercase tracking-wider">Collected</th>
                        <th class="text-end text-uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales->sortByDesc('sale_time') as $sale)
                        <tr>
                            <td class="smaller fw-600">
                                <span class="text-dark">{{ \Carbon\Carbon::parse($sale->sale_time)->format('d M Y') }}</span><br>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($sale->sale_time)->format('h:i A') }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">#{{ $sale->id }}</span><br>
                                <small class="text-muted">{{ $sale->customer?->name ?? 'Private Client' }}</small>
                            </td>
                            <td class="text-end fw-800">
                                Rs. {{ number_format($sale->total, 2) }}
                            </td>
                            <td class="text-end fw-600">
                                Rs. {{ number_format($sale->payments()->sum('amount'), 2) }}
                            </td>
                            <td class="text-end">
                                @if($sale->outstanding_balance > 0)
                                    <span class="badge bg-soft-warning text-warning px-2 py-1 rounded-pill smaller fw-800">CREDIT</span>
                                @else
                                    <span class="badge bg-soft-success text-success px-2 py-1 rounded-pill smaller fw-800">SETTLED</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">No sales activity for this weekly period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-6">
                        <div class="sig-line mb-2 w-75"></div>
                        <p class="small fw-bold text-uppercase mb-0">Prepared By</p>
                        <p class="smaller text-muted">Business Analytics Unit</p>
                    </div>
                    <div class="col-6 text-end">
                        <div class="sig-line mb-2 w-75 ms-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Validated By</p>
                        <p class="smaller text-muted">Finance Controller</p>
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
        border-left: 3px solid #0dcaf0;
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
        padding: 15px 10px;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .bg-premium-dark { background: #1a1a1a; }
    .text-gold { color: #d4af37 !important; }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.08); }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .rounded-16 { border-radius: 16px; }
    .rounded-24 { border-radius: 24px; }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

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
        @page { size: A4; margin: 1.5cm; }
        .bg-premium-dark { background: #1a1a1a !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendCtx = document.getElementById('trendChart')?.getContext('2d');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendData->keys()) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($trendData->values()) !!},
                    borderColor: '#d4af37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1a1a1a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f1f1' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endsection
