@extends('layouts.app')

@section('title', 'Session Intelligence History')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Connection History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.users.index') }}" class="text-decoration-none text-muted">User Registry</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.users.show', $user) }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Intelligence
            </a>
        </div>
    </div>

    <!-- Stats row for specific user -->
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-history text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total Lifecycle Events</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">{{ $sessions->total() }}</h2>
                <span class="text-muted small fw-600">Archived connection tickets</span>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Authentication Archival</h5>
                    <p class="text-muted small mb-0">Historical log of authentication events for {{ $user->name }}</p>
                </div>
                <div class="badge bg-premium-dark text-gold px-3 py-2 rounded-pill fw-700 shadow-sm">
                    <i class="fas fa-database me-1"></i> Audit-Ready
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Established</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Vector & Device</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Duration / Active</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Terminal State</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="fw-800 text-dark">{{ $session->login_at->format('M d, Y') }}</div>
                                <div class="text-muted smaller fw-600">{{ $session->login_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-700 small"><i class="fas fa-desktop me-1 text-muted opacity-50"></i>{{ $session->device_type ?? 'Browser Environment' }}</span>
                                    <span class="font-monospace text-primary smaller fw-600">{{ $session->ip_address }}</span>
                                </div>
                            </td>
                            <td>
                                @if($session->terminated_at)
                                    <div class="badge bg-light text-muted px-3 py-2 rounded-pill fw-700">
                                        <i class="fas fa-hourglass-end me-1"></i>
                                        {{ $session->login_at->diffInMinutes($session->terminated_at) }} Minutes
                                    </div>
                                @else
                                    <div class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-700">
                                        <i class="fas fa-circle me-1 animate-pulse"></i>
                                        Active Channel
                                    </div>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                @if($session->terminated_at)
                                    <span class="badge bg-danger-soft text-danger px-3 py-2 rounded-pill fw-700 smaller">
                                        Terminated
                                    </span>
                                @else
                                    <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-700 smaller">
                                        Live
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-clock-rotate-left fa-4x text-light mb-4"></i>
                                    <h4 class="fw-800 text-dark">No Historical Data</h4>
                                    <p class="text-muted">No archived session tickets found for this subject.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top p-4">
            {{ $sessions->links() }}
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    
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
        position: relative;
    }
    
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
    .uppercase { text-transform: uppercase; }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endsection
