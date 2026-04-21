@extends('layouts.app')

@section('title', 'Weight & Price Calculator - ' . config('app.name', 'MAGIA LUPOS'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-1">
                <i class="fas fa-calculator me-2" style="color: var(--primary);"></i>
                Weight & Price Calculator
            </h2>
            <p class="text-muted">Professional Gold, Silver & Gemstone Pricing Calculator</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('calculator.history') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-history me-2"></i>Calculation History
            </a>
            <button class="btn btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#presetsOffcanvas">
                <i class="fas fa-star me-2"></i>Presets
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Input Form -->
        <div class="col-lg-7">
            <!-- Weight Input -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-weight me-2"></i>Weight Input
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Gross Weight</label>
                            <div class="input-group">
                                <input type="number" step="0.0001" id="grossWeight" class="form-control form-control-lg" placeholder="Enter weight" value="10">
                                <select id="weightUnit" class="form-select">
                                    <option value="g" selected>Grams (g)</option>
                                    <option value="tola">Tola</option>
                                    <option value="ratti">Ratti</option>
                                    <option value="carat">Carat (ct)</option>
                                    <option value="mg">Milligrams (mg)</option>
                                    <option value="oz">Troy Ounce</option>
                                </select>
                            </div>
                            <small class="text-muted">1 Tola = 11.66g | 1 Ratti = 0.12g | 1 Carat = 0.2g</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ratti Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="rattiType" id="sunari" value="sunari" checked>
                                <label class="btn btn-outline-primary" for="sunari">Sunari</label>

                                <input type="radio" class="btn-check" name="rattiType" id="pakki" value="pakki">
                                <label class="btn btn-outline-primary" for="pakki">Pakki (1.5x)</label>
                            </div>
                            <small class="text-muted d-block mt-2">Pakki Ratti = 1.5 × Sunari Ratti</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metal Properties -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-crown me-2"></i>Metal Properties
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Karat</label>
                            <select id="karat" class="form-select form-select-lg">
                                <option value="24">24K - 99.9% Pure</option>
                                <option value="22" selected>22K - 91.7% Pure</option>
                                <option value="21">21K - 87.5% Pure</option>
                                <option value="20">20K - 83.3% Pure</option>
                                <option value="18">18K - 75% Pure</option>
                                <option value="14">14K - 58.3% Pure</option>
                                <option value="10">10K - 41.7% Pure</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Rate per Gram (Rs.)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="ratePerGram" class="form-control form-control-lg" placeholder="0.00" value="5500">
                                <button class="btn btn-outline-info" type="button" id="fetchRateBtn">
                                    <i class="fas fa-sync me-2"></i>Fetch Latest
                                </button>
                            </div>
                            <small class="text-muted">Current market rate <span id="rateTimestamp" class="ms-2 text-muted"></span></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wastage & Making Charges -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Wastage & Making Charges
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Wastage</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="wastageValue" class="form-control" placeholder="0" value="2">
                                <select id="wastageType" class="form-select">
                                    <option value="percentage" selected>%</option>
                                    <option value="fixed">Grams</option>
                                </select>
                            </div>
                            <small class="text-muted">Material loss during crafting</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Making Charges</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="makingChargeValue" class="form-control" placeholder="0" value="50">
                                <select id="makingChargeType" class="form-select">
                                    <option value="fixed" selected>Rs. Fixed</option>
                                    <option value="per_gram">Rs./g</option>
                                    <option value="percentage">%</option>
                                </select>
                            </div>
                            <small class="text-muted">Labor & crafting cost</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gemstones (Optional) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-gem me-2"></i>Gemstones (Optional)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stone Weight</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="stoneWeight" class="form-control" placeholder="0">
                                <select id="stoneUnit" class="form-select">
                                    <option value="carat" selected>Carat (ct)</option>
                                    <option value="ratti">Ratti</option>
                                    <option value="mg">Milligrams</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price per Carat (Rs.)</label>
                            <input type="number" step="0.01" id="stonePricePerCarat" class="form-control" placeholder="0">
                            <small class="text-muted">Diamond/Gemstone pricing</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Taxes & Discounts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-percent me-2"></i>Taxes & Discounts
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax (%)</label>
                            <input type="number" step="0.01" id="taxPercentage" class="form-control" placeholder="0" value="0">
                            <small class="text-muted">GST or other taxes</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount (%)</label>
                            <input type="number" step="0.01" id="discountPercentage" class="form-control" placeholder="0" value="0">
                            <small class="text-muted">Customer discount</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 d-md-flex">
                <button type="button" class="btn btn-primary btn-lg" id="calculateBtn">
                    <i class="fas fa-calculator me-2"></i>Calculate Price
                </button>
                <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
            </div>
        </div>

        <!-- Right Column: Results & Conversions -->
        <div class="col-lg-5">
            <!-- Quick Conversion Panel -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>Quick Conversions
                    </h5>
                </div>
                <div class="card-body" id="conversionPanel">
                    <p class="text-muted text-center">Enter weight and unit to see all conversions</p>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="card border-0 shadow-sm mb-4" id="breakdownCard" style="display: none;">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Price Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div id="breakdownContent"></div>

                    <!-- Final Total -->
                    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px;">
                        <small>FINAL TOTAL</small>
                        <div class="display-4 fw-bold">Rs.<span id="finalTotal">0.00</span></div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 d-grid gap-2 d-md-flex">
                        <button type="button" class="btn btn-success" id="printBtn">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <button type="button" class="btn btn-info" id="saveBtn">
                            <i class="fas fa-save me-2"></i>Save
                        </button>
                        <button type="button" class="btn btn-warning" id="usePosBtn">
                            <i class="fas fa-shopping-cart me-2"></i>Use in POS
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Presets Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="presetsOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Quick Presets</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-primary text-start" data-preset="22k-standard">
                <i class="fas fa-gem me-2"></i>
                <strong>22K Standard</strong>
                <small class="d-block text-muted">22K, 2% wastage, Rs.50 making</small>
            </button>
            <button type="button" class="btn btn-outline-primary text-start" data-preset="18k-premium">
                <i class="fas fa-crown me-2"></i>
                <strong>18K Premium</strong>
                <small class="d-block text-muted">18K, 3% wastage, Rs.75 making</small>
            </button>
            <button type="button" class="btn btn-outline-primary text-start" data-preset="gold-with-stones">
                <i class="fas fa-ring me-2"></i>
                <strong>Gold with Stones</strong>
                <small class="d-block text-muted">22K + gemstone pricing</small>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE = '{{ route("calculator.index") }}';
    const CONV_ENDPOINT = '{{ route("calculator.convert") }}';
    const CALC_ENDPOINT = '{{ route("calculator.calculate") }}';
    const SAVE_ENDPOINT = '{{ route("calculator.save") }}';
    const PRINT_ENDPOINT = '/calculator';
    const USE_POS_ENDPOINT = '{{ route("calculator.use-in-pos") }}';

    // Form elements
    const grossWeightInput = document.getElementById('grossWeight');
    const weightUnitSelect = document.getElementById('weightUnit');
    const karatSelect = document.getElementById('karat');
    const ratePerGramInput = document.getElementById('ratePerGram');
    const wastageValueInput = document.getElementById('wastageValue');
    const wastageTypeSelect = document.getElementById('wastageType');
    const makingChargeValueInput = document.getElementById('makingChargeValue');
    const makingChargeTypeSelect = document.getElementById('makingChargeType');
    const stoneWeightInput = document.getElementById('stoneWeight');
    const stoneUnitSelect = document.getElementById('stoneUnit');
    const stonePriceInput = document.getElementById('stonePricePerCarat');
    const taxInput = document.getElementById('taxPercentage');
    const discountInput = document.getElementById('discountPercentage');
    const rattiTypeRadios = document.querySelectorAll('input[name="rattiType"]');

    // Buttons
    const calculateBtn = document.getElementById('calculateBtn');
    const resetBtn = document.getElementById('resetBtn');
    const printBtn = document.getElementById('printBtn');
    const saveBtn = document.getElementById('saveBtn');
    const usePosBtn = document.getElementById('usePosBtn');
    const fetchRateBtn = document.getElementById('fetchRateBtn');

    // Elements
    const conversionPanel = document.getElementById('conversionPanel');
    const breakdownCard = document.getElementById('breakdownCard');
    const breakdownContent = document.getElementById('breakdownContent');
    const finalTotalSpan = document.getElementById('finalTotal');

    // Event Listeners
    calculateBtn.addEventListener('click', performCalculation);
    resetBtn.addEventListener('click', resetForm);
    printBtn.addEventListener('click', printCalculation);
    saveBtn.addEventListener('click', saveCalculation);
    usePosBtn.addEventListener('click', useInPOS);
    fetchRateBtn.addEventListener('click', fetchLatestRate);

    // Real-time conversion on weight input change
    grossWeightInput.addEventListener('input', updateConversions);
    weightUnitSelect.addEventListener('change', updateConversions);
    rattiTypeRadios.forEach(radio => radio.addEventListener('change', updateConversions));

    // Preset buttons
    document.querySelectorAll('[data-preset]').forEach(btn => {
        btn.addEventListener('click', function() {
            applyPreset(this.dataset.preset);
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('presetsOffcanvas'));
            offcanvas.hide();
        });
    });

    function updateConversions() {
        const weight = parseFloat(grossWeightInput.value) || 0;
        const unit = weightUnitSelect.value;
        const rattiType = document.querySelector('input[name="rattiType"]:checked').value;

        if (weight <= 0) {
            conversionPanel.innerHTML = '<p class="text-muted text-center">Enter weight to see conversions</p>';
            return;
        }

        // Simulate conversions (normally would call API)
        const conversions = getConversions(weight, unit, rattiType);
        
        conversionPanel.innerHTML = `
            <div class="row g-2">
                <div class="col-6"><small class="text-muted">Grams:</small><br><strong>${conversions.grams.toFixed(4)} g</strong></div>
                <div class="col-6"><small class="text-muted">Tola:</small><br><strong>${conversions.tola.toFixed(4)}</strong></div>
                <div class="col-6"><small class="text-muted">Ratti:</small><br><strong>${conversions.ratti.toFixed(2)}</strong></div>
                <div class="col-6"><small class="text-muted">Carat:</small><br><strong>${conversions.carat.toFixed(2)} ct</strong></div>
                <div class="col-6"><small class="text-muted">Troy Oz:</small><br><strong>${conversions.troyOz.toFixed(4)}</strong></div>
                <div class="col-6"><small class="text-muted">Milligrams:</small><br><strong>${conversions.mg.toFixed(2)} mg</strong></div>
            </div>
        `;
    }

    function getConversions(value, unit, rattiType) {
        let grams = value;
        
        // Convert to grams first
        switch(unit) {
            case 'tola': grams = value * 11.6638038; break;
            case 'ratti': 
                const sunariRatti = rattiType === 'pakki' ? value / 1.5 : value;
                grams = sunariRatti * 0.121497956; 
                break;
            case 'carat': grams = value * 0.2; break;
            case 'mg': grams = value / 1000; break;
            case 'oz': grams = value * 31.1034768; break;
        }

        return {
            grams: grams,
            tola: grams / 11.6638038,
            ratti: grams / (rattiType === 'pakki' ? 0.121497956 / 1.5 : 0.121497956),
            carat: grams / 0.2,
            troyOz: grams / 31.1034768,
            mg: grams * 1000
        };
    }

    async function performCalculation() {
        try {
            const data = {
                _token: document.querySelector('meta[name="csrf-token"]').content,
                input_weight: parseFloat(grossWeightInput.value),
                input_unit: weightUnitSelect.value,
                karat: parseInt(karatSelect.value),
                rate_per_gram: parseFloat(ratePerGramInput.value),
                wastage_type: wastageTypeSelect.value,
                wastage_value: parseFloat(wastageValueInput.value),
                making_charge_type: makingChargeTypeSelect.value,
                making_charge_value: parseFloat(makingChargeValueInput.value),
                stone_weight: parseFloat(stoneWeightInput.value) || 0,
                stone_unit: stoneUnitSelect.value,
                stone_price_per_carat: parseFloat(stonePriceInput.value) || 0,
                tax_percentage: parseFloat(taxInput.value) || 0,
                discount_percentage: parseFloat(discountInput.value) || 0,
                ratti_type: document.querySelector('input[name="rattiType"]:checked').value,
                notes: ''
            };

            const response = await fetch(CALC_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('Calculation failed');

            const result = await response.json();
            displayBreakdown(result);
            breakdownCard.style.display = 'block';
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    function displayBreakdown(data) {
        const details = data.calculation_details;
        
        let html = `
            <div class="breakdown-item mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Pure Metal</small><br><small class="text-success">${details.pure_weight.toFixed(4)}g @ Rs.${details.rate_per_gram.toFixed(0)}/g</small></div>
                    <h5 class="text-success mb-0">Rs.${details.metal_value.toFixed(2)}</h5>
                </div>
            </div>
            <div class="breakdown-item mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Wastage</small><br><small>${details.wastage_weight.toFixed(4)}g (${details.wastage_type === 'percentage' ? details.wastage_input + '%' : details.wastage_input + 'g'})</small></div>
                    <h5 class="text-warning mb-0">Rs.${details.wastage_value.toFixed(2)}</h5>
                </div>
            </div>
            <div class="breakdown-item mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Making Charges</small><br><small>${details.making_charge_description}</small></div>
                    <h5 class="text-info mb-0">Rs.${details.making_charges.toFixed(2)}</h5>
                </div>
            </div>
        `;

        if (details.stone_total_cost > 0) {
            html += `
                <div class="breakdown-item mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div><small class="text-muted">Gemstones</small><br><small>${details.stone_weight_carats.toFixed(4)}ct @ Rs.${details.stone_price_per_carat.toFixed(0)}/ct</small></div>
                        <h5 class="text-danger mb-0">Rs.${details.stone_total_cost.toFixed(2)}</h5>
                    </div>
                </div>
            `;
        }

        if (details.tax_amount > 0) {
            html += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Tax (${details.tax_percentage.toFixed(2)}%)</small>
                        <small class="text-danger">+ Rs.${details.tax_amount.toFixed(2)}</small>
                    </div>
                </div>
            `;
        }

        if (details.discount_amount > 0) {
            html += `
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Discount (${details.discount_percentage.toFixed(2)}%)</small>
                        <small class="text-success">- Rs.${details.discount_amount.toFixed(2)}</small>
                    </div>
                </div>
            `;
        }

        breakdownContent.innerHTML = html;
        finalTotalSpan.textContent = details.final_total.toFixed(2);
        
        // Store calculation data for later use
        window.currentCalculation = data;
    }

    function resetForm() {
        grossWeightInput.value = '10';
        weightUnitSelect.value = 'g';
        karatSelect.value = '22';
        ratePerGramInput.value = '5500';
        wastageValueInput.value = '2';
        wastageTypeSelect.value = 'percentage';
        makingChargeValueInput.value = '50';
        makingChargeTypeSelect.value = 'fixed';
        stoneWeightInput.value = '';
        stonePriceInput.value = '';
        taxInput.value = '0';
        discountInput.value = '0';
        document.getElementById('sunari').checked = true;
        breakdownCard.style.display = 'none';
        updateConversions();
    }

    function printCalculation() {
        if (!window.currentCalculation) {
            alert('Please calculate first');
            return;
        }
        if (window.currentCalculation.id) {
            window.open(PRINT_ENDPOINT + '/' + window.currentCalculation.id + '?print=1');
        } else {
            alert('Please save the calculation first before printing');
        }
    }

    async function saveCalculation() {
        if (!window.currentCalculation) {
            alert('Please calculate first');
            return;
        }

        try {
            const details = window.currentCalculation.calculation_details;
            const response = await fetch(SAVE_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    input_weight: details.gross_weight_input,
                    input_unit: details.input_unit,
                    karat: details.karat,
                    rate_per_gram: details.rate_per_gram,
                    wastage_type: details.wastage_type,
                    wastage_value: details.wastage_input,
                    making_charge_type: document.querySelector('#makingChargeType').value || 'fixed',
                    making_charge_value: details.making_charges,
                    stone_weight: details.stone_weight || 0,
                    stone_unit: details.stone_unit || 'carat',
                    stone_price_per_carat: details.stone_price_per_carat || 0,
                    tax_percentage: details.tax_percentage || 0,
                    discount_percentage: details.discount_percentage || 0,
                    ratti_type: details.ratti_type || 'sunari',
                    custom_charges: details.custom_charges || 0,
                    calculation_details: details,
                    final_price: details.final_total,
                    notes: document.querySelector('#notesInput')?.value || ''
                })
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.error || 'Save failed');
            }

            alert('Calculation saved successfully!');
            if (result.redirect) {
                window.location.href = result.redirect;
            }
        } catch (error) {
            alert('Error: ' + error.message);
            console.error('Save error:', error);
        }
    }

    async function useInPOS() {
        try {
            if (!window.currentCalculation) {
                alert('Please calculate first');
                return;
            }

            // If calculation has an id (saved), use it; otherwise save first
            let calcId = window.currentCalculation.id || null;
            if (!calcId) {
                // Attempt to save temporarily
                const details = window.currentCalculation.calculation_details;
                const saveResp = await fetch(SAVE_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        input_weight: details.gross_weight_input,
                        input_unit: details.input_unit,
                        karat: details.karat,
                        rate_per_gram: details.rate_per_gram,
                        wastage_type: details.wastage_type,
                        wastage_value: details.wastage_input,
                        making_charge_type: document.querySelector('#makingChargeType').value || 'fixed',
                        making_charge_value: details.making_charges,
                        stone_weight: details.stone_weight || 0,
                        stone_unit: details.stone_unit || 'carat',
                        stone_price_per_carat: details.stone_price_per_carat || 0,
                        tax_percentage: details.tax_percentage || 0,
                        discount_percentage: details.discount_percentage || 0,
                        ratti_type: details.ratti_type || 'sunari',
                        custom_charges: details.custom_charges || 0,
                        calculation_details: details,
                        final_price: details.final_total,
                    })
                });

                if (!saveResp.ok) throw new Error('Unable to save calculation for POS');
                const saveJson = await saveResp.json();
                calcId = saveJson.id;
            }

            // Send to POS endpoint
            const resp = await fetch(USE_POS_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]').content,
                    calculation_id: calcId
                })
            });

            if (!resp.ok) throw new Error('POS transfer failed');
            const json = await resp.json();

            // For now just notify and open POS page if available
            alert('Calculation sent to POS successfully');
            // Optionally redirect to POS create page with payload id
            // window.location.href = '/pos/sales/create?calc=' + calcId;
        } catch (err) {
            alert('Error: ' + (err.message || 'Unable to send to POS'));
        }
    }

    function fetchLatestRate() {
        (async function(){
            try {
                const resp = await fetch('{{ route("calculator.market-rate") }}?metal=gold', { headers: { 'Accept': 'application/json' } });
                if (!resp.ok) throw new Error('Unable to fetch rate');
                const json = await resp.json();
                if (json && json.rate && json.rate.rate_per_gram) {
                    ratePerGramInput.value = json.rate.rate_per_gram;
                    document.getElementById('rateTimestamp').textContent = '(updated: ' + json.rate.timestamp + ')';
                } else {
                    alert('No rate received');
                }
            } catch (err) {
                alert('Error fetching rate: ' + err.message);
            }
        })();
    }

    function applyPreset(preset) {
        switch(preset) {
            case '22k-standard':
                karatSelect.value = '22';
                wastageValueInput.value = '2';
                makingChargeValueInput.value = '50';
                break;
            case '18k-premium':
                karatSelect.value = '18';
                wastageValueInput.value = '3';
                makingChargeValueInput.value = '75';
                break;
            case 'gold-with-stones':
                karatSelect.value = '22';
                stoneWeightInput.value = '1';
                stonePriceInput.value = '5000';
                break;
        }
    }

    // Initialize
    window.addEventListener('load', function() {
        updateConversions();
    });
</script>
@endpush

@endsection
