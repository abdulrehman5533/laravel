@extends('layouts.app')

@section('title', 'Provision Identity')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Provision Identity</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.users.index') }}" class="text-decoration-none text-muted">User Registry</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">New Subject</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.users.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
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
                            <i class="fas fa-user-plus fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-800 text-dark">Subject Particulars</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <form action="{{ route('security-manager.users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-5 justify-content-center">
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-700 small text-uppercase text-muted d-block mb-3">Identity Visualization (Avatar)</label>
                                <div class="position-relative d-inline-block avatar-upload-wrapper">
                                    <img src="https://ui-avatars.com/api/?name=New+User&color=7F9CF5&background=EBF4FF" alt="New User" class="rounded-circle shadow-lg border border-4 border-white mb-3" style="width: 150px; height: 150px; object-fit: cover;" id="avatar-preview">
                                    <div class="position-absolute bottom-0 end-0 mb-3">
                                        <label for="avatar" class="btn btn-primary btn-sm rounded-circle shadow-sm p-2" style="width: 35px; height: 35px; cursor: pointer;">
                                            <i class="fas fa-camera"></i>
                                            <input type="file" name="avatar" id="avatar" class="d-none" accept="image/png, image/jpeg, image/jpg" onchange="previewAvatar(this)">
                                        </label>
                                    </div>
                                </div>
                                @error('avatar')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Full Name</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-id-card text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-0 bg-light rounded-end-12 py-3 @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Legal full name">
                                    @error('name')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Email Address</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-0 bg-light rounded-end-12 py-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="Official email contact">
                                    @error('email')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Initial Credential</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-0 bg-light rounded-end-12 py-3 @error('password') is-invalid @enderror" required placeholder="Secure passphrase">
                                    @error('password')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Confirm Credential</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-shield-check text-muted"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control border-0 bg-light rounded-end-12 py-3" required placeholder="Re-enter passphrase">
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Authorization Level</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-user-shield text-muted"></i></span>
                                    <select name="role_id" class="form-select border-0 bg-light rounded-end-12 py-3 @error('role_id') is-invalid @enderror" required>
                                        <option value="">Select Domain Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Provisioning State</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-toggle-on text-muted"></i></span>
                                    <select name="user_status" class="form-select border-0 bg-light rounded-end-12 py-3 @error('user_status') is-invalid @enderror" required>
                                        <option value="active" {{ old('user_status') == 'active' ? 'selected' : '' }}>Operational (Active)</option>
                                        <option value="suspended" {{ old('user_status') == 'suspended' ? 'selected' : '' }}>Quarantined (Suspended)</option>
                                    </select>
                                    @error('user_status')
                                        <div class="invalid-feedback ms-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-700 small text-uppercase text-muted">Contact Vector (Optional)</label>
                                <div class="input-group premium-input-group">
                                    <span class="input-group-text border-0 bg-light rounded-start-12"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control border-0 bg-light rounded-end-12 py-3" value="{{ old('phone') }}" placeholder="Mobile or internal extension">
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="p-3 bg-light rounded-15 border w-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="force_password_change" id="force_password_change" value="1" {{ old('force_password_change') ? 'checked' : 'checked' }}>
                                        <label class="form-check-label fw-700 text-dark ms-2" for="force_password_change">Mandatory Credential Rotation</label>
                                    </div>
                                    <div class="text-muted smaller fw-600 ms-5">Forces rotation on initial authentication</div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-top d-flex gap-3">
                            <button type="submit" class="btn btn-premium-dark px-5 py-3 rounded-12 fw-700 shadow-sm">
                                <i class="fas fa-check-circle me-2 text-gold"></i>Commit Identity
                            </button>
                            <a href="{{ route('security-manager.users.index') }}" class="btn btn-light px-5 py-3 rounded-12 fw-700">Discard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-premium rounded-20 bg-premium-dark text-white overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="card-bg-icon">
                        <i class="fas fa-shield-halved text-gold opacity-10"></i>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-2 bg-gold text-dark rounded-10 me-3">
                            <i class="fas fa-info-circle fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-800 text-gold">Security Protocols</h5>
                    </div>
                    
                    <div class="protocol-item mb-4">
                        <h6 class="fw-700 text-white mb-1">Identity Integrity</h6>
                        <p class="text-white-50 smaller fw-600 mb-0">Email addresses serve as the primary unique identifier for multi-factor orchestration.</p>
                    </div>
                    
                    <div class="protocol-item mb-4">
                        <h6 class="fw-700 text-white mb-1">Least Privilege</h6>
                        <p class="text-white-50 smaller fw-600 mb-0">Assign only the minimum authorization necessary for operational requirements.</p>
                    </div>
                    
                    <div class="protocol-item mb-4">
                        <h6 class="fw-700 text-white mb-1">Credential Standard</h6>
                        <p class="text-white-50 smaller fw-600 mb-0">Passwords must meet global complexity requirements including case-variance and symbols.</p>
                    </div>

                    <div class="p-3 bg-white-10 rounded-12 mt-5">
                        <div class="d-flex align-items-center text-gold">
                            <i class="fas fa-fingerprint me-2"></i>
                            <span class="fw-800 small">Audit Tracking Active</span>
                        </div>
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
    
    .premium-input-group .form-control, .premium-input-group .form-select {
        transition: all 0.3s ease;
    }
    
    .premium-input-group .form-control:focus, .premium-input-group .form-select:focus {
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

    .avatar-upload-wrapper img {
        transition: all 0.3s ease;
    }

    .avatar-upload-wrapper:hover img {
        filter: brightness(0.8);
    }
</style>

<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
