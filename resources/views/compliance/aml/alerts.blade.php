@extends('layouts.app')

@section('title', 'AML Alerts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">AML — Suspicious Transaction Alerts</h1>
            <small class="text-muted">Anti-Money Laundering: High-value & suspicious transactions</small>
        </div>
        <a href="{{ route('compliance.tax.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="alert alert-warning d-flex align-items-start gap-2">
        <i class="fas fa-exclamation-triangle mt-1"></i>
        <div>
            <strong>Mandatory Reporting Threshold:</strong> Transactions exceeding <strong>Rs. 50,000</strong> require customer CNIC/NTN verification.
            Transactions above <strong>Rs. 100,000</strong> are classified as <span class="badge bg-danger">High Risk</span>.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-danger mb-0">{{ $alerts->where('risk_level', 'High')->count() }}</h4>
                <small class="text-muted">High Risk Transactions</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-warning mb-0">{{ $alerts->where('risk_level', 'Medium')->count() }}</h4>
                <small class="text-muted">Medium Risk Transactions</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h4 class="text-primary mb-0">Rs. {{ number_format($alerts->sum('amount'), 2) }}</h4>
                <small class="text-muted">Total Flagged Amount</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Flagged Transactions</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Risk Level</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr class="{{ $alert['risk_level'] === 'High' ? 'table-danger' : 'table-warning' }}">
                        <td><code>#{{ $alert['sale_id'] }}</code></td>
                        <td>{{ $alert['customer'] }}</td>
                        <td><strong>Rs. {{ number_format($alert['amount'], 2) }}</strong></td>
                        <td>{{ $alert['date'] }}</td>
                        <td>
                            <span class="badge bg-{{ $alert['risk_level'] === 'High' ? 'danger' : 'warning text-dark' }}">
                                {{ $alert['risk_level'] }}
                            </span>
                        </td>
                        <td><small>{{ $alert['reason'] }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                            No suspicious transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
