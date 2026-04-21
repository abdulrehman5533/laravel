@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Plan Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Contract Hub</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <div class="d-flex justify-content-md-end gap-2">
                <a href="{{ route('accounts.installment.dashboard') }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                    <i class="fas fa-chart-line text-primary me-2"></i>Analytics
                </a>
                <a href="{{ route('accounts.installment.create') }}" class="btn btn-gold shadow-gold px-4 rounded-12 fw-700">
                    <i class="fas fa-plus-circle me-2"></i>New Contract
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="card border-0 shadow-premium rounded-24 mb-5 overflow-hidden">
        <div class="card-header bg-premium-dark py-3 px-4">
            <h6 class="text-white mb-0 fw-700 small text-uppercase ls-1">
                <i class="fas fa-filter me-2 text-gold"></i>Audit Filter
            </h6>
        </div>
        <div class="card-body p-4 bg-glass">
            <form method="GET" class="row g-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Contract Status</label>
                    <div class="input-group glass-input-group">
                        <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-signal text-gold"></i></span>
                        <select name="status" class="form-select border-0 bg-transparent py-2">
                            <option value="">All Operational Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="defaulted" {{ request('status') == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-gold w-100 rounded-12 py-2 fw-700 h-100">
                        <i class="fas fa-magic me-2"></i>Apply Analytics
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('accounts.installment.index') }}" class="btn btn-premium-dark w-100 rounded-12 py-2 h-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Installments Table -->
    <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-5">
        <div class="card-header bg-white py-4 px-5 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-800 text-dark">Contract Portfolio</h5>
                <p class="text-muted small mb-0 fw-600">Overview of all active and historical installment plans</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark rounded-pill px-3 py-2 fw-700 shadow-sm border">
                    <i class="fas fa-file-contract me-2 text-gold"></i>{{ $installments->total() }} Contracts
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover-premium align-middle mb-0">
                    <thead class="bg-premium-dark text-white">
                        <tr>
                            <th class="ps-5 py-3 small text-uppercase ls-1">Stakeholder</th>
                            <th class="py-3 small text-uppercase ls-1">Structure</th>
                            <th class="py-3 small text-uppercase ls-1">Financials</th>
                            <th class="py-3 small text-uppercase ls-1" style="width: 200px;">Liquidation Progress</th>
                            <th class="py-3 small text-uppercase ls-1 text-center">Status</th>
                            <th class="pe-5 py-3 small text-uppercase ls-1 text-center">Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installments as $installment)
                            <tr>
                                <td class="ps-5 py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-box me-3">
                                            <div class="bg-gold-soft text-gold rounded-12 d-flex align-items-center justify-content-center fw-800" style="width: 42px; height: 42px;">
                                                {{ substr($installment->customer->name ?? 'C', 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-800 text-dark mb-0">{{ $installment->customer->name ?? 'N/A' }}</div>
                                            <div class="text-muted smaller fw-600">{{ $installment->customer->email ?? 'No email provided' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="fw-800 text-dark mb-0">{{ $installment->plan->name ?? 'Standard Plan' }}</div>
                                    <div class="text-muted smaller fw-600">{{ $installment->total_installments }} Cycles • {{ $installment->start_date->format('d M Y') }}</div>
                                </td>
                                <td class="py-4">
                                    <div class="d-flex flex-column">
                                        <div class="fw-800 text-dark small mb-1">Total: Rs. {{ number_format($installment->total_amount, 2) }}</div>
                                        <div class="text-danger fw-700 smaller">Bal: Rs. {{ number_format($installment->outstanding_amount, 2) }}</div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    @php
                                        $paid = $installment->total_amount - $installment->outstanding_amount;
                                        $percentage = $installment->total_amount > 0 ? ($paid / $installment->total_amount) * 100 : 0;
                                    @endphp
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="smaller fw-800 text-dark">{{ round($percentage) }}%</span>
                                            <span class="smaller text-muted fw-600">Recovered</span>
                                        </div>
                                        <div class="progress bg-light" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-success shadow-sm" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 text-center">
                                    @switch($installment->status)
                                        @case('active')
                                            <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-800 smaller">ACTIVE</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-info-soft text-info rounded-pill px-3 py-2 fw-800 smaller">SETTLED</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-2 fw-800 smaller">VOID</span>
                                            @break
                                        @case('defaulted')
                                            <span class="badge bg-premium-dark text-white rounded-pill px-3 py-2 fw-800 smaller">DEFAULTED</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-muted rounded-pill px-3 py-2 fw-800 smaller">{{ strtoupper($installment->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="pe-5 py-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('accounts.installment.show', $installment->id) }}" class="btn btn-icon-premium" title="Examine">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('accounts.installment.edit', $installment->id) }}" class="btn btn-icon-premium text-info" title="Configure">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        @if($installment->status === 'active')
                                            <form method="POST" action="{{ route('accounts.installment.destroy', $installment->id) }}" class="d-inline" onsubmit="return confirm('Archive this contract?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon-premium text-danger" title="Archive">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-center">
                                    <div class="empty-state p-5">
                                        <div class="icon-circle bg-gold-soft mb-4 mx-auto">
                                            <i class="fas fa-folder-open text-gold fs-1"></i>
                                        </div>
                                        <h5 class="fw-800 text-dark">Portfolio Empty</h5>
                                        <p class="text-muted fw-600">No installment contracts found matching your audit criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Hub -->
    <div class="d-flex justify-content-center mt-5">
        {{ $installments->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    /* Premium Styling */
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    
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

    .glass-input-group {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .glass-input-group:focus-within {
        background: #fff;
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1);
        border-color: #d4af37;
    }

    .table-hover-premium tbody tr { border-bottom: 1px solid #f8f9fa; transition: all 0.2s ease; cursor: pointer; }
    .table-hover-premium tbody tr:hover { background-color: #fdfdfd; transform: scale(1.002); }
    
    .btn-icon-premium {
        width: 36px; height: 36px; border-radius: 10px; display: inline-flex;
        align-items: center; justify-content: center; background: #f8f9fa;
        color: #1a1a1a; border: none; transition: all 0.3s ease;
    }
    .btn-icon-premium:hover { background: #1a1a1a; color: #fff; transform: rotate(10deg); }

    .bg-glass { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); }
    .smaller { font-size: 0.75rem; }
    .empty-state .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
</style>
@endsection
