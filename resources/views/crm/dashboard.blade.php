@extends('layouts.app')

@section('title', 'CRM Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">CRM Dashboard</h1>
        <a href="{{ route('customers.create') }}" class="btn btn-gold">
            <i class="fas fa-plus-circle me-2"></i> Add Customer
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Customers</p>
                            <h2 class="mb-0">{{ $totalCustomers }}</h2>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">VIP Customers</p>
                            <h2 class="mb-0">0</h2>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0">Recent Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Total Purchases</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers ?? [] as $customer)
                                    <tr>
                                        <td><strong>{{ $customer->name }}</strong></td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>{{ $customer->email ?? 'N/A' }}</td>
                                        <td><span class="badge badge-gold">{{ $customer->purchase_count }} orders</span></td>
                                        <td>
                                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No customers yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0">Recent Interactions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($interactions ?? [] as $interaction)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $interaction->type }}</h6>
                                        <p class="small text-muted mb-0">{{ $interaction->customer->name ?? 'Unknown' }}</p>
                                    </div>
                                    <small class="text-muted">{{ $interaction->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3">No interactions</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
