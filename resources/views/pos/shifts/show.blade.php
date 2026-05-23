@extends('layouts.app')
@section('title', 'Shift Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Shift Details</h1>
            <small class="text-muted">{{ \Carbon\Carbon::parse($shift->shift_date)->format('d M Y') }} — {{ $shift->cashier->name ?? '—' }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos.shifts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <button onclick="window.print()" class="btn btn-outline-primary"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-success mb-0">Rs. {{ number_format($shift->opening_balance, 0) }}</h4>
                <small class="text-muted">Opening Balance</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-primary mb-0">Rs. {{ number_format($shift->total_cash_in, 0) }}</h4>
                <small class="text-muted">Cash Collected</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-warning mb-0">Rs. {{ number_format($shift->closing_balance, 0) }}</h4>
                <small class="text-muted">Expected Closing</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 {{ ($shift->variance ?? 0) < 0 ? 'border-danger' : 'border-success' }}">
                <h4 class="{{ ($shift->variance ?? 0) < 0 ? 'text-danger' : 'text-success' }} mb-0">
                    Rs. {{ number_format(abs($shift->variance ?? 0), 0) }}
                    {{ ($shift->variance ?? 0) < 0 ? '(Short)' : '(Over)' }}
                </h4>
                <small class="text-muted">Variance</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Sales During This Shift ({{ $salesDuring->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Invoice</th><th>Time</th><th>Customer</th><th class="text-end">Total</th><th>Payment</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($salesDuring as $sale)
                    <tr>
                        <td><a href="{{ route('pos.sales.show', $sale) }}" class="text-decoration-none"><code>{{ $sale->invoice_no }}</code></a></td>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_time)->format('h:i A') }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($sale->total, 0) }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($sale->payment_method ?? 'N/A') }}</span></td>
                        <td><span class="badge bg-{{ $sale->payment_status == 'paid' ? 'success' : 'warning text-dark' }}">{{ ucfirst($sale->payment_status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No sales during this shift.</td></tr>
                    @endforelse
                </tbody>
                @if($salesDuring->count())
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="3" class="fw-bold">TOTAL</td>
                        <td class="text-end fw-bold text-warning">Rs. {{ number_format($salesDuring->sum('total'), 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
