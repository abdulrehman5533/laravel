@extends('layouts.app')

@section('title', 'Professional Girvi Creation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark"><i class="fas fa-file-invoice-dollar me-2 text-gold"></i>Professional Girvi Management</h2>
                <div class="text-muted">
                    <span class="me-3"><i class="fas fa-clock me-1"></i> {{ date('l, d M Y') }}</span>
                    <span class="badge bg-gold px-3 py-2">Production Ready</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('girvi.loans.store') }}" method="POST" enctype="multipart/form-data" id="girviForm">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Main Form -->
            <div class="col-lg-9">
                <!-- 1. Customer & Loan Basics -->
                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-user-circle me-2 text-primary"></i>1. Customer & Loan Information</h5>
                    </div>
                    <div class="card-body bg-light-subtle">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Select Customer</label>
                                <select name="customer_id" id="customer_id" class="form-select select2" required>
                                    <option value="">Search by Name, Phone or ID...</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Branch</label>
                                <select name="branch_id" class="form-select border-0 shadow-sm" required>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Girvi Date</label>
                                <input type="date" name="girvi_date" class="form-control border-0 shadow-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Principal Amount</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-0">Rs.</span>
                                    <input type="number" name="loan_amount" id="loan_amount" class="form-control border-0" step="0.01" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Interest Rate (% p.m.)</label>
                                <div class="input-group shadow-sm">
                                    <input type="number" name="interest_rate" id="interest_rate" class="form-control border-0" step="0.01" value="2.00" required>
                                    <span class="input-group-text bg-white border-0">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Interest Type</label>
                                <select name="interest_type" class="form-select border-0 shadow-sm">
                                    <option value="simple">Simple Interest</option>
                                    <option value="compound">Compound Interest</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Maturity Period</label>
                                <select id="maturity_preset" class="form-select border-0 shadow-sm">
                                    <option value="">Custom Date</option>
                                    <option value="3">3 Months</option>
                                    <option value="6" selected>6 Months</option>
                                    <option value="12">12 Months (1 Year)</option>
                                </select>
                                <input type="hidden" name="maturity_date" id="maturity_date">
                            </div>

                            <!-- New Production Fields -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loan Purpose</label>
                                <input type="text" name="loan_purpose" class="form-control border-0 shadow-sm" placeholder="e.g. Business, Medical, Education">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" id="auto_renew" value="1">
                                    <label class="form-check-label fw-bold" for="auto_renew">Auto-Renew on Maturity</label>
                                </div>
                            </div>

                            <!-- Guarantor Details Section -->
                            <div class="col-12 mt-3">
                                <div class="p-3 bg-white rounded border border-light">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-user-shield me-2 text-info"></i>Guarantor Details (Optional)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label small">Guarantor Name</label>
                                            <input type="text" name="guarantor_name" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Phone Number</label>
                                            <input type="text" name="guarantor_phone" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">ID Type</label>
                                            <select name="guarantor_id_type" class="form-select form-select-sm">
                                                <option value="">Select ID Type</option>
                                                <option value="Aadhar">Aadhar / ID Card</option>
                                                <option value="PAN">PAN / Tax ID</option>
                                                <option value="Voter ID">Voter ID</option>
                                                <option value="Passport">Passport</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">ID Number</label>
                                            <input type="text" name="guarantor_id_number" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Pledged Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-gem me-2 text-warning"></i>2. Pledged Items Asset Declaration</h5>
                        <div class="d-flex gap-2">
                            <div style="width: 300px;">
                                <select id="inventorySearch" class="form-select select2-inventory" placeholder="Pick from Inventory..."></select>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm px-3" id="addItem">
                                <i class="fas fa-plus me-1"></i> Manual Add
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="itemsContainer" class="p-3 bg-light-subtle">
                            <!-- Items will be injected here as professional cards -->
                        </div>
                        <div id="emptyState" class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No items added yet. Use "Quick Add" or "Manual Add" to begin.</p>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Barcodes will be auto-generated upon submission.</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="me-3">Total Items: <strong id="totalItemsCount">0</strong></span>
                                <span>Total Gross Wt: <strong id="totalGrossWeightText">0.000g</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Risk & Finalization -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="kyc_verified" id="kycCheck" value="1" required>
                                    <label class="form-check-label fw-bold" for="kycCheck">I confirm that KYC documents have been verified and physical items are checked for authenticity.</label>
                                </div>
                                <textarea name="internal_notes" class="form-control bg-light border-0" rows="2" placeholder="Internal risk assessment notes or special conditions..."></textarea>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="submit" name="print_after_save" value="1" class="btn btn-outline-primary btn-lg px-4 py-3 shadow-sm me-2">
                                    <i class="fas fa-print me-2"></i> Finalize & Print
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-3 shadow">
                                    <i class="fas fa-check-double me-2"></i> Finalize Loan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Analytics & Sidebar -->
            <div class="col-lg-3">
                <!-- Market Rates -->
                <div class="card border-0 shadow-sm mb-4 bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-danger">LIVE MARKET</span>
                            <small class="opacity-75">Source: MCX</small>
                        </div>
                        <div class="mb-3">
                            <label class="small opacity-75 d-block uppercase">Locked Gold Rate (24K/10g)</label>
                            <h3 class="fw-bold mb-0 text-gold">Rs. {{ number_format($goldRate * 10, 2) }}</h3>
                            <input type="hidden" name="locked_gold_rate" value="{{ $goldRate }}">
                        </div>
                        <div>
                            <label class="small opacity-75 d-block">Silver Rate/kg</label>
                            <h5 class="fw-bold mb-0">Rs. {{ number_format($silverRate * 1000, 2) }}</h5>
                            <input type="hidden" name="locked_silver_rate" value="{{ $silverRate }}">
                        </div>
                    </div>
                </div>

                <!-- Loan Analytics Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold">Loan Analytics</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total Asset Value</span>
                            <span class="fw-bold" id="totalValuationText">Rs. 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted small">LTV Ratio</span>
                            <span class="fw-bold" id="ltvRatioText">0.0%</span>
                        </div>
                        <div class="progress mb-3" style="height: 10px; border-radius: 5px;">
                            <div id="ltvProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="ltvWarning" class="alert alert-danger py-2 small d-none shadow-sm">
                            <i class="fas fa-exclamation-triangle me-2"></i> Risk: LTV Exceeds Limit!
                        </div>
                        <hr>
                        <div class="mt-3">
                            <h6 class="small fw-bold text-muted mb-3">PROJECTED INTEREST</h6>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Monthly Interest:</span>
                                <span class="fw-bold text-danger" id="monthlyInterestText">Rs. 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>6 Months Total:</span>
                                <span class="fw-bold" id="sixMonthInterestText">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer History Sidebar -->
                <div id="customerHistoryCard" class="card border-0 shadow-sm d-none">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="card-title mb-0 fw-bold">Customer History</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Active Loans:</span>
                                <span class="badge bg-danger" id="activeLoansCount">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small text-muted">Total O/S:</span>
                                <span class="fw-bold" id="customerOutstandingText">Rs. 0</span>
                            </div>
                        </div>
                        <div id="customerLoanList" class="list-group list-group-flush small overflow-auto" style="max-height: 200px;">
                            <!-- Loans will be listed here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template for Item Row (Card-style) -->
