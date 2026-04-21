@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-book text-primary me-2"></i>Supplier Ledger
            </h1>
            <p class="text-muted mt-1">{{ $supplier->name }} - Transaction History</p>
        </div>
        <a href="{{ route('accounts.suppliers.show', $supplier) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-uppercase">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" 
                           value="{{ request('from_date', $fromDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-uppercase">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" 
                           value="{{ request('to_date', $toDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('accounts.suppliers.ledger', $supplier) }}" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Purchases</p>
                            <h3 class="fw-bold text-primary">Rs.{{ number_format($totalPurchases, 2) }}</h3>
                        </div>
                        <div class="p-2 bg-primary bg-opacity-10 rounded-circle">
                            <i class="fas fa-shopping-cart text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Payments</p>
                            <h3 class="fw-bold text-success">Rs.{{ number_format($totalPayments, 2) }}</h3>
                        </div>
                        <div class="p-2 bg-success bg-opacity-10 rounded-circle">
                            <i class="fas fa-money-bill text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Balance</p>
                            <h3 class="fw-bold text-danger">Rs.{{ number_format($totalPurchases - $totalPayments, 2) }}</h3>
                        </div>
                        <div class="p-2 bg-danger bg-opacity-10 rounded-circle">
                            <i class="fas fa-exclamation-circle text-danger" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Purchases -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-bold">Purchase Orders</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4">PO Number</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('purchase-orders.show', $purchase) }}" class="text-decoration-none">
                                                {{ $purchase->po_number }}
                                            </a>
                                        </td>
                                        <td>{{ $purchase->po_date->format('d M Y') }}</td>
                                        <td class="text-end pe-4 fw-semibold">Rs.{{ number_format($purchase->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No purchase orders found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($purchases->hasPages())
                        <div class="card-footer bg-light border-top">
                            {{ $purchases->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-bold">Payments</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4">Date</th>
                                    <th class="border-0">Method</th>
                                    <th class="border-0 text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="ps-4">{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td class="text-end pe-4 fw-semibold">Rs.{{ number_format($payment->amount_paid, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No payments found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($payments->hasPages())
                        <div class="card-footer bg-light border-top">
                            {{ $payments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
