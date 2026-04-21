@extends('layouts.app')

@section('title', 'Identity Intelligence')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">{{ $user->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.users.index') }}" class="text-decoration-none text-muted">Users</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Profile Intelligence</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                @can('update', $user)
                <a href="{{ route('security-manager.users.edit', $user) }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-user-pen me-2"></i>Edit Identity
                </a>
                @endcan
                <a href="{{ route('security-manager.users.index') }}" class="btn btn-light shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2"></i>Back to Registry
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Identity Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden h-100">
                <div class="card-body p-4 text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-20 shadow-lg" style="width: 120px; height: 120px; object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 p-2 bg-{{ $user->user_status === 'active' ? 'success' : 'danger' }} border border-4 border-white rounded-circle shadow-sm"></span>
                    </div>
                    <h4 class="fw-800 text-dark mb-1">{{ $user->name }}</h4>
                    <p class="text-muted fw-600 mb-4">{{ $user->email }}</p>
                    
                    <div class="d-grid gap-2">
                        <div class="p-3 bg-light rounded-15 border">
                            <div class="small text-muted fw-700 text-uppercase mb-1">Functional Designation</div>
                            <span class="badge bg-premium-dark text-gold rounded-pill px-3 py-2 fw-700 fs-6">
                                {{ $user->role?->name ?? 'Unassigned Entity' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 p-4">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h5 fw-800 text-dark mb-0">{{ $user->sessions()->active()->count() }}</div>
                            <div class="smaller text-muted fw-700 uppercase">Live Channels</div>
                        </div>
                        <div class="col-6">
                            <div class="h5 fw-800 text-dark mb-0">{{ $user->auditLogs()->count() }}</div>
                            <div class="smaller text-muted fw-700 uppercase">Event Logs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Parameters -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-primary-soft text-primary rounded-10 me-3">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Security Clearance & State</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-15 border h-100">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block">Account Status</label>
                                @if($user->user_status === 'active')
                                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700">
                                        <i class="fas fa-check-circle me-1"></i> Active / Operational
                                    </span>
                                @elseif($user->user_status === 'suspended')
                                    <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-700">
                                        <i class="fas fa-pause-circle me-1"></i> Suspended Access
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-700">
                                        <i class="fas fa-lock me-1"></i> Locked / Restricted
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-15 border h-100">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block">Last Authentication</label>
                                <span class="fw-800 text-dark">{{ $user->last_login_at?->format('M d, Y H:i') ?? 'No History' }}</span>
                                <div class="text-muted smaller fw-600 mt-1">{{ $user->last_login_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-15 border h-100">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block">Failed Attempts</label>
                                <span class="fw-800 text-{{ $user->failed_login_attempts > 0 ? 'danger' : 'success' }}">
                                    {{ $user->failed_login_attempts }} attempts
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-15 border h-100">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block">Identity Since</label>
                                <span class="fw-800 text-dark">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Intelligence (Audit) -->
        <div class="col-12">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-info-soft text-info rounded-10 me-3">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Recent Identity Activity</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Event</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted">IP Vector</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Details</th>
                                    <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $log)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-700">
                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td><small class="font-monospace text-muted">{{ $log->ip_address }}</small></td>
                                    <td><span class="small fw-600 text-dark">{{ Str::limit($log->description, 60) }}</span></td>
                                    <td class="pe-4 text-end">
                                        <span class="text-muted small fw-600">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent activity detected</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    
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
    .smaller { font-size: 0.75rem; }
    .uppercase { text-transform: uppercase; }
</style>
@endsection
