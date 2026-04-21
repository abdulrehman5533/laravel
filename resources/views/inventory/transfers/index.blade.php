@extends('layouts.app')

@section('title', 'Branch Transfers | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-exchange-alt me-2 text-primary"></i>Inter-Branch Stock Transfers</h2>
        <a href="{{ route('inventory.transfers.create') }}" class="btn btn-dark">
            <i class="fas fa-plus me-2"></i>New Transfer Request
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Transfer #</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                    <tr>
                        <td class="fw-bold text-primary">{{ $transfer->transfer_number }}</td>
                        <td>{{ $transfer->fromBranch->name }}</td>
                        <td>{{ $transfer->toBranch->name }}</td>
                        <td>
                            @php
                                $statusColor = match($transfer->status) {
                                    'requested' => 'warning',
                                    'approved' => 'info',
                                    'dispatched' => 'primary',
                                    'received' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">
                                {{ strtoupper($transfer->status) }}
                            </span>
                        </td>
                        <td>{{ $transfer->requestedBy->name }}</td>
                        <td>{{ $transfer->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('inventory.transfers.show', $transfer) }}" class="btn btn-sm btn-dark px-3">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3 opacity-25"></i>
                            <p class="text-muted">No stock transfers found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
