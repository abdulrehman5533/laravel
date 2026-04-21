@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="h2 mb-1 text-dark fw-800">Statement of Financial Position</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Financials</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Balance Sheet</li>
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
                        <li><a class="dropdown-item py-2 fw-600" href="{{ route('accounts.financial-reports.export-pdf') }}?report=balance-sheet"><i class="fas fa-file-pdf text-danger me-2"></i>PDF Document</a></li>
                        <li><a class="dropdown-item py-2 fw-600" href="{{ route('accounts.financial-reports.export-excel') }}?report=balance-sheet"><i class="fas fa-file-excel text-success me-2"></i>Excel Spreadsheet</a></li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn btn-premium-dark shadow-premium px-4 py-2 rounded-12 fw-700 text-white">
                    <i class="fas fa-print me-2 text-gold"></i>Print Balance Sheet
                </button>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm rounded-20 mb-5 overflow-hidden">
        <div class="card-body p-4 bg-light-gradient">
            <form method="GET" class="row g-3 align-items-end justify-content-center">
                <div class="col-md-4">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Reporting Date (As Of)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-light rounded-start-12"><i class="fas fa-calendar-check text-gold"></i></span>
                        <input type="date" name="as_of_date" class="form-control border-light rounded-end-12 py-2" value="{{ request('as_of_date', $asOfDate->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-12 fw-700">
                        Update Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Balance Sheet core -->
    <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-premium-dark p-5 border-0 text-center">
            <div class="mb-3">
                <img src="{{ asset('assets/images/logo-gold.png') }}" alt="Company Logo" height="50" class="opacity-75">
            </div>
            <h4 class="text-gold fw-800 mb-2 tracking-widest text-uppercase">Certified Balance Sheet</h4>
            <p class="text-white-50 mb-0 fw-600">Financial Snapshot as of {{ $asOfDate->format('d F, Y') }}</p>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <div class="row g-5">
                <!-- LEFT COLUMN: ASSETS -->
                <div class="col-lg-6 border-end-premium">
                    <div class="report-section mb-5">
                        <div class="d-flex align-items-center mb-4 pb-2 border-bottom-premium">
                            <h5 class="fw-800 text-dark mb-0 text-uppercase tracking-wider">I. Assets</h5>
                        </div>
                        
                        <!-- Current Assets -->
                        <div class="asset-group mb-4">
                            <h6 class="fw-800 text-primary small text-uppercase mb-3 tracking-widest">Current Assets</h6>
                            @foreach($currentAssets as $asset)
                                <div class="row py-2 border-bottom-light">
                                    <div class="col-8 fw-600 text-secondary ps-3">{{ $asset['account_name'] }}</div>
                                    <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($asset['amount'], 2) }}</div>
                                </div>
                            @endforeach
                            <div class="row py-2 bg-light-soft rounded-8 mt-2">
                                <div class="col-8 fw-700 text-dark ps-3">Total Current Assets</div>
                                <div class="col-4 text-end fw-800 text-dark">Rs.{{ number_format($totalCurrentAssets, 2) }}</div>
                            </div>
                        </div>

                        <!-- Fixed Assets -->
                        <div class="asset-group mb-4">
                            <h6 class="fw-800 text-primary small text-uppercase mb-3 tracking-widest">Fixed Assets (Net)</h6>
                            @foreach($fixedAssets as $asset)
                                <div class="row py-2 border-bottom-light">
                                    <div class="col-8 fw-600 text-secondary ps-3">{{ $asset['account_name'] }}</div>
                                    <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($asset['amount'], 2) }}</div>
                                </div>
                            @endforeach
                            <div class="row py-2 bg-light-soft rounded-8 mt-2">
                                <div class="col-8 fw-700 text-dark ps-3">Total Fixed Assets</div>
                                <div class="col-4 text-end fw-800 text-dark">Rs.{{ number_format($totalFixedAssets, 2) }}</div>
                            </div>
                        </div>

                        @if(count($otherAssets) > 0)
                            <!-- Other Assets -->
                            <div class="asset-group mb-4">
                                <h6 class="fw-800 text-primary small text-uppercase mb-3 tracking-widest">Other Assets</h6>
                                @foreach($otherAssets as $asset)
                                    <div class="row py-2 border-bottom-light">
                                        <div class="col-8 fw-600 text-secondary ps-3">{{ $asset['account_name'] }}</div>
                                        <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($asset['amount'], 2) }}</div>
                                    </div>
                                @endforeach
                                <div class="row py-2 bg-light-soft rounded-8 mt-2">
                                    <div class="col-8 fw-700 text-dark ps-3">Total Other Assets</div>
                                    <div class="col-4 text-end fw-800 text-dark">Rs.{{ number_format($totalOtherAssets, 2) }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-auto pt-4">
                        <div class="row py-4 px-3 bg-premium-dark text-white rounded-20 shadow-sm align-items-center">
                            <div class="col-7 fw-800 tracking-wider text-gold">TOTAL ASSETS VALUE</div>
                            <div class="col-5 text-end fw-800 fs-4">Rs.{{ number_format($totalAssets, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: LIABILITIES & EQUITY -->
                <div class="col-lg-6">
                    <div class="report-section mb-5">
                        <div class="d-flex align-items-center mb-4 pb-2 border-bottom-premium">
                            <h5 class="fw-800 text-dark mb-0 text-uppercase tracking-wider">II. Liabilities & Equity</h5>
                        </div>

                        <!-- Current Liabilities -->
                        <div class="liability-group mb-4">
                            <h6 class="fw-800 text-danger small text-uppercase mb-3 tracking-widest">Current Liabilities</h6>
                            @foreach($currentLiabilities as $liability)
                                <div class="row py-2 border-bottom-light">
                                    <div class="col-8 fw-600 text-secondary ps-3">{{ $liability['account_name'] }}</div>
                                    <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($liability['amount'], 2) }}</div>
                                </div>
                            @endforeach
                            <div class="row py-2 bg-danger-soft rounded-8 mt-2">
                                <div class="col-8 fw-700 text-dark ps-3">Total Current Liabilities</div>
                                <div class="col-4 text-end fw-800 text-dark">Rs.{{ number_format($totalCurrentLiabilities, 2) }}</div>
                            </div>
                        </div>

                        <!-- Long Term Liabilities -->
                        @if(count($longTermLiabilities) > 0)
                            <div class="liability-group mb-4">
                                <h6 class="fw-800 text-danger small text-uppercase mb-3 tracking-widest">Long Term Liabilities</h6>
                                @foreach($longTermLiabilities as $liability)
                                    <div class="row py-2 border-bottom-light">
                                        <div class="col-8 fw-600 text-secondary ps-3">{{ $liability['account_name'] }}</div>
                                        <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($liability['amount'], 2) }}</div>
                                    </div>
                                @endforeach
                                <div class="row py-2 bg-danger-soft rounded-8 mt-2">
                                    <div class="col-8 fw-700 text-dark ps-3">Total Long-Term Liabilities</div>
                                    <div class="col-4 text-end fw-800 text-dark">Rs.{{ number_format($totalLongTermLiabilities, 2) }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Shareholder's Equity -->
                        <div class="equity-group mb-4">
                            <h6 class="fw-800 text-success small text-uppercase mb-3 tracking-widest">Shareholder's Equity</h6>
                            @foreach($equity as $item)
                                <div class="row py-2 border-bottom-light">
                                    <div class="col-8 fw-600 text-secondary ps-3">{{ $item['account_name'] }}</div>
                                    <div class="col-4 text-end fw-700 text-dark">Rs.{{ number_format($item['amount'], 2) }}</div>
                                </div>
                            @endforeach
                            <div class="row py-2 bg-success-soft rounded-8 mt-2">
                                <div class="col-8 fw-700 text-dark ps-3">Total Capital & Equity</div>
                                <div class="col-4 text-end fw-800 text-dark">Rs.{{ number_format($totalEquity, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-4">
                        <div class="row py-4 px-3 bg-light-gradient text-dark rounded-20 shadow-sm border align-items-center">
                            <div class="col-7 fw-800 tracking-wider text-muted">TOTAL LIABILITIES & EQUITY</div>
                            <div class="col-5 text-end fw-800 fs-4">Rs.{{ number_format($totalLiabilitiesEquity, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Footer -->
            <div class="mt-5 p-4 rounded-24 {{ abs($totalAssets - $totalLiabilitiesEquity) < 0.01 ? 'bg-success-soft border-success-soft' : 'bg-warning-soft border-warning-soft' }} border text-center">
                <div class="d-inline-flex align-items-center">
                    <div class="p-3 rounded-circle {{ abs($totalAssets - $totalLiabilitiesEquity) < 0.01 ? 'bg-success text-white' : 'bg-warning text-dark' }} me-3 shadow-sm">
                        <i class="fas {{ abs($totalAssets - $totalLiabilitiesEquity) < 0.01 ? 'fa-balance-scale' : 'fa-exclamation-triangle' }} fs-4"></i>
                    </div>
                    <div class="text-start">
                        <h6 class="mb-1 fw-800 text-dark text-uppercase tracking-wider">Verification Statement</h6>
                        <p class="mb-0 fw-600 text-secondary small">
                            {{ abs($totalAssets - $totalLiabilitiesEquity) < 0.01 
                                ? 'Balanced: Total Assets perfectly reconcile with Total Liabilities and Equity.' 
                                : 'Out of Balance: A discrepancy of Rs.' . number_format(abs($totalAssets - $totalLiabilitiesEquity), 2) . ' exists between Assets and Liabilities.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer bg-white p-5 text-center border-0 opacity-50">
            <div class="d-flex justify-content-center gap-5">
                <div class="text-center">
                    <div class="signature-line mb-2" style="width: 180px; height: 1px; background: #ddd;"></div>
                    <p class="mb-0 fw-700 text-uppercase smaller tracking-widest">Financial Director</p>
                </div>
                <div class="text-center">
                    <div class="signature-line mb-2" style="width: 180px; height: 1px; background: #ddd;"></div>
                    <p class="mb-0 fw-700 text-uppercase smaller tracking-widest">Lead Auditor</p>
                </div>
            </div>
            <p class="smaller mt-4 italic text-muted">This document is an electronic representation of the official ledger as of {{ now()->format('d M Y, H:i') }}</p>
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
    .italic { font-style: italic; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.15em; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    
    .bg-light-gradient { background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%); }
    .bg-light-soft { background: rgba(0,0,0,0.02); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.05); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.05); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.05); }
    
    .border-success-soft { border-color: rgba(25, 135, 84, 0.2) !important; }
    .border-warning-soft { border-color: rgba(255, 193, 7, 0.2) !important; }
    
    .shadow-premium { box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
    
    .border-bottom-premium { border-bottom: 2px solid #d4af37; }
    .border-bottom-light { border-bottom: 1px solid #f0f0f0; }
    .border-end-premium { border-right: 1px solid #eee; }

    .btn-white {
        background: #fff;
        border: 1px solid #eee;
        transition: all 0.2s;
    }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

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

    @media (max-width: 991.98px) {
        .border-end-premium { border-right: none; border-bottom: 1px solid #eee; padding-bottom: 2rem; }
    }

    @media print {
        .btn, nav, .card-header form, .sidebar, .navbar { display: none !important; }
        .container-fluid { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
        .card-header { background: #1a1a1a !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
        .text-gold { color: #d4af37 !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
        .bg-premium-dark { background: #1a1a1a !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
        body { background: white !important; }
    }
</style>
@endsection
