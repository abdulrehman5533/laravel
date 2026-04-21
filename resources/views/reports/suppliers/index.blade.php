@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Supply Chain Analytics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.dashboard') }}" class="text-decoration-none text-muted">Reports</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Supplier Performance</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('reports.suppliers.due') }}" class="btn btn-dark px-4 py-2 rounded-pill fw-700 shadow-sm">
                <i class="fas fa-exclamation-circle me-1"></i> Due Payments Audit
            </a>
        </div>
    </div>

    <!-- Monthly Overview Cards -->
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-gold text-dark shadow-sm">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Total Partners</span>
                </div>
                <h3 class="fw-800 text-dark mb-1">{{ $totalSuppliers }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Active Suppliers</p>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-premium-dark text-white shadow-sm">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Pending Dues</span>
                </div>
                <h3 class="fw-800 text-danger mb-1">{{ $suppliersWithDue }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Suppliers with Balance</p>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 p-4 rounded-24 shadow-soft h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon bg-success-soft text-success shadow-sm">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-2 py-1 smaller fw-700">Total Payable</span>
                </div>
                <h3 class="fw-800 text-success mb-1">Rs. {{ number_format($totalAmountDue, 2) }}</h3>
                <p class="text-muted smaller fw-700 text-uppercase mb-0 tracking-wider">Outstanding Liability</p>
            </div>
        </div>
    </div>

    <!-- Supplier Performance Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden">
        <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-800 text-dark">Supplier Performance Matrix</h5>
            <div class="text-muted smaller fw-600">Audit-Ready Performance Data</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-0">
                        <tr>
                            <th class="ps-4 text-uppercase smaller fw-700 tracking-wider text-muted py-3">Supplier Name</th>
                            <th class="text-uppercase smaller fw-700 tracking-wider text-muted py-3">Company</th>
                            <th class="text-uppercase smaller fw-700 tracking-wider text-muted py-3">Procurement</th>
                            <th class="text-uppercase smaller fw-700 tracking-wider text-muted py-3 text-end">Total Volume</th>
                            <th class="text-uppercase smaller fw-700 tracking-wider text-muted py-3 text-end">Outstanding</th>
                            <th class="text-uppercase smaller fw-700 tracking-wider text-muted py-3 text-center pe-4">Vendor Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr class="border-bottom-faint">
                            <td class="ps-4">
                                <div class="fw-800 text-dark">{{ $supplier->name }}</div>
                                <div class="smaller text-muted">{{ $supplier->phone_primary }}</div>
                            </td>
                            <td>
                                <span class="badge bg-soft-dark text-dark px-2 py-1 rounded-pill smaller fw-700">{{ $supplier->company_name }}</span>
                            </td>
                            <td>
                                <div class="fw-600 smaller">{{ $supplier->purchases_count }} Orders</div>
                            </td>
                            <td class="text-end">
                                <div class="fw-700">Rs. {{ number_format($supplier->total_purchased_amount, 2) }}</div>
                                <div class="smaller text-muted">Paid: {{ number_format($supplier->total_paid_amount, 2) }}</div>
                            </td>
                            <td class="text-end">
                                <span class="fw-800 {{ $supplier->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    Rs. {{ number_format($supplier->outstanding_balance, 2) }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $supplier->rating ? 'text-gold' : 'text-muted-light' }} smaller"></i>
                                    @endfor
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted italic">No supplier intelligence data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .rounded-24 { border-radius: 24px; }
    .smaller { font-size: 0.7rem; }
    .tracking-wider { letter-spacing: 0.05em; }

    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold { background: #d4af37; }
    .text-gold { color: #d4af37 !important; }
    .text-muted-light { color: #e0e0e0; }
    
    .bg-success-soft { background: rgba(16, 185, 129, 0.08); }
    .bg-soft-dark { background: rgba(0, 0, 0, 0.05); }
    
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    
    .stat-card { background: white; transition: all 0.3s ease; }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }

    .border-bottom-faint {
        border-bottom: 1px solid rgba(0,0,0,0.02) !important;
    }

    tr:hover {
        background-color: rgba(0,0,0,0.01);
    }
</style>
@endsection
