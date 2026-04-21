@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt text-primary"></i> Payment Details #{{ $payment->id }}</h2>
        <div>
            @if($payment->status === 'pending')
            <a href="{{ route('pos.payments.continue', $payment) }}" class="btn btn-success" onclick="return confirm('Complete this pending payment?')">
                <i class="fas fa-check"></i> Complete Payment
            </a>
            @endif
            <a href="{{ route('pos.payments.edit', $payment) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button class="btn btn-danger" onclick="deletePayment()">
                <i class="fas fa-trash"></i> Delete
            </button>
            <a href="{{ route('pos.payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Payment Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> Payment Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Payment ID</small>
                        <h6>#{{ $payment->id }}</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Payment Date</small>
                        <h6>{{ $payment->created_at->format('d M Y, h:i A') }}</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <h6>{{ $payment->updated_at->format('d M Y, h:i A') }}</h6>
                    </div>
                </div>
            </div>

            <!-- Sale Info -->
            @if($payment->sale)
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-shopping-cart"></i> Sale Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Invoice Number</small>
                        <h6>
                            <a href="{{ route('pos.sales.show', $payment->sale) }}" class="text-decoration-none">
                                {{ $payment->sale->invoice_no }}
                            </a>
                        </h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Sale Total</small>
                        <h6>{{ $payment->sale->currency ?? 'Rs' }} {{ number_format($payment->sale->total, 2) }}</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Sale Status</small>
                        <span class="badge bg-secondary">{{ ucfirst($payment->sale->status) }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Payment Status</small>
                        <span class="badge bg-{{ $payment->sale->payment_status === 'paid' ? 'success' : ($payment->sale->payment_status === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($payment->sale->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Customer Info -->
            @if($payment->customer)
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-user"></i> Customer</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>{{ $payment->customer->name }}</strong>
                    </div>
                    <p class="small text-muted mb-1"><i class="fas fa-phone"></i> {{ $payment->customer->phone ?? 'N/A' }}</p>
                    <p class="small text-muted mb-1"><i class="fas fa-envelope"></i> {{ $payment->customer->email ?? 'N/A' }}</p>
                    @if($payment->customer->loyalty_points > 0)
                    <span class="badge bg-warning mt-2">
                        <i class="fas fa-gift"></i> {{ $payment->customer->loyalty_points }} Points
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Payment Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-money-bill-wave"></i> Payment Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Payment Method</small>
                            <h5>
                                @if($payment->payment_method === 'cash')
                                    💵
                                @elseif($payment->payment_method === 'card')
                                    💳
                                @elseif($payment->payment_method === 'check')
                                    🏦
                                @elseif($payment->payment_method === 'bank_transfer')
                                    🏛️
                                @elseif($payment->payment_method === 'upi')
                                    📱
                                @elseif($payment->payment_method === 'crypto')
                                    ₿
                                @endif
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted d-block">Amount</small>
                            <h3 class="text-success m-0">{{ $payment->currency ?? 'Rs' }} {{ number_format($payment->amount, 2) }}</h3>
                        </div>
                    </div>

                    <hr>

                    @if($payment->payment_method === 'check')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Cheque Number</small>
                            <h6>{{ $payment->cheque_number ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Bank Name</small>
                            <h6>{{ $payment->bank_name ?? 'N/A' }}</h6>
                        </div>
                    </div>
                    @elseif($payment->payment_method === 'bank_transfer')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Bank Name</small>
                            <h6>{{ $payment->bank_name ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Reference / UTR</small>
                            <h6>{{ $payment->reference ?? 'N/A' }}</h6>
                        </div>
                    </div>
                    @elseif($payment->payment_method === 'card')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Transaction ID</small>
                            <h6>{{ $payment->transaction_id ?? 'N/A' }}</h6>
                        </div>
                    </div>
                    @endif

                    @if($payment->reference)
                    <div class="mb-3">
                        <small class="text-muted d-block">Reference</small>
                        <h6>{{ $payment->reference }}</h6>
                    </div>
                    @endif

                    @if($payment->notes)
                    <div class="mb-3">
                        <small class="text-muted d-block">Notes</small>
                        <p class="bg-light p-3 rounded">{{ $payment->notes }}</p>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Recorded By</small>
                            <h6>{{ $payment->recordedByUser?->name ?? 'System' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Exchange Rate</small>
                            <h6>{{ number_format($payment->exchange_rate, 6) }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History for this Sale -->
            @if($payment->sale && $payment->sale->payments()->count() > 1)
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0"><i class="fas fa-history"></i> Other Payments for This Sale</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->sale->payments as $p)
                                @if($p->id !== $payment->id)
                                <tr>
                                    <td><small>{{ $p->created_at->format('d M Y, h:i A') }}</small></td>
                                    <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</span></td>
                                    <td><strong>{{ $p->currency ?? 'Rs' }} {{ number_format($p->amount, 2) }}</strong></td>
                                    <td><small>{{ $p->reference ?? '-' }}</small></td>
                                    <td><span class="badge bg-{{ $p->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($p->status) }}</span></td>
                                    <td>
                                        <a href="{{ route('pos.payments.show', $p) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<form id="deleteForm" action="{{ route('pos.payments.destroy', $payment) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function deletePayment() {
        if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endsection
