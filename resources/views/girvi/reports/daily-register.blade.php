@extends('layouts.app')

@section('title', 'Daily Girvi Register')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-calendar-day me-2 text-primary"></i>Daily Girvi Register</h2>
            <p class="text-muted small mb-0">Date: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-primary"><i class="fas fa-print me-2"></i>Print</button>
            <a href="{{ route('girvi.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Select Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}" max="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Load Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">New Loans</h6>
                    <h3 class="fw-bold">{{ $summary['new_loans_count'] }}</h3>
                    <small class="text-muted">₹{{ number_format($summary['new_loans_amount'], 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Payments Received</h6>
                    <h3 class="fw-bold text-success">{{ $summary['payments_count'] }}</h3>
                    <small class="text-muted">₹{{ number_format($summary['payments_amount'], 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted">Interest Collected</h6>
                    <h3 class="fw-bold text-info">₹{{ number_format($summary['interest_collected'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Closures</h6>
                    <h3 class="fw-bold text-warning">{{ $summary['closures_count'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- New Loans -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>New Loans Disbursed</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Girvi No.</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th class="text-end">Amount</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newLoans as $loan)
                    <tr>
                        <td><a href="{{ route('girvi.loans.show', $loan) }}">{{ $loan->girvi_number }}</a></td>
                        <td>{{ $loan->customer->name }}</td>
                        <td>{{ $loan->branch->name }}</td>
                        <td class="text-end fw-bold">₹{{ number_format($loan->loan_amount, 2) }}</td>
                        <td>{{ $loan->items->count() }} items</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-3 text-muted">No new loans</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payments -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payments Received</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Girvi No.</th>
                        <th>Customer</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Principal</th>
                        <th class="text-end">Interest</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><a href="{{ route('girvi.loans.show', $payment->girvi) }}">{{ $payment->girvi->girvi_number }}</a></td>
                        <td>{{ $payment->girvi->customer->name }}</td>
                        <td class="text-end fw-bold">₹{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-end">₹{{ number_format($payment->principal_component, 2) }}</td>
                        <td class="text-end">₹{{ number_format($payment->interest_component, 2) }}</td>
                        <td>{{ $payment->payment_method }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-3 text-muted">No payments received</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Closures -->
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Loans Closed</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Girvi No.</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th class="text-end">Principal</th>
                        <th class="text-end">Interest Earned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($closures as $closure)
                    <tr>
                        <td>{{ $closure->girvi_number }}</td>
                        <td>{{ $closure->customer->name }}</td>
                        <td>{{ $closure->branch->name }}</td>
                        <td class="text-end">₹{{ number_format($closure->loan_amount, 2) }}</td>
                        <td class="text-end">₹{{ number_format($closure->interest_paid, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-3 text-muted">No closures</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .sidebar, .navbar, .card-body form { display: none !important; }
}
</style>
@endsection
