@extends('layouts.central')

@section('title', 'Manage Tenants')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tenants</h1>
            <p class="text-muted">Total registered businesses on the platform.</p>
        </div>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New Tenant
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tenantsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Subdomain</th>
                            <th>Domain</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>
                                <strong>{{ $tenant->name }}</strong>
                            </td>
                            <td><code>{{ $tenant->subdomain }}</code></td>
                            <td>{{ $tenant->domain ?: '-' }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $tenant->plan->name ?? 'None' }}
                                </span>
                            </td>
                            <td>
                                @if($tenant->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="btn btn-outline-info" title="View Settings"><i class="fas fa-cog"></i></a>
                                    <button class="btn btn-outline-danger" title="Suspend"><i class="fas fa-ban"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
