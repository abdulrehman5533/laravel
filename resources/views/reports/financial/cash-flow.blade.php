@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 no-print-padding">
    <!-- Page Header (Web Only) -->
    <div class="row align-items-center mb-4 d-print-none">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.financial.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Financial Reporting</h4>
                    <span class="text-muted small">Statement of Cash Flows</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="{{ route('reports.financial.cash-flow') }}" method="GET" class="d-inline-flex gap-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}">
                <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                <a href="{{ route('reports.financial.cash-flow.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
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
                    <h2 class="company-name mb-1">{{ config('app.name', 'MAGIA LUPOS Jewellery Management System (JMS)') }}</h2>
                    <p class="company-details mb-0 text-uppercase tracking-tighter">
                        Premium Jewelry Solutions & ERP<br>
                        Treasury & Liquidity Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">CASH FLOW STATEMENT</h4>
                        <div class="report-period mt-1">
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- CASH INFLOWS -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Cash Inflows (Operating Activities)</th>
                        <th class="text-end text-uppercase tracking-wider">Reference</th>
                        <th class="text-end text-uppercase tracking-wider" style="width: 150px;">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>POS Sales Collection (Cash & Bank)</td>
                        <td class="text-end text-muted">Direct Sales</td>
                        <td class="text-end">{{ number_format($salesInflows, 2) }}</td>
                    </tr>
                    @foreach($inflowsByCategory as $item)
                        <tr>
                            <td>{{ ucfirst($item->category) }}</td>
                            <td class="text-end text-muted">External Receipt</td>
                            <td class="text-end">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-active-row">
                        <td class="fw-bold">Total Cash Receipts</td>
                        <td></td>
                        <td class="text-end fw-bold border-bottom-single">{{ number_format($inflows + $salesInflows, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- CASH OUTFLOWS -->
            <table class="table table-report mb-4">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Cash Outflows (Operating & Investment)</th>
                        <th class="text-end text-uppercase tracking-wider">Category</th>
                        <th class="text-end text-uppercase tracking-wider" style="width: 150px;">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outflowsByCategory as $item)
                        <tr>
                            <td>Business Expenditure / Procurement</td>
                            <td class="text-end text-muted">{{ ucfirst($item->category) }}</td>
                            <td class="text-end">({{ number_format($item->total, 2) }})</td>
                        </tr>
                    @endforeach
                    <tr class="table-active-row text-danger">
                        <td class="fw-bold">Total Cash Payments</td>
                        <td></td>
                        <td class="text-end fw-bold border-bottom-single">({{ number_format($outflows, 2) }})</td>
                    </tr>
                </tbody>
            </table>

            <!-- NET CASH FLOW -->
            <div class="row mt-4 mb-5">
                <div class="col-md-7">
                    <div class="audit-note p-3 border rounded">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Cash Position Summary</h6>
                        <p class="small text-muted mb-0">
                            The net cash flow represents the change in the company's liquidity for the selected period. 
                            Positive values indicate an increase in cash reserves, while negative values indicate a 
                            reduction in liquid assets.
                        </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="final-summary">
                        <div class="d-flex justify-content-between py-3 border-top-double">
                            <span class="fw-bold fs-4">NET CASH FLOW</span>
                            <span class="fw-bold fs-4 {{ ($netCashFlow + $salesInflows) >= 0 ? 'text-success' : 'text-danger' }}">
                                Rs. {{ number_format($netCashFlow + $salesInflows, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Audit Trail -->
            <div class="mt-5 d-print-none">
                <h5 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Detailed Audit Trail (Chronological)</h5>
                <div class="table-responsive">
                    <table class="table table-sm small table-hover">
                        <thead>
                            <tr class="bg-light">
                                <th>Date</th>
                                <th>Category</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashbookEntries as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($entry->category) }}</span></td>
                                    <td><code>{{ $entry->reference_no ?? 'N/A' }}</code></td>
                                    <td class="text-muted">{{ Str::limit($entry->description, 50) }}</td>
                                    <td class="text-end fw-bold {{ $entry->entry_type == 'receipt' ? 'text-success' : 'text-danger' }}">
                                        {{ $entry->entry_type == 'receipt' ? '+' : '-' }}{{ number_format($entry->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No entries found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Cashier / Prepared By</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Verified By</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Authorized By</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Professional Typography & Layout */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f4f7f6;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .report-paper {
        width: 100%;
        max-width: 1000px;
        min-height: 29.7cm; /* A4 Ratio */
        padding: 60px;
        color: #2c3e50;
    }

    .company-name {
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #1a1a1a;
        font-size: 1.75rem;
    }

    .company-details {
        font-size: 0.75rem;
        line-height: 1.6;
        color: #6c757d;
        font-weight: 500;
    }

    .report-title-box {
        border-left: 4px solid #1a1a1a;
        padding-left: 20px;
        text-align: right;
    }

    .report-type-title {
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #1a1a1a;
    }

    .report-period {
        font-size: 0.85rem;
        font-weight: 600;
        color: #d4af37;
    }

    .border-bottom-double {
        border-bottom: 3px double #dee2e6;
        padding-bottom: 30px;
    }

    .border-top-double {
        border-top: 3px double #1a1a1a;
    }

    .border-bottom-single {
        border-bottom: 1px solid #1a1a1a;
    }

    /* Table Styling */
    .table-report {
        margin-bottom: 2rem;
    }

    .table-report thead th {
        border-top: none;
        border-bottom: 2px solid #1a1a1a;
        font-size: 0.7rem;
        font-weight: 700;
        color: #1a1a1a;
        padding: 12px 8px;
    }

    .table-report tbody td {
        padding: 10px 8px;
        font-size: 0.9rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .table-active-row td {
        background-color: #fafafa;
    }

    .audit-note {
        background-color: #f8f9fa;
        border-color: #eee !important;
    }

    .sig-line {
        width: 80%;
        height: 1px;
        background: #333;
        margin: 50px auto 0;
    }

    .tracking-tighter {
        letter-spacing: -0.01em;
    }

    /* Print Specifics */
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
        .container-fluid { width: 100%; padding: 0; margin: 0; }
        .d-print-none { display: none !important; }
        
        .table-report thead th {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
        
        @page {
            size: A4;
            margin: 1.5cm;
        }
    }
</style>
@endsection
