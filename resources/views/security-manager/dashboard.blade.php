@extends('layouts.app')

@section('title', 'Security Manager Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Security Command Center</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Security Manager</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('security-manager.audit.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-file-shield me-2 text-secondary"></i>Audit Logs
                </a>
                <a href="{{ route('security-manager.users.index') }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-user-shield me-2"></i>User Management
                </a>
            </div>
        </div>
    </div>

    <!-- Premium Metrics Row -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden group">
                <div class="card-bg-icon">
                    <i class="fas fa-users text-primary opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-primary-soft text-primary me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="text-muted fw-600 small">Total Users</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark" id="totalUsersStat">{{ $totalUsers }}</h2>
                <span class="text-success small fw-600"><i class="fas fa-check-circle me-1"></i>System Registry</span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('security-manager.sessions.index') }}" class="text-decoration-none h-100">
                <div class="stat-card border-0 overflow-hidden h-100">
                    <div class="card-bg-icon">
                        <i class="fas fa-wifi text-info opacity-10"></i>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-info-soft text-info me-3">
                            <i class="fas fa-broadcast-tower"></i>
                        </div>
                        <span class="text-muted fw-600 small">Active Sessions</span>
                    </div>
                    <h2 class="fw-800 mb-2 text-dark" id="activeSessionsStat">{{ $activeSessions }}</h2>
                    <span class="text-info small fw-600"><i class="fas fa-circle me-1 animate-pulse"></i>Live Connections</span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-shield-heart text-success opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-success-soft text-success me-3">
                        <i class="fas fa-shield-heart"></i>
                    </div>
                    <span class="text-muted fw-600 small">Security Score</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark">94%</h2>
                <div class="progress rounded-pill mb-0" style="height: 6px; width: 80%;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 94%"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 overflow-hidden">
                <div class="card-bg-icon">
                    <i class="fas fa-shield-virus text-danger opacity-10"></i>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon bg-danger-soft text-danger me-3">
                        <i class="fas fa-shield-virus"></i>
                    </div>
                    <span class="text-muted fw-600 small">Security Alerts</span>
                </div>
                <h2 class="fw-800 mb-2 text-dark" id="securityAlertsStat">{{ $securityAlerts }}</h2>
                <span class="text-danger small fw-600 fw-bold">Critical attention needed</span>
            </div>
        </div>
    </div>

    <!-- Advanced Analytics Row -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-20 h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-light rounded-10 me-3">
                                <i class="fas fa-chart-line text-primary"></i>
                            </div>
                            <h5 class="mb-0 fw-800">Authentication Velocity</h5>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-8 px-3 fw-600 border-0 shadow-none" type="button" data-bs-toggle="dropdown">
                                Last 30 Days <i class="fas fa-chevron-down ms-1 smaller"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div style="height: 300px;">
                        <canvas id="loginTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-premium rounded-20 h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-light rounded-10 me-3">
                            <i class="fas fa-pie-chart text-info"></i>
                        </div>
                        <h5 class="mb-0 fw-800">User Registry</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0 d-flex flex-column justify-content-center">
                    <div style="height: 220px;" class="mb-4">
                        <canvas id="userStatusChart"></canvas>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="d-flex align-items-center p-2 rounded-12 bg-light">
                                <div class="dot bg-success me-2"></div>
                                <span class="small fw-600 text-muted">Active</span>
                                <span class="ms-auto fw-800 small" id="activeUsersStat">{{ $activeUsers }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-2 rounded-12 bg-light">
                                <div class="dot bg-warning me-2"></div>
                                <span class="small fw-600 text-muted">Suspended</span>
                                <span class="ms-auto fw-800 small" id="suspendedUsersStat">{{ $suspendedUsers }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-2 rounded-12 bg-light">
                                <div class="dot bg-danger me-2"></div>
                                <span class="small fw-600 text-muted">Locked</span>
                                <span class="ms-auto fw-800 small" id="lockedUsersStat">{{ $lockedUsers }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-2 rounded-12 bg-light">
                                <div class="dot bg-secondary me-2"></div>
                                <span class="small fw-600 text-muted">Inactive</span>
                                <span class="ms-auto fw-800 small" id="inactiveUsersStat">{{ $totalUsers - $activeUsers - $suspendedUsers - $lockedUsers }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Global Threat Monitor -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-premium rounded-20 bg-premium-dark text-white overflow-hidden" style="min-height: 400px;">
                <div class="card-header bg-transparent border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-glass rounded-10 me-3">
                                <i class="fas fa-earth-americas text-gold"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-800 text-white">Global Threat Monitor</h5>
                                <p class="text-white-50 small mb-0">Active perimeter surveillance and signal analysis</p>
                            </div>
                        </div>
                        <div class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 animate-pulse">
                            <i class="fas fa-shield-radar me-1"></i> LIVE MONITORING
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 position-relative">
                    <!-- Simulated Map/Grid Background -->
                    <div class="threat-grid-bg"></div>
                    
                    <div class="row h-100 p-4 position-relative">
                        <div class="col-md-8 d-flex align-items-center justify-content-center">
                            <div class="radar-container">
                                <div class="radar-circle"></div>
                                <div class="radar-sweep"></div>
                                <div class="radar-ping" style="top: 30%; left: 40%;"></div>
                                <div class="radar-ping" style="top: 60%; left: 70%;"></div>
                                <div class="radar-ping alert" style="top: 45%; left: 25%;"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-glass rounded-20 border border-white-10">
                                <h6 class="fw-700 text-gold mb-3 small text-uppercase tracking-wider">Active Signal Intelligence</h6>
                                <div class="signal-item mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="smaller fw-600 text-white-50">Local Perimeter</span>
                                        <span class="smaller fw-700 text-success">SECURE</span>
                                    </div>
                                    <div class="progress bg-white-10" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div class="signal-item mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="smaller fw-600 text-white-50">Remote Gateway</span>
                                        <span class="smaller fw-700 text-warning">TRAFFIC</span>
                                    </div>
                                    <div class="progress bg-white-10" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: 65%"></div>
                                    </div>
                                </div>
                                <div class="signal-item mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="smaller fw-600 text-white-50">Database Integrity</span>
                                        <span class="smaller fw-700 text-info">ENCRYPTED</span>
                                    </div>
                                    <div class="progress bg-white-10" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: 90%"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button class="btn btn-gold w-100 py-3 rounded-12 fw-700 small shadow-gold">
                                        <i class="fas fa-shield-halved me-2"></i>Initiate System Scan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Status Distribution -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-premium rounded-20 h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-light rounded-10 me-3">
                            <i class="fas fa-shield-virus text-danger"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Threat Intelligence</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="alert bg-danger-soft border-0 rounded-15 p-3 mb-4">
                        <div class="d-flex align-items-center text-danger mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span class="fw-800 small">Active Security Risks</span>
                        </div>
                        <p class="small mb-0 text-danger opacity-75 fw-600">The following subjects have exceeded authentication failure thresholds and require immediate review.</p>
                    </div>
                    
                    <div class="threat-list" id="threatListContainer">
                        @php
                            $riskyUsers = \App\Models\User::where('failed_login_attempts', '>', 0)
                                ->orWhereNotNull('locked_until')
                                ->limit(3)
                                ->get();
                        @endphp
                        
                        @forelse($riskyUsers as $rUser)
                        <div class="d-flex align-items-center p-3 mb-3 bg-light rounded-15 border-start border-4 border-danger">
                            <div class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold">
                                {{ strtoupper(substr($rUser->name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-700 text-dark">{{ $rUser->name }}</h6>
                                <span class="smaller text-muted fw-600">{{ $rUser->failed_login_attempts }} failed attempts</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger-soft text-danger rounded-pill px-2 py-1 fw-700 smaller">
                                    {{ $rUser->locked_until ? 'Account Locked' : 'Risk Detected' }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-shield-check text-success fa-3x mb-3 opacity-20"></i>
                            <p class="text-muted small fw-600 mb-0">No active threats detected in the registry</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-premium rounded-20 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-light rounded-10 me-3">
                            <i class="fas fa-bolt text-warning"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Operational Shortcuts</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('security-manager.users.index') }}" class="list-group-item list-group-item-action p-4 border-0 d-flex align-items-center group">
                            <div class="icon-box bg-primary-soft text-primary rounded-12 me-3">
                                <i class="fas fa-user-gear"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-700">Account Governance</h6>
                                <p class="small text-muted mb-0">Manage users, passwords, and statuses</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-light group-hover-translate-x"></i>
                        </a>
                        <a href="{{ route('security-manager.roles.permission-matrix') }}" class="list-group-item list-group-item-action p-4 border-0 d-flex align-items-center group">
                            <div class="icon-box bg-success-soft text-success rounded-12 me-3">
                                <i class="fas fa-table-list"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-700">Authorization Matrix</h6>
                                <p class="small text-muted mb-0">Configure role-based access control</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-light group-hover-translate-x"></i>
                        </a>
                        <a href="{{ route('security-manager.settings.index') }}" class="list-group-item list-group-item-action p-4 border-0 d-flex align-items-center group">
                            <div class="icon-box bg-info-soft text-info rounded-12 me-3">
                                <i class="fas fa-gears"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-700">Policy Orchestration</h6>
                                <p class="small text-muted mb-0">Global security parameters and protocols</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-light group-hover-translate-x"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-light rounded-10 me-3">
                        <i class="fas fa-clock-rotate-left text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-800">Security Event Ledger</h5>
                        <p class="text-muted small mb-0">Real-time tracking of critical system events</p>
                    </div>
                </div>
                <a href="{{ route('security-manager.audit.index') }}" class="btn btn-light rounded-8 px-4 fw-600">
                    Full Audit Trail
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 fw-700 text-uppercase small text-muted">Operator</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Action</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Category</th>
                            <th class="py-3 border-0 fw-700 text-uppercase small text-muted">Description</th>
                            <th class="pe-4 py-3 border-0 fw-700 text-uppercase small text-muted text-end">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="fw-700 text-dark">{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-700">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td><span class="text-muted small fw-600">{{ ucfirst($log->category) }}</span></td>
                            <td><span class="text-dark small">{{ $log->description }}</span></td>
                            <td class="pe-4 text-end">
                                <span class="badge bg-light text-muted rounded-8 px-2 py-1 fw-600">
                                    {{ $log->created_at?->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted py-3">
                                    <i class="fas fa-shield-blank fa-3x mb-3 opacity-20"></i>
                                    <p class="mb-0 fw-600">No security events recorded in the last 24 hours</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// @ts-nocheck
/* eslint-disable */
document.addEventListener('DOMContentLoaded', function() {
    let trendChart, statusChart;
    
    // Initialize Trend Chart
    const trendCtx = document.getElementById('loginTrendChart').getContext('2d');
    const loginTrendData = @json($loginTrend);
    
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: loginTrendData.map(item => item.date),
            datasets: [{
                label: 'Logins',
                data: loginTrendData.map(item => item.count),
                borderColor: '#1a1a1a',
                backgroundColor: 'rgba(26, 26, 26, 0.05)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#d4af37',
                pointBorderColor: '#fff',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: { font: { weight: '600' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: '600' } }
                }
            }
        }
    });

    // Initialize Status Chart
    const statusCtx = document.getElementById('userStatusChart').getContext('2d');
    const statusData = [{{ $activeUsers }}, {{ $suspendedUsers }}, {{ $lockedUsers }}, {{ $inactiveUsers }}];
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Suspended', 'Locked', 'Inactive'],
            datasets: [{
                data: statusData,
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Real-time Update Function
    function updateDashboard() {
        fetch('{{ route('security-manager.stats') }}')
            .then(response => response.json())
            .then(data => {
                // Update Stats
                document.getElementById('totalUsersStat').innerText = data.stats.totalUsers;
                document.getElementById('activeSessionsStat').innerText = data.stats.activeSessions;
                document.getElementById('securityAlertsStat').innerText = data.stats.securityAlerts;
                
                // Update Registry Stats
                document.getElementById('activeUsersStat').innerText = data.stats.activeUsers;
                document.getElementById('suspendedUsersStat').innerText = data.stats.suspendedUsers;
                document.getElementById('lockedUsersStat').innerText = data.stats.lockedUsers;
                document.getElementById('inactiveUsersStat').innerText = data.stats.inactiveUsers;

                // Update Status Chart
                statusChart.data.datasets[0].data = [
                    data.stats.activeUsers,
                    data.stats.suspendedUsers,
                    data.stats.lockedUsers,
                    data.stats.inactiveUsers
                ];
                statusChart.update();

                // Update Trend Chart
                trendChart.data.labels = data.loginTrend.map(item => item.date);
                trendChart.data.datasets[0].data = data.loginTrend.map(item => item.count);
                trendChart.update();

                // Update Threat List
                const threatContainer = document.getElementById('threatListContainer');
                if (data.riskyUsers.length > 0) {
                    let html = '';
                    data.riskyUsers.forEach(user => {
                        html += `
                        <div class="d-flex align-items-center p-3 mb-3 bg-light rounded-15 border-start border-4 border-danger">
                            <div class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">
                                ${user.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-700 text-dark">${user.name}</h6>
                                <span class="smaller text-muted fw-600">${user.failed_login_attempts} failed attempts</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger-soft text-danger rounded-pill px-2 py-1 fw-700 smaller">
                                    ${user.locked_until ? 'Account Locked' : 'Risk Detected'}
                                </span>
                            </div>
                        </div>`;
                    });
                    threatContainer.innerHTML = html;
                } else {
                    threatContainer.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-shield-check text-success fa-3x mb-3 opacity-20"></i>
                            <p class="text-muted small fw-600 mb-0">No active threats detected in the registry</p>
                        </div>`;
                }
            })
            .catch(error => console.error('Error fetching security stats:', error));
    }

    // Poll every 10 seconds for real-time updates
    setInterval(updateDashboard, 10000);
});
</script>

<style>
    .bg-glass { background-color: rgba(255, 255, 255, 0.05) !important; }
    .border-white-10 { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.1) !important; }
    
    .threat-grid-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            linear-gradient(rgba(212, 175, 55, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(212, 175, 55, 0.05) 1px, transparent 1px);
        background-size: 30px 30px;
        opacity: 0.3;
    }

    .radar-container {
        position: relative;
        width: 300px;
        height: 300px;
    }

    .radar-circle {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 2px solid rgba(212, 175, 55, 0.2);
        border-radius: 50%;
    }

    .radar-circle::before, .radar-circle::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 1px solid rgba(212, 175, 55, 0.1);
        border-radius: 50%;
    }

    .radar-circle::before { width: 66%; height: 66%; }
    .radar-circle::after { width: 33%; height: 33%; }

    .radar-sweep {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: conic-gradient(from 0deg, rgba(212, 175, 55, 0.4) 0deg, transparent 90deg);
        border-radius: 50%;
        animation: rotate 4s linear infinite;
    }

    .radar-ping {
        position: absolute;
        width: 8px;
        height: 8px;
        background: #d4af37;
        border-radius: 50%;
        box-shadow: 0 0 10px #d4af37;
        animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    .radar-ping.alert {
        background: #ef4444;
        box-shadow: 0 0 15px #ef4444;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes ping {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(3); opacity: 0; }
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    
    .rounded-10 { border-radius: 10px !important; }
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
    
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .stat-card:hover { transform: translateY(-5px); }
    
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
    
    .icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .bg-premium-dark { background: #1a1a1a !important; }
    .text-gold { color: #d4af37 !important; }
    
    .group:hover .group-hover-translate-x {
        transform: translateX(5px);
        transition: transform 0.2s ease;
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endsection
