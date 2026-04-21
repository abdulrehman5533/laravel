@extends('layouts.central')

@section('title', 'Subscription Plans')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Subscription Plans</h1>
            <p class="text-muted">Define pricing and feature limits for your SaaS.</p>
        </div>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Create New Plan
        </button>
    </div>

    <div class="row g-4">
        @foreach($plans as $plan)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title fw-bold mb-0">{{ $plan->name }}</h5>
                        <span class="badge bg-primary rounded-pill">₹{{ number_format($plan->price) }}/{{ $plan->billing_cycle }}</span>
                    </div>
                    <p class="text-muted small mb-4">{{ $plan->description }}</p>
                    
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-users me-2 text-primary" style="width: 20px;"></i>
                            <span>{{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }} Users</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-code-branch me-2 text-primary" style="width: 20px;"></i>
                            <span>{{ $plan->max_branches == -1 ? 'Unlimited' : $plan->max_branches }} Branches</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-box me-2 text-primary" style="width: 20px;"></i>
                            <span>{{ $plan->max_products == -1 ? 'Unlimited' : $plan->max_products }} Products</span>
                        </li>
                    </ul>

                    <div class="mb-4">
                        <h6 class="fw-bold small text-uppercase text-muted">Key Features</h6>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($plan->features as $feature)
                                <span class="badge bg-light text-dark border">{{ $feature }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-end pb-3">
                    <button class="btn btn-sm btn-outline-secondary me-2">Edit</button>
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
