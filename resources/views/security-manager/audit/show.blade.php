@extends('layouts.app')

@section('title', 'Event Intelligence Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Event Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.audit.index') }}" class="text-decoration-none text-muted">Audit Logs</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Log #{{ $log->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.audit.index') }}" class="btn btn-light shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2"></i>Return to Registry
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Event Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-primary-soft text-primary rounded-10 me-3">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Operational Particulars</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-700 small text-uppercase text-muted">Functional Action</label>
                            <div class="p-3 bg-light rounded-12">
                                <span class="badge bg-premium-dark text-gold rounded-pill px-3 py-2 fw-700">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-700 small text-uppercase text-muted">Registry Domain</label>
                            <div class="p-3 bg-light rounded-12">
                                <span class="fw-800 text-dark">{{ ucfirst($log->category) }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-700 small text-uppercase text-muted">Event Descriptor</label>
                            <div class="p-3 bg-light rounded-12">
                                <p class="mb-0 fw-600 text-dark">{{ $log->description }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-700 small text-uppercase text-muted">Operational Context (Subject)</label>
                            <div class="p-3 bg-light rounded-12">
                                <span class="text-primary fw-800">{{ $log->subject ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meta Information -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-success-soft text-success rounded-10 me-3">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Machine Intelligence</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-4">
                        <label class="form-label fw-700 small text-uppercase text-muted">Operator Identity</label>
                        <div class="d-flex align-items-center p-2 bg-light rounded-12 border">
                            <div class="avatar-sm bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 35px; height: 35px;">
                                {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="fw-700 text-dark">{{ $log->user?->name ?? 'System Process' }}</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-700 small text-uppercase text-muted">Vector Address</label>
                        <div class="p-2 bg-light rounded-12 border">
                            <code class="fw-800 text-danger">{{ $log->ip_address }}</code>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-700 small text-uppercase text-muted">Execution Chronology</label>
                        <div class="p-2 bg-light rounded-12 border">
                            <span class="fw-700 text-dark">{{ $log->created_at->format('M d, Y') }}</span>
                            <span class="text-muted smaller ms-2">{{ $log->created_at->format('H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($log->metadata)
        <!-- Advanced Metadata -->
        <div class="col-12">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-info-soft text-info rounded-10 me-3">
                            <i class="fas fa-code"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Extended Payload Metadata</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="p-4 bg-premium-dark rounded-20">
                        <pre class="mb-0 text-gold shadow-none border-0" style="font-family: 'Fira Code', 'Courier New', monospace; font-size: 0.9rem;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .smaller { font-size: 0.75rem; }
</style>
@endsection
