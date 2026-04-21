@extends('layouts.app')

@section('title', 'New Sale - POS Terminal')

@section('content')
<div class="mb-3">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>
<style>
    .erp-header {
        background: #fff;
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
        margin: -24px -24px 24px -24px;
    }
    .erp-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        box-shadow: var(--card-shadow);
        margin-bottom: 20px;
    }
    .erp-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-top-left-radius: var(--radius-md);
        border-top-right-radius: var(--radius-md);
    }
    .erp-card-body {
        padding: 16px;
    }
    .customer-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        background: #f8fafc;
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-sm);
    }
    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-uppercase;
        margin-bottom: 4px;
    }
    .info-value {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }
    .selection-card {
        cursor: pointer;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 12px;
        transition: all 0.2s;
        height: 100%;
    }
    .selection-card:hover {
        border-color: var(--secondary);
        background: #f8fafc;
    }
    .selection-card.active {
        border-color: var(--primary);
        background: #f0f7ff;
        border-width: 2px;
    }
    .status-active-dot {
        width: 10px;
        height: 10px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
</style>

<!-- ERP Header -->
<div class="erp-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item small"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item small"><a href="{{ route('pos.sales.index') }}">POS Terminal</a></li>
                    <li class="breadcrumb-item small active">New Sale</li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Initiate New Transaction</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos.sales.index') }}" class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-history me-1"></i> Sales History</a>
            <a href="{{ route('pos.customers.index') }}" class="btn btn-sm btn-gold"><i class="fas fa-users me-1"></i> Customers</a>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <div class="row">
        <div class="col-lg-8">
            <!-- Customer Selection -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <span class="fw-bold"><i class="fas fa-user-tag me-2 text-primary"></i>Customer Information</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="walkinToggle">
                        <label class="form-check-label small fw-bold" for="walkinToggle">Walk-in Customer</label>
                    </div>
                </div>
                <div class="erp-card-body">
                    <div id="customerSearchGroup">
                        <label class="form-label small fw-bold text-uppercase text-muted">Search Existing Customer</label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="customerSearch" class="form-control border-start-0 ps-0" placeholder="Type name, mobile number or email...">
                            </div>
                            <div id="customerResults" class="list-group shadow-lg mt-1 border-0" style="z-index: 1050; display: none; position: absolute; width: 100%; top: 100%; left: 0;"></div>
                        </div>
                    </div>

                    <div id="selectedCustomer" class="mt-3 d-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded me-3">
                                    <i class="fas fa-user-check fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold" id="cust_name"></h5>
                                    <span class="text-muted small" id="cust_details"></span>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-primary px-2 py-1" id="cust_type"></span>
                                <span class="badge bg-gold text-dark px-2 py-1 ms-1" id="cust_tier"></span>
                            </div>
                        </div>
                        
                        <div class="customer-info-grid">
                            <div>
                                <div class="info-label">Cash Balance</div>
                                <div class="info-value text-danger">Rs. <span id="cust_balance">0.00</span></div>
                            </div>
                            <div>
                                <div class="info-label">Gold Balance</div>
                                <div class="info-value text-warning"><span id="cust_gold">0.000</span> g</div>
                            </div>
                            <div>
                                <div class="info-label">Silver Balance</div>
                                <div class="info-value text-secondary"><span id="cust_silver">0.000</span> g</div>
                            </div>
                            <div>
                                <div class="info-label">Loyalty Points</div>
                                <div class="info-value text-primary"><span id="cust_points">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-4 rounded-0 border-0 border-start border-4 border-danger">
                    <ul class="mb-0 small fw-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Transaction Settings -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <span class="fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Transaction Configuration</span>
                </div>
                <div class="erp-card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Sale Channel <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="selection-card text-center active" data-group="sale_channel" data-value="consumer" id="card_channel_consumer">
                                        <i class="fas fa-user d-block mb-1 text-info"></i>
                                        <span class="small fw-bold">Consumer</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="selection-card text-center" data-group="sale_channel" data-value="wholesale" id="card_channel_wholesale">
                                        <i class="fas fa-users d-block mb-1 text-warning"></i>
                                        <span class="small fw-bold">Wholesale</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="sale_channel" id="sale_channel_input" value="consumer">
                        </div>
                        <script>
                            document.querySelectorAll('.selection-card[data-group="sale_channel"]').forEach(function(card) {
                                card.addEventListener('click', function() {
                                    document.querySelectorAll('.selection-card[data-group="sale_channel"]').forEach(function(c) {
                                        c.classList.remove('active');
                                    });
                                    card.classList.add('active');
                                    document.getElementById('sale_channel_input').value = card.getAttribute('data-value');
                                });
                            });
                        </script>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="status-active-dot"></span>
                            <span class="small fw-bold text-muted italic">Ready for transaction</span>
                        </div>
                        <form id="startSaleForm" action="{{ route('pos.sales.store') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="customer_id" id="pos_customer_id">
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? $branches->first()->id ?? 1 }}">
                            <input type="hidden" name="is_wholesale" id="is_wholesale_val" value="0">
                            <input type="hidden" name="invoice_type" id="invoice_type_val" value="Non-Tax">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" id="startSaleBtn" disabled>
                                <i class="fas fa-cash-register me-2"></i>OPEN TERMINAL
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <span class="fw-bold"><i class="fas fa-history me-2 text-primary"></i>Recent Terminal History</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 small fw-bold text-muted text-uppercase">Invoice</th>
                                <th class="small fw-bold text-muted text-uppercase">Customer</th>
                                <th class="small fw-bold text-muted text-uppercase">Amount</th>
                                <th class="text-end pe-3 small fw-bold text-muted text-uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $rsale)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $rsale->invoice_no }}</td>
                                <td class="text-muted">{{ $rsale->customer->name ?? 'Walk-in' }}</td>
                                <td class="fw-bold text-dark">Rs. {{ number_format($rsale->total, 2) }}</td>
                                <td class="text-end pe-3">
                                    @php
                                        $statusClass = match($rsale->status) {
                                            'completed' => 'success',
                                            'held' => 'warning',
                                            default => 'info'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}-soft text-{{ $statusClass }} rounded-pill px-2 py-1 small fw-bold">
                                        {{ strtoupper($rsale->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small fw-bold">No recent activity detected</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Instructions/Help -->
            <div class="erp-card bg-light border-0">
                <div class="erp-card-body">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-info-circle me-2"></i>Quick Guide</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Search for a customer by Name or Mobile to load their profiles and balances.</span>
                        </li>
                        <li class="mb-2 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Select <strong>Walk-in</strong> for anonymous sales where customer details aren't required.</span>
                        </li>
                        <li class="mb-2 d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Choose <strong>Wholesale</strong> channel for dealer-to-dealer transactions with tax benefits.</span>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span>Click <strong>Open Terminal</strong> to begin adding items to the cart.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Terminal Stats -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <span class="fw-bold"><i class="fas fa-microchip me-2 text-primary"></i>Terminal Status</span>
                </div>
                <div class="erp-card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Current Session</span>
                            <span class="fw-bold text-success">Active</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Operator</span>
                            <span class="fw-bold">{{ auth()->user()->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Terminal ID</span>
                            <span class="fw-bold">POS-{{ str_pad(auth()->user()->id, 3, '0', STR_PAD_LEFT) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Default Branch</span>
                            <span class="fw-bold text-primary">{{ auth()->user()->branch->name ?? 'Main' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const search = document.getElementById('customerSearch');
    const results = document.getElementById('customerResults');
    const selectedWrap = document.getElementById('selectedCustomer');
    const walkinToggle = document.getElementById('walkinToggle');
    const posCustomerId = document.getElementById('pos_customer_id');
    const startBtn = document.getElementById('startSaleBtn');
    const searchGroup = document.getElementById('customerSearchGroup');

    let debounceTimer = null;

    function formatCurrency(v){
        try { return Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}); } catch(e){ return v; }
    }

    function renderResults(items){
        results.innerHTML = '';
        if (!items || items.length === 0) {
            results.style.display = 'none';
            return;
        }
        items.forEach(item => {
            const a = document.createElement('a');
            a.href = '#';
            a.className = 'list-group-item list-group-item-action py-2';
            a.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">${item.name}</div>
                    <div class="small text-muted">${item.phone || ''}</div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary small">${item.customer_type}</span>
            </div>`;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                selectCustomer(item);
            });
            results.appendChild(a);
        });
        results.style.display = 'block';
    }

    function selectCustomer(item){
        document.getElementById('cust_name').textContent = item.name;
        document.getElementById('cust_details').textContent = `${item.phone || 'No Phone'} • ${item.email || 'No Email'}`;
        document.getElementById('cust_type').textContent = item.customer_type || '';
        document.getElementById('cust_tier').textContent = (item.vip_tier && item.vip_tier !== 'none') ? item.vip_tier : '';
        document.getElementById('cust_balance').textContent = formatCurrency(item.current_balance || 0);
        document.getElementById('cust_gold').textContent = (item.current_gold_balance || 0).toFixed(3);
        document.getElementById('cust_silver').textContent = (item.current_silver_balance || 0).toFixed(3);
        document.getElementById('cust_points').textContent = (item.loyalty_points || 0);

        selectedWrap.classList.remove('d-none');
        searchGroup.classList.add('d-none');
        posCustomerId.value = item.id;
        startBtn.disabled = false;
        walkinToggle.checked = false;
        results.style.display = 'none';
    }

    function clearSelection(){
        posCustomerId.value = '';
        startBtn.disabled = true;
        selectedWrap.classList.add('d-none');
        searchGroup.classList.remove('d-none');
        search.value = '';
    }

    search.addEventListener('input', function(){
        const q = this.value.trim();
        if (debounceTimer) clearTimeout(debounceTimer);
        if (q.length < 2) {
            results.style.display = 'none';
            return;
        }
        debounceTimer = setTimeout(async () => {
            try {
                const resp = await fetch(`{{ route('pos.customers.lookup') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();
                renderResults(Array.isArray(data) ? data : []);
            } catch (err) {
                results.style.display = 'none';
            }
        }, 250);
    });

    document.addEventListener('click', function(e){
        if (!results.contains(e.target) && e.target !== search) results.style.display = 'none';
    });

    walkinToggle.addEventListener('change', function(){
        if (this.checked) {
            clearSelection();
            startBtn.disabled = false;
            searchGroup.classList.add('d-none');
        } else {
            searchGroup.classList.remove('d-none');
            if (!posCustomerId.value) startBtn.disabled = true;
        }
    });

    const invTypeVal = document.getElementById('invoice_type_val');
    const isWholesaleVal = document.getElementById('is_wholesale_val');

    document.querySelectorAll('.selection-card').forEach(card => {
        card.addEventListener('click', function() {
            const group = this.dataset.group;
            const value = this.dataset.value;
            if (group === 'invoice_type') invTypeVal.value = value;
            else if (group === 'is_wholesale') isWholesaleVal.value = value;
            document.querySelectorAll(`.selection-card[data-group="${group}"]`).forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });
})();
</script>
@endsection
