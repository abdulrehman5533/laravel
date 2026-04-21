@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">Instrument Issuance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.accounting-dashboard.index') }}" class="text-decoration-none text-muted">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounts.installment.index') }}" class="text-decoration-none text-muted">Contracts</a></li>
                    <li class="breadcrumb-item active fw-600 text-secondary" aria-current="page">New Instrument</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-print-none">
            <a href="{{ route('accounts.installment.index') }}" class="btn btn-white shadow-sm border-0 rounded-12 fw-600">
                <i class="fas fa-arrow-left me-2"></i>Back to Portfolio
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-premium rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-premium-dark py-4 px-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-gold-soft text-gold rounded-12 p-3 me-3">
                            <i class="fas fa-file-signature fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-0 fw-800">Contract Configuration</h5>
                            <p class="text-white-50 small mb-0 fw-600">Define financial terms and recovery schedule</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 bg-glass">
                    <form method="POST" action="{{ route('accounts.installment.store') }}">
                        @csrf

                        <!-- Row 1: Customer and Plan -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Customer / Stakeholder</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-user text-gold"></i></span>
                                    <select name="customer_id" class="form-select border-0 bg-transparent py-2 @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Stakeholder</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->phone ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('customer_id')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Recovery Structure</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-calendar-alt text-gold"></i></span>
                                    <select name="plan_id" class="form-select border-0 bg-transparent py-2 @error('plan_id') is-invalid @enderror" required>
                                        <option value="">Select Plan</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
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

                        <!-- Row 2: Amount and Down Payment -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Total Valuation</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3 text-gold fw-800">Rs.</span>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control border-0 bg-transparent py-2 @error('total_amount') is-invalid @enderror"
                                           value="{{ old('total_amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                                @error('total_amount')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Initial Deposit</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3 text-gold fw-800">Rs.</span>
                                    <input type="number" name="down_payment_amount" id="down_payment_amount" class="form-control border-0 bg-transparent py-2 @error('down_payment_amount') is-invalid @enderror"
                                           value="{{ old('down_payment_amount', 0) }}" step="0.01" min="0" placeholder="0.00">
                                </div>
                                @error('down_payment_amount')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Row 3: Start Date -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Execution Start Date</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-clock text-gold"></i></span>
                                    <input type="date" name="start_date" class="form-control border-0 bg-transparent py-2 @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date', date('Y-m-d')) }}" required>
                                </div>
                                @error('start_date')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-800 text-muted mb-2 text-uppercase ls-1">Audit Notes</label>
                                <div class="input-group glass-input-group">
                                    <span class="input-group-text border-0 bg-transparent ps-3"><i class="fas fa-pen text-gold"></i></span>
                                    <input type="text" name="internal_notes" class="form-control border-0 bg-transparent py-2 @error('internal_notes') is-invalid @enderror" placeholder="Official remarks" value="{{ old('internal_notes') }}">
                                </div>
                                @error('internal_notes')
                                    <div class="text-danger smaller fw-700 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Calculation Info -->
                        <div class="card bg-premium-dark border-0 rounded-20 mb-5 overflow-hidden">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h6 class="text-gold mb-0 fw-800 text-uppercase ls-1"><i class="fas fa-calculator me-2"></i>Financial Projection</h6>
                                    <span class="badge bg-gold text-dark rounded-pill px-3 py-1 fw-800 smaller">REAL-TIME ENGINE</span>
                                </div>
                                <div class="row text-center">
                                    <div class="col-4 border-end border-white-10">
                                        <p class="text-white-50 smaller fw-700 text-uppercase mb-2 ls-1">Amount to Finance</p>
                                        <h4 class="fw-900 text-white mb-0" id="calc_financed">Rs. 0.00</h4>
                                    </div>
                                    <div class="col-4 border-end border-white-10">
                                        <p class="text-white-50 smaller fw-700 text-uppercase mb-2 ls-1">Total Cycles</p>
                                        <h4 class="fw-900 text-gold mb-0" id="calc_count">0</h4>
                                    </div>
                                    <div class="col-4">
                                        <p class="text-white-50 smaller fw-700 text-uppercase mb-2 ls-1">Monthly Recovery</p>
                                        <h4 class="fw-900 text-success mb-0" id="calc_monthly">Rs. 0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-3 pt-4 border-top">
                            <button type="submit" class="btn btn-gold px-5 py-3 rounded-12 fw-800">
                                <i class="fas fa-check-circle me-2"></i>Finalize & Issue Instrument
                            </button>
                            <a href="{{ route('accounts.installment.index') }}" class="btn btn-premium-dark px-4 py-3 rounded-12 fw-700">
                                <i class="fas fa-times me-2"></i>Abort
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar: Help/Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-soft rounded-24 overflow-hidden mb-4">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-dark">
                        <i class="fas fa-shield-alt text-gold me-2"></i>Issuance Protocol
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="protocol-item mb-4 d-flex align-items-start">
                        <div class="step-number bg-gold text-dark rounded-circle me-3 fw-900">1</div>
                        <div>
                            <h6 class="fw-800 text-dark mb-1 small uppercase ls-1">Verification</h6>
                            <p class="text-muted smaller fw-600 mb-0">Ensure stakeholder creditworthiness and AML compliance.</p>
                        </div>
                    </div>
                    <div class="protocol-item mb-4 d-flex align-items-start">
                        <div class="step-number bg-gold text-dark rounded-circle me-3 fw-900">2</div>
                        <div>
                            <h6 class="fw-800 text-dark mb-1 small uppercase ls-1">Structuring</h6>
                            <p class="text-muted smaller fw-600 mb-0">Define total valuation and select appropriate recovery cycle.</p>
                        </div>
                    </div>
                    <div class="protocol-item mb-4 d-flex align-items-start">
                        <div class="step-number bg-gold text-dark rounded-circle me-3 fw-900">3</div>
                        <div>
                            <h6 class="fw-800 text-dark mb-1 small uppercase ls-1">Validation</h6>
                            <p class="text-muted smaller fw-600 mb-0">Review the financial projection summary for accuracy.</p>
                        </div>
                    </div>

                    <div class="glass-alert bg-gold-soft rounded-15 p-3 mb-0">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle text-gold me-2"></i>
                            <h6 class="text-dark fw-800 mb-0 smaller">Critical Notice</h6>
                        </div>
                        <p class="text-dark-50 smaller fw-600 mb-0">Once issued, the amortization schedule is automatically generated and recorded in the audit trail.</p>
                    </div>
                </div>
            </div>

            <!-- Available Plans -->
            <div class="card border-0 shadow-premium rounded-24 bg-premium-dark text-white overflow-hidden">
                <div class="card-header py-4 px-4 border-0">
                    <h6 class="mb-0 fw-800 text-gold text-uppercase ls-1">
                        <i class="fas fa-layer-group me-2"></i>Active Frameworks
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($plans as $plan)
                            <div class="list-group-item bg-transparent border-white-10 px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-800 text-white mb-0">{{ $plan->name }}</h6>
                                    <span class="badge bg-gold text-dark rounded-pill fw-800 smaller">{{ $plan->number_of_installments }}M</span>
                                </div>
                                <div class="d-flex gap-3">
                                    <span class="text-white-50 smaller fw-600"><i class="fas fa-sync me-1"></i>{{ ucfirst($plan->frequency) }}</span>
                                    @if($plan->interest_rate)
                                        <span class="text-gold smaller fw-800"><i class="fas fa-percentage me-1"></i>{{ $plan->interest_rate }}% APR</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center">
                                <p class="text-white-50 smaller fw-600 mb-0">No active frameworks found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
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
    .rounded-15 { border-radius: 15px; }
    .rounded-12 { border-radius: 12px; }
    
    .bg-premium-dark { background: #1a1a1a; }
    .bg-gold-soft { background: rgba(212, 175, 55, 0.1); }
    .bg-white-5 { background: rgba(255, 255, 255, 0.05); }
    .border-white-10 { border: 1px solid rgba(255, 255, 255, 0.1); }
    
    .text-gold { color: #d4af37; }
    .text-white-50 { color: rgba(255,255,255,0.5); }
    .text-dark-50 { color: rgba(0,0,0,0.5); }
    
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
    .step-number { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0; }
    
    .smaller { font-size: 0.75rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalInput = document.getElementById('total_amount');
        const downPaymentInput = document.getElementById('down_payment_amount');
        const planSelect = document.querySelector('select[name="plan_id"]');
        
        const calcFinanced = document.getElementById('calc_financed');
        const calcCount = document.getElementById('calc_count');
        const calcMonthly = document.getElementById('calc_monthly');

        function updateCalculation() {
            const total = parseFloat(totalInput.value) || 0;
            const downPayment = parseFloat(downPaymentInput.value) || 0;
            const financed = Math.max(0, total - downPayment);
            
            let installments = 0;
            const selectedOption = planSelect.options[planSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const text = selectedOption.text;
                const match = text.match(/\((\d+)\s+months\)/);
                if (match) installments = parseInt(match[1]);
            }

            const monthly = installments > 0 ? financed / installments : 0;

            calcFinanced.textContent = 'Rs. ' + financed.toLocaleString(undefined, {minimumFractionDigits: 2});
            calcCount.textContent = installments;
            calcMonthly.textContent = 'Rs. ' + monthly.toLocaleString(undefined, {minimumFractionDigits: 2});
        }

        totalInput.addEventListener('input', updateCalculation);
        downPaymentInput.addEventListener('input', updateCalculation);
        planSelect.addEventListener('change', updateCalculation);
        
        updateCalculation();
    });
</script>
@endsection
