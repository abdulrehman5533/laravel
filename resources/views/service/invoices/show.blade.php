@extends('layouts.app')

@section('title', 'Karigar Invoice Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Invoice {{ $invoice->invoice_number }}</h1>
            <p class="text-muted mb-0">Issued to: {{ $invoice->karigar->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <a href="{{ route('service.invoices.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Invoice Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Job #</th>
                                    <th>Description</th>
                                    <th>Weight</th>
                                    <th>Labor</th>
                                    <th>Wastage</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->serviceJobItem->serviceJob->job_number }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ number_format($item->weight, 3) }}g</td>
                                    <td>Rs. {{ number_format($item->labor_amount, 2) }}</td>
                                    <td>{{ number_format($item->wastage_weight, 3) }}g</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">Totals:</td>
                                    <td>{{ number_format($invoice->total_weight, 3) }}g</td>
                                    <td>Rs. {{ number_format($invoice->total_labor, 2) }}</td>
                                    <td>-</td>
                                    <td class="text-end">Rs. {{ number_format($invoice->grand_total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Invoice Date</span>
                        <span>{{ $invoice->invoice_date->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Labor</span>
                        <span>Rs. {{ number_format($invoice->total_labor, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Wastage Weight</span>
                        <span>{{ number_format($invoice->items->sum('wastage_weight'), 3) }}g</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="h5 mb-0">Grand Total</span>
                        <span class="h5 mb-0 text-primary">Rs. {{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="card-title">Accounting Info</h6>
                    <p class="small text-muted mb-2">This invoice has been posted to the Karigar ledger.</p>
                    <div class="d-flex justify-content-between">
                        <span class="small">Status:</span>
                        <span class="badge bg-info">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .navbar, .sidebar, footer { display: none !important; }
        .card { border: none !important; shadow: none !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; }
    }
</style>
@endsection