<template id="itemTemplate">
    <div class="item-row card border-0 shadow-sm mb-3 position-relative overflow-hidden">
        <div class="row g-0">
            <div class="col-md-2 bg-light d-flex align-items-center justify-content-center p-2 border-end border-light item-image-container">
                <i class="fas fa-camera fa-2x text-muted opacity-50"></i>
            </div>
            <div class="col-md-10">
                <div class="card-body p-3">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item" title="Remove Item"></button>
                    <input type="hidden" class="inventory_product_id" name="items[INDEX][inventory_product_id]">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small text-muted mb-1 d-block">Item Name & Brand</label>
                            <input type="text" name="items[INDEX][item_name]" class="form-control form-control-sm item-name fw-bold border-0 bg-light" placeholder="e.g. Gold Bangle" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted mb-1 d-block">Type</label>
                            <select name="items[INDEX][item_type]" class="form-select form-select-sm item-type-select border-0 bg-light">
                                <option value="Gold">Gold</option>
                                <option value="Silver">Silver</option>
                                <option value="Diamond">Diamond</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted mb-1 d-block">Purity</label>
                            <input type="text" name="items[INDEX][purity]" class="form-control form-control-sm purity-input border-0 bg-light text-center" placeholder="22K" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-1 d-block">Asset Condition</label>
                            <select name="items[INDEX][item_condition]" class="form-select form-select-sm border-0 bg-light">
                                <option value="New">New / Mint</option>
                                <option value="Good" selected>Good / Used</option>
                                <option value="Fair">Fair / Worn</option>
                                <option value="Poor">Poor / Dented</option>
                                <option value="Broken">Broken / Scrap</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-0 small">Gross</span>
                                <input type="number" name="items[INDEX][gross_weight]" class="form-control border-0 bg-light gross-weight" step="0.001" placeholder="0.000" required>
                                <span class="input-group-text bg-white border-0 small">g</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-0 small">Stones</span>
                                <input type="number" name="items[INDEX][stone_weight]" class="form-control border-0 bg-light stone-weight" step="0.001" value="0.000">
                                <span class="input-group-text bg-white border-0 small">g</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-0 small">Net</span>
                                <input type="number" class="form-control border-0 bg-white net-weight" step="0.001" value="0.000" readonly>
                                <span class="input-group-text bg-white border-0 small">g</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-primary text-white border-0 small">Valuation</span>
                                <input type="number" name="items[INDEX][estimated_value]" class="form-control border-0 bg-white fw-bold estimated-value" step="0.01" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <input type="text" name="items[INDEX][description]" class="form-control form-control-sm border-0 bg-light-subtle italic" placeholder="Add specific markings, hallmarks, or damage notes...">
                        </div>
                        <div class="col-md-4">
                            <input type="file" name="items[INDEX][item_photo]" class="form-control form-control-sm border-0 bg-light" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@push('styles')
