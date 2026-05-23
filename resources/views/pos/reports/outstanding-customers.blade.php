@extends('layouts.app')
@section('title', 'Outstanding Customers')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Outstanding Customer Balances</h1>
            <small class="text-muted">Customers with pending payments</small>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> POS</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3 border-danger">
                <h4 class="text-danger mb-0">Rs. {{ number_format($totalOutstanding, 0) }}</h4>
                <small class="text-muted">Total Outstanding</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-warning mb-0">{{ $totalCustomers }}</h4>
                <small class="text-muted">Customers with Balance</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-danger mb-0">{{ $overdueCount }}</h4>
                <small class="text-muted">Overdue (30+ days)</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Outstanding Customers ({{ $customers->total() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Customer</th><th>Phone</th><th>Pending Sales</th><th class="text-end">Outstanding Balance</th><th>Last Sale</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none fw-semibold">{{ $customer->name }}</a>
                        </td>
                        <td>{{ $customer->phone }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $customer->sales->count() }} pending</span></td>
                        <td class="text-end fw-bold text-danger">Rs. {{ number_format($customer->current_balance, 0) }}</td>
                        <td>
                            @if($customer->sales->first())
                                {{ \Carbon\Carbon::parse($customer->sales->first()->sale_time)->format('d M Y') }}
                            @else — @endif
                        </td>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-success py-4"><i class="fas fa-check-circle me-2"></i>No outstanding balances!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $customers->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
