@extends('layouts.app')

@section('title', 'Transfer Details | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i>Transfer #{{ $transfer->transfer_number }}</h2>
        <div class="d-flex gap-2">
            @if($transfer->status === 'requested')
                <form action="{{ route('inventory.transfers.approve', $transfer) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-2"></i>Approve Transfer</button>
                </form>
            @endif

            @if($transfer->status === 'approved')
                <form action="{{ route('inventory.transfers.dispatch', $transfer) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fas fa-shipping-fast me-2"></i>Dispatch Stock</button>
                </form>
            @endif

            @if($transfer->status === 'dispatched')
                <form action="{{ route('inventory.transfers.receive', $transfer) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-gold"><i class="fas fa-download me-2"></i>Confirm Receipt</button>
                </form>
            @endif
            
            <button class="btn btn-outline-dark" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Challan
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Transfer Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Transfer Information</h5>
                    <div class="mb-4">
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
                        <span class="badge bg-{{ $statusColor }} px-3 py-2 fs-6">
                            {{ strtoupper($transfer->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted d-block">From Branch</label>
                        <span class="fw-bold h6">{{ $transfer->fromBranch->name }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">To Branch</label>
                        <span class="fw-bold h6">{{ $transfer->toBranch->name }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Requested By</label>
                        <span class="fw-bold">{{ $transfer->requestedBy->name }}</span>
                        <div class="small text-muted">{{ $transfer->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    @if($transfer->notes)
                    <div class="mb-0">
                        <label class="small text-muted d-block">Notes</label>
                        <p class="mb-0 small">{{ $transfer->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Workflow Tracking</h5>
                    <div class="timeline small">
                        <div class="mb-3 d-flex">
                            <i class="fas fa-dot-circle text-success me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold">Requested</div>
                                <div class="text-muted">{{ $transfer->created_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                        @if($transfer->approved_by)
                        <div class="mb-3 d-flex">
                            <i class="fas fa-dot-circle text-info me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold">Approved</div>
                                <div class="text-muted">By {{ $transfer->approvedBy->name }} at {{ $transfer->updated_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($transfer->dispatched_at)
                        <div class="mb-3 d-flex">
                            <i class="fas fa-dot-circle text-primary me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold">Dispatched</div>
                                <div class="text-muted">By {{ $transfer->dispatchedBy->name }} at {{ $transfer->dispatched_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($transfer->received_at)
                        <div class="d-flex">
                            <i class="fas fa-dot-circle text-success me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold">Received</div>
                                <div class="text-muted">By {{ $transfer->receivedBy->name }} at {{ $transfer->received_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Items to Transfer</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Item Details</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfer->items as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $item->product->name }}</div>
                                    <small class="text-muted">{{ $item->product->sku }}</small>
                                </td>
                                <td>{{ $item->product->category->name }}</td>
                                <td><span class="h6 mb-0">{{ number_format($item->quantity, 3) }}</span> {{ $item->product->unit }}</td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('inventory.products.show', $item->product) }}" class="btn btn-sm btn-outline-dark rounded-circle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
