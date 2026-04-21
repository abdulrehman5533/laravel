@extends('layouts.app')

@section('title', 'Girvi Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-file-invoice-dollar me-2 text-secondary"></i>Girvi #{{ $girvi->girvi_number }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('girvi.loans.print', $girvi) }}" target="_blank" class="btn btn-outline-dark">
                <i class="fas fa-print me-2"></i>Print Manifest
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#interestModal">
                <i class="fas fa-percentage me-2"></i>Post Interest
            </button>
            <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-plus me-2"></i>Record Payment
            </button>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#releaseModal">
                <i class="fas fa-hand-holding-usd me-2"></i>Release & Close
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#waiverModal"><i class="fas fa-hand-holding-heart me-2"></i>Request Waiver</button></li>
                    <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="fas fa-exchange-alt me-2"></i>Transfer Girvi</button></li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('payment_id'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 bg-success text-white" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">Payment Successful!</h5>
                    <p class="mb-0">Transaction has been recorded and accounts updated.</p>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('girvi.payments.receipt', session('payment_id')) }}" target="_blank" class="btn btn-light fw-bold">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </a>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Summary & Balances -->
        <div class="col-md-4">
            <div class="stat-card mb-4">
                <h5 class="fw-bold mb-4">Financial Overview</h5>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Principal Amount</span>
                    <span class="fw-bold">Rs. {{ number_format($girvi->loan_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-danger">
                    <span>Accrued Interest</span>
                    <span class="fw-bold">+ Rs. {{ number_format($girvi->interest_accrued, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-success">
                    <span>Total Paid</span>
                    <span class="fw-bold">- Rs. {{ number_format($girvi->principal_paid + $girvi->interest_paid, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h6 mb-0">Balance Due</span>
                    <span class="h4 fw-bold text-primary mb-0">Rs. {{ number_format($girvi->outstanding_amount, 2) }}</span>
                </div>
            </div>

            <div class="stat-card mb-4 border-start border-4 border-{{ $girvi->risk_score === 'High' ? 'danger' : ($girvi->risk_score === 'Medium' ? 'warning' : 'success') }}">
                <h5 class="fw-bold mb-3">Risk Analytics</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">LTV Ratio</span>
                    <span class="fw-bold {{ $girvi->ltv_ratio > 85 ? 'text-danger' : '' }}">{{ number_format($girvi->ltv_ratio, 1) }}%</span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-{{ $girvi->risk_score === 'High' ? 'danger' : ($girvi->risk_score === 'Medium' ? 'warning' : 'success') }}" 
                         role="progressbar" style="width: {{ min($girvi->ltv_ratio, 100) }}%"></div>
                </div>
                <p class="small text-muted mb-0">
                    <i class="fas fa-shield-alt me-1"></i>Risk Score: <strong>{{ $girvi->risk_score }}</strong>
                </p>
            </div>

            <div class="stat-card mb-4">
                <h5 class="fw-bold mb-4">Customer Info</h5>
                <p class="mb-1 fw-bold">{{ $girvi->customer->name }}</p>
                <p class="mb-1 text-muted"><i class="fas fa-phone me-2"></i>{{ $girvi->customer->phone }}</p>
                <p class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-2"></i>{{ $girvi->customer->address }}</p>
                <div class="mt-3">
                    <span class="badge bg-{{ $girvi->kyc_verified ? 'success' : 'danger' }}">
                        KYC {{ $girvi->kyc_verified ? 'VERIFIED' : 'PENDING' }}
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <h5 class="fw-bold mb-4">Girvi Status</h5>
                <div class="mb-3">
                    <label class="small text-muted d-block">Current Status</label>
                    <span class="badge bg-{{ $girvi->status === 'active' ? 'success' : ($girvi->status === 'settled' ? 'primary' : 'danger') }} fs-6">
                        {{ strtoupper($girvi->status) }}
                    </span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Interest Rate</label>
                    <span class="fw-bold">{{ $girvi->interest_rate }}% ({{ ucfirst($girvi->interest_cycle) }})</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Maturity Date</label>
                    <span class="fw-bold">{{ $girvi->maturity_date ? $girvi->maturity_date->format('d M Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Tabs -->
        <div class="col-md-8">
            <div class="stat-card">
                <ul class="nav nav-tabs mb-4" id="girviTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button">Pledged Items</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button">Payment History</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="interest-tab" data-bs-toggle="tab" data-bs-target="#interest" type="button">Interest Logs</button>
                    </li>
                </ul>

                <div class="tab-content" id="girviTabsContent">
                    <div class="tab-pane fade show active" id="items" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Item Name</th>
                                        <th>Barcode</th>
                                        <th>Type</th>
                                        <th>Weights (G/S/N)</th>
                                        <th>Fine Wt</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($girvi->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->item_photo)
                                                <img src="{{ asset('storage/' . $item->item_photo) }}" alt="Photo" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-camera"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $item->item_name }}</div>
                                            @if($item->inventory_product_id)
                                                <a href="{{ route('inventory.products.show', $item->inventory_product_id) }}" class="small text-primary text-decoration-none">
                                                    <i class="fas fa-box-open me-1"></i>Linked to Inventory
                                                </a>
                                            @endif
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark border small">{{ $item->item_condition ?? 'Good' }}</span>
                                                <small class="text-muted ms-1">{{ $item->description }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <code>{{ $item->barcode }}</code>
                                        </td>
                                        <td>{{ $item->item_type }} ({{ $item->purity }})</td>
                                        <td>{{ $item->gross_weight }}g / {{ $item->stone_weight }}g / {{ $item->net_weight }}g</td>
                                        <td class="fw-bold text-primary">{{ number_format($item->fine_weight, 3) }}g</td>
                                        <td class="fw-bold">Rs. {{ number_format($item->estimated_value, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="payments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Method</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($girvi->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                                        <td>Rs. {{ number_format($payment->principal_component, 2) }}</td>
                                        <td>Rs. {{ number_format($payment->interest_component, 2) }}</td>
                                        <td>{{ $payment->payment_method }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('girvi.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">No payments recorded.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="interest" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Post Date</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($girvi->interestPostings as $posting)
                                    <tr>
                                        <td>{{ $posting->posting_date->format('d M Y') }}</td>
                                        <td>{{ $posting->period_start->format('d/m/y') }} to {{ $posting->period_end->format('d/m/y') }}</td>
                                        <td>Rs. {{ number_format($posting->interest_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $posting->is_manual ? 'info' : 'secondary' }}">
                                                {{ $posting->is_manual ? 'MANUAL' : 'AUTO' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No interest postings.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('girvi.payment', $girvi) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waiver Amount</label>
                        <input type="number" name="waiver_amount" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference #</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gold w-100">Record Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Interest Modal -->
<div class="modal fade" id="interestModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('girvi.post-interest', $girvi) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manual Interest Posting</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-calculator fa-3x text-secondary mb-3"></i>
                    <p>Proceed to calculate and post interest for this Girvi until today?</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Proceed</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Release Modal -->
<div class="modal fade" id="releaseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('girvi.release', $girvi) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Release & Close Girvi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>Final interest will be posted automatically before release.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Closure Type</label>
                        <select name="closure_type" class="form-select" id="closureTypeSelect">
                            <option value="normal">Normal Release (Fully Paid)</option>
                            <option value="early">Early Settlement</option>
                            <option value="loss">Loss Settlement (Write-off)</option>
                            <option value="auction">Auction / Forced Closure</option>
                        </select>
                    </div>
                    <div id="finalPaymentSection">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Final Settlement Payment</h6>
                        <div class="mb-3">
                            <label class="form-label">Settlement Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="payment[amount]" class="form-control" value="{{ $girvi->outstanding_amount }}" step="0.01">
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Method</label>
                                <select name="payment[payment_method]" class="form-select">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="payment[payment_date]" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger w-100">Confirm Release</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Waiver Modal -->
<div class="modal fade" id="waiverModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('girvi.waiver-request', $girvi) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Interest Waiver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Waiver Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <small class="text-muted">Total Accrued: Rs. {{ number_format($girvi->interest_accrued - $girvi->interest_paid, 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Waiver</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Describe why this waiver is requested..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary w-100">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('closureTypeSelect').addEventListener('change', function() {
        const section = document.getElementById('finalPaymentSection');
        if (this.value === 'loss' || this.value === 'auction') {
            section.style.opacity = '0.5';
            section.querySelectorAll('input, select').forEach(el => el.disabled = true);
        } else {
            section.style.opacity = '1';
            section.querySelectorAll('input, select').forEach(el => el.disabled = false);
        }
    });
</script>
@endpush
