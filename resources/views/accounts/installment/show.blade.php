@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Instrument Audit</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.installment.index') }}" class="text-decoration-none text-muted">Contracts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Contract #{{ $installment->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.installment.edit', $installment->id) }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-edit text-primary me-2"></i>Configure
                </a>
                <a href="{{ route('accounts.installment.index') }}" class="btn btn-premium-dark shadow-premium px-4 rounded-12 fw-700">
                    <i class="fas fa-arrow-left me-2"></i>Back to Portfolio
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Pulse Row -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-money-bill-wave text-primary opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <p class="text-muted small fw-800 text-uppercase mb-1 ls-1">Total Valuation</p>
                    <h3 class="fw-800 text-dark mb-0">Rs. {{ number_format($installment->total_amount, 2) }}</h3>
                    <div class="text-success smaller fw-700 mt-2">
                        <i class="fas fa-arrow-up me-1"></i>Projected Revenue
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-premium h-100 bg-premium-dark text-white overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-exclamation-circle text-gold opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <p class="text-white-50 small fw-800 text-uppercase mb-1 ls-1">Live Exposure</p>
                    <h3 class="fw-800 text-gold mb-0">Rs. {{ number_format($installment->outstanding_amount, 2) }}</h3>
                    <div class="text-gold smaller fw-700 mt-2">
                        <i class="fas fa-hourglass-half me-1"></i>Awaiting Recovery
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-check-double text-success opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <p class="text-muted small fw-800 text-uppercase mb-1 ls-1">Net Recovered</p>
                    <h3 class="fw-800 text-success mb-0">Rs. {{ number_format($installment->total_amount - $installment->outstanding_amount, 2) }}</h3>
                    <div class="text-muted smaller fw-700 mt-2">
                        <i class="fas fa-university me-1"></i>Vaulted Funds
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-percentage text-info opacity-10"></i></div>
                <div class="p-4 position-relative">
                    @php
                        $paid = $installment->total_amount - $installment->outstanding_amount;
                        $percentage = $installment->total_amount > 0 ? ($paid / $installment->total_amount) * 100 : 0;
                    @endphp
                    <p class="text-muted small fw-800 text-uppercase mb-1 ls-1">Contract Maturity</p>
                    <h3 class="fw-800 text-dark mb-2">{{ round($percentage) }}%</h3>
                    <div class="progress bg-light" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar bg-info shadow-sm" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Amortization Schedule -->
        <div class="col-xl-8 mb-5">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden h-100">
                <div class="card-header bg-white py-4 px-5 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-800 text-dark">Amortization Schedule</h5>
                        <p class="text-muted small mb-0 fw-600">Dynamic ledger of payment cycles and maturity</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-gold-soft text-gold rounded-pill px-3 py-2 fw-700 smaller">Cycle Count: {{ $installment->total_installments }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover-premium align-middle mb-0">
                        <thead class="bg-premium-dark text-white">
                            <tr>
                                <th class="ps-5 py-3 small text-uppercase ls-1">Cycle</th>
                                <th class="py-3 small text-uppercase ls-1">Maturity Date</th>
                                <th class="py-3 small text-uppercase ls-1 text-end">Due Amount</th>
                                <th class="py-3 small text-uppercase ls-1 text-end">Paid Amount</th>
                                <th class="py-3 small text-uppercase ls-1 text-center">Status</th>
                                <th class="pe-5 py-3 small text-uppercase ls-1 text-center">Protocol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($installment->schedules as $schedule)
                                <tr class="{{ $schedule->isOverdue() ? 'bg-danger-soft-row' : '' }}">
                                    <td class="ps-5 py-4">
                                        <div class="fw-900 text-dark">#{{ str_pad($schedule->sequence_number, 2, '0', STR_PAD_LEFT) }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="fw-800 text-dark">{{ $schedule->due_date->format('d M, Y') }}</div>
                                        @if($schedule->isOverdue())
                                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 smaller fw-700">OVERDUE</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-end">
                                        <div class="fw-800 text-dark">Rs. {{ number_format($schedule->amount + $schedule->late_fee, 2) }}</div>
                                        @if($schedule->late_fee > 0)
                                            <div class="text-warning smaller fw-700"><i class="fas fa-plus-circle me-1"></i>Rs. {{ number_format($schedule->late_fee, 2) }} Late Fee</div>
                                        @endif
                                    </td>
                                    <td class="py-4 text-end">
                                        @if($schedule->amount_paid > 0)
                                            <div class="fw-800 text-success">Rs. {{ number_format($schedule->amount_paid, 2) }}</div>
                                            <div class="text-muted smaller fw-600">Cleared</div>
                                        @else
                                            <span class="text-light-gray fw-800">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-center">
                                        @switch($schedule->status)
                                            @case('pending')
                                                <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-800 smaller">PENDING</span>
                                                @break
                                            @case('paid')
                                                <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-800 smaller">CLEARED</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-800 smaller">DEFAULTED</span>
                                                @break
                                            @case('partial')
                                                <span class="badge bg-info-soft text-info rounded-pill px-3 py-2 fw-800 smaller">PARTIAL</span>
                                                @break
                                            @case('skipped')
                                                <span class="badge bg-light text-muted rounded-pill px-3 py-2 fw-800 smaller">SKIPPED</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="pe-5 py-4 text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($schedule->status !== 'paid' && $schedule->status !== 'skipped')
                                                <button type="button" class="btn btn-icon-premium bg-gold text-dark" 
                                                        data-bs-toggle="modal" data-bs-target="#paymentModal"
                                                        data-schedule-id="{{ $schedule->id }}"
                                                        data-amount="{{ $schedule->amount + $schedule->late_fee - ($schedule->amount_paid ?? 0) }}"
                                                        data-sequence="{{ $schedule->sequence_number }}">
                                                    <i class="fas fa-cash-register"></i>
                                                </button>
                                                
                                                @if(!$schedule->reminder_sent_at)
                                                    <form action="{{ route('accounts.installment.send-reminder', $schedule->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-icon-premium" title="Dispatch Reminder">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="btn-icon-premium bg-success-soft text-success" title="Alert Sent">
                                                        <i class="fas fa-check-double"></i>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="btn-icon-premium bg-light text-muted opacity-50">
                                                    <i class="fas fa-lock"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-5 text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times text-muted fs-1 opacity-20 mb-3 d-block"></i>
                                            <p class="text-muted fw-600">No amortization cycles generated</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stakeholder & Protocol Sidebar -->
        <div class="col-xl-4 mb-5">
            <!-- Critical Alerts -->
            @if(count($overdueSchedules) > 0)
                <div class="card border-0 shadow-premium rounded-24 bg-danger text-white mb-4 overflow-hidden position-relative">
                    <div class="card-bg-icon"><i class="fas fa-exclamation-triangle text-white opacity-20"></i></div>
                    <div class="card-body p-4 position-relative z-1">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-800 mb-0">Critical Recovery</h5>
                            <span class="badge bg-white text-danger rounded-pill fw-900">{{ count($overdueSchedules) }} ALERT</span>
                        </div>
                        <div class="overdue-list">
                            @foreach($overdueSchedules->take(3) as $schedule)
                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-16 bg-white-10 border-white-10">
                                    <div>
                                        <div class="fw-800 small mb-0">Cycle #{{ $schedule->sequence_number }}</div>
                                        <div class="text-white-50 smaller fw-600">Due: {{ $schedule->due_date->format('d M Y') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-900">Rs. {{ number_format($schedule->amount, 2) }}</div>
                                        <div class="smaller fw-800 text-white-50">{{ \Carbon\Carbon::now()->diffInDays($schedule->due_date, false) }}d Late</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-white w-100 rounded-12 fw-800 text-danger mt-2 py-2">
                            <i class="fas fa-phone-alt me-2"></i>Initiate Recovery Call
                        </button>
                    </div>
                </div>
            @endif

            <!-- Stakeholder Profile -->
            <div class="card border-0 shadow-soft rounded-24 mb-4 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-dark">Stakeholder Profile</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-premium-dark text-gold rounded-circle d-flex align-items-center justify-content-center fw-900 fs-4 me-3" style="width: 50px; height: 50px;">
                            {{ substr($installment->customer->name ?? 'C', 0, 1) }}
                        </div>
                        <div>
                            <h5 class="fw-800 text-dark mb-0">{{ $installment->customer->name ?? 'N/A' }}</h5>
                            <p class="text-muted smaller fw-600 mb-0"><i class="fas fa-map-marker-alt text-gold me-1"></i>{{ $installment->customer->address ?? 'No address registered' }}</p>
                        </div>
                    </div>
                    
                    <div class="profile-meta p-3 bg-light rounded-20 mb-3">
                        <div class="row text-center g-0">
                            <div class="col-6 border-end">
                                <p class="text-muted smaller fw-800 text-uppercase mb-1">Mobile</p>
                                <p class="text-dark fw-800 mb-0">{{ $installment->customer->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted smaller fw-800 text-uppercase mb-1">Email</p>
                                <p class="text-dark fw-800 mb-0 text-truncate px-2">{{ $installment->customer->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-alert bg-gold-soft rounded-15 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-dark fw-800 smaller">Loyalty Status</span>
                            <span class="text-gold fw-900 smaller">GOLD TIER</span>
                        </div>
                        <div class="progress bg-white" style="height: 4px; border-radius: 10px;">
                            <div class="progress-bar bg-gold" style="width: 85%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recovery Insights -->
            <div class="card border-0 shadow-premium rounded-24 bg-premium-dark text-white overflow-hidden">
                <div class="card-header py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-gold text-uppercase ls-1">Recovery Insights</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="insight-item mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 smaller fw-700">CYCLES COMPLETED</span>
                            <span class="fw-900 text-gold">{{ $installment->paid_installments }} / {{ $installment->total_installments }}</span>
                        </div>
                        <div class="progress bg-white-10" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar bg-gold shadow-gold" style="width: {{ ($installment->paid_installments / $installment->total_installments) * 100 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="insight-item mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 smaller fw-700">NET EXPOSURE</span>
                            <span class="fw-900 text-danger">Rs. {{ number_format($installment->outstanding_amount, 2) }}</span>
                        </div>
                        <p class="text-white-50 smaller fw-600 mb-0">Target liquidation by {{ $installment->schedules->last()->due_date->format('M Y') }}</p>
                    </div>

                    <button class="btn btn-gold w-100 rounded-12 fw-800 py-2">
                        <i class="fas fa-file-invoice me-2"></i>Full Audit Statement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-premium rounded-24 overflow-hidden">
            <div class="modal-header bg-premium-dark py-4 px-4 border-0">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-gold-soft text-gold rounded-12 p-3 me-3">
                        <i class="fas fa-cash-register fs-4"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0 fw-800">Clear Cycle #<span id="display_sequence"></span></h5>
                        <p class="text-white-50 small mb-0 fw-600">Liquidate pending installment amount</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-glass">
                <form action="{{ route('accounts.installment.record-payment') }}" method="POST">
                    @csrf
                    <input type="hidden" name="schedule_id" id="modal_schedule_id">
                    
                    <div class="mb-4">
                        <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Collection Amount</label>
                        <div class="input-group glass-input-group">
                            <span class="input-group-text border-0 bg-transparent ps-3 text-gold fw-800">Rs.</span>
                            <input type="number" name="amount" id="modal_amount" class="form-control border-0 bg-transparent py-3 fw-900 fs-4" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Clearance Date</label>
                            <div class="input-group glass-input-group">
                                <input type="date" name="payment_date" class="form-control border-0 bg-transparent py-2 fw-700" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Payment Protocol</label>
                            <div class="input-group glass-input-group">
                                <select name="payment_method" class="form-select border-0 bg-transparent py-2 fw-700" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Card">Credit/Debit Card</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Reference / Transaction ID</label>
                        <div class="input-group glass-input-group">
                            <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-fingerprint text-gold"></i></span>
                            <input type="text" name="reference" class="form-control border-0 bg-transparent py-2 fw-700" placeholder="Optional audit ref">
                        </div>
                    </div>

                    <div class="d-grid pt-2">
                        <button type="submit" class="btn btn-gold py-3 rounded-12 fw-800 fs-5">
                            <i class="fas fa-check-circle me-2"></i>Finalize Collection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Styling */
    .fw-900 { font-weight: 900; }
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-20 { border-radius: 20px; }
    .rounded-16 { border-radius: 16px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .bg-danger-soft-row { background-color: rgba(220, 53, 69, 0.02) !important; }
    
    .bg-white-10 { background: rgba(255, 255, 255, 0.1); }
    .border-white-10 { border: 1px solid rgba(255, 255, 255, 0.1); }
    
    .text-gold { color: #d4af37; }
    .text-white-50 { color: rgba(255,255,255,0.5); }
    .text-light-gray { color: #e0e0e0; }
    
    .shadow-premium { box-shadow: 0 15px 50px -12px rgba(0,0,0,0.15); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #f1e5ac 50%, #c5a02e 100%);
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4); color: #1a1a1a; }

    .btn-white { background: #fff; border: 1px solid #f0f0f0; transition: all 0.3s ease; }
    .btn-white:hover { background: #f8f9fa; transform: translateY(-1px); }

    .btn-premium-dark { background: #1a1a1a; color: #fff; border: none; transition: all 0.3s ease; }
    .btn-premium-dark:hover { background: #000; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-2px); color: #fff; }

    .stat-card { background: white; border-radius: 24px; transition: all 0.3s ease; }
    .card-bg-icon { position: absolute; bottom: -20px; right: -10px; font-size: 80px; pointer-events: none; transition: all 0.5s ease; }
    
    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.001); }
    
    .btn-icon-premium {
        width: 38px; height: 38px; border-radius: 12px; display: inline-flex;
        align-items: center; justify-content: center; background: #f8f9fa;
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-icon-premium:hover { background: #1a1a1a; color: #fff; transform: rotate(10deg); }

    .glass-input-group {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .glass-input-group:focus-within {
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1);
        border-color: #d4af37;
    }

    .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
    .smaller { font-size: 0.75rem; }
    .empty-state .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const scheduleId = button.getAttribute('data-schedule-id');
                const amount = button.getAttribute('data-amount');
                const sequence = button.getAttribute('data-sequence');

                paymentModal.querySelector('#modal_schedule_id').value = scheduleId;
                paymentModal.querySelector('#modal_amount').value = amount;
                paymentModal.querySelector('#display_sequence').textContent = sequence;
            });
        }
    });
</script>
@endsection
