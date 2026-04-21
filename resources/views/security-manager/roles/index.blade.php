@extends('layouts.app')

@section('title', 'Role Architecture')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Role Architecture</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('security-manager.roles.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>Define New Role
                </a>
                <a href="{{ route('security-manager.dashboard') }}" class="btn btn-light shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-12 mb-4 d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-3 fs-4"></i>
        <div class="fw-600">{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Roles Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Privilege Tiers</h5>
                    <p class="text-muted small mb-0">Total of {{ $roles->total() }} authorization levels defined</p>
                </div>
                <a href="{{ route('security-manager.roles.permission-matrix') }}" class="btn btn-primary-soft text-primary px-3 py-2 rounded-pill fw-700">
                    <i class="fas fa-th me-1"></i> Permission Matrix
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Role Designation</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">System Identifier</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Population</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Authority level</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Persistence</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Operational State</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-premium-dark text-gold rounded-12 d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 35px; height: 35px;">
                                        <i class="fas fa-user-shield smaller"></i>
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">{{ $role->name }}</div>
                                        <div class="text-muted smaller fw-600">{{ Str::limit($role->description, 40) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-muted border px-2 py-1 fw-700">{{ $role->slug }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="fw-800 text-dark me-2">{{ $role->users_count }}</span>
                                    <span class="text-muted smaller fw-600">Entities</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px; background: #f0f0f0;">
                                        @php $pCount = $role->permissions()->count(); @endphp
                                        <div class="progress-bar bg-primary" style="width: {{ min(($pCount / 50) * 100, 100) }}%"></div>
                                    </div>
                                    <span class="fw-700 text-dark small">{{ $pCount }} Caps</span>
                                </div>
                            </td>
                            <td><span class="text-muted small fw-600"><i class="fas fa-clock me-1"></i> {{ $role->session_timeout_minutes }}m Timeout</span></td>
                            <td>
                                @if($role->is_active)
                                <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-check-circle me-1 small"></i> Validated
                                </span>
                                @else
                                <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-times-circle me-1 small"></i> Decommissioned
                                </span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-none">
                                    <a href="{{ route('security-manager.roles.show', $role) }}" class="btn btn-icon btn-light rounded-10 me-1">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <a href="{{ route('security-manager.roles.edit', $role) }}" class="btn btn-icon btn-light rounded-10">
                                        <i class="fas fa-pen-to-square text-warning"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-user-lock fa-4x text-light mb-4"></i>
                                    <h4 class="fw-800 text-dark">No Roles Defined</h4>
                                    <p class="text-muted">Initiate role provisioning to start managing access control.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top p-4">
            {{ $roles->links() }}
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    
    .rounded-10 { border-radius: 10px !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .smaller { font-size: 0.75rem; }

    .btn-icon {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
