@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="h2 mb-1 text-dark fw-800">Profit & Loss Statement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Financials</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">P&L Statement</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <div class="dropdown">
                    <button class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2 text-secondary"></i>Export
                    </button>
                    <ul class="dropdown-menu border-0 shadow-lg rounded-15">
                        <li><a class="dropdown-item py-2 fw-600" href="{{ route('accounts.financial-reports.export-pdf') }}?report=profit-loss"><i class="fas fa-file-pdf text-danger me-2"></i>PDF Document</a></li>
                        <li><a class="dropdown-item py-2 fw-600" href="{{ route('accounts.financial-reports.export-excel') }}?report=profit-loss"><i class="fas fa-file-excel text-success me-2"></i>Excel Spreadsheet</a></li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn btn-premium-dark shadow-premium px-4 py-2 rounded-12 fw-700 text-white">
                    <i class="fas fa-print me-2 text-gold"></i>Print Statement
                </button>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-20 mb-5 overflow-hidden">
        <div class="card-body p-4 bg-light-gradient">
            <form method="GET" class="row g-3 align-items-end justify-content-center">
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Reporting Period From</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-light rounded-start-12"><i class="fas fa-calendar-alt text-gold"></i></span>
                        <input type="date" name="start_date" class="form-control border-light rounded-end-12 py-2" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Reporting Period To</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-light rounded-start-12"><i class="fas fa-calendar-check text-gold"></i></span>
                        <input type="date" name="end_date" class="form-control border-light rounded-end-12 py-2" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-12 fw-700">
                        Regenerate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Profit & Loss Statement Core -->
    <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-premium-dark p-5 border-0 text-center">
            <h4 class="text-gold fw-800 mb-2 tracking-widest text-uppercase">Financial Performance Statement</h4>
            <p class="text-white-50 mb-0 fw-600">Reporting Interval: {{ $startDate->format('d F Y') }} — {{ $endDate->format('d F Y') }}</p>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <!-- REVENUE SECTION -->
            <div class="report-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom-premium">
                    <h5 class="fw-800 text-dark mb-0"><i class="fas fa-coins text-gold me-2"></i> OPERATING REVENUE</h5>
                </div>
                <div class="ps-md-4">
                    @foreach($revenues as $revenue)
                        <div class="row py-2 border-bottom-light align-items-center">
                            <div class="col-8 fw-600 text-secondary">{{ $revenue['account_name'] }}</div>
                            <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($revenue['amount'], 2) }}</div>
                        </div>
                    @endforeach
                    <div class="row py-3 mt-2 bg-light-soft rounded-12 align-items-center">
                        <div class="col-8 fw-800 text-dark ps-3">GROSS OPERATING REVENUE</div>
                        <div class="col-4 text-end fw-800 text-success pe-3 fs-5">Rs.{{ number_format($totalRevenue, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- COGS SECTION -->
            <div class="report-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom-premium">
                    <h5 class="fw-800 text-dark mb-0"><i class="fas fa-box-open text-gold me-2"></i> COST OF GOODS SOLD (COGS)</h5>
                </div>
                <div class="ps-md-4">
                    @foreach($costOfSales as $cost)
                        <div class="row py-2 border-bottom-light align-items-center">
                            <div class="col-8 fw-600 text-secondary">{{ $cost['account_name'] }}</div>
                            <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($cost['amount'], 2) }}</div>
                        </div>
                    @endforeach
                    <div class="row py-3 mt-2 align-items-center">
                        <div class="col-8 fw-700 text-muted ps-3">TOTAL COST OF SALES</div>
                        <div class="col-4 text-end fw-700 text-danger pe-3">(Rs.{{ number_format($totalCogs, 2) }})</div>
                    </div>
                    <div class="row py-4 mt-3 bg-premium-dark text-white rounded-20 align-items-center shadow-sm">
                        <div class="col-8 fw-800 ps-4 tracking-wider text-gold">GROSS PROFIT MARGIN</div>
                        <div class="col-4 text-end fw-800 pe-4 fs-4">Rs.{{ number_format($grossProfit, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- OPERATING EXPENSES SECTION -->
            <div class="report-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom-premium">
                    <h5 class="fw-800 text-dark mb-0"><i class="fas fa-file-invoice-dollar text-gold me-2"></i> OPERATING EXPENDITURE</h5>
                </div>
                <div class="ps-md-4">
                    @foreach($operatingExpenses as $expense)
                        <div class="row py-2 border-bottom-light align-items-center">
                            <div class="col-8 fw-600 text-secondary">{{ $expense['account_name'] }}</div>
                            <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($expense['amount'], 2) }}</div>
                        </div>
                    @endforeach
                    <div class="row py-3 mt-2 bg-danger-soft rounded-12 align-items-center">
                        <div class="col-8 fw-800 text-dark ps-3">TOTAL OPERATING EXPENSES</div>
                        <div class="col-4 text-end fw-800 text-danger pe-3">Rs.{{ number_format($totalOperatingExpenses, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- OTHER INCOME/EXPENSES SECTION -->
            <div class="report-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom-premium">
                    <h5 class="fw-800 text-dark mb-0"><i class="fas fa-plus-minus text-gold me-2"></i> NON-OPERATING INCOME & (EXPENSES)</h5>
                </div>
                <div class="ps-md-4">
                    @forelse($otherIncomeExpense as $other)
                        <div class="row py-2 border-bottom-light align-items-center">
                            <div class="col-8 fw-600 text-secondary">{{ $other['account_name'] }}</div>
                            <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($other['amount'], 2) }}</div>
                        </div>
                    @empty
                        <p class="text-muted small italic ps-2">No non-operating entries recorded for this period.</p>
                    @endforelse
                </div>
            </div>

            <!-- NET PERFORMANCE SECTION -->
            <div class="performance-summary mt-5">
                <div class="row g-0">
                    <div class="col-lg-6 ms-auto">
                        <div class="card border-0 bg-light-gradient rounded-24 shadow-sm p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-800 text-muted text-uppercase tracking-wider small">Final Computation</span>
                                <span class="badge bg-gold text-dark fw-800 rounded-pill px-3 py-1 smaller">AUDITED</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-700 text-secondary">Operating Margin:</span>
                                <span class="fw-800 text-dark">{{ $totalRevenue > 0 ? number_format(($grossProfit / $totalRevenue) * 100, 2) : 0 }}%</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                <span class="fw-800 text-dark fs-5">NET REVENUE / (LOSS)</span>
                                <span class="fw-800 fs-3 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rs.{{ number_format($netProfit, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer bg-white p-5 text-center border-0 opacity-50">
            <div class="signature-line mx-auto mb-3" style="width: 200px; height: 1px; background: #ddd;"></div>
            <p class="mb-0 fw-700 text-uppercase tracking-widest small">Authorized Signature & Seal</p>
            <p class="smaller mt-2">Generated on {{ now()->format('d M Y, h:i A') }}</p>
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
    .rounded-24 { border-radius: 24px; }
    
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.15em; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    
    .bg-light-gradient { background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%); }
    .bg-light-soft { background: rgba(0,0,0,0.02); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.05); }
    
    .shadow-premium { box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
    
    .border-bottom-premium { border-bottom: 2px solid #d4af37; }
    .border-bottom-light { border-bottom: 1px solid #f0f0f0; }

    .btn-white {
        background: #fff;
        border: 1px solid #eee;
        transition: all 0.2s;
    }
    .btn-white:hover {
        background: #f8f9fa;
        transform: translateY(-1px);
    }

    .btn-premium-dark {
        background: #1a1a1a;
        color: #fff;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-premium-dark:hover {
        background: #000;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .stat-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    @media print {
        .btn, nav, .card-header form, .sidebar, .navbar { display: none !important; }
        .container-fluid { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
        .card-header { background: #1a1a1a !important; -webkit-print-color-adjust: exact; }
        .text-gold { color: #d4af37 !important; -webkit-print-color-adjust: exact; }
        body { background: white !important; }
    }
</style>
@endsection
