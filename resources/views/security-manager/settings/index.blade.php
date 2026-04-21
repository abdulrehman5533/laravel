@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Global Security Policy</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Security Settings</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.dashboard') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Banner for Static Config -->
    <div class="alert bg-premium-dark text-gold border-0 shadow-sm rounded-20 mb-5 d-flex align-items-center p-4">
        <div class="p-3 bg-gold text-dark rounded-circle me-4 shadow-gold">
            <i class="fas fa-shield-halved fs-4"></i>
        </div>
        <div>
            <h5 class="fw-800 mb-1">Environmental Configuration Active</h5>
            <p class="mb-0 opacity-75">Global security parameters are currently orchestrated via environment variables and system-level defaults for maximum integrity.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('security-manager.settings.update') }}">
        @csrf
        <div class="row g-4 mb-5">
            <!-- Password Policy Card -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-premium rounded-20 h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning-soft text-warning rounded-10 me-3">
                                <i class="fas fa-key fs-5"></i>
                            </div>
                            <h5 class="mb-0 fw-800 text-dark">Password Complexity</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase text-muted mb-2">Minimum Length Threshold</label>
                            <input type="number" name="password_min_length" class="form-control py-3 rounded-12 border shadow-none fw-700 text-dark" value="{{ $settings['password_min_length'] }}">
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-12 d-flex align-items-center bg-light opacity-75">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-600 text-muted small">Mixed Case (A-z)</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-12 d-flex align-items-center bg-light opacity-75">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-600 text-muted small">Numeric Values (0-9)</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-12 d-flex align-items-center bg-light opacity-75">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-600 text-muted small">Special Symbols</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-12 d-flex align-items-center bg-light opacity-50">
                                    <i class="fas fa-circle-info text-muted me-2"></i>
                                    <span class="fw-600 text-muted small">Enforced by Core</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session & Connection Card -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-premium rounded-20 h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info-soft text-info rounded-10 me-3">
                                <i class="fas fa-clock fs-5"></i>
                            </div>
                            <h5 class="mb-0 fw-800 text-dark">Session Governance</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase text-muted mb-2">Default Session Lifetime (Minutes)</label>
                            <input type="number" name="session_lifetime" class="form-control py-3 rounded-12 border shadow-none fw-700 text-dark" value="{{ $settings['session_lifetime'] }}">
                        </div>
                        <div class="list-group list-group-flush border rounded-12 overflow-hidden mb-4">
                            <div class="list-group-item p-3 d-flex align-items-center justify-content-between bg-white">
                                <div>
                                    <h6 class="mb-0 fw-700 text-dark">Single Session Enforcement</h6>
                                    <p class="text-muted smaller mb-0 fw-600">Prevents concurrent multi-device access</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="multi_session" value="1" {{ !$settings['multi_session'] ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('security-manager.roles.index') }}" class="btn btn-info-soft w-100 py-3 rounded-12 fw-700">
                            <i class="fas fa-user-gear me-2"></i>Configure Role-based Expiries
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bruteforce & Locking Card -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-premium rounded-20 h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-danger-soft text-danger rounded-10 me-3">
                                <i class="fas fa-user-lock fs-5"></i>
                            </div>
                            <h5 class="mb-0 fw-800 text-dark">Defensive Locking</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-15 border">
                                    <label class="small fw-700 text-uppercase text-muted mb-2">Failure Limit</label>
                                    <input type="number" name="max_failed_attempts" class="form-control border-0 bg-transparent h2 fw-800 text-dark mb-0 p-0 shadow-none" value="{{ $settings['max_failed_attempts'] }}">
                                    <div class="text-danger smaller fw-700">Attempts</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-15 border">
                                    <label class="small fw-700 text-uppercase text-muted mb-2">Penalty Duration</label>
                                    <input type="number" name="lockout_duration" class="form-control border-0 bg-transparent h2 fw-800 text-dark mb-0 p-0 shadow-none" value="{{ $settings['lockout_duration'] }}">
                                    <div class="text-danger smaller fw-700">Minutes</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-danger-soft rounded-12 d-flex align-items-center">
                            <i class="fas fa-shield-virus text-danger me-3 fs-4"></i>
                            <span class="text-danger small fw-600 lh-sm">Accounts are automatically isolated after repeated authentication failures to prevent credential stuffing.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compliance & Archival Card -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-premium rounded-20 h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-success-soft text-success rounded-10 me-3">
                                <i class="fas fa-file-contract fs-5"></i>
                            </div>
                            <h5 class="mb-0 fw-800 text-dark">Data Governance</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase text-muted mb-2">Audit Log Retention Lifecycle (Days)</label>
                            <input type="number" name="audit_retention" class="form-control py-3 rounded-12 border shadow-none fw-700 text-dark" value="{{ $settings['audit_retention'] }}">
                        </div>
                        <div class="p-3 bg-success-soft rounded-12 mb-3">
                            <div class="d-flex align-items-center text-success mb-1">
                                <i class="fas fa-check-double me-2"></i>
                                <span class="fw-800">Comprehensive Logging Active</span>
                            </div>
                            <p class="text-success small mb-0 opacity-75 fw-600">All administrative and authentication events are cryptographically tracked.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Save Action -->
        <div class="card border-0 shadow-premium rounded-20 bg-white overflow-hidden mb-5">
            <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-light rounded-10 me-3">
                        <i class="fas fa-fingerprint text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-800 text-dark">Security Orchestrator</h6>
                        <p class="text-muted smaller mb-0 fw-600">Global security changes require system-level privileges</p>
                    </div>
                </div>
                <button type="submit" class="btn btn-premium-dark px-5 py-3 rounded-12 fw-700">
                    <i class="fas fa-shield-check me-2 text-gold"></i>Commit Global Changes
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    
    .info-soft { color: #3b82f6; }
    
    .btn-info-soft {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border: none;
    }
    .btn-info-soft:hover {
        background-color: #3b82f6;
        color: white;
    }
    
    .rounded-12 { border-radius: 12px !important; }
    .rounded-15 { border-radius: 15px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    
    .shadow-gold {
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3) !important;
    }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .btn-premium-dark {
        background: #1a1a1a;
        color: white;
        border: none;
    }
    .btn-premium-dark:hover {
        background: #000;
        color: white;
    }
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    
    .smaller { font-size: 0.75rem; }
    
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: not-allowed;
    }
    
    .form-switch .form-check-input:checked {
        background-color: #1a1a1a;
        border-color: #1a1a1a;
    }
</style>
@endsection
