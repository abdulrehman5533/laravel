@extends('layouts.app')

@section('title', 'Role Intelligence')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">{{ $role->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.roles.index') }}" class="text-decoration-none text-muted">Role Registry</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Role Intelligence</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('security-manager.roles.edit', $role) }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-edit me-2"></i>Edit Specification
                </a>
                <a href="{{ route('security-manager.roles.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Registry
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Role Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden h-100">
                <div class="card-body p-4 text-center">
                    <div class="mb-4">
                        <div class="stat-icon bg-premium-dark text-gold mx-auto shadow-lg" style="width: 80px; height: 80px; font-size: 2rem; border-radius: 20px;">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>
                    <h4 class="fw-800 text-dark mb-1">{{ $role->name }}</h4>
                    <div class="badge bg-light text-muted fw-700 rounded-pill px-3 py-2 mb-4">{{ $role->slug }}</div>
                    
                    <div class="p-3 bg-light rounded-15 border mb-4">
                        <div class="small text-muted fw-700 text-uppercase mb-1">Architectural Status</div>
                        @if($role->is_active)
                            <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700">
                                <i class="fas fa-check-circle me-1"></i> Operational (Active)
                            </span>
                        @else
                            <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-700">
                                <i class="fas fa-times-circle me-1"></i> Decommissioned (Inactive)
                            </span>
                        @endif
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 bg-primary-soft rounded-12">
                                <div class="h4 fw-800 text-dark mb-0">{{ $role->users()->count() }}</div>
                                <div class="smaller text-muted fw-700 uppercase">Subjects</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-info-soft rounded-12">
                                <div class="h4 fw-800 text-dark mb-0">{{ $role->permissions()->count() }}</div>
                                <div class="smaller text-muted fw-700 uppercase">Capabilities</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Particulars -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-primary-soft text-primary rounded-10 me-3">
                            <i class="fas fa-circle-info"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Operational Particulars</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-4">
                        <label class="form-label fw-700 small text-uppercase text-muted">Scope Definition</label>
                        <div class="p-3 bg-light rounded-12 border">
                            <p class="mb-0 fw-600 text-dark">{{ $role->description ?? 'No formal description defined for this architectural role.' }}</p>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-12 border h-100">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block">Session Lifecycle (TTL)</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <span class="fw-800 text-dark">{{ $role->session_timeout_minutes }} Minutes</span>
                                </div>
                                <div class="text-muted smaller fw-600 mt-1">Automatic revocation after inactivity</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-12 border h-100">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block">Last Modification</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    <span class="fw-800 text-dark">{{ $role->updated_at->format('M d, Y') }}</span>
                                </div>
                                <div class="text-muted smaller fw-600 mt-1">{{ $role->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Binding Capabilities -->
        <div class="col-12">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info-soft text-info rounded-10 me-3">
                                <i class="fas fa-table-list"></i>
                            </div>
                            <h5 class="mb-0 fw-800">Bound Capabilities Matrix</h5>
                        </div>
                        <a href="{{ route('security-manager.roles.permission-matrix') }}" class="btn btn-primary-soft btn-sm rounded-8 fw-700">
                            Configure Matrix
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    @php
                        $groupedPermissions = $role->permissions->groupBy('resource');
                    @endphp
                    
                    <div class="row g-4">
                        @forelse($groupedPermissions as $resource => $permissions)
                        <div class="col-md-6 col-xl-4">
                            <div class="p-3 border rounded-20 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-premium-dark text-gold rounded-pill px-3 py-2 fw-700 smaller text-uppercase tracking-wider">
                                        {{ $resource }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($permissions as $permission)
                                    <span class="badge bg-light text-dark border fw-600 rounded-8 px-2 py-1" style="font-size: 0.75rem;">
                                        {{ $permission->name }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-triangle-exclamation fa-3x mb-3 opacity-20"></i>
                                <p class="fw-600">No atomic capabilities bound to this role definition.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    
    .rounded-12 { border-radius: 12px !important; }
    .rounded-15 { border-radius: 15px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    
    .smaller { font-size: 0.75rem; }
    .uppercase { text-transform: uppercase; }
    
    .btn-primary-soft {
        background-color: rgba(26, 26, 26, 0.05);
        color: #1a1a1a;
        border: none;
    }
    .btn-primary-soft:hover {
        background-color: #1a1a1a;
        color: white;
    }
</style>
@endsection
