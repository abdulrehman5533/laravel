@extends('layouts.app')

@section('title', 'Define Role')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Architectural Role</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.roles.index') }}" class="text-decoration-none text-muted">Role Registry</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">New Definition</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.roles.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Registry
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-primary-soft text-primary rounded-10 me-3">
                            <i class="fas fa-shield-halved fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-800 text-dark">Role Specification</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="{{ route('security-manager.roles.store') }}">
                        @csrf
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Functional Designation (Name)</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-tag text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-0 bg-light rounded-end-12 py-3 @error('name') is-invalid @enderror" required placeholder="e.g. Senior Auditor">
                                    @error('name')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">System Identifier (Slug)</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-code text-muted"></i></span>
                                    <input type="text" name="slug" class="form-control border-0 bg-light rounded-end-12 py-3 @error('slug') is-invalid @enderror" required placeholder="e.g. senior-auditor">
                                    @error('slug')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase text-muted">Scope Description</label>
                            <textarea name="description" class="form-control border-0 bg-light rounded-12 py-3" rows="3" placeholder="Define the operational boundaries and responsibilities of this role..."></textarea>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Session TTL (Minutes)</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-clock text-muted"></i></span>
                                    <input type="number" name="session_timeout_minutes" class="form-control border-0 bg-light rounded-end-12 py-3" value="120" min="5" max="1440" required>
                                </div>
                                <div class="text-muted smaller fw-600 mt-2">Maximum duration of inactivity before revocation</div>
                            </div>
                            
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="p-3 bg-light rounded-15 border w-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                                        <label class="form-check-label fw-700 text-dark ms-2" for="is_active">Operational Readiness</label>
                                    </div>
                                    <div class="text-muted smaller fw-600 ms-5">Immediately enable role for assignment</div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-top d-flex gap-3">
                            <button type="submit" class="btn btn-premium-dark px-5 py-3 rounded-12 fw-700 shadow-sm">
                                <i class="fas fa-check-circle me-2 text-gold"></i>Commit Definition
                            </button>
                            <a href="{{ route('security-manager.roles.index') }}" class="btn btn-light px-5 py-3 rounded-12 fw-700">Discard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-premium rounded-20 bg-premium-dark text-white overflow-hidden h-100">
                <div class="card-body p-4 position-relative">
                    <div class="card-bg-icon">
                        <i class="fas fa-users-gear text-gold opacity-10"></i>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-2 bg-gold text-dark rounded-10 me-3">
                            <i class="fas fa-sitemap fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-800 text-gold">Role Architecture</h5>
                    </div>
                    
                    <div class="protocol-item mb-4">
                        <h6 class="fw-700 text-white mb-1">Functional Separation</h6>
                        <p class="text-white-50 smaller fw-600 mb-0">Roles should be defined based on business functions to ensure strict compliance with separation of duties.</p>
                    </div>
                    
                    <div class="protocol-item mb-4">
                        <h6 class="fw-700 text-white mb-1">Session Lifecycle</h6>
                        <p class="text-white-50 smaller fw-600 mb-0">High-privilege roles should have shorter session timeouts to minimize risk exposure.</p>
                    </div>

                    <div class="p-4 bg-white-10 rounded-20 mt-5">
                        <div class="d-flex align-items-center text-gold mb-3">
                            <i class="fas fa-wand-magic-sparkles me-2"></i>
                            <span class="fw-800 small">Next Phase: Permissions</span>
                        </div>
                        <p class="text-white-50 smaller fw-600 mb-0">After committing this definition, you will be redirected to the authorization matrix to bind capabilities.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-15 { border-radius: 15px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
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
    
    .premium-input-group .form-control {
        transition: all 0.3s ease;
    }
    
    .premium-input-group .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1) !important;
    }
    
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.05); }
    
    .card-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 8rem;
        transform: rotate(-15deg);
    }

    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.25em;
    }
    .form-switch .form-check-input:checked {
        background-color: #d4af37;
        border-color: #d4af37;
    }
</style>
@endsection
