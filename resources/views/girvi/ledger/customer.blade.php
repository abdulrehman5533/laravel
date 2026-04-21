@extends('layouts.app')

@section('title', 'Customer Ledger - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-book me-2 text-primary"></i>Customer Ledger</h2>
            <p class="text-muted small mb-0">Complete transaction history for {{ $customer->name }}</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-primary"><i class="fas fa-print me-2"></i>Print</button>
            <a href="{{ route('girvi.loans.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <!-- Customer Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Customer Name:</strong><br>{{ $customer->name }}
                </div>
                <div class="col-md-3">
                    <strong>Phone:</strong><br>{{ $customer->phone }}
                </div>
                <div class="col-md-3">
                    <strong>Total Loans:</strong><br>{{ $girvis->count() }}
                </div>
                <div class="col-md-3">
                    <strong>Active Loans:</strong><br>{{ $girvis->where('status', 'active')->count() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Girvi Number</th>
                            <th>Narration</th>
                            <th class="text-end">Debit (₹)</th>
                            <th class="text-end">Credit (₹)</th>
                            <th class="text-end">Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($txn['date'])->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $txn['type'] == 'Payment Received' ? 'success' : 'info' }}">
                                    {{ $txn['type'] }}
                                </span>
                            </td>
                            <td>{{ $txn['girvi_number'] }}</td>
                            <td>{{ $txn['narration'] }}</td>
                            <td class="text-end">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                            <td class="text-end">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                            <td class="text-end fw-bold {{ $txn['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($txn['balance']), 2) }} {{ $txn['balance'] > 0 ? 'Dr' : 'Cr' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total:</th>
                            <th class="text-end">{{ number_format($transactions->sum('debit'), 2) }}</th>
                            <th class="text-end">{{ number_format($transactions->sum('credit'), 2) }}</th>
                            <th class="text-end fw-bold">{{ number_format(abs($transactions->last()['balance']), 2) }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .sidebar, .navbar { display: none !important; }
}
</style>
@endsection
