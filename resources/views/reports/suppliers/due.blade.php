@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.suppliers.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Liability Audit</h4>
                    <span class="text-muted small">Supplier Due Payments</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('reports.suppliers.due.print') }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm">
                <i class="fas fa-print me-1"></i> Print Proper Report
            </a>
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
                        Procurement & Payables Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">SUPPLIER DUES AUDIT</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            Status as of {{ now()->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- SUMMARY STATS -->
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="p-4 border rounded-24 bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase smaller fw-800 text-muted mb-1 tracking-wider">Total Outstanding Liability</h6>
                            <h2 class="fw-800 mb-0 text-danger">Rs. {{ number_format($suppliers->sum('outstanding_balance'), 2) }}</h2>
                        </div>
                        <div class="text-end">
                            <h6 class="text-uppercase smaller fw-800 text-muted mb-1 tracking-wider">Affected Vendors</h6>
                            <h3 class="fw-800 mb-0 text-dark">{{ $suppliers->count() }} Suppliers</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETAILED DUES TABLE -->
            <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Outstanding Accounts Ledger</h6>
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Supplier / Company</th>
                        <th class="text-uppercase tracking-wider">Contact Details</th>
                        <th class="text-center text-uppercase tracking-wider">Pending POs</th>
                        <th class="text-uppercase tracking-wider">Oldest Aging</th>
                        <th class="text-end text-uppercase tracking-wider">Balance Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <span class="fw-800 text-dark">{{ $supplier->name }}</span><br>
                                <span class="smaller text-muted text-uppercase fw-600">{{ $supplier->company_name }}</span>
                            </td>
                            <td>
                                <span class="smaller fw-600">{{ $supplier->phone_primary }}</span><br>
                                <span class="smaller text-muted">{{ $supplier->email }}</span>
                            </td>
                            <td class="text-center fw-700">
                                {{ $supplier->purchases->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])->count() }}
                            </td>
                            <td>
                                @php 
                                    $oldest = $supplier->purchases->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])->min('po_date');
                                    $days = $oldest ? now()->diffInDays($oldest) : 0;
                                @endphp
                                <span class="smaller fw-700 text-dark">{{ $oldest ? $oldest->format('d M Y') : 'N/A' }}</span><br>
                                <span class="badge {{ $days > 30 ? 'bg-soft-danger text-danger' : 'bg-soft-warning text-warning' }} rounded-pill smaller fw-800">
                                    {{ $days }} DAYS AGED
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-800 text-danger fs-5">
                                    Rs. {{ number_format($supplier->outstanding_balance, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">No outstanding supplier liabilities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Audit Note -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="audit-note p-4 border rounded-24 bg-white shadow-sm">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Payables Audit Note</h6>
                        <p class="small text-muted mb-0">
                            This report enumerates all outstanding balances owed to suppliers as per the current procurement ledger. 
                            Aging is calculated from the Purchase Order (PO) date. Overdue accounts (Aged > 30 days) require 
                            immediate treasury attention to maintain vendor relations and credit standing.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Accounts Payable</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Procurement Head</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Treasury Approval</p>
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
        max-width: 1000px;
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
        padding: 15px 10px;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .text-gold { color: #d4af37 !important; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
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
        @page { size: A4; margin: 1.5cm; }
    }
</style>
@endsection