<style>
    .bg-gold { background-color: #d4af37; color: white; }
    .text-gold { color: #d4af37; }
    .bg-light-subtle { background-color: #f8f9fa; }
    .select2-container--bootstrap-5 .select2-selection { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .item-row { transition: transform 0.2s, box-shadow 0.2s; }
    .item-row:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important; }
    .item-image-container img { width: 100%; height: 100%; object-fit: cover; }
    .italic { font-style: italic; font-size: 0.85rem; }
</style>
@endpush

@push('scripts')
<script>
    let itemIndex = 0;
    const goldRate = parseFloat($('input[name="locked_gold_rate"]').val()) || 0;
    const silverRate = parseFloat($('input[name="locked_silver_rate"]').val()) || 0;

    $(document).ready(function() {
        // Initialize Select2 for Customer
        $('#customer_id').select2({
            theme: 'bootstrap-5',
            width: '100%'
        }).on('change', function() {
            loadCustomerHistory($(this).val());
        });

        // Initialize Inventory Search
        $('.select2-inventory').select2({
            theme: 'bootstrap-5',
            ajax: {
                url: "{{ route('girvi.lookup-products') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) { return { results: data }; },
                cache: true
            },
            placeholder: 'Quick Add from Inventory (Name, SKU, Barcode)...',
            minimumInputLength: 2,
            templateResult: formatProductResult,
            templateSelection: (p) => p.name || p.text
        }).on('select2:select', function(e) {
            addNewRow(e.params.data);
            $(this).val(null).trigger('change');
        });

        // Maturity Date Auto-calculation
        $('#maturity_preset').on('change', function() {
            calculateMaturity();
        });
        calculateMaturity(); // Initial call

        // Manual Add Button
        $('#addItem').on('click', () => addNewRow());

        // Dynamic Event Delegation for Calculations
        $('#itemsContainer').on('input', '.gross-weight, .stone-weight, .purity-input, .item-type-select', function() {
            const row = $(this).closest('.item-row');
            calculateRow(row);
        });

        $('#itemsContainer').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                updateEmptyState();
                calculateTotalAnalytics();
            });
        });

        $('#loan_amount, #interest_rate').on('input', function() {
            calculateTotalAnalytics();
        });
    });

    function formatProductResult(p) {
        if (p.loading) return p.text;
        return $(`
            <div class="d-flex align-items-center">
                <img src="${p.image || 'https://via.placeholder.com/40'}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                <div>
                    <div class="fw-bold">${p.name}</div>
                    <small class="text-muted">${p.type} | ${p.purity} | ${p.gross_weight}g</small>
                </div>
            </div>
        `);
    }

    function addNewRow(data = null) {
        $('#emptyState').addClass('d-none');
        const container = document.getElementById('itemsContainer');
        const template = document.getElementById('itemTemplate').innerHTML;
        const html = template.replace(/INDEX/g, itemIndex);
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const newRow = tempDiv.firstElementChild;
        container.appendChild(newRow);

        if (data) {
            $(newRow).find('.inventory_product_id').val(data.id);
            $(newRow).find('.item-name').val(data.name);
            $(newRow).find('.item-type-select').val(data.type);
            $(newRow).find('.gross-weight').val(data.gross_weight);
            $(newRow).find('.purity-input').val(data.purity);
            
            if (data.image) {
                $(newRow).find('.item-image-container').html(`<img src="${data.image}" class="rounded">`);
            }
        }

        itemIndex++;
        calculateRow($(newRow));
        updateEmptyState();
    }

    function calculateRow(row) {
        const type = row.find('.item-type-select').val();
        const gross = parseFloat(row.find('.gross-weight').val()) || 0;
        const stone = parseFloat(row.find('.stone-weight').val()) || 0;
        const purityStr = row.find('.purity-input').val() || '22K';
        
        const net = Math.max(0, gross - stone);
        row.find('.net-weight').val(net.toFixed(3));

        // Purity Parsing
        let purity = 22;
        if(purityStr.toLowerCase().includes('24')) purity = 24;
        else if(purityStr.toLowerCase().includes('22')) purity = 22;
        else if(purityStr.toLowerCase().includes('18')) purity = 18;
        else purity = parseFloat(purityStr) || 22;

        const fine = (net * (purity > 24 ? purity / 100 * 24 : purity)) / 24;
        
        let value = 0;
        if (type === 'Gold') {
            value = fine * goldRate;
        } else if (type === 'Silver') {
            const silverPurity = purity > 24 ? purity : (purity / 24 * 100);
            value = (net * silverPurity / 100) * silverRate;
        } else {
            // Diamond - Placeholder for manual valuation or cost-based
            value = net * 5000; // Default placeholder
        }

        row.find('.estimated-value').val(value.toFixed(2));
        calculateTotalAnalytics();
    }

    function calculateTotalAnalytics() {
        let totalValuation = 0;
        let totalGross = 0;
        let count = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            totalValuation += parseFloat(row.querySelector('.estimated-value').value) || 0;
            totalGross += parseFloat(row.querySelector('.gross-weight').value) || 0;
            count++;
        });

        const loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
        const ltv = totalValuation > 0 ? (loanAmount / totalValuation * 100) : 0;
        const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;

        // Update UI
        $('#totalItemsCount').text(count);
        $('#totalGrossWeightText').text(totalGross.toFixed(3) + 'g');
        $('#totalValuationText').text('Rs. ' + totalValuation.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#ltvRatioText').text(ltv.toFixed(1) + '%');
        
        const progressBar = $('#ltvProgressBar');
        progressBar.css('width', Math.min(ltv, 100) + '%');
        
        if (ltv > 85) {
            progressBar.removeClass().addClass('progress-bar bg-danger');
            $('#ltvWarning').removeClass('d-none');
        } else if (ltv > 75) {
            progressBar.removeClass().addClass('progress-bar bg-warning');
            $('#ltvWarning').removeClass('d-none');
        } else {
            progressBar.removeClass().addClass('progress-bar bg-success');
            $('#ltvWarning').addClass('d-none');
        }

        // Interest Projections
        const monthlyInterest = (loanAmount * interestRate) / 100;
        $('#monthlyInterestText').text('Rs. ' + monthlyInterest.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#sixMonthInterestText').text('Rs. ' + (monthlyInterest * 6).toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    function calculateMaturity() {
        const months = $('#maturity_preset').val();
        if (!months) return;

        const date = new Date();
        date.setMonth(date.getMonth() + parseInt(months));
        const formatted = date.toISOString().split('T')[0];
        $('#maturity_date').val(formatted);
    }

    function loadCustomerHistory(customerId) {
        if (!customerId) {
            $('#customerHistoryCard').addClass('d-none');
            return;
        }

        const url = "{{ route('girvi.customers.history', ':id') }}".replace(':id', customerId);
        $.get(url, function(data) {
            $('#customerHistoryCard').removeClass('d-none');
            $('#activeLoansCount').text(data.count);
            $('#customerOutstandingText').text('Rs. ' + data.total_outstanding.toLocaleString());
            
            let html = '';
            data.loans.forEach(loan => {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 border-light">
                        <div>
                            <div class="fw-bold">#${loan.number}</div>
                            <div class="text-muted small">${loan.date}</div>
                        </div>
                        <span class="fw-bold">Rs. ${loan.amount.toLocaleString()}</span>
                    </div>
                `;
            });
            $('#customerLoanList').html(html || '<div class="p-3 text-center text-muted">No active loans</div>');
        });
    }

    function updateEmptyState() {
        if (document.querySelectorAll('.item-row').length > 0) {
            $('#emptyState').addClass('d-none');
        } else {
            $('#emptyState').removeClass('d-none');
        }
    }
</script>
@endpush
@endsection
