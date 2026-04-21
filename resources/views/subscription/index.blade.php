@extends('layouts.app')

@section('title', 'Subscription & Usage')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4 text-gray-800">Subscription & Usage</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Current Usage Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Current Plan: <span class="text-primary">{{ $tenant->plan->name }}</span></h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Users Usage -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Users</h6>
                                    <span class="badge bg-light text-dark">
                                        {{ $tenant->getCurrentUsage('users') }} / {{ $tenant->plan->max_users == -1 ? '∞' : $tenant->plan->max_users }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    @php
                                        $userUsage = $tenant->getCurrentUsage('users');
                                        $userLimit = $tenant->plan->max_users;
                                        $userPercent = $userLimit == -1 ? 0 : ($userUsage / $userLimit) * 100;
                                    @endphp
                                    <div class="progress-bar {{ $userPercent > 90 ? 'bg-danger' : ($userPercent > 70 ? 'bg-warning' : 'bg-success') }}" 
                                         role="progressbar" style="width: {{ $userLimit == -1 ? 10 : $userPercent }}%" 
                                         aria-valuenow="{{ $userPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Branches Usage -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Branches</h6>
                                    <span class="badge bg-light text-dark">
                                        {{ $tenant->getCurrentUsage('branches') }} / {{ $tenant->plan->max_branches == -1 ? '∞' : $tenant->plan->max_branches }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    @php
                                        $branchUsage = $tenant->getCurrentUsage('branches');
                                        $branchLimit = $tenant->plan->max_branches;
                                        $branchPercent = $branchLimit == -1 ? 0 : ($branchUsage / $branchLimit) * 100;
                                    @endphp
                                    <div class="progress-bar {{ $branchPercent > 90 ? 'bg-danger' : ($branchPercent > 70 ? 'bg-warning' : 'bg-success') }}" 
                                         role="progressbar" style="width: {{ $branchLimit == -1 ? 10 : $branchPercent }}%" 
                                         aria-valuenow="{{ $branchPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Products Usage -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Products</h6>
                                    <span class="badge bg-light text-dark">
                                        {{ $tenant->getCurrentUsage('products') }} / {{ $tenant->plan->max_products == -1 ? '∞' : $tenant->plan->max_products }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    @php
                                        $prodUsage = $tenant->getCurrentUsage('products');
                                        $prodLimit = $tenant->plan->max_products;
                                        $prodPercent = $prodLimit == -1 ? 0 : ($prodUsage / $prodLimit) * 100;
                                    @endphp
                                    <div class="progress-bar {{ $prodPercent > 90 ? 'bg-danger' : ($prodPercent > 70 ? 'bg-warning' : 'bg-success') }}" 
                                         role="progressbar" style="width: {{ $prodLimit == -1 ? 10 : $prodPercent }}%" 
                                         aria-valuenow="{{ $prodPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Selection -->
    <div class="row g-4">
        @foreach($plans as $plan)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm border-0 {{ $tenant->plan_id == $plan->id ? 'border-primary border-top border-4' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">{{ $plan->name }}</h5>
                        @if($tenant->plan_id == $plan->id)
                            <span class="badge bg-primary rounded-pill">Current Plan</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <span class="h2 fw-bold text-dark">₹{{ number_format($plan->price) }}</span>
                        <span class="text-muted">/{{ $plan->billing_cycle }}</span>
                    </div>
                    <p class="text-muted small mb-4">{{ $plan->description }}</p>
                    
                    <ul class="list-unstyled mb-4 small">
                        @foreach($plan->features as $feature)
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 pb-4">
                    @if($tenant->plan_id != $plan->id)
                        <form action="{{ route('subscription.upgrade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="btn btn-outline-primary w-100 fw-bold">Upgrade to {{ $plan->name }}</button>
                        </form>
                    @else
                        <button class="btn btn-light w-100 fw-bold disabled" disabled>Your Active Plan</button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
