@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-clock text-warning me-2"></i>Pending Payments
            </h1>
            <p class="text-muted mt-1">Purchase orders awaiting payment</p>
        </div>
        <a href="{{ route('accounts.purchases.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Pending Orders</p>
                            <h3 class="fw-bold text-warning">{{ $pendingPayments->count() }}</h3>
                        </div>
                        <div class="p-2 bg-warning bg-opacity-10 rounded-circle">
                            <i class="fas fa-shopping-cart text-warning" style="font-size: 1.5rem;"></i>
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
                            <p class="text-muted small mb-1">Total Amount Due</p>
                            <h3 class="fw-bold text-danger">Rs.{{ number_format($pendingPayments->sum('amount_due'), 2) }}</h3>
                        </div>
                        <div class="p-2 bg-danger bg-opacity-10 rounded-circle">
                            <i class="fas fa-money-bill text-danger" style="font-size: 1.5rem;"></i>
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
                            <p class="text-muted small mb-1">Total Paid</p>
                            <h3 class="fw-bold text-success">Rs.{{ number_format($pendingPayments->sum('amount_paid'), 2) }}</h3>
                        </div>
                        <div class="p-2 bg-success bg-opacity-10 rounded-circle">
                            <i class="fas fa-check-circle text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 py-3 ps-4">PO Number</th>
                        <th class="border-0 py-3">Supplier</th>
                        <th class="border-0 py-3">PO Date</th>
                        <th class="border-0 py-3">Total Amount</th>
                        <th class="border-0 py-3">Paid</th>
                        <th class="border-0 py-3">Due</th>
                        <th class="border-0 py-3">Payment Status</th>
                        <th class="border-0 py-3 text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingPayments as $order)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $order->po_number }}</td>
                            <td>{{ $order->supplier->name ?? '-' }}</td>
                            <td>{{ $order->po_date->format('d M Y') }}</td>
                            <td class="fw-semibold">Rs.{{ number_format($order->total_amount, 2) }}</td>
                            <td class="fw-semibold text-success">Rs.{{ number_format($order->amount_paid, 2) }}</td>
                            <td class="fw-semibold text-danger">Rs.{{ number_format($order->amount_due, 2) }}</td>
                            <td>
                                @switch($order->payment_status)
                                    @case('pending')
                                        <span class="badge bg-danger">Pending</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-warning">Partial</span>
                                        @break
                                    @case('paid')
                                        <span class="badge bg-success">Paid</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                                @endswitch
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('accounts.purchases.orders.show', $order) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success" title="Record Payment" 
                                            data-bs-toggle="modal" data-bs-target="#paymentModal{{ $order->id }}">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Payment Modal -->
                        <div class="modal fade" id="paymentModal{{ $order->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Record Payment - {{ $order->po_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="#" class="payment-form">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Amount Due</label>
                                                <input type="text" class="form-control" value="Rs.{{ number_format($order->amount_due, 2) }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="amount" class="form-control" step="0.01" 
                                                       max="{{ $order->amount_due }}" placeholder="0.00" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                                <select name="payment_method" class="form-select" required>
                                                    <option value="">Select Method</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="check">Check</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="online">Online</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Payment Date</label>
                                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Reference Number</label>
                                                <input type="text" name="reference_number" class="form-control" placeholder="Check number, transaction ID, etc.">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Record Payment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No pending payments</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
    }
</style>
@endsection
