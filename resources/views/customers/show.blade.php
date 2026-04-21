@extends('layouts.app')

@section('title', 'Client Profile Intelligence - ' . $customer->full_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Client Profile Intelligence</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none text-muted">Registry</a></li>
                    <li class="breadcrumb-item active fw-600" aria-current="page">{{ $customer->customer_code }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('customers.index') }}" class="btn btn-white shadow-sm px-4 py-2 border-0 rounded-12 fw-600">
                    <i class="fas fa-arrow-left me-2 text-secondary"></i>Back to Registry
                </a>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-gold shadow-gold px-4 py-2 rounded-12 fw-700">
                    <i class="fas fa-edit me-2"></i>Modify Credentials
                </a>
                <a href="{{ route('customers.export-pdf', $customer) }}" class="btn btn-premium-dark px-4 py-2 rounded-12 fw-700 text-white" target="_blank">
                    <i class="fas fa-file-pdf me-2 text-gold"></i>Audit Report
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Profile -->
        <div class="col-xl-3 col-lg-4">
            <div class="stat-card border-0 text-center p-4 mb-4">
                <div class="position-relative d-inline-block mb-4">
                    <div class="avatar-circle bg-premium-dark text-gold mx-auto shadow-gold" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 800;">
                        {{ strtoupper(substr($customer->first_name, 0, 1)) }}
                    </div>
                    @if($customer->is_active)
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white border-4 rounded-circle p-2" title="Active Account"></span>
                    @else
                        <span class="position-absolute bottom-0 end-0 bg-danger border border-white border-4 rounded-circle p-2" title="Inactive Account"></span>
                    @endif
                </div>
                <h4 class="fw-800 text-dark mb-1">{{ $customer->full_name }}</h4>
                <p class="text-muted small fw-700 text-uppercase mb-3">{{ $customer->customer_code }}</p>
                
                <div class="mb-4">
                    <span class="badge bg-gold-light text-gold rounded-pill px-3 py-2 fw-800" style="font-size: 0.75rem;">
                        <i class="fas fa-crown me-1"></i> {{ strtoupper($customer->membership_level) }} MEMBER
                    </span>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-gold w-100 py-2 rounded-12 fw-700 small shadow-sm" onclick="addLoyaltyPoints()">
                        <i class="fas fa-plus-circle me-1"></i> Add Points
                    </button>
                    <button class="btn btn-white border w-100 py-2 rounded-12 fw-700 small text-dark" onclick="deductLoyaltyPoints()">
                        <i class="fas fa-minus-circle me-1 text-warning"></i> Deduct Points
                    </button>
                </div>
            </div>

            <div class="stat-card border-0 p-4">
                <h6 class="fw-800 text-dark mb-4 text-uppercase small">Contact Intelligence</h6>
                <div class="space-y-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-gold-light text-gold me-3 mb-0" style="width: 35px; height: 35px; font-size: 14px;">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-600">Phone Connection</div>
                            <div class="fw-800 text-dark">{{ $customer->phone ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-gold-light text-gold me-3 mb-0" style="width: 35px; height: 35px; font-size: 14px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-600">Digital Identity</div>
                            <div class="fw-800 text-dark" style="word-break: break-all; font-size: 0.85rem;">{{ $customer->email ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-gold-light text-gold me-3 mb-0" style="width: 35px; height: 35px; font-size: 14px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-600">Geographic Node</div>
                            <div class="fw-700 text-dark small">{{ $customer->full_address ?: 'No registry entry' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Strategic Metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card border-0 p-4">
                        <div class="text-muted small fw-700 text-uppercase mb-2">Total Volume</div>
                        <h3 class="fw-800 text-dark mb-0">Rs. {{ number_format($customer->total_purchases, 0) }}</h3>
                        <div class="text-success small fw-700 mt-2">
                            <i class="fas fa-arrow-up me-1"></i> Lifecycle Value
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 p-4">
                        <div class="text-muted small fw-700 text-uppercase mb-2">Manifest Count</div>
                        <h3 class="fw-800 text-dark mb-0">{{ $customer->sales->count() }}</h3>
                        <div class="text-muted small fw-600 mt-2">Closed Transactions</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 p-4 bg-premium-dark text-white overflow-hidden">
                        <div class="card-bg-icon"><i class="fas fa-award text-gold opacity-10"></i></div>
                        <div class="text-gold-light small fw-700 text-uppercase mb-2">Rewards Balance</div>
                        <h3 class="fw-800 text-white mb-0">{{ number_format($customer->loyalty_points) }}</h3>
                        <div class="text-gold small fw-700 mt-2">Elite Points</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 p-4">
                        <div class="text-muted small fw-700 text-uppercase mb-2">Current Liability</div>
                        <h3 class="fw-800 {{ $customer->current_balance > 0 ? 'text-danger' : 'text-success' }} mb-0">Rs. {{ number_format($customer->current_balance, 0) }}</h3>
                        <div class="text-muted small fw-600 mt-2">Limit: Rs. {{ number_format($customer->credit_limit, 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="stat-card border-0 mb-4 p-0 overflow-hidden">
                <div class="card-header bg-transparent border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-800 text-dark mb-0">
                            <i class="fas fa-history text-gold me-2"></i>Recent Transaction Manifests
                        </h5>
                        <a href="{{ route('customers.purchase-history', $customer) }}" class="btn btn-white btn-sm fw-700 px-3 py-2 rounded-8">
                            View Historical Log
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 fw-700 text-muted small text-uppercase">Manifest ID</th>
                                <th class="fw-700 text-muted small text-uppercase">Financials</th>
                                <th class="text-center fw-700 text-muted small text-uppercase">Volume</th>
                                <th class="fw-700 text-muted small text-uppercase">Settlement</th>
                                <th class="fw-700 text-muted small text-uppercase">Timestamp</th>
                                <th class="pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->sales->take(5) as $sale)
                            <tr class="group">
                                <td class="ps-4">
                                    <div class="fw-800 text-dark">{{ $sale->invoice_no }}</div>
                                    <div class="text-muted small fw-600">Ref: #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td>
                                    <div class="fw-800 text-dark">Rs. {{ number_format($sale->total, 2) }}</div>
                                </td>
                                <td class="text-center fw-700 text-dark">{{ $sale->items->count() }} items</td>
                                <td>
                                    @if($sale->status === 'completed')
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-700" style="font-size: 0.65rem;">SETTLED</span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-700" style="font-size: 0.65rem;">{{ strtoupper($sale->status) }}</span>
                                    @endif
                                </td>
                                <td class="fw-600 text-dark small">
                                    {{ $sale->created_at->format('M d, Y') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-white btn-sm rounded-8" title="View Manifest">
                                        <i class="fas fa-external-link-alt text-primary small"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="opacity-25 mb-3">
                                        <i class="fas fa-receipt fa-3x"></i>
                                    </div>
                                    <h6 class="fw-800 text-muted">No Transaction History</h6>
                                    <p class="text-muted small mb-0">No entries detected in the current lifecycle.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Extended Intelligence -->
            <div class="stat-card border-0 mb-4 p-0 overflow-hidden">
                <div class="card-header bg-transparent border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-800 text-dark mb-0">
                            <i class="fas fa-comments text-gold me-2"></i>Relationship Interactions
                        </h5>
                        <a href="{{ route('crm.customers.interactions', $customer) }}" class="btn btn-white btn-sm fw-700 px-3 py-2 rounded-8">
                            Interaction Registry
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 fw-700 text-muted small text-uppercase">Channel</th>
                                <th class="fw-700 text-muted small text-uppercase">Observation</th>
                                <th class="fw-700 text-muted small text-uppercase">Operator</th>
                                <th class="pe-4 fw-700 text-muted small text-uppercase">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->interactions->take(3) as $interaction)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-gold-light text-gold rounded-pill px-3 py-1 small fw-700">
                                        <i class="fas fa-{{ $interaction->getInteractionTypeIcon() }} me-1"></i>
                                        {{ strtoupper($interaction->interaction_type) }}
                                    </span>
                                </td>
                                <td class="small fw-600 text-dark" style="max-width: 300px;">
                                    {{ Str::limit($interaction->description, 60) }}
                                </td>
                                <td class="small fw-700 text-muted">
                                    {{ $interaction->createdBy->name ?? 'System' }}
                                </td>
                                <td class="pe-4 small fw-600 text-dark text-end">
                                    {{ $interaction->interaction_date->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small fw-600">No interaction signals detected</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-card border-0 p-4 h-100">
                        <h6 class="fw-800 text-dark mb-4 text-uppercase small"><i class="fas fa-info-circle text-gold me-2"></i>Profile Metadata</h6>
                        <div class="space-y-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-600 small">Account Type</span>
                                <span class="fw-700 text-dark small">{{ ucfirst($customer->customer_type) }}</span>
                            </div>
                            @if($customer->company_name)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-600 small">Corporate Identity</span>
                                <span class="fw-700 text-dark small text-end">{{ $customer->company_name }}</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-600 small">Strategic Discount</span>
                                <span class="fw-800 text-gold small">{{ $customer->special_discount_percentage }}%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-600 small">Gender Classifier</span>
                                <span class="fw-700 text-dark small">{{ $customer->gender ? ucfirst($customer->gender) : 'Unspecified' }}</span>
                            </div>
                            @if($customer->date_of_birth)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-600 small">Temporal Origin (DOB)</span>
                                <span class="fw-700 text-dark small">{{ $customer->date_of_birth->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card border-0 p-4 h-100">
                        <h6 class="fw-800 text-dark mb-4 text-uppercase small"><i class="fas fa-sticky-note text-gold me-2"></i>Operator Observations</h6>
                        <div class="bg-light rounded-12 p-3 fw-600 text-muted small" style="min-height: 120px;">
                            {{ $customer->notes ?: 'No observational data recorded in current session.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjustment Modals -->
<div class="modal fade" id="loyaltyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-20 overflow-hidden">
            <div class="modal-header bg-premium-dark border-0 p-4">
                <h5 class="modal-title fw-800 text-white" id="loyaltyModalTitle">Intelligence Adjustment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="loyaltyForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <div class="text-muted small fw-700 text-uppercase mb-1">Current Points</div>
                        <h2 class="fw-800 text-gold mb-0">{{ number_format($customer->loyalty_points) }}</h2>
                    </div>
                    
                    <div class="mb-3">
                        <label for="points" class="form-label fw-700 text-muted small text-uppercase">Delta Value (Points)</label>
                        <input type="number" name="points" id="points" class="form-control rounded-12 fw-800 py-3" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-700 text-muted small text-uppercase">Strategic Justification</label>
                        <textarea name="reason" id="reason" class="form-control rounded-12 fw-600" rows="3" placeholder="State reason for intelligence modification..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-white rounded-12 fw-700 px-4 py-2" data-bs-dismiss="modal">Abort</button>
                    <button type="submit" class="btn btn-gold rounded-12 fw-800 px-4 py-2 shadow-sm">COMMIT ADJUSTMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .text-gold { color: #d4af37 !important; }
    .text-gold-light { color: #f1e5ac !important; }
    .bg-gold { background: var(--gold-gradient) !important; }
    .bg-gold-light { background: rgba(212, 175, 55, 0.1) !important; }
    .bg-premium-dark { background: #1a1a1a !important; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
    .stat-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    .rounded-8 { border-radius: 8px !important; }
    .fw-800 { font-weight: 800 !important; }
    .fw-700 { font-weight: 700 !important; }
    .fw-600 { font-weight: 600 !important; }
    .shadow-gold { box-shadow: 0 10px 30px -5px rgba(212, 175, 55, 0.3) !important; }
    .avatar-circle { border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .btn-white { background: white; border: 1px solid #edf2f7; transition: all 0.3s; }
    .btn-white:hover { background: #f9fafb; transform: translateY(-1px); }
    .table-hover tr.group:hover { background-color: #fcfaf2 !important; }
</style>

<script>
const loyaltyModal = new bootstrap.Modal(document.getElementById('loyaltyModal'));

function addLoyaltyPoints() {
    document.getElementById('loyaltyModalTitle').textContent = 'Incentivize Client (Add Points)';
    document.getElementById('loyaltyForm').action = '{{ route("customers.add-loyalty-points", $customer) }}';
    document.getElementById('points').removeAttribute('max');
    loyaltyModal.show();
}

function deductLoyaltyPoints() {
    document.getElementById('loyaltyModalTitle').textContent = 'Reclaim Incentive (Deduct Points)';
    document.getElementById('loyaltyForm').action = '{{ route("customers.deduct-loyalty-points", $customer) }}';
    document.getElementById('points').setAttribute('max', '{{ $customer->loyalty_points }}');
    loyaltyModal.show();
}
</script>
@endsection
