@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Trial Balance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Financial Reports</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Account Verification</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.financial-reports.export-pdf') }}?report=trial-balance" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                </a>
                <a href="{{ route('accounts.financial-reports.export-excel') }}?report=trial-balance" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-file-excel text-success me-2"></i>Excel
                </a>
                <button onclick="window.print()" class="btn btn-gold shadow-gold px-4 rounded-12 fw-700">
                    <i class="fas fa-print me-2"></i>Print Statement
                </button>
            </div>
        </div>
    </div>

    <!-- Date Filter & High-Level Summary -->
    <div class="row mb-5">
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-soft rounded-24 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-800 text-dark mb-4"><i class="fas fa-filter text-secondary me-2"></i>Report Parameters</h6>
                    <form method="GET" class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-700 text-muted mb-1">Statement Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-12"><i class="fas fa-calendar-day text-secondary"></i></span>
                                <input type="date" name="as_of_date" class="form-control bg-light border-0 rounded-end-12 py-2" value="{{ request('as_of_date', $asOfDate->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-premium-dark w-100 rounded-12 py-2 fw-700">
                                <i class="fas fa-sync-alt me-2"></i>Regenerate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="row h-100">
                <div class="col-md-4 mb-4">
                    <div class="stat-card border-0 shadow-soft h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-info-soft text-info me-3">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <span class="text-muted fw-600 small">Total Debits</span>
                        </div>
                        <h3 class="fw-800 text-dark mb-0">Rs. {{ number_format($totalDebits, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card border-0 shadow-soft h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-success-soft text-success me-3">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <span class="text-muted fw-600 small">Total Credits</span>
                        </div>
                        <h3 class="fw-800 text-dark mb-0">Rs. {{ number_format($totalCredits, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card border-0 shadow-soft h-100 {{ abs($totalDebits - $totalCredits) < 0.01 ? 'bg-premium-dark' : 'bg-danger-soft' }}">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-gold text-dark me-3">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                            <span class="{{ abs($totalDebits - $totalCredits) < 0.01 ? 'text-white-50' : 'text-danger' }} fw-600 small">Balance Status</span>
                        </div>
                        <h4 class="fw-800 mb-0 {{ abs($totalDebits - $totalCredits) < 0.01 ? 'text-white' : 'text-danger' }}">
                            {{ abs($totalDebits - $totalCredits) < 0.01 ? 'PERFECTLY BALANCED' : 'Rs. ' . number_format(abs($totalDebits - $totalCredits), 2) }}
                        </h4>
                        @if(abs($totalDebits - $totalCredits) >= 0.01)
                            <small class="text-danger fw-700">Imbalance Detected</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-5 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-800 text-dark">Comprehensive Account Balances</h5>
            <div class="badge bg-light text-muted px-3 py-2 rounded-8">As of {{ $asOfDate->format('d M Y') }}</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover-premium mb-0">
                    <thead>
                        <tr>
                            <th class="ps-5 py-4 fw-700 text-uppercase small text-muted">Account Identifier</th>
                            <th class="py-4 fw-700 text-uppercase small text-muted">Account Designation</th>
                            <th class="py-4 fw-700 text-uppercase small text-muted">Classification</th>
                            <th class="py-4 fw-700 text-uppercase small text-muted text-end" style="width: 180px;">Debit (Rs.)</th>
                            <th class="pe-5 py-4 fw-700 text-uppercase small text-muted text-end" style="width: 180px;">Credit (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trialBalance as $item)
                            <tr class="align-middle">
                                <td class="ps-5 py-3">
                                    <span class="account-badge">{{ $item['account_code'] }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-700 text-dark">{{ $item['account_name'] }}</div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-muted px-2 py-1">{{ $item['account_type'] }}</span>
                                </td>
                                <td class="py-3 text-end fw-800">
                                    @if($item['debit'] > 0)
                                        <span class="text-primary">{{ number_format($item['debit'], 2) }}</span>
                                    @else
                                        <span class="text-light-gray">-</span>
                                    @endif
                                </td>
                                <td class="pe-5 py-3 text-end fw-800">
                                    @if($item['credit'] > 0)
                                        <span class="text-secondary">{{ number_format($item['credit'], 2) }}</span>
                                    @else
                                        <span class="text-light-gray">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-premium-dark text-white">
                        <tr>
                            <td colspan="3" class="ps-5 py-4 fw-800 fs-5">TRIAL BALANCE TOTALS</td>
                            <td class="py-4 text-end fw-800 fs-5 text-gold">{{ number_format($totalDebits, 2) }}</td>
                            <td class="pe-5 py-4 text-end fw-800 fs-5 text-gold">{{ number_format($totalCredits, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Certification Footer -->
    <div class="report-footer text-center p-5 rounded-24 bg-white shadow-soft">
        <div class="row align-items-center">
            <div class="col-md-4 text-start">
                <p class="text-muted small mb-1">Report Generated By</p>
                <h6 class="fw-800 text-dark mb-0">{{ auth()->user()->name }}</h6>
                <p class="text-muted small mb-0">{{ now()->format('d M Y, H:i A') }}</p>
            </div>
            <div class="col-md-4">
                <div class="brand-logo-small mb-2">
                    <i class="fas fa-gem text-gold me-2"></i>
                    <span class="fw-800 text-dark">JEWELLERY PRO ERP</span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-inline-block text-center border-top border-dark pt-2" style="min-width: 180px;">
                    <p class="text-dark small fw-800 mb-0">Authorized Signature</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium UI Styles */
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-24 { border-radius: 24px; }
    .rounded-12 { border-radius: 12px; }
    .rounded-8 { border-radius: 8px; }
    
    .text-secondary { color: #d4af37 !important; }
    .bg-info-soft { background: rgba(59, 130, 246, 0.1); }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.1); }
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37; }
    .text-light-gray { color: #e0e0e0; }

    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4); color: #1a1a1a; }

    .btn-white { background: #fff; border: 1px solid #f0f0f0; transition: all 0.3s ease; }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

    .btn-premium-dark { background: #1a1a1a; color: #fff; border: none; transition: all 0.3s ease; }
    .btn-premium-dark:hover { background: #000; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-2px); }

    .stat-card { background: white; padding: 25px; border-radius: 20px; transition: all 0.3s ease; }
    .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

    .account-badge {
        background: #f8f9fa; color: #1a1a1a; font-weight: 800; padding: 4px 10px; border-radius: 6px;
        font-family: 'Monaco', 'Courier New', monospace; font-size: 0.85rem; border: 1px solid #eee;
    }

    .table-hover-premium tbody tr { transition: all 0.2s ease; border-bottom: 1px solid #f8f9fa; }
    .table-hover-premium tbody tr:hover { background-color: #fcfcfc; }
    .table-hover-premium thead th { border-bottom: 1px solid #f0f0f0; }

    .rounded-start-12 { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    .rounded-end-12 { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

    @media print {
        .d-print-none { display: none !important; }
        body { background: white !important; }
        .container-fluid { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
        .bg-premium-dark { background: #1a1a1a !important; color-adjust: exact; }
        .text-gold { color: #d4af37 !important; }
        .stat-card { border: 1px solid #eee !important; }
    }
</style>
@endsection
