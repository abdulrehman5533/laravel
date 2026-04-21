@extends('layouts.app')

@section('title', 'Loan Ledger - ' . $girvi->girvi_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-file-invoice me-2 text-primary"></i>Loan Ledger</h2>
            <p class="text-muted small mb-0">Transaction history for {{ $girvi->girvi_number }}</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-primary"><i class="fas fa-print me-2"></i>Print</button>
            <a href="{{ route('girvi.loans.show', $girvi) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <!-- Loan Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Girvi Number:</strong><br>{{ $girvi->girvi_number }}
                </div>
                <div class="col-md-3">
                    <strong>Customer:</strong><br>{{ $girvi->customer->name }}
                </div>
                <div class="col-md-3">
                    <strong>Loan Date:</strong><br>{{ $girvi->girvi_date->format('d M Y') }}
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong><br>
                    <span class="badge bg-{{ $girvi->status == 'active' ? 'success' : 'secondary' }}">
                        {{ strtoupper($girvi->status) }}
                    </span>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    <strong>Principal:</strong><br>₹{{ number_format($girvi->loan_amount, 2) }}
                </div>
                <div class="col-md-3">
                    <strong>Interest Rate:</strong><br>{{ $girvi->interest_rate }}% p.a.
                </div>
                <div class="col-md-3">
                    <strong>Outstanding:</strong><br>
                    <span class="text-danger fw-bold">₹{{ number_format($girvi->outstanding_amount, 2) }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Maturity Date:</strong><br>{{ $girvi->maturity_date ? $girvi->maturity_date->format('d M Y') : 'N/A' }}
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
                            <th>Transaction Type</th>
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
                                <span class="badge bg-{{ 
                                    $txn['type'] == 'Payment' ? 'success' : 
                                    ($txn['type'] == 'Interest Accrued' ? 'warning' : 'info') 
                                }}">
                                    {{ $txn['type'] }}
                                </span>
                            </td>
                            <td>{{ $txn['narration'] }}</td>
                            <td class="text-end">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                            <td class="text-end">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                            <td class="text-end fw-bold {{ $txn['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($txn['balance']), 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th class="text-end">{{ number_format($transactions->sum('debit'), 2) }}</th>
                            <th class="text-end">{{ number_format($transactions->sum('credit'), 2) }}</th>
                            <th class="text-end fw-bold text-danger">{{ number_format($transactions->last()['balance'], 2) }}</th>
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
