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
                    <h4 class="mb-0 fw-bold">Inventory Audit</h4>
                    <span class="text-muted small">Metal Book (Fine Weight Ledger)</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="{{ route('reports.financial.metal-book') }}" method="GET" class="d-inline-flex gap-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                <a href="{{ route('reports.financial.metal-book.print', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
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
                        Metal Inventory Control Division<br>
                        <span class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</span>
                    </p>
                </div>
                <div class="col-5 text-end">
                    <div class="report-title-box">
                        <h4 class="report-type-title mb-0">METAL BOOK</h4>
                        <div class="report-period mt-1">
                            {{ $startDate->format('M d, Y') }} — {{ $endDate->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-body">
            <!-- Summary Stats Table -->
            <table class="table table-report mb-5">
                <thead>
                    <tr>
                        <th class="text-uppercase tracking-wider">Metal Position Summary</th>
                        <th class="text-end text-uppercase tracking-wider" style="width: 200px;">Weight (Fine Grams)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Opening Fine Metal Balance (MTD)</td>
                        <td class="text-end">{{ number_format($openingFine, 3) }} g</td>
                    </tr>
                    @php
                        $inflow = $movements->where('type', 'add')->sum(fn($m) => ($m->weight * ($m->product->purity->percentage ?? 100)) / 100);
                        $outflow = $movements->where('type', 'subtract')->sum(fn($m) => ($m->weight * ($m->product->purity->percentage ?? 100)) / 100);
                        $closing = $openingFine + $inflow - $outflow;
                    @endphp
                    <tr class="text-success">
                        <td>Gross Metal Inflow (Receipts & Purchases)</td>
                        <td class="text-end">+{{ number_format($inflow, 3) }} g</td>
                    </tr>
                    <tr class="text-danger">
                        <td>Gross Metal Outflow (Sales & Wastage)</td>
                        <td class="text-end">({{ number_format($outflow, 3) }}) g</td>
                    </tr>
                    <tr class="table-active-row">
                        <td class="fw-bold">Closing Fine Metal Position</td>
                        <td class="text-end fw-bold border-bottom-double">Rs. {{ number_format($closing, 3) }} g</td>
                    </tr>
                </tbody>
            </table>

            <!-- TRANSACTION LOG -->
            <div class="mt-4">
                <h5 class="fw-bold text-uppercase small border-bottom pb-2 mb-3">Metal Movement Audit Trail</h5>
                <div class="table-responsive">
                    <table class="table table-report table-sm small">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Reference</th>
                                <th>Product Details</th>
                                <th class="text-end">Actual Wt.</th>
                                <th class="text-end">Purity</th>
                                <th class="text-end">Fine In</th>
                                <th class="text-end">Fine Out</th>
                                <th class="text-end fw-bold">Running Bal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $running = $openingFine; @endphp
                            @forelse($movements as $m)
                                @php
                                    $fine = ($m->weight * ($m->product->purity->percentage ?? 100)) / 100;
                                    if ($m->type === 'add') {
                                        $running += $fine;
                                        $fineIn = $fine;
                                        $fineOut = 0;
                                    } else {
                                        $running -= $fine;
                                        $fineIn = 0;
                                        $fineOut = $fine;
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td><code>{{ $m->reference ?? 'MANUAL' }}</code></td>
                                    <td>{{ $m->product->name }}</td>
                                    <td class="text-end">{{ number_format($m->weight, 3) }}g</td>
                                    <td class="text-end">{{ $m->product->purity->percentage ?? 100 }}%</td>
                                    <td class="text-end text-success">@if($fineIn > 0) +{{ number_format($fineIn, 3) }} @endif</td>
                                    <td class="text-end text-danger">@if($fineOut > 0) -{{ number_format($fineOut, 3) }} @endif</td>
                                    <td class="text-end fw-bold">{{ number_format($running, 3) }}g</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No metal movements recorded for this period</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Audit Note -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="audit-note p-3 border rounded">
                        <h6 class="fw-bold text-uppercase small border-bottom pb-2 mb-2">Technical Disclaimer</h6>
                        <p class="small text-muted mb-0">
                            The "Fine Weight" is calculated by multiplying the actual weight of the item by its purity percentage. 
                            This report tracks the movement of pure 24K equivalent gold within the system. Any discrepancies 
                            should be reported to the Inventory Audit Department immediately.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="report-footer mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Stock Officer</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Verified By</p>
                    </div>
                    <div class="col-4 text-center">
                        <div class="sig-line mb-2"></div>
                        <p class="small fw-bold text-uppercase mb-0">Auditor Approval</p>
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
