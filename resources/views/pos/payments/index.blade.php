@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-credit-card text-primary"></i> Payment Management</h2>
        <div>
            <a href="{{ route('pos.payments.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-sync"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Collected</h6>
                    <h4 class="card-text">Rs {{ number_format($payments->sum('amount'), 2) }}</h4>
                    <small>All recorded payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Transactions</h6>
                    <h4 class="card-text">{{ $payments->count() }}</h4>
                    <small>Payment records</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed Payments</h6>
                    <h4 class="card-text">{{ $payments->where('status', 'completed')->count() }}</h4>
                    <small>Verified transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Average Payment</h6>
                    <h4 class="card-text">Rs {{ number_format($payments->avg('amount'), 2) }}</h4>
                    <small>Per transaction</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0"><i class="fas fa-filter"></i> Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Invoice / Customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                        <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>💳 Card</option>
                        <option value="jazzcash" {{ request('method') === 'jazzcash' ? 'selected' : '' }}>📱 JazzCash</option>
                        <option value="easypaisa" {{ request('method') === 'easypaisa' ? 'selected' : '' }}>📱 EasyPaisa</option>
                        <option value="bank_js" {{ request('method') === 'bank_js' ? 'selected' : '' }}>🏦 JS Bank</option>
                        <option value="check" {{ request('method') === 'check' ? 'selected' : '' }}>🏦 Check</option>
                        <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>🏛️ Bank Transfer</option>
                        <option value="upi" {{ request('method') === 'upi' ? 'selected' : '' }}>📱 UPI</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>✓ Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="m-0"><i class="fas fa-list"></i> Payment Records</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped m-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Recorded By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>
                            <small class="text-muted">{{ $payment->created_at->format('Y-m-d') }}</small><br>
                            <small>{{ $payment->created_at->format('H:i') }}</small>
                        </td>
                        <td><strong>{{ $payment->sale->invoice_no ?? 'N/A' }}</strong></td>
                        <td>
                            {{ $payment->customer->name ?? 'Walk-in' }}<br>
                            <small class="text-muted">{{ $payment->customer->phone ?? '-' }}</small>
                        </td>
                        <td class="fw-bold">Rs {{ number_format($payment->amount, 2) }}</td>
                        <td>
                            @php
                                $methodIcons = [
                                    'cash' => '💵',
                                    'card' => '💳',
                                    'check' => '🏦',
                                    'bank_transfer' => '🏛️',
                                    'upi' => '📱',
                                    'crypto' => '₿',
                                    'jazzcash' => '📱',
                                    'easypaisa' => '📱',
                                    'bank_js' => '🏦'
                                ];
                                $icon = $methodIcons[$payment->payment_method] ?? '💰';
                            @endphp
                            <span class="badge bg-secondary">
                                {{ $icon }} {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $payment->reference ?? $payment->transaction_id ?? $payment->cheque_number ?? '-' }}</small>
                        </td>
                        <td>
                            <small class="text-muted">{{ $payment->recordedByUser->name ?? '-' }}</small>
                        </td>
                        <td>
                            @if($payment->status === 'completed')
                                <span class="badge bg-success">✓ Completed</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning">⏳ Pending</span>
                            @else
                                <span class="badge bg-danger">✗ {{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pos.payments.show', $payment) }}" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('pos.payments.edit', $payment) }}" class="btn btn-sm btn-warning" title="Edit Payment">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($payment->status === 'pending')
                            <a href="{{ route('pos.payments.continue', $payment) }}" class="btn btn-sm btn-success" title="Complete Payment" onclick="return confirm('Complete this pending payment?')">
                                <i class="fas fa-check"></i>
                            </a>
                            @endif
                            <button class="btn btn-sm btn-danger" onclick="deletePayment({{ $payment->id }})" title="Delete Payment">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No payments recorded</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $payments->links() }}
    </div>

    <!-- Payment Methods Summary -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="m-0"><i class="fas fa-chart-pie"></i> Payments by Method</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @php
                            $byMethod = $payments->groupBy('payment_method')->map(function($group) {
                                return [
                                    'count' => $group->count(),
                                    'total' => $group->sum('amount')
                                ];
                            });
                        @endphp
                        @foreach($byMethod as $method => $data)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $method)) }}</td>
                            <td class="text-end">
                                <span class="badge bg-secondary">{{ $data['count'] }}</span>
                            </td>
                            <td class="text-end">
                                <strong>Rs {{ number_format($data['total'], 2) }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="m-0"><i class="fas fa-chart-bar"></i> Top Customers (by Payment)</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @php
                            $topCustomers = $payments->groupBy('pos_customer_id')->map(function($group) {
                                return [
                                    'count' => $group->count(),
                                    'total' => $group->sum('amount'),
                                    'customer' => $group->first()->customer
                                ];
                            })->sortByDesc('total')->take(5);
                        @endphp
                        @foreach($topCustomers as $data)
                        <tr>
                            <td>{{ $data['customer']->name ?? 'Walk-in' }}</td>
                            <td class="text-end">
                                <span class="badge bg-secondary">{{ $data['count'] }}</span>
                            </td>
                            <td class="text-end">
                                <strong>Rs {{ number_format($data['total'], 2) }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
function deletePayment(paymentId) {
    if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/pos/payments/${paymentId}`;
        form.submit();
    }
}
</script>
@endsection
