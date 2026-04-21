@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Configure Instrument</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.installment.index') }}" class="text-decoration-none text-muted">Contracts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">Revise Contract</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <a href="{{ route('accounts.installment.show', $installment->id) }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                <i class="fas fa-eye me-2 text-primary"></i>Inspect Live
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark py-4 px-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-gold-soft text-gold rounded-12 p-3 me-3">
                            <i class="fas fa-edit fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-0 fw-800">Operational Revision</h5>
                            <p class="text-white-50 small mb-0 fw-600">Adjusting terms for Contract #{{ $installment->id }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 bg-glass">
                    <form method="POST" action="{{ route('accounts.installment.update', $installment->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Row 1: Customer and Plan -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Primary Stakeholder</label>
                                <div class="input-group glass-input-group readonly">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-user-lock text-muted"></i></span>
                                    <input type="text" class="form-control border-0 bg-transparent py-2 fw-700" value="{{ $installment->customer->name ?? 'N/A' }}" readonly>
                                </div>
                                <div class="text-muted smaller fw-600 mt-1">Immutable stakeholder identity</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Recovery Framework</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-calendar-alt text-gold"></i></span>
                                    <select name="plan_id" class="form-select border-0 bg-transparent py-2 @error('plan_id') is-invalid @enderror" required>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ $installment->plan_id == $plan->id ? 'selected' : '' }}>
                                                {{ $plan->name }} ({{ $plan->number_of_installments }} months)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('plan_id')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Row 2: Amounts -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Revised Total Valuation</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3 text-gold fw-800">Rs.</span>
                                    <input type="number" name="total_amount" class="form-control border-0 bg-transparent py-2 fw-800 @error('total_amount') is-invalid @enderror"
                                           value="{{ old('total_amount', $installment->total_amount) }}" step="0.01" min="0.01" required>
                                </div>
                                @error('total_amount')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Current Exposure (Outstanding)</label>
                                <div class="input-group glass-input-group readonly">
                                    <span class="input-group-text border-0 bg-transparent ps-3 text-danger fw-800">Rs.</span>
                                    <input type="text" class="form-control border-0 bg-transparent py-2 fw-800" value="{{ number_format($installment->outstanding_amount, 2) }}" readonly>
                                </div>
                                <div class="text-muted smaller fw-600 mt-1">Dynamic balance tracking</div>
                            </div>
                        </div>

                        <!-- Row 3: Status -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Operational Status</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-signal text-gold"></i></span>
                                    <select name="status" class="form-select border-0 bg-transparent py-2 @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ $installment->status == 'active' ? 'selected' : '' }}>ACTIVE</option>
                                        <option value="completed" {{ $installment->status == 'completed' ? 'selected' : '' }}>SETTLED</option>
                                        <option value="cancelled" {{ $installment->status == 'cancelled' ? 'selected' : '' }}>VOID</option>
                                        <option value="defaulted" {{ $installment->status == 'defaulted' ? 'selected' : '' }}>DEFAULTED</option>
                                    </select>
                                </div>
                                @error('status')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Original Execution Date</label>
                                <div class="input-group glass-input-group readonly">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="text" class="form-control border-0 bg-transparent py-2 fw-700" value="{{ $installment->start_date->format('d M, Y') }}" readonly>
                                </div>
                                <div class="text-muted smaller fw-600 mt-1">Historical baseline</div>
                            </div>
                        </div>

                        <div class="glass-alert bg-gold-soft rounded-15 p-4 mb-5 border-gold">
                             <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-gold fs-4 me-3"></i>
                                <div>
                                    <h6 class="text-dark fw-800 mb-1">Contract Integrity Check</h6>
                                    <p class="text-dark-50 smaller fw-600 mb-0">The system will recalculate future amortizations based on the revised total valuation. Historical cleared cycles will remain recorded.</p>
                                </div>
                             </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-3 pt-4 border-top">
                            <button type="submit" class="btn btn-gold px-5 py-3 rounded-12 fw-800">
                                <i class="fas fa-save me-2"></i>Apply Revisions
                            </button>
                            <a href="{{ route('accounts.installment.show', $installment->id) }}" class="btn btn-premium-dark px-4 py-3 rounded-12 fw-700">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-dark">Snapshot Info</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="snapshot-item mb-3 p-3 bg-light rounded-16">
                        <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Monthly Recovery</label>
                        <h5 class="fw-800 text-dark mb-0">Rs. {{ number_format($installment->installment_amount, 2) }}</h5>
                    </div>
                    <div class="snapshot-item mb-3 p-3 bg-light rounded-16">
                        <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Amortization Period</label>
                        <h5 class="fw-800 text-dark mb-0">{{ $installment->total_installments }} Months</h5>
                    </div>
                    <div class="snapshot-item p-3 bg-light rounded-16">
                        <label class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Inception Date</label>
                        <h5 class="fw-800 text-dark mb-0">{{ $installment->created_at->format('d M Y') }}</h5>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-premium rounded-24 bg-premium-dark text-white overflow-hidden">
                <div class="card-header py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-gold text-uppercase ls-1">
                        <i class="fas fa-history me-2"></i>Revision Rules
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex mb-3">
                            <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                            <span class="smaller text-white-50 fw-600">Total amount changes impact only future unpaid installments.</span>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                            <span class="smaller text-white-50 fw-600">Stakeholder assignment is permanent to ensure audit trail.</span>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                            <span class="smaller text-white-50 fw-600">Voiding a contract cancels all future pending recovery cycles.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Styling */
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ls-1 { letter-spacing: 1px; }
    .rounded-24 { border-radius: 24px; }
    .rounded-16 { border-radius: 16px; }
    .rounded-15 { border-radius: 15px; }
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .border-gold { border: 1px solid rgba(212, 175, 55, 0.2) !important; }
    
    .text-gold { color: #d4af37; }
    .text-white-50 { color: rgba(255,255,255,0.5); }
    .text-dark-50 { color: rgba(0,0,0,0.5); }
    
    .shadow-premium { box-shadow: 0 15px 50px -12px rgba(0,0,0,0.15); }
    .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

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
    .glass-input-group.readonly { background: #f8f9fa; }

    .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
    .smaller { font-size: 0.75rem; }
</style>
@endsection
