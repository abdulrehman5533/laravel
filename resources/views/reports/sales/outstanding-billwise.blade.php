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
                    <h4 class="mb-0 fw-bold">Receivables Management</h4>
                    <span class="text-muted small">Bill-wise Outstanding Audit</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-inline-flex gap-2">
                <select name="customer_id" class="form-select form-select-sm border-0 shadow-sm px-3 rounded-pill" style="min-width: 200px;">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="type" value="bill">
                <button type="submit" class="btn btn-dark btn-sm px-3 rounded-pill shadow-sm">Filter</button>
                <a href="{{ route('reports.sales.outstanding.print', ['type' => 'bill', 'customer_id' => request('customer_id')]) }}" target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm">
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
                        Credit Control & Receivables Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">BILL-WISE OUTSTANDING</h4>
                        <div class="report-period mt-1 text-gold fw-bold small">
                            AS OF {{ now()->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- SUMMARY CARDS -->
            <div class="row mb-5 g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded-16 bg-premium-dark text-white text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-gold mb-2 tracking-wider">Total Receivables</h6>
                        <h4 class="fw-800 mb-0 text-gold">Rs. {{ number_format($totalOutstanding, 2) }}</h4>
                        <p class="smaller text-white-50 fw-bold mb-0 mt-1">{{ $outstandingSales->count() }} Pending Invoices</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Active Debtors</h6>
                        <h4 class="fw-800 mb-0 text-dark">{{ $outstandingSales->unique('pos_customer_id')->count() }} Clients</h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">Across all accounts</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-16 bg-light text-center h-100">
                        <h6 class="text-uppercase smaller fw-800 text-muted mb-2 tracking-wider">Avg. Bill Age</h6>
                        <h4 class="fw-800 mb-0 text-dark">
                            @php
                                $totalDays = 0;
                                foreach($outstandingSales as $s) {
                                    $totalDays += now()->diffInDays($s->sale_time);
                                }
                                $avgAge = $outstandingSales->count() > 0 ? round($totalDays / $outstandingSales->count()) : 0;
                            @endphp
                            {{ $avgAge }} Days
                        </h4>
                        <p class="smaller text-muted fw-bold mb-0 mt-1">Portfolio Velocity</p>
                    </div>
                </div>
            </div>

            <!-- TAB NAVIGATION (Web Only) -->
            <div class="d-print-none mb-4">
                <ul class="nav nav-pills rounded-pill bg-light p-1">
                    <li class="nav-item">
                        <a class="nav-link active rounded-pill px-4 fw-bold" href="{{ route('reports.sales.outstanding', ['type' => 'bill']) }}">Bill-wise View</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-4 fw-bold" href="{{ route('reports.sales.outstanding', ['type' => 'age']) }}">Ageing Analysis</a>
                    </li>
                </ul>
            </div>

            <!-- DETAILED TABLE -->
            <h6 class="fw-800 text-uppercase smaller text-muted mb-3 tracking-wider">Outstanding Invoice Registry</h6>
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Date / Age</th>
                        <th class="text-uppercase tracking-wider">Invoice #</th>
                        <th class="text-uppercase tracking-wider">Customer Name</th>
                        <th class="text-end text-uppercase tracking-wider">Total Bill</th>
                        <th class="text-end text-uppercase tracking-wider">Balance Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outstandingSales as $sale)
                        <tr>
                            <td class="smaller fw-600">
                                <span class="text-dark">{{ \Carbon\Carbon::parse($sale->sale_time)->format('d M Y') }}</span><br>
                                <span class="text-danger">{{ now()->diffInDays($sale->sale_time) }} days old</span>
                            </td>
                            <td class="fw-bold">#{{ $sale->id }}</td>
                            <td>
                                <span class="fw-700 text-dark">{{ $sale->customer?->name ?? 'Private Client' }}</span>
                                <br><small class="text-muted">{{ $sale->customer?->phone ?? 'No contact' }}</small>
                            </td>
                            <td class="text-end text-muted small">Rs. {{ number_format($sale->total, 2) }}</td>
                            <td class="text-end fw-800 text-danger">Rs. {{ number_format($sale->outstanding_balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted italic">No outstanding balances found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <td colspan="4" class="text-end fw-800 text-uppercase smaller">Grand Total Outstanding</td>
                        <td class="text-end fw-800 text-dark">Rs. {{ number_format($totalOutstanding, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- FOOTER NOTES -->
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="audit-note p-4 border rounded-24 bg-light shadow-sm">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Credit Terms & Audit Disclosure</h6>
                        <p class="small text-muted mb-0">
                            This document serves as an official record of unsettled accounts receivable. 
                            Aging is calculated from the date of invoice issuance. 
                            Amounts shown reflect the current net balance after all partial payments and adjustments.
                            Unauthorized credit extensions must be flagged immediately to the Credit Management Department.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Credit Controller</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Sales Head</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2 w-75 mx-auto"></div>
                        <p class="small fw-bold text-uppercase mb-0">Finance Director</p>
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
        @page { size: A4; margin: 1.5cm; }
        .bg-premium-dark { background: #1a1a1a !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection
