@extends('layouts.app')

@section('title', 'Access History')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Access Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Login History</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.dashboard') }}" class="btn btn-light shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Login Trend Chart (Optional, if Chart.js is available) -->
    <div class="card border-0 shadow-premium rounded-20 mb-5 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-success-soft text-success rounded-10 me-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h5 class="mb-0 fw-800">Authentication Velocity</h5>
            </div>
        </div>
        <div class="card-body p-4 pt-0">
            <canvas id="loginTrendChart" height="100"></canvas>
        </div>
    </div>

    <!-- History Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Access Ledger</h5>
                    <p class="text-muted small mb-0">Chronological record of successful authentication events</p>
                </div>
                <div class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-700">
                    <i class="fas fa-shield-check me-1"></i> Secure Endpoints
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Authenticated Identity</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">IP Vector</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Interface</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Access Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 35px; height: 35px;">
                                        {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="fw-700 text-dark">{{ $log->user?->name ?? 'Unknown Entity' }}</div>
                                </div>
                            </td>
                            <td><small class="font-monospace text-muted bg-light px-2 py-1 rounded-8 border">{{ $log->ip_address }}</small></td>
                            <td>
                                <span class="badge bg-info-soft text-info rounded-pill px-3 py-2 fw-700">
                                    <i class="fas fa-desktop me-1 small"></i> {{ $log->metadata['device_type'] ?? 'Web Terminal' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="d-block fw-700 text-dark small">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-muted smaller fw-600">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-history fa-4x text-light mb-4 opacity-50"></i>
                                    <h4 class="fw-800 text-dark">No Access Records</h4>
                                    <p class="text-muted">No successful logins have been recorded in the current cycle.</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('loginTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLogins->pluck('date')) !!},
            datasets: [{
                label: 'Logins',
                data: {!! json_encode($dailyLogins->pluck('count')) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#10b981'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush

<style>
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
