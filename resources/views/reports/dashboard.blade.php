@extends('layouts.app')

@section('title', 'Intelligence & Analytics - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid py-4">
    <!-- Dashboard Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="h2 mb-1 text-dark fw-800">Intelligence & Analytics</h1>
            <p class="text-muted mb-0 fw-500">Premium business intelligence suite for real-time operational oversight.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-2">
                <span class="badge bg-soft-dark text-dark px-3 py-2 rounded-pill fw-700">
                    <i class="fas fa-shield-alt me-1 text-gold"></i> Secure Audit Access
                </span>
                <button class="btn btn-dark btn-sm px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-file-export me-1"></i> Global Export
                </button>
            </div>
        </div>
    </div>

    <!-- Analytics Navigation Grid -->
    <div class="row g-4 mb-5">
        <!-- Sales Intelligence -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.sales.index') }}" class="report-gateway-card group h-100">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-start mb-4">
                        <div class="gateway-icon bg-gold text-dark shadow-gold">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-800 text-dark mb-1">Sales Intelligence</h5>
                            <p class="text-muted smaller fw-600 mb-0">Revenue audits & trend analysis</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4">Comprehensive tracking of daily, weekly, and monthly revenue streams with SKU-level granularity.</p>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800 smaller text-uppercase tracking-wider">
                        Access Division <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Financial Performance -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.financial.index') }}" class="report-gateway-card group h-100">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-start mb-4">
                        <div class="gateway-icon bg-premium-dark text-white shadow-premium">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-800 text-dark mb-1">Financial Audit</h5>
                            <p class="text-muted smaller fw-600 mb-0">Statements & Fiscal performance</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4">Formal financial reporting including Profit & Loss, Cash Flow, and Trading Accounts for audit compliance.</p>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800 smaller text-uppercase tracking-wider">
                        Access Division <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Inventory Control -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.inventory.index') }}" class="report-gateway-card group h-100">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-start mb-4">
                        <div class="gateway-icon bg-info text-white shadow-sm">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-800 text-dark mb-1">Inventory Control</h5>
                            <p class="text-muted smaller fw-600 mb-0">Valuation & Stock movement</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4">Real-time stock ledger, fine weight trial balance, and automated valuation of precious metal assets.</p>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800 smaller text-uppercase tracking-wider">
                        Access Division <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Supply Chain -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.suppliers.index') }}" class="report-gateway-card group h-100">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-start mb-4">
                        <div class="gateway-icon bg-dark text-white shadow-sm">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-800 text-dark mb-1">Supply Chain</h5>
                            <p class="text-muted smaller fw-600 mb-0">Vendors & Procurement audit</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4">Supplier performance matrix, procurement history, and detailed outstanding liability tracking.</p>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800 smaller text-uppercase tracking-wider">
                        Access Division <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Operational KPIs -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('reports.performance.index') }}" class="report-gateway-card group h-100">
                <div class="card border-0 shadow-soft rounded-24 p-4 h-100 transition-up">
                    <div class="d-flex align-items-start mb-4">
                        <div class="gateway-icon bg-success-soft text-success shadow-sm">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-800 text-dark mb-1">Human Capital</h5>
                            <p class="text-muted smaller fw-600 mb-0">Efficiency & KPI benchmarks</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-4">Employee productivity metrics, sales contribution analysis and workforce efficiency tracking.</p>
                    <div class="mt-auto d-flex align-items-center text-gold fw-800 smaller text-uppercase tracking-wider">
                        Access Division <i class="fas fa-arrow-right ms-2 transition-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Custom Intelligence -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-dashed bg-light rounded-24 p-4 h-100">
                <div class="d-flex align-items-start mb-4 opacity-50">
                    <div class="gateway-icon bg-secondary text-white shadow-sm">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-800 text-dark mb-1">Custom Builder</h5>
                        <p class="text-muted smaller fw-600 mb-0">Tailored data projections</p>
                    </div>
                </div>
                <p class="text-muted small mb-4 italic">The custom report constructor is being prepared for the next deployment. Stay tuned for advanced data modeling.</p>
                <div class="mt-auto">
                    <span class="badge bg-white text-muted border px-3 py-2 rounded-pill smaller fw-700 text-uppercase tracking-wider">Coming Soon</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Intelligence Definitions -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mt-2">
        <div class="card-header bg-premium-dark text-white p-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-book-open text-gold me-3 fs-4"></i>
                <div>
                    <h5 class="mb-0 fw-800">ERP Analytics Dictionary</h5>
                    <p class="text-white-50 smaller mb-0">Reference for core reporting metrics and logic</p>
                </div>
            </div>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 border-start border-gold border-4 bg-light rounded-end">
                        <h6 class="fw-800 text-dark mb-2">Revenue Recognition</h6>
                        <p class="small text-muted mb-0">All sales reports utilize 'Gross Realizable Value' excluding discounts but including applicable luxury taxes and making charges.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border-start border-premium-dark border-4 bg-light rounded-end">
                        <h6 class="fw-800 text-dark mb-2">Inventory Valuation</h6>
                        <p class="small text-muted mb-0">Calculated based on 'Fine Weight' (Pure Metal Content) multiplied by the current market spot rate defined in the master settings.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-24 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3">
                <h6 class="modal-title fw-800 text-dark text-uppercase tracking-wider smaller">Generate Export Bundle</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label smaller fw-800 text-muted text-uppercase tracking-wider mb-2">Intelligence Module</label>
                        <select class="form-select border-0 bg-light rounded-pill px-3 py-2 fw-600" required>
                            <option value="">Select Division...</option>
                            <option value="sales">Sales & Transactions</option>
                            <option value="financial">Financial Accounts</option>
                            <option value="inventory">Inventory Registry</option>
                            <option value="suppliers">Supplier Ledger</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label smaller fw-800 text-muted text-uppercase tracking-wider mb-2">Standard Format</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="fmt" id="fmtPdf" checked>
                                <label class="form-check-label fw-600 small" for="fmtPdf">Formal PDF</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="fmt" id="fmtExcel">
                                <label class="form-check-label fw-600 small" for="fmtExcel">Excel Spreadsheet</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label smaller fw-800 text-muted text-uppercase tracking-wider mb-2">Temporal Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" class="form-control border-0 bg-light rounded-pill px-3 py-2 small">
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control border-0 bg-light rounded-pill px-3 py-2 small">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-sm text-muted fw-700 text-uppercase tracking-wider me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm px-4 rounded-pill fw-800 shadow-sm">
                        <i class="fas fa-download me-1"></i> INITIATE EXPORT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f4f7f6;
        font-family: 'Inter', sans-serif;
    }

    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .fw-500 { font-weight: 500; }
    
    .rounded-24 { border-radius: 24px; }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .bg-soft-dark { background: rgba(0, 0, 0, 0.05); }
    
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }

    .report-gateway-card {
        text-decoration: none;
        display: block;
    }

    .gateway-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        font-size: 1.25rem;
    }

    .transition-up { transition: all 0.3s ease; }
    .group:hover .transition-up { transform: translateY(-10px); }
    .transition-right { transition: transform 0.3s ease; }
    .group:hover .transition-right { transform: translateX(5px); }

    .border-gold { border-color: #d4af37 !important; }
    .border-premium-dark { border-color: #1a1a1a !important; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important; }
    
    .italic { font-style: italic; }
</style>
@endsection
