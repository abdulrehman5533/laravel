@extends('layouts.app')

@section('title', 'AI Stock Intelligence | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="fas fa-brain me-2 text-primary"></i>AI Stock Intelligence & Aging</h2>
        <p class="text-muted">Advisory system predicting slow-moving items and capital risks.</p>
    </div>

    <!-- KPI Summary -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small opacity-75">Dead Stock Items</h6>
                    <h2 class="fw-bold mb-0">{{ $stats['dead_stock'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small opacity-75">Slow Moving Items</h6>
                    <h2 class="fw-bold mb-0">{{ $stats['slow_moving'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small opacity-75">High Risk Value</h6>
                    <h2 class="fw-bold mb-0">Rs.{{ number_format($stats['high_risk_value'], 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Stock Heatmap / Aging Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Stock Aging Heatmap</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Item</th>
                                <th>Age (Days)</th>
                                <th>Movement</th>
                                <th>Risk Score</th>
                                <th>AI Suggestions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </td>
                                <td>{{ $product->getAgeInDays() }}</td>
                                <td>
                                    @php
                                        $speedColor = match($product->intelligence->movement_speed) {
                                            'fast' => 'success',
                                            'medium' => 'info',
                                            'slow' => 'warning',
                                            'dead' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $speedColor }}-soft text-{{ $speedColor }}">
                                        {{ strtoupper($product->intelligence->movement_speed) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 10px; width: 100px;">
                                        @php
                                            $riskColor = $product->intelligence->risk_score > 70 ? 'bg-danger' : ($product->intelligence->risk_score > 40 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="progress-bar {{ $riskColor }}" style="width: {{ $product->intelligence->risk_score }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $product->intelligence->risk_score }}%</small>
                                </td>
                                <td>
                                    @if($product->intelligence->ai_suggestions)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                View Actions
                                            </button>
                                            <ul class="dropdown-menu shadow border-0">
                                                @foreach($product->intelligence->ai_suggestions as $suggestion)
                                                <li>
                                                    <div class="dropdown-item py-2">
                                                        <div class="fw-bold text-{{ $suggestion['type'] === 'critical' ? 'danger' : ($suggestion['type'] === 'warning' ? 'warning' : 'primary') }}">
                                                            {{ $suggestion['action'] }}
                                                        </div>
                                                        <small class="text-muted d-block text-wrap" style="width: 200px;">{{ $suggestion['reason'] }}</small>
                                                    </div>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted small">No suggestions</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Intelligence Insights -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Branch Demand Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="branchChart" height="200"></canvas>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2"></i>Strategic Insights</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-info-circle mt-1 me-3"></i>
                            <p class="small mb-0">Dead stock increased by 5% this month. Consider metal conversion to recover capital.</p>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-info-circle mt-1 me-3"></i>
                            <p class="small mb-0">High demand detected in Branch B for "Necklace" category. Suggest transfer from HQ.</p>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-info-circle mt-1 me-3"></i>
                            <p class="small mb-0">AI predicts gold price surge. Holding premium 22K inventory is advisable.</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Data Integrity</span>
                        <span class="text-success fw-bold">Optimal</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Scanning Accuracy</span>
                        <span class="text-success fw-bold">99.8%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>AI Confidence</span>
                        <span class="text-primary fw-bold">High</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const branchCtx = document.getElementById('branchChart').getContext('2d');
        const branchData = @json($stats['branch_distribution']);
        
        new Chart(branchCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(branchData),
                datasets: [{
                    data: Object.values(branchData),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush
