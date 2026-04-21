@extends('layouts.app')

@section('title', 'Customer Ledger - ' . $customer->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
            Customer Ledger: {{ $customer->full_name }}
        </h2>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tools fa-4x text-warning mb-3"></i>
                    <h3>Ledger Under Construction</h3>
                    <p class="text-muted">The advanced customer ledger and financial history is being integrated with the accounting module.</p>
                    <div class="mt-4">
                        <div class="row justify-content-center">
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded shadow-sm">
                                    <small class="text-muted d-block">Current Balance</small>
                                    <h4 class="mb-0">${{ number_format($customer->current_balance, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded shadow-sm">
                                    <small class="text-muted d-block">Credit Limit</small>
                                    <h4 class="mb-0">${{ number_format($customer->credit_limit, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
