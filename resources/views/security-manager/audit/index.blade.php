@extends('layouts.app')

@section('title', 'Security Audit Logs')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Security Audit Trail</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Audit Logs</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2 text-secondary"></i>Intelligence Report
                    </button>
                    <ul class="dropdown-menu border-0 shadow-premium rounded-15">
                        <li><a class="dropdown-item fw-600 py-2" href="{{ route('security-manager.audit.export') }}"><i class="fas fa-file-csv me-2 text-success"></i>Export CSV Registry</a></li>
                        <li><a class="dropdown-item fw-600 py-2" href="#"><i class="fas fa-file-pdf me-2 text-danger"></i>Generate Security Dossier</a></li>
                    </ul>
                </div>
                <a href="{{ route('security-manager.dashboard') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-shield-halved me-2"></i>Command Center
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Pulse Filters -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('security-manager.audit.index', ['category' => 'auth']) }}" class="btn btn-white border shadow-none rounded-pill px-4 fw-600 small">
            <i class="fas fa-key me-2 text-warning"></i>Authentication Events
        </a>
        <a href="{{ route('security-manager.audit.index', ['category' => 'security']) }}" class="btn btn-white border shadow-none rounded-pill px-4 fw-600 small">
            <i class="fas fa-shield-virus me-2 text-danger"></i>Security Alerts
        </a>
        <a href="{{ route('security-manager.audit.index', ['category' => 'user']) }}" class="btn btn-white border shadow-none rounded-pill px-4 fw-600 small">
            <i class="fas fa-user-gear me-2 text-primary"></i>User Governance
        </a>
        <a href="{{ route('security-manager.audit.index', ['date_from' => now()->format('Y-m-d')]) }}" class="btn btn-white border shadow-none rounded-pill px-4 fw-600 small">
            <i class="fas fa-clock me-2 text-info"></i>Recorded Today
        </a>
        <a href="{{ route('security-manager.audit.index') }}" class="btn btn-premium-dark rounded-pill px-4 fw-700 small ms-auto text-gold">
            <i class="fas fa-rotate me-2"></i>Reset All Parameters
        </a>
    </div>

    <!-- Smart Filters -->
    <div class="card border-0 shadow-sm rounded-20 mb-5 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-light rounded-10 me-3">
                    <i class="fas fa-filter text-primary"></i>
                </div>
                <h5 class="mb-0 fw-800">Advanced Search</h5>
            </div>
        </div>
        <div class="card-body p-4 pt-0">
            <form method="GET" action="{{ route('security-manager.audit.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Date From</label>
                    <input type="date" name="date_from" class="form-control rounded-12 border-light shadow-none" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Date To</label>
                    <input type="date" name="date_to" class="form-control rounded-12 border-light shadow-none" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-700 small text-uppercase tracking-wider text-muted">Category</label>
                    <select name="category" class="form-select rounded-12 border-light shadow-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100 shadow-sm rounded-12 overflow-hidden">
                        <button type="submit" class="btn btn-primary py-2 fw-600">
                            Apply Filter
                        </button>
                        <a href="{{ route('security-manager.audit.index') }}" class="btn btn-light py-2 fw-600">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Event Registry</h5>
                    <p class="text-muted small mb-0">Total of {{ $logs->total() }} recorded security events</p>
                </div>
                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-700">
                    <i class="fas fa-shield-halved me-1"></i> System Secured
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Operator</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Activity</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Domain</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Subject</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">IP Source</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 35px; height: 35px;">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="fw-700 text-dark">{{ $log->user?->name ?? 'System' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-700">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td><span class="badge bg-light text-muted rounded-8 px-2 py-1 fw-600">{{ ucfirst($log->category) }}</span></td>
                            <td><span class="text-dark small fw-500">{{ $log->subject }}</span></td>
                            <td><small class="font-monospace text-muted">{{ $log->ip_address }}</small></td>
                            <td class="pe-4 text-end">
                                <span class="d-block fw-700 text-dark small">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-muted smaller fw-600">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-folder-open fa-4x text-light mb-4"></i>
                                    <h4 class="fw-800 text-dark">No Logs Found</h4>
                                    <p class="text-muted">Adjust your filters to see more security events.</p>
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
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
    .shadow-gold {
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3) !important;
    }
    .btn-gold {
        background-color: #d4af37;
        color: #1a1a1a;
        border: none;
    }
    .btn-gold:hover {
        background-color: #bfa030;
        color: #000;
    }
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    .smaller { font-size: 0.75rem; }
</style>
@endsection
