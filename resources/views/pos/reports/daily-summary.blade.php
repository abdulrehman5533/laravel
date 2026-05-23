@extends('layouts.app')
@section('title', 'Daily Sales Summary')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Daily Sales Summary</h1>
            <small class="text-muted">{{ \Carbon\Carbon::parse($date)->format('d M Y, l') }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> POS</a>
            <button onclick="window.print()" class="btn btn-outline-primary"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-primary mb-0">{{ $summary['total_sales'] }}</h4>
                <small class="text-muted">Total Sales</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center py-3" style="background:#1a1a1a">
                <h4 class="text-warning mb-0">Rs. {{ number_format($summary['total_revenue'], 0) }}</h4>
                <small class="text-white-50">Total Revenue</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-success mb-0">Rs. {{ number_format($summary['cash_sales'], 0) }}</h4>
                <small class="text-muted">Cash Sales</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-info mb-0">Rs. {{ number_format($summary['card_sales'], 0) }}</h4>
                <small class="text-muted">Card Sales</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-danger mb-0">Rs. {{ number_format($summary['unpaid_amount'], 0) }}</h4>
                <small class="text-muted">Unpaid</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-secondary mb-0">Rs. {{ number_format($summary['avg_sale_value'], 0) }}</h4>
                <small class="text-muted">Avg Sale</small>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Payment Breakdown --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">Payment Method Breakdown</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Method</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            @forelse($summary['payment_breakdown'] as $pb)
                            <tr>
                                <td><span class="badge bg-secondary">{{ ucfirst($pb->payment_method) }}</span></td>
                                <td class="text-end fw-semibold">Rs. {{ number_format($pb->total, 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No payments today</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">Top Products Sold Today</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Revenue</th></tr></thead>
                        <tbody>
                            @forelse($summary['top_products'] as $tp)
                            <tr>
                                <td>{{ $tp->description }}</td>
                                <td class="text-end">{{ number_format($tp->qty, 2) }}</td>
                                <td class="text-end fw-semibold">Rs. {{ number_format($tp->total, 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No sales today</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales List --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Sales — {{ \Carbon\Carbon::parse($date)->format('d M Y') }} ({{ $sales->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Invoice</th><th>Time</th><th>Customer</th><th>Items</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td><a href="{{ route('pos.sales.show', $sale) }}" class="text-decoration-none"><code>{{ $sale->invoice_no }}</code></a></td>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_time)->format('h:i A') }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $sale->items->count() }}</td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($sale->total, 0) }}</td>
                        <td class="text-end text-success">Rs. {{ number_format($sale->total - $sale->outstanding_balance, 0) }}</td>
                        <td class="text-end {{ $sale->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">
                            Rs. {{ number_format($sale->outstanding_balance, 0) }}
                        </td>
                        <td><span class="badge bg-{{ $sale->payment_status == 'paid' ? 'success' : ($sale->payment_status == 'partial' ? 'warning text-dark' : 'danger') }}">{{ ucfirst($sale->payment_status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales on this date.</td></tr>
                    @endforelse
                </tbody>
                @if($sales->count())
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="4" class="fw-bold">TOTAL</td>
                        <td class="text-end fw-bold text-warning">Rs. {{ number_format($sales->sum('total'), 0) }}</td>
                        <td class="text-end fw-bold text-success">Rs. {{ number_format($sales->sum('total') - $sales->sum('outstanding_balance'), 0) }}</td>
                        <td class="text-end fw-bold text-danger">Rs. {{ number_format($sales->sum('outstanding_balance'), 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
