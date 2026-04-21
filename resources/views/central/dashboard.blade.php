@extends('layouts.central')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">SaaS Platform Administration</h1>
            <p class="text-muted">Manage your tenants, plans, and platform-wide metrics.</p>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Total Tenants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tenants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_tenants }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Tenants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $active_tenants }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Plans -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Subscription Plans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_plans }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Global Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_users }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Insights -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 bg-primary text-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">Estimated MRR</div>
                            <div class="h3 mb-0 font-weight-bold text-white">₹{{ number_format($estimated_mrr) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Tenant Signups</h6>
                    <a href="{{ route('central.tenants.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subdomain</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_tenants as $tenant)
                                <tr>
                                    <td>{{ $tenant->name }}</td>
                                    <td>{{ $tenant->subdomain }}</td>
                                    <td>{{ $tenant->plan->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($tenant->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Plan Distribution</h6>
                </div>
                <div class="card-body">
                    @foreach($plan_distribution as $plan)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small font-weight-bold">{{ $plan->name }} <span class="text-muted">({{ $plan->tenants_count }})</span></span>
                            <span class="small font-weight-bold">{{ $total_tenants > 0 ? round(($plan->tenants_count / $total_tenants) * 100) : 0 }}%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $total_tenants > 0 ? ($plan->tenants_count / $total_tenants) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('central.tenants.index') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus me-2"></i> Manage Tenants
                        </a>
                        <a href="{{ route('central.plans.index') }}" class="btn btn-outline-info text-start">
                            <i class="fas fa-edit me-2"></i> Configure Plans
                        </a>
                        <button class="btn btn-outline-secondary text-start">
                            <i class="fas fa-cog me-2"></i> Platform Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
