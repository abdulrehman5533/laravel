@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800 mb-0">Enterprise Admin Dashboard</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <!-- Employees Stat Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_employees'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approvals</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_approvals'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Documents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_documents'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Keys Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active API Keys</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_api_keys'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Approvals Table -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Approval Workflows</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Workflow</th>
                                    <th>Approver</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_approvals'] as $approval)
                                <tr>
                                    <td>{{ $approval->workflow->name ?? 'N/A' }}</td>
                                    <td>{{ $approval->approver->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge {{ $approval->status == 'approved' ? 'bg-success' : ($approval->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                            {{ ucfirst($approval->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $approval->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent approvals found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.workflows.index') }}" class="btn btn-primary text-start">
                            <i class="fas fa-sitemap me-2"></i> Manage Workflows
                        </a>
                        <a href="{{ route('admin.dms.index') }}" class="btn btn-info text-white text-start">
                            <i class="fas fa-folder-open me-2"></i> Document Center
                        </a>
                        <a href="{{ route('admin.api.index') }}" class="btn btn-secondary text-start">
                            <i class="fas fa-code me-2"></i> API Management
                        </a>
                        <a href="{{ route('admin.multi-company.index') }}" class="btn btn-success text-start">
                            <i class="fas fa-building me-2"></i> Company Settings
                        </a>
                        <a href="{{ route('admin.backup.index') }}" class="btn btn-warning text-start">
                            <i class="fas fa-database me-2"></i> System Backup
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
