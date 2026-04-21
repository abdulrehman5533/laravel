@extends('layouts.app')

@section('title', 'Threat Intelligence')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Threat Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Failed Attempts</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.dashboard') }}" class="btn btn-light shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Banner for High Volume (Optional Logic could be added in controller) -->
    <div class="alert bg-premium-dark text-gold border-0 shadow-sm rounded-20 mb-5 d-flex align-items-center p-4">
        <div class="p-3 bg-gold text-dark rounded-circle me-4">
            <i class="fas fa-shield-virus fs-4"></i>
        </div>
        <div>
            <h5 class="fw-800 mb-1">Access Barrier Monitoring</h5>
            <p class="mb-0 opacity-75">Monitoring unauthorized access attempts and potential credential stuffing activities in real-time.</p>
        </div>
    </div>

    <!-- Failed Attempts Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Anomaly Registry</h5>
                    <p class="text-muted small mb-0">Registry of blocked and failed authentication signatures</p>
                </div>
                <div class="badge bg-danger-soft text-danger px-3 py-2 rounded-pill fw-700">
                    <i class="fas fa-user-xmark me-1"></i> Restricted Access
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Identity/Subject</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Vector IP</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Client Signature</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Violation Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="bg-danger-hover">
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-danger-soft text-danger rounded-12 me-3">
                                        <i class="fas fa-user-secret"></i>
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark">{{ $log->user?->name ?? 'External Subject' }}</div>
                                        <div class="text-danger smaller fw-700">{{ $log->subject }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><small class="font-monospace text-muted bg-light px-2 py-1 rounded-8 border">{{ $log->ip_address }}</small></td>
                            <td>
                                <div class="text-muted smaller fw-600" title="{{ $log->user_agent }}">
                                    <i class="fas fa-microchip me-1 opacity-50"></i> {{ Str::limit($log->user_agent, 50) }}
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="d-block fw-700 text-danger small">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-muted smaller fw-600">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-shield-check fa-4x text-success opacity-20 mb-4"></i>
                                    <h4 class="fw-800 text-dark">Clear Perimeter</h4>
                                    <p class="text-muted">No unauthorized access attempts detected in this cycle.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top p-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<style>
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    
    .smaller { font-size: 0.75rem; }
    
    .icon-box {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .bg-danger-hover:hover {
        background-color: rgba(239, 68, 68, 0.02);
    }
</style>
@endsection
