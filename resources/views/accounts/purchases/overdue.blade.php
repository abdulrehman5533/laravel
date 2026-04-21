@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-exclamation-triangle text-danger me-2"></i>Overdue Purchase Orders
            </h1>
            <p class="text-muted mt-1">Purchase orders past their expected delivery date</p>
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
                            <p class="text-muted small mb-1">Total Overdue Orders</p>
                            <h3 class="fw-bold text-danger">{{ $overdueOrders->count() }}</h3>
                        </div>
                        <div class="p-2 bg-danger bg-opacity-10 rounded-circle">
                            <i class="fas fa-shopping-cart text-danger" style="font-size: 1.5rem;"></i>
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
                            <p class="text-muted small mb-1">Total Amount Pending</p>
                            <h3 class="fw-bold text-warning">Rs.{{ number_format($overdueOrders->sum('amount_due'), 2) }}</h3>
                        </div>
                        <div class="p-2 bg-warning bg-opacity-10 rounded-circle">
                            <i class="fas fa-money-bill text-warning" style="font-size: 1.5rem;"></i>
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
                            <p class="text-muted small mb-1">Average Days Overdue</p>
                            <h3 class="fw-bold text-info">{{ $overdueOrders->count() > 0 ? round($overdueOrders->avg('days_overdue'), 0) : 0 }} days</h3>
                        </div>
                        <div class="p-2 bg-info bg-opacity-10 rounded-circle">
                            <i class="fas fa-calendar text-info" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 py-3 ps-4">PO Number</th>
                        <th class="border-0 py-3">Supplier</th>
                        <th class="border-0 py-3">Expected Delivery</th>
                        <th class="border-0 py-3">Days Overdue</th>
                        <th class="border-0 py-3">Total Amount</th>
                        <th class="border-0 py-3">Amount Due</th>
                        <th class="border-0 py-3">Status</th>
                        <th class="border-0 py-3 text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueOrders as $order)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $order->po_number }}</td>
                            <td>{{ $order->supplier->name ?? '-' }}</td>
                            <td>{{ $order->expected_delivery_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-danger">{{ $order->days_overdue }} days</span>
                            </td>
                            <td class="fw-semibold">Rs.{{ number_format($order->total_amount, 2) }}</td>
                            <td class="fw-semibold text-warning">Rs.{{ number_format($order->amount_due, 2) }}</td>
                            <td>
                                @switch($order->status)
                                    @case('Draft')
                                        <span class="badge bg-secondary">Draft</span>
                                        @break
                                    @case('Confirmed')
                                        <span class="badge bg-info">Confirmed</span>
                                        @break
                                    @case('Received')
                                        <span class="badge bg-success">Received</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $order->status }}</span>
                                @endswitch
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('accounts.purchases.orders.show', $order) }}" 
                                   class="btn btn-outline-primary btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No overdue purchase orders</p>
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
