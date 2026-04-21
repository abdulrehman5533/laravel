@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="h3 mb-4 text-gray-800">Customer Loyalty Program</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Loyal Customers</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Points</th>
                                <th>Current Tier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $customer->loyalty_points }}</span></td>
                                <td>{{ $customer->loyaltyTier?->name ?? 'Basic' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#pointsModal{{ $customer->id }}">Add Points</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Loyalty Tiers</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($tiers as $tier)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $tier->name }}
                            <span class="badge bg-primary rounded-pill">{{ $tier->min_points }}+ pts</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
