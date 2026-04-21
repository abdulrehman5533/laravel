@extends('layouts.app')
@section('title', 'Interest Collection Report')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h2 class="fw-bold"><i class="fas fa-chart-line me-2 text-success"></i>Interest Collection Report</h2>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Print</button>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3"><input type="date" name="start_date" class="form-control" value="{{ $startDate }}"></div>
                <div class="col-md-3"><input type="date" name="end_date" class="form-control" value="{{ $endDate }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Load</button></div>
            </form>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Interest</h6><h3 class="text-success">₹{{ number_format($summary['total_interest'], 0) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Principal</h6><h3>₹{{ number_format($summary['total_principal'], 0) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Transactions</h6><h3>{{ $summary['transaction_count'] }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Amount</h6><h3>₹{{ number_format($summary['total_amount'], 0) }}</h3></div></div></div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light"><tr><th>Date</th><th>Girvi No.</th><th>Customer</th><th class="text-end">Interest</th><th class="text-end">Principal</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @foreach($collections as $c)
                    <tr><td>{{ $c->payment_date->format('d/m/Y') }}</td><td>{{ $c->girvi->girvi_number }}</td><td>{{ $c->girvi->customer->name }}</td><td class="text-end">₹{{ number_format($c->interest_component, 2) }}</td><td class="text-end">₹{{ number_format($c->principal_component, 2) }}</td><td class="text-end fw-bold">₹{{ number_format($c->amount, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
