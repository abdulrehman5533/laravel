@extends('layouts.app')

@section('title', 'Credential Rotation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="text-center mb-5">
                <div class="stat-icon bg-premium-dark text-gold mx-auto mb-3 shadow-lg" style="width: 70px; height: 70px; font-size: 1.5rem; border-radius: 18px;">
                    <i class="fas fa-shield-keyhole"></i>
                </div>
                <h2 class="fw-800 text-dark mb-1">Mandatory Rotation</h2>
                <p class="text-muted fw-600">Your security credentials require synchronization before perimeter access is granted.</p>
            </div>

            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    @if (session('warning'))
                        <div class="alert bg-danger-soft text-danger border-0 rounded-12 mb-4 d-flex align-items-center p-3">
                            <i class="fas fa-triangle-exclamation me-3 fs-5"></i>
                            <span class="small fw-700">{{ session('warning') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase text-muted">Current Credential</label>
                            <div class="input-group premium-input-group">
                                <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="current_password" class="form-control border-0 bg-light rounded-end-12 py-3 @error('current_password') is-invalid @enderror" required placeholder="Verify current passphrase">
                                @error('current_password')
                                    <div class="invalid-feedback ms-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-700 small text-uppercase text-muted">New Secure Passphrase</label>
                            <div class="input-group premium-input-group">
                                <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-0 bg-light rounded-end-12 py-3 @error('password') is-invalid @enderror" required placeholder="Enter new credential">
                                @error('password')
                                    <div class="invalid-feedback ms-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="p-3 bg-primary-soft rounded-12 mt-3">
                                <div class="d-flex align-items-center text-primary mb-1">
                                    <i class="fas fa-circle-info me-2 smaller"></i>
                                    <span class="fw-800 smaller text-uppercase">Standard Compliance</span>
                                </div>
                                <p class="text-muted smaller mb-0 fw-600">Minimum 8 characters with alphanumeric variance and symbols.</p>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-700 small text-uppercase text-muted">Re-enter New Passphrase</label>
                            <div class="input-group premium-input-group">
                                <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-shield-check text-muted"></i></span>
                                <input type="password" name="password_confirmation" class="form-control border-0 bg-light rounded-end-12 py-3" required placeholder="Confirm new credential">
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-premium-dark py-3 rounded-12 fw-700 shadow-gold">
                                <i class="fas fa-sync-alt me-2 text-gold"></i>Synchronize Credentials
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted text-decoration-none smaller fw-700 text-uppercase tracking-wider">
                                <i class="fas fa-power-off me-2"></i>Terminate Session & Defer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-center">
                <div class="badge bg-white shadow-sm text-muted rounded-pill px-4 py-2 border fw-600">
                    <i class="fas fa-shield-halved text-primary me-2"></i>Enforced by Security Command Center
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    
    .shadow-premium {
        box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.1), 0 5px 15px -5px rgba(0, 0, 0, 0.05) !important;
    }
    
    .shadow-gold {
        box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3) !important;
    }
    
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .btn-premium-dark {
        background: #1a1a1a;
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-premium-dark:hover {
        background: #000;
        transform: translateY(-2px);
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
    
    .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
