@extends('layouts.app')

@section('title', 'Session Control Manager')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Session Control Manager</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Session Policy</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.sessions.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-network-wired me-2 text-primary"></i>Active Sessions
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-12 border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-3 fa-lg"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Policy Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-15 shadow-sm d-inline-flex" id="policyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-12 fw-700 px-4 py-2" id="global-tab" data-bs-toggle="pill" data-bs-target="#global" type="button" role="tab">
                <i class="fas fa-globe me-2"></i>Global Policy
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-12 fw-700 px-4 py-2" id="roles-tab" data-bs-toggle="pill" data-bs-target="#roles" type="button" role="tab">
                <i class="fas fa-users-cog me-2"></i>Role-Based Policy
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-12 fw-700 px-4 py-2" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab">
                <i class="fas fa-user-shield me-2"></i>User Overrides
            </button>
        </li>
    </ul>

    <div class="tab-content" id="policyTabsContent">
        <!-- Global Policy Tab -->
        <div class="tab-pane fade show active" id="global" role="tabpanel">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-800 text-dark">Enterprise-Wide Default Policy</h5>
                    <p class="text-muted small mb-0">These rules apply to all users unless overridden at the role or user level.</p>
                </div>
                <form action="{{ route('security-manager.sessions.control.global.update') }}" method="POST">
                    @csrf
                    <div class="card-body p-4 pt-0">
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-700 small text-uppercase text-muted">Session Lifetime (Min)</label>
                                <input type="number" name="default_session_timeout" class="form-control rounded-12 border-light bg-light-soft" value="{{ $globalSettings['default_session_timeout'] }}" required min="1">
                                <div class="form-text smaller">Total duration before forced logout.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-700 small text-uppercase text-muted">Idle Timeout (Min)</label>
                                <input type="number" name="default_idle_timeout" class="form-control rounded-12 border-light bg-light-soft" value="{{ $globalSettings['default_idle_timeout'] }}" required min="1">
                                <div class="form-text smaller">Inactivity period before logout.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-700 small text-uppercase text-muted">Minimize Timeout (Min)</label>
                                <input type="number" name="default_minimize_timeout" class="form-control rounded-12 border-light bg-light-soft" value="{{ $globalSettings['default_minimize_timeout'] }}" required min="1">
                                <div class="form-text smaller">Time allowed if browser is minimized.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-700 small text-uppercase text-muted">Background Timeout (Min)</label>
                                <input type="number" name="default_background_timeout" class="form-control rounded-12 border-light bg-light-soft" value="{{ $globalSettings['default_background_timeout'] }}" required min="1">
                                <div class="form-text smaller">Time allowed if tab is in background.</div>
                            </div>
                            <div class="col-lg-6">
                                <div class="bg-light-soft p-3 rounded-15 border border-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0 fw-700">Idle-Based Logout</h6>
                                            <p class="text-muted smaller mb-0">Enable automatic logout for inactive users.</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_idle_logout" value="1" {{ $globalSettings['enable_idle_logout'] ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0 fw-700">Minimize-Based Logout</h6>
                                            <p class="text-muted smaller mb-0">Logout user when window is minimized for too long.</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_minimize_logout" value="1" {{ $globalSettings['enable_minimize_logout'] ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-700">Background-Based Logout</h6>
                                            <p class="text-muted smaller mb-0">Logout user when tab is in background for too long.</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_background_logout" value="1" {{ $globalSettings['enable_background_logout'] ?? true ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="bg-light-soft p-3 rounded-15 border border-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0 fw-700">Expiry Warning Popup</h6>
                                            <p class="text-muted smaller mb-0">Warn user N minutes before session expires.</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <input type="number" name="popup_warning_minutes" class="form-control form-control-sm rounded-8 border-light me-2" style="width: 60px;" value="{{ $globalSettings['popup_warning_minutes'] }}">
                                            <span class="smaller fw-600 text-muted">Min</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-700">Auto-Extend on Activity</h6>
                                            <p class="text-muted smaller mb-0">Reset session timer when user interacts with system.</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="auto_extend_session" value="1" {{ $globalSettings['auto_extend_session'] ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light-soft border-0 p-4 text-end">
                        <button type="submit" class="btn btn-primary px-5 rounded-12 fw-700 shadow-sm">
                            <i class="fas fa-save me-2"></i>Save Global Policy
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role-Based Policy Tab -->
        <div class="tab-pane fade" id="roles" role="tabpanel">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-800 text-dark">Group-wise Session Policies</h5>
                    <p class="text-muted small mb-0">New users in these roles will automatically inherit these rules.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Role Identity</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Timeout (Min)</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Idle Logout</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Minimize Logout</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Background Logout</th>
                                    <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td class="ps-4 py-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 35px; height: 35px;">
                                                {{ strtoupper(substr($role->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-800 text-dark">{{ $role->name }}</div>
                                                <div class="text-muted smaller fw-600">{{ $role->users_count }} active subjects</div>
                                            </div>
                                        </div>
                                    </td>
                                    <form action="{{ route('security-manager.sessions.control.roles.update', $role) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <td class="text-center">
                                            <input type="number" name="session_timeout_minutes" class="form-control form-control-sm rounded-8 border-light d-inline-block text-center" style="width: 80px;" value="{{ $role->session_timeout_minutes }}">
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="enable_idle_logout" value="1" {{ $role->enable_idle_logout ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="enable_minimize_logout" value="1" {{ $role->enable_minimize_logout ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="enable_background_logout" value="1" {{ $role->enable_background_logout ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button type="submit" class="btn btn-white btn-sm shadow-sm rounded-8 border-0 fw-700 text-primary px-3">
                                                Update Policy
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Overrides Tab -->
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="mb-0 fw-800 text-dark">Subject-Specific Overrides</h5>
                    <p class="text-muted small mb-0">Individual policies that bypass role and global rules for high-security users.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Subject</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Session Life</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Idle Wait</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Background</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">Idle Log</th>
                                    <th class="py-3 border-0 fw-700 text-uppercase small text-muted text-center">BG Log</th>
                                    <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold smaller" style="width: 30px; height: 30px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-700 text-dark smaller">{{ $user->name }}</div>
                                                <div class="badge bg-light text-muted smaller" style="font-size: 0.65rem;">{{ $user->role?->name ?? 'Subject' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <form action="{{ route('security-manager.sessions.control.users.update', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <td class="text-center">
                                            <input type="number" name="session_timeout_minutes" class="form-control form-control-sm rounded-8 border-light d-inline-block text-center smaller" style="width: 70px;" value="{{ $user->session_timeout_minutes }}" placeholder="Inherit">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="idle_timeout_minutes" class="form-control form-control-sm rounded-8 border-light d-inline-block text-center smaller" style="width: 70px;" value="{{ $user->idle_timeout_minutes }}" placeholder="Inherit">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="background_timeout_minutes" class="form-control form-control-sm rounded-8 border-light d-inline-block text-center smaller" style="width: 70px;" value="{{ $user->background_timeout_minutes }}" placeholder="Inherit">
                                        </td>
                                        <td class="text-center">
                                            <select name="enable_idle_logout" class="form-select form-select-sm rounded-8 border-light d-inline-block smaller" style="width: 80px;">
                                                <option value="" {{ is_null($user->enable_idle_logout) ? 'selected' : '' }}>Inh</option>
                                                <option value="1" {{ $user->enable_idle_logout === true ? 'selected' : '' }}>On</option>
                                                <option value="0" {{ $user->enable_idle_logout === false ? 'selected' : '' }}>Off</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <select name="enable_background_logout" class="form-select form-select-sm rounded-8 border-light d-inline-block smaller" style="width: 80px;">
                                                <option value="" {{ is_null($user->enable_background_logout) ? 'selected' : '' }}>Inh</option>
                                                <option value="1" {{ $user->enable_background_logout === true ? 'selected' : '' }}>On</option>
                                                <option value="0" {{ $user->enable_background_logout === false ? 'selected' : '' }}>Off</option>
                                            </select>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button type="submit" class="btn btn-white btn-sm shadow-sm rounded-8 border-0 fw-700 text-primary p-2">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-15 { border-radius: 15px !important; }
    .rounded-20 { border-radius: 20px !important; }
    .rounded-8 { border-radius: 8px !important; }
    .bg-light-soft { background-color: #f8f9fa !important; }
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    .smaller { font-size: 0.75rem; }
    .nav-pills .nav-link { color: #6c757d; }
    .nav-pills .nav-link.active { background-color: #1a1a1a; color: #d4af37; }
</style>
@endsection
