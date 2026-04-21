@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-history text-primary me-2"></i>Price History
            </h1>
            <p class="text-muted mt-1">{{ $supplier->name }} - Historical Price Records</p>
        </div>
        <a href="{{ route('accounts.suppliers.show', $supplier) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Price History Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 py-3 ps-4">Product Name</th>
                        <th class="border-0 py-3">Material Type</th>
                        <th class="border-0 py-3">Unit</th>
                        <th class="border-0 py-3">Old Price</th>
                        <th class="border-0 py-3">New Price</th>
                        <th class="border-0 py-3">Change</th>
                        <th class="border-0 py-3">Effective From</th>
                        <th class="border-0 py-3">Recorded Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priceHistories as $history)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $history->product_name ?? '-' }}</td>
                            <td>{{ $history->material_type ?? '-' }}</td>
                            <td>{{ $history->unit ?? '-' }}</td>
                            <td>Rs.{{ number_format($history->old_price, 2) }}</td>
                            <td class="fw-semibold">Rs.{{ number_format($history->new_price, 2) }}</td>
                            <td>
                                @php
                                    $change = $history->new_price - $history->old_price;
                                    $changePercent = ($change / $history->old_price) * 100;
                                @endphp
                                <span class="badge bg-{{ $change >= 0 ? 'danger' : 'success' }}">
                                    {{ $change >= 0 ? '+' : '' }}{{ number_format($changePercent, 2) }}%
                                </span>
                            </td>
                            <td>{{ $history->effective_from->format('d M Y') }}</td>
                            <td>{{ $history->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No price history records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($priceHistories->hasPages())
            <div class="card-footer bg-light border-top">
                {{ $priceHistories->links() }}
            </div>
        @endif
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
