@extends('layouts.app')

@section('title', 'User Governance')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Account Governance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                @can('create', App\Models\User::class)
                <a href="{{ route('security-manager.users.create') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-plus me-2"></i>Provision New User
                </a>
                @endcan
                <a href="{{ route('security-manager.dashboard') }}" class="btn btn-light shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-primary-soft text-primary rounded-10 me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-600">Total</div>
                        <div class="h5 fw-800 mb-0">{{ $users->total() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-success-soft text-success rounded-10 me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-600">Active</div>
                        <div class="h5 fw-800 mb-0">{{ $users->where('user_status', 'active')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-warning-soft text-warning rounded-10 me-3">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-600">Suspended</div>
                        <div class="h5 fw-800 mb-0">{{ $users->where('user_status', 'suspended')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-danger-soft text-danger rounded-10 me-3">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-600">Locked</div>
                        <div class="h5 fw-800 mb-0">{{ $users->where('user_status', 'locked')->count() }}</div>
                    </div>
                </div>
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

    <!-- User Registry Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Identity Registry</h5>
                    <p class="text-muted small mb-0">Consolidated view of all system identities</p>
                </div>
                <div class="position-relative" style="width: 300px;">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control border-light bg-light rounded-pill ps-5 shadow-none" placeholder="Search Identity..." id="userSearch">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Identity</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Functional Role</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Risk Profile</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Security State</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Last Activity</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Sessions</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-box me-3">
                                        <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-12 shadow-sm" width="45" height="45" style="object-fit: cover;">
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">{{ $user->name }}</div>
                                        <div class="text-muted smaller fw-600">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role)
                                <span class="badge bg-premium-dark text-gold rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-user-tag me-1 small"></i> {{ $user->role->name }}
                                </span>
                                @else
                                <span class="badge bg-light text-muted rounded-pill px-3 py-2 fw-700">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $riskScore = 0;
                                    if ($user->failed_login_attempts > 0) $riskScore += 30;
                                    if ($user->failed_login_attempts > 3) $riskScore += 40;
                                    if ($user->user_status !== 'active') $riskScore += 20;
                                    if (!$user->last_login_at || $user->last_login_at->diffInDays() > 30) $riskScore += 10;
                                    
                                    $riskLevel = 'Low';
                                    $riskClass = 'success';
                                    if ($riskScore > 70) { $riskLevel = 'Critical'; $riskClass = 'danger'; }
                                    elseif ($riskScore > 40) { $riskLevel = 'Elevated'; $riskClass = 'warning'; }
                                @endphp
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 bg-light rounded-pill me-2" style="height: 4px; max-width: 50px;">
                                        <div class="progress-bar bg-{{ $riskClass }}" style="width: {{ $riskScore }}%"></div>
                                    </div>
                                    <span class="fw-700 smaller text-{{ $riskClass }}">{{ $riskLevel }}</span>
                                </div>
                            </td>
                            <td>
                                @if($user->user_status === 'active')
                                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700 d-inline-flex align-items-center">
                                        <i class="fas fa-circle me-1 small animate-pulse"></i> Operational
                                    </span>
                                @elseif($user->user_status === 'suspended')
                                    <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-700">
                                        <i class="fas fa-pause-circle me-1 small"></i> Suspended
                                    </span>
                                @elseif($user->user_status === 'locked')
                                    <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-700">
                                        <i class="fas fa-lock me-1 small"></i> Compromised
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-3 py-2 fw-700">{{ ucfirst($user->user_status) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark small fw-600">{{ $user->last_login_at?->diffForHumans() ?? 'No Records' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-soft text-info rounded-8 px-3 py-2 fw-800 fs-6">
                                    {{ $user->sessions()->active()->count() }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-light rounded-10 shadow-none border-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-15">
                                        <li class="px-2 py-1"><a class="dropdown-item rounded-10 py-2 fw-600" href="{{ route('security-manager.users.show', $user) }}">
                                            <i class="fas fa-id-badge me-2 text-primary"></i> Profile Intelligence
                                        </a></li>
                                        @can('update', $user)
                                        <li class="px-2 py-1"><a class="dropdown-item rounded-10 py-2 fw-600" href="{{ route('security-manager.users.edit', $user) }}">
                                            <i class="fas fa-pen-to-square me-2 text-warning"></i> Edit Profile
                                        </a></li>
                                        @endcan
                                        <li class="px-2 py-1"><a class="dropdown-item rounded-10 py-2 fw-600" href="{{ route('security-manager.sessions.user-sessions', $user) }}">
                                            <i class="fas fa-network-wired me-2 text-info"></i> Active Channels
                                        </a></li>
                                        @can('update', $user)
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <!-- Reset Password Option -->
                                        <li class="px-2 py-1">
                                            <button type="button" class="dropdown-item rounded-10 py-2 fw-600 text-primary" data-bs-toggle="modal" data-bs-target="#resetPasswordModal-{{ $user->id }}">
                                                <i class="fas fa-key me-2"></i> Reset Password
                                            </button>
                                        </li>
                                        @if($user->user_status === 'active')
                                        <li class="px-2 py-1">
                                            <form method="POST" action="{{ route('security-manager.users.suspend', $user) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item rounded-10 py-2 fw-600 text-danger" onclick="return confirm('Initiate suspension protocol for this identity?')">
                                                    <i class="fas fa-ban me-2"></i> Suspend Access
                                                </button>
                                            </form>
                                        </li>
                                        @else
                                        <li class="px-2 py-1">
                                            <form method="POST" action="{{ route('security-manager.users.activate', $user) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item rounded-10 py-2 fw-600 text-success">
                                                    <i class="fas fa-shield-check me-2"></i> Restore Access
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @endcan
                                                <!-- Reset Password Modal -->
                                                <div class="modal fade" id="resetPasswordModal-{{ $user->id }}" tabindex="-1" aria-labelledby="resetPasswordModalLabel-{{ $user->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('security-manager.users.reset-password', $user) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title fw-700" id="resetPasswordModalLabel-{{ $user->id }}">Reset Password for {{ $user->name }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="new_password_{{ $user->id }}" class="form-label fw-600">New Password</label>
                                                                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password_{{ $user->id }}" name="new_password" required minlength="8" autocomplete="new-password">
                                                                        <div class="form-text">Password must be at least 8 characters.</div>
                                                                        @error('new_password')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="confirm_password_{{ $user->id }}" class="form-label fw-600">Confirm Password</label>
                                                                        <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" id="confirm_password_{{ $user->id }}" name="new_password_confirmation" required minlength="8" autocomplete="new-password">
                                                                        @error('new_password_confirmation')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary fw-700">Reset Password</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                    </tbody>
                                    @if(($errors->has('new_password') || $errors->has('new_password_confirmation')) && old('user_id'))
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                var modalId = 'resetPasswordModal-' + @json(old('user_id'));
                                                var modalEl = document.getElementById(modalId);
                                                if (modalEl) {
                                                    var modal = new bootstrap.Modal(modalEl);
                                                    modal.show();
                                                }
                                            });
                                        </script>
                                    @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-users-slash fa-4x text-light mb-4 opacity-50"></i>
                                    <h4 class="fw-800 text-dark">No Identity Records</h4>
                                    <p class="text-muted">No users matching your criteria were found in the registry.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top p-4">
            {{ $users->links() }}
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    
    .rounded-10 { border-radius: 10px !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-15 { border-radius: 15px !important; }
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

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
    
    .stat-card {
        background: white;
        transition: transform 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-3px); }
</style>
@endsection
