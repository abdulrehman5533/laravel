@extends('layouts.app')

@section('title', 'Session Control')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Session Oversight</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Active Sessions</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.sessions.control.index') }}" class="btn btn-primary shadow-sm px-4 py-2 border-0 rounded-12 fw-600 me-2">
                <i class="fas fa-cog me-2"></i>Policy Settings
            </a>
            <a href="{{ route('security-manager.dashboard') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Premium Metrics Row -->
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden group">
                <div class="card-bg-icon">
                    <i class="fas fa-wifi text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total Active Sessions</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $sessionStats['total_active'] }}</h2>
                <span class="text-primary small fw-600"><i class="fas fa-circle me-1 animate-pulse"></i>Live Connections</span>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-users text-success opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-success-soft text-success me-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span class="text-muted fw-600 small">Unique Users Online</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $sessionStats['total_users_online'] }}</h2>
                <span class="text-muted small fw-600">Authenticated subjects</span>
            </div>
        </div>

        <div class="col-xl-4 col-md-12 mb-4">
            <div class="stat-card border-0 overflow-hidden bg-premium-dark text-white">
                <div class="card-bg-icon">
                    <i class="fas fa-user-slash text-gold opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-gold text-dark me-3">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <span class="text-white-50 fw-600 small">Terminated (24h)</span>
                </div>
                <h2 class="fw-800 mb-2 text-white">{{ $sessionStats['total_terminated_today'] }}</h2>
                <span class="text-white-50 small fw-600">Manual & auto expiries</span>
            </div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Connection Registry</h5>
                    <p class="text-muted small mb-0">Active authentication tickets across the perimeter</p>
                </div>
                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-700">
                    <i class="fas fa-fingerprint me-1"></i> Real-time Oversight
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Subject Identity</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Vector & Signature</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Last Signal</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Session Age</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Countermeasures</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeSessions as $session)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($session->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">{{ $session->user->name }}</div>
                                        <div class="badge bg-light text-muted fw-600 smaller">{{ $session->user->role?->name ?? 'No Role' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $agent = $session->user_agent;
                                    $browser = 'Unknown';
                                    if (str_contains($agent, 'Chrome')) $browser = 'Chrome';
                                    elseif (str_contains($agent, 'Firefox')) $browser = 'Firefox';
                                    elseif (str_contains($agent, 'Safari')) $browser = 'Safari';
                                    elseif (str_contains($agent, 'Edge')) $browser = 'Edge';
                                    
                                    $os = 'Unknown';
                                    if (str_contains($agent, 'Windows')) $os = 'Windows';
                                    elseif (str_contains($agent, 'Macintosh')) $os = 'macOS';
                                    elseif (str_contains($agent, 'Linux')) $os = 'Linux';
                                @endphp
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-700 small"><i class="fas fa-desktop me-1 text-muted opacity-50"></i>{{ $os }} / {{ $browser }}</span>
                                    <span class="font-monospace text-primary smaller fw-600">{{ $session->ip_address }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-muted smaller fw-600">
                                    <i class="fas fa-clock-rotate-left me-1 opacity-50"></i>
                                    {{ $session->last_activity_at->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-muted smaller fw-600">
                                    <i class="fas fa-hourglass-start me-1 opacity-50"></i>
                                    {{ $session->login_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                @can('update', $session->user)
                                <form method="POST" action="{{ route('security-manager.sessions.terminate', $session) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-soft btn-sm rounded-8 fw-700" onclick="return confirm('Immediately invalidate this authentication session?')">
                                        <i class="fas fa-hand-slash me-1"></i> Terminate
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-light text-muted px-3 py-2 rounded-pill">Restricted</span>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-shield-halved fa-4x text-light mb-4"></i>
                                    <h4 class="fw-800 text-dark">No Active Signals</h4>
                                    <p class="text-muted">The perimeter is currently silent. No active sessions detected.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top p-4">
            {{ $activeSessions->links() }}
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .btn-danger-soft { 
        background-color: rgba(239, 68, 68, 0.1); 
        color: #ef4444;
        border: none;
    }
    .btn-danger-soft:hover {
        background-color: #ef4444;
        color: white;
    }
    
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .stat-card:hover { transform: translateY(-5px); }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .card-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        transform: rotate(-15deg);
    }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    
    .smaller { font-size: 0.75rem; }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endsection
