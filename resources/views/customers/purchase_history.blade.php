@extends('layouts.app')

@section('title', 'Purchase History - ' . $customer->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-bag text-primary me-2"></i>
            Purchase History: {{ $customer->full_name }}
        </h2>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <!-- Summary Stats -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4 text-muted">Customer Summary</h5>
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Spent</small>
                        <h3 class="text-primary">${{ number_format($customer->total_purchases, 2) }}</h3>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Orders</small>
                        <h4>{{ $customer->sales->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Average Order Value</small>
                        <h4>${{ number_format($customer->average_order_value, 2) }}</h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Last Purchase</small>
                        <h4>{{ $customer->last_purchase_date ? $customer->last_purchase_date->format('M d, Y') : 'N/A' }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales List -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">All Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Invoice #</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td class="ps-4">{{ $sale->created_at->format('M d, Y') }}</td>
                                        <td><span class="fw-bold text-primary">{{ $sale->invoice_no }}</span></td>
                                        <td>{{ $sale->items->count() }} items</td>
                                        <td><span class="fw-bold">${{ number_format($sale->total, 2) }}</span></td>
                                        <td>
                                            @if($sale->status === 'completed')
                                                <span class="badge bg-success rounded-pill">Completed</span>
                                            @elseif($sale->status === 'cancelled')
                                                <span class="badge bg-danger rounded-pill">Cancelled</span>
                                            @else
                                                <span class="badge bg-warning rounded-pill">{{ ucfirst($sale->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            No purchases found for this customer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($sales->hasPages())
                    <div class="card-footer bg-white">
                        {{ $sales->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
