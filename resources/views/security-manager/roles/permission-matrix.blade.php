@extends('layouts.app')

@section('title', 'Authorization Matrix')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Authorization Matrix</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('security-manager.dashboard') }}" class="text-decoration-none text-muted">Security Manager</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">Capabilities Bindings</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('security-manager.roles.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Registry
            </a>
        </div>
    </div>

    <!-- Matrix Visualization -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card border-0 shadow-premium rounded-20 bg-premium-dark text-white overflow-hidden">
                <div class="card-header bg-transparent border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-glass rounded-10 me-3">
                            <i class="fas fa-microchip text-gold"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-800 text-white">Coverage Intelligence</h5>
                            <p class="text-white-50 small mb-0">Visual analysis of capability distribution across architectural domains</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <div style="height: 350px;">
                                <canvas id="coverageChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-4 bg-glass rounded-20 border border-white-10">
                                <h6 class="fw-700 text-gold mb-3 small text-uppercase tracking-wider">Top-Tier Roles</h6>
                                @foreach($roles->sortByDesc(fn($r) => $r->permissions->count())->take(3) as $topRole)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm bg-gold text-dark rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold small">
                                        {{ strtoupper(substr($topRole->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-700 text-white small">{{ $topRole->name }}</div>
                                        <div class="progress bg-white-10 mt-1" style="height: 4px;">
                                            @php
                                                $totalPerms = \App\Models\Permission::count();
                                                $percent = $totalPerms > 0 ? ($topRole->permissions->count() / $totalPerms) * 100 : 0;
                                            @endphp
                                            <div class="progress-bar bg-gold" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                    <span class="ms-3 fw-800 text-gold small">{{ round($percent) }}%</span>
                                </div>
                                @endforeach
                                
                                <div class="mt-4 p-3 bg-white-5 rounded-12 border border-white-10">
                                    <p class="smaller text-white-50 mb-0 italic">
                                        <i class="fas fa-info-circle me-1"></i> Radar chart displays relative capability density across identified system domains.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Matrix Table -->
    <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-800">Capability Bindings</h5>
                    <p class="text-muted small mb-0">Bind atomic permissions to architectural roles across all domains</p>
                </div>
                <div class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-700 shadow-gold">
                    <i class="fas fa-microchip me-1"></i> System Core Enforced
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1000px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-4 border-0 fw-700 text-uppercase small text-muted" style="width: 300px;">Domain & Capabilities</th>
                            @foreach($roles as $role)
                            <th class="text-center py-4 border-0">
                                <div class="fw-800 text-dark mb-0">{{ $role->name }}</div>
                                <div class="badge bg-premium-dark text-gold smaller rounded-pill px-2 fw-700 mt-1 shadow-sm">{{ $role->slug }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissionsByResource as $resource => $perms)
                            <tr class="bg-premium-dark border-0">
                                <td colspan="{{ count($roles) + 1 }}" class="ps-4 py-2">
                                    <span class="text-gold fw-800 smaller text-uppercase tracking-widest"><i class="fas fa-folder-open me-2"></i>{{ $resource }} Domain</span>
                                </td>
                            </tr>
                            @foreach($perms as $permission)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-700 text-dark">{{ $permission->name }}</div>
                                        <div class="font-monospace smaller text-muted opacity-75">{{ $permission->slug }}</div>
                                    </td>
                                    @foreach($roles as $role)
                                        <td class="text-center">
                                            @php
                                                $hasPermission = $role->permissions->contains($permission->id);
                                                $isAdmin = $role->slug === 'admin';
                                            @endphp
                                            
                                            <form action="{{ route('security-manager.roles.permissions.toggle', $role) }}" method="POST" class="d-inline permission-toggle-form">
                                                @csrf
                                                <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                                                
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input permission-checkbox" type="checkbox" 
                                                        @if($hasPermission) checked @endif 
                                                        @if($isAdmin) disabled title="Admin capabilities are immutable" @endif
                                                        data-role-id="{{ $role->id }}"
                                                        data-permission-id="{{ $permission->id }}">
                                                </div>
                                            </form>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-4 border-top">
            <div class="d-flex align-items-center p-3 bg-primary-soft rounded-12">
                <div class="p-2 bg-white rounded-circle me-3 shadow-sm">
                    <i class="fas fa-shield-halved text-primary"></i>
                </div>
                <div class="text-dark small fw-600">
                    Changes are synchronized in real-time across the infrastructure. <span class="text-primary fw-800">Administrative capabilities</span> are secured and locked for safety.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(26, 26, 26, 0.05) !important; }
    .rounded-12 { border-radius: 12px !important; }
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
    .text-gold { color: #d4af37 !important; }
    .bg-gold { background-color: #d4af37 !important; }
    
    .smaller { font-size: 0.75rem; }
    
    .bg-glass { background-color: rgba(255, 255, 255, 0.05) !important; }
    .bg-white-5 { background-color: rgba(255, 255, 255, 0.05) !important; }
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.1) !important; }
    .border-white-10 { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .rounded-10 { border-radius: 10px !important; }
    .italic { font-style: italic; }

    .form-switch .form-check-input {
        width: 3.5em;
        height: 1.75em;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .form-switch .form-check-input:checked {
        background-color: #d4af37;
        border-color: #d4af37;
    }
    
    .form-switch .form-check-input:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(212, 175, 55, 0.02);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Coverage Radar Chart
    const ctx = document.getElementById('coverageChart').getContext('2d');
    const resources = @json($resources);
    const coverage = @json($coverage);
    
    const datasets = [];
    const colors = [
        'rgba(212, 175, 55, 0.7)', // Gold
        'rgba(59, 130, 246, 0.7)',  // Blue
        'rgba(16, 185, 129, 0.7)',  // Green
        'rgba(239, 68, 68, 0.7)',   // Red
        'rgba(168, 85, 247, 0.7)'   // Purple
    ];

    let i = 0;
    for (const [role, data] of Object.entries(coverage)) {
        datasets.push({
            label: role,
            data: data,
            backgroundColor: colors[i % colors.length],
            borderColor: colors[i % colors.length].replace('0.7', '1'),
            borderWidth: 2,
            pointBackgroundColor: '#fff'
        });
        i++;
    }

    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: resources,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: 'rgba(255, 255, 255, 0.1)' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    pointLabels: { color: '#fff', font: { weight: '600' } },
                    ticks: { display: false },
                    suggestedMin: 0,
                    suggestedMax: 100
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#fff', font: { weight: '600' } }
                }
            }
        }
    });

    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const roleId = this.dataset.roleId;
            const permissionId = this.dataset.permissionId;
            const isChecked = this.checked;
            const form = this.closest('form');
            const url = form.action;
            const csrfToken = form.querySelector('input[name="_token"]').value;

            // Show loading state
            this.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    permission_id: permissionId
                })
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    console.log(data.message);
                } else {
                    // Revert if failed
                    this.checked = !isChecked;
                    alert('Failed to update capability: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                this.disabled = false;
                this.checked = !isChecked;
                console.error('Error:', error);
                alert('A telemetry error occurred while updating capability.');
            });
        });
    });
});
</script>
@endsection
