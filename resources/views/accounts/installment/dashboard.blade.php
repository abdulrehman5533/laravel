@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Installment Analytics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Revenue Recovery</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <form action="{{ route('accounts.installment.apply-late-fees') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                        <i class="fas fa-bolt text-warning me-2"></i>Late Fee Engine
                    </button>
                </form>
                <a href="{{ route('accounts.installment.index') }}" class="btn btn-premium-dark shadow-premium px-4 rounded-12 fw-700">
                    <i class="fas fa-list-ul me-2"></i>View All Plans
                </a>
                <a href="{{ route('accounts.installment.create') }}" class="btn btn-gold shadow-gold px-4 rounded-12 fw-700">
                    <i class="fas fa-plus-circle me-2"></i>New Instrument
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-file-invoice-dollar text-primary opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon bg-primary-soft text-primary">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <span class="badge bg-primary-soft text-primary rounded-pill px-2 py-1 smaller fw-700">ACTIVE</span>
                    </div>
                    <p class="text-muted small fw-800 text-uppercase mb-1 ls-1">Active Plans</p>
                    <h3 class="fw-800 text-dark mb-0">{{ $activeInstallments }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-wallet text-success opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon bg-success-soft text-success">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <span class="badge bg-success-soft text-success rounded-pill px-2 py-1 smaller fw-700">SECURITIZED</span>
                    </div>
                    <p class="text-muted small fw-800 text-uppercase mb-1 ls-1">Total Outstanding</p>
                    <h3 class="fw-800 text-dark mb-0">Rs. {{ number_format($totalOutstanding, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-premium h-100 bg-premium-dark text-white overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-clock text-gold opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon bg-gold text-dark">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <span class="badge bg-danger text-white rounded-pill px-2 py-1 smaller fw-700">OVERDUE</span>
                    </div>
                    <p class="text-white-50 small fw-800 text-uppercase mb-1 ls-1">Critical Recovery</p>
                    <h3 class="fw-800 text-gold mb-0">{{ $overdueSchedules }} Accounts</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-0 shadow-soft h-100 overflow-hidden position-relative group">
                <div class="card-bg-icon"><i class="fas fa-chart-pie text-info opacity-10"></i></div>
                <div class="p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stat-icon bg-info-soft text-info">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <span class="badge bg-info-soft text-info rounded-pill px-2 py-1 smaller fw-700">EFFICIENCY</span>
                    </div>
                    <p class="text-muted small fw-800 text-uppercase mb-1 ls-1">Recovery Rate</p>
                    <h3 class="fw-800 text-dark mb-2">{{ round($recoveryRate, 1) }}%</h3>
                    <div class="progress bg-light" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar bg-info" style="width: {{ $recoveryRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Due -->
        <div class="col-lg-7 mb-5">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden h-100">
                <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-800 text-dark">Due Today</h5>
                        <p class="text-muted small mb-0 fw-600">Pending collections for current business cycle</p>
                    </div>
                    <span class="badge bg-gold-soft text-gold rounded-pill px-3 py-2 fw-700">{{ count($todayDue) }} Pending</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover-premium align-middle mb-0">
                        <thead class="bg-premium-dark text-white">
                            <tr>
                                <th class="ps-4 py-3 small text-uppercase ls-1">Customer / Instrument</th>
                                <th class="py-3 small text-uppercase ls-1">Maturity Amount</th>
                                <th class="pe-4 py-3 small text-uppercase ls-1 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayDue as $due)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-box me-3">
                                                <div class="bg-gold-soft text-gold rounded-circle d-flex align-items-center justify-content-center fw-800" style="width: 40px; height: 40px;">
                                                    {{ substr($due->installment->customer->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-800 text-dark mb-0">{{ $due->installment->customer->name }}</div>
                                                <div class="text-muted smaller fw-600">Contract #{{ $due->installment_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-800 text-dark">Rs. {{ number_format($due->amount, 2) }}</div>
                                        <div class="text-gold smaller fw-700">Principal + Charges</div>
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        <a href="{{ route('accounts.installment.show', $due->installment_id) }}" class="btn btn-icon-premium">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-5 text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-check-circle text-success fs-1 opacity-20 mb-3 d-block"></i>
                                            <p class="text-muted fw-600">All cleared for today</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-lg-5 mb-5">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden h-100">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <h5 class="mb-0 fw-800 text-dark">Recent Collections</h5>
                    <p class="text-muted small mb-0 fw-600">Latest revenue stream activity</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentPayments as $payment)
                            <div class="list-group-item px-4 py-3 border-light group hover-bg-light transition-all">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-icon-box bg-success-soft text-success rounded-12 p-2 me-3">
                                            <i class="fas fa-check-double smaller"></i>
                                        </div>
                                        <div>
                                            <div class="fw-800 text-dark small mb-0">{{ $payment->installment->customer->name }}</div>
                                            <div class="text-muted smaller fw-600">{{ $payment->paid_date->format('d M, Y') }} • {{ $payment->payment_method }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-success fw-900">
                                            +Rs. {{ number_format($payment->amount_paid, 2) }}
                                        </div>
                                        <div class="text-muted smaller fw-700">Cleared</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-5 text-center">
                                <i class="fas fa-receipt fs-1 text-muted opacity-10 mb-3 d-block"></i>
                                <p class="text-muted fw-600">No recent transactions</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if(count($recentPayments) > 0)
                    <div class="card-footer bg-light py-3 text-center border-0">
                        <a href="{{ route('accounts.installment.index') }}" class="text-secondary fw-800 small text-decoration-none">
                            Audit Full History <i class="fas fa-arrow-right ms-2 smaller"></i>
                        </a>
                    </div>
                @endif
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
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    
    .text-secondary { color: #d4af37 !important; }
    .text-gold { color: #d4af37; }
    
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .shadow-gold { box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25); }
    .shadow-premium { box-shadow: 0 15px 50px -12px rgba(0,0,0,0.15); }

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
    .stat-card:hover { transform: translateY(-5px); }
    .card-bg-icon { position: absolute; bottom: -20px; right: -10px; font-size: 80px; pointer-events: none; transition: all 0.5s ease; }
    .stat-card:hover .card-bg-icon { transform: scale(1.1) rotate(-10deg); opacity: 0.15 !important; }

    .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }

    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; cursor: pointer; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.002); }
    
    .btn-icon-premium {
        width: 38px; height: 38px; border-radius: 12px; display: inline-flex;
        align-items: center; justify-content: center; background: #f8f9fa;
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-icon-premium:hover { background: #1a1a1a; color: #fff; transform: rotate(10deg); }

    .smaller { font-size: 0.75rem; }
    .transition-all { transition: all 0.3s ease; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
</style>
@endsection
