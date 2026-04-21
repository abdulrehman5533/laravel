@extends('layouts.app')

@section('title', 'Add New Product | ' . config('app.name'))

@section('content')
<div class="mb-3">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold text-slate-800 mb-1">Product Registration</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">New Product</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.products.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Inventory List
            </a>
            <button type="submit" form="productForm" class="btn btn-sm btn-gold">
                <i class="fas fa-save me-1"></i> Save Product
            </button>
        </div>
    </div>

    <!-- Market Rates -->
    @if($goldRate)
    <div class="card mb-4 border-0 shadow-sm bg-light">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center small">
                    <span class="badge bg-secondary me-3">MARKET RATES</span>
                    <span class="me-3 border-end pe-3 text-muted">Date: {{ \Carbon\Carbon::parse($goldRate->date)->format('d/m/Y') }}</span>
                    <div class="d-flex gap-4">
                        <span><strong>24K:</strong> Rs. {{ number_format($goldRate->rate_24k, 2) }}</span>
                        <span><strong>22K:</strong> Rs. {{ number_format($goldRate->rate_22k, 2) }}</span>
                        <span><strong>18K:</strong> Rs. {{ number_format($goldRate->rate_18k, 2) }}</span>
                        <span><strong>Silver:</strong> Rs. {{ number_format($goldRate->silver_rate, 2) }}</span>
                    </div>
                </div>
                <a href="{{ route('gold-rate') }}" class="btn btn-xs btn-link text-decoration-none">Update Rates</a>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('inventory.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        <div class="row g-4">
            <div class="col-xl-9 col-lg-8">
                <!-- Section 1: Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Basic Identification
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">SKU Code</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="sku" id="skuInput" class="form-control bg-light fw-bold" value="{{ old('sku') }}" readonly>
                                    <button type="button" class="btn btn-outline-secondary" id="regenSku"><i class="fas fa-sync-alt"></i></button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tag / Barcode</label>
                                <input type="text" name="tag_id" class="form-control form-control-sm" value="{{ old('tag_id') }}" placeholder="Physical Tag ID">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Serial Number</label>
                                <input type="text" name="serial_number" id="serialInput" class="form-control form-control-sm bg-light" value="{{ old('serial_number') }}" readonly>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name') }}" placeholder="Full product title" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select form-select-sm select2" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Collection</label>
                                <input type="text" name="collection" class="form-control form-control-sm" value="{{ old('collection') }}" placeholder="e.g. Traditional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Gender Target</label>
                                <select name="gender" class="form-select form-select-sm">
                                    <option value="Women" {{ old('gender') == 'Women' ? 'selected' : '' }}>Women</option>
                                    <option value="Men" {{ old('gender') == 'Men' ? 'selected' : '' }}>Men</option>
                                    <option value="Unisex" {{ old('gender') == 'Unisex' ? 'selected' : '' }}>Unisex</option>
                                    <option value="Kids" {{ old('gender') == 'Kids' ? 'selected' : '' }}>Kids</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Detailed Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Material Specifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-gem me-2 text-primary"></i>Material & Specifications
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Purity</label>
                                <select name="purity_id" id="puritySelect" class="form-select form-select-sm">
                                    <option value="">-- Select --</option>
                                    @foreach($purities as $purity)
                                        <option value="{{ $purity->id }}" 
                                            data-karat="{{ $purity->karat }}"
                                            data-rate="{{ $goldRate ? ($purity->karat == 24 ? $goldRate->rate_24k : ($purity->karat == 22 ? $goldRate->rate_22k : ($purity->karat == 18 ? $goldRate->rate_18k : 0))) : 0 }}"
                                            {{ old('purity_id') == $purity->id ? 'selected' : '' }}>
                                            {{ $purity->name }} ({{ $purity->karat }}K)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Metal Color</label>
                                <select name="metal_color" class="form-select form-select-sm">
                                    <option value="Yellow Gold">Yellow Gold</option>
                                    <option value="White Gold">White Gold</option>
                                    <option value="Rose Gold">Rose Gold</option>
                                    <option value="Bicolor">Bicolor</option>
                                    <option value="Tricolor">Tricolor</option>
                                    <option value="Silver">Silver</option>
                                    <option value="Platinum">Platinum</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Weight (g) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="weight" id="weightInput" step="0.001" class="form-control" value="{{ old('weight') }}" required>
                                    <span class="input-group-text text-xs">g</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Gross Weight (g)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="gross_weight" step="0.001" class="form-control" value="{{ old('gross_weight') }}">
                                    <span class="input-group-text text-xs">g</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Net Weight (g)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="net_weight" step="0.001" class="form-control" value="{{ old('net_weight') }}">
                                    <span class="input-group-text text-xs">g</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Wastage (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="wastage_percentage" id="wastageInput" step="0.01" class="form-control" value="{{ old('wastage_percentage', 0) }}">
                                    <span class="input-group-text text-xs">%</span>
                                </div>
                            </div>

                            <div class="col-12 py-2"><hr class="my-0 opacity-10"></div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Stone Type</label>
                                <input type="text" name="stone_type" class="form-control form-control-sm" value="{{ old('stone_type') }}" placeholder="Diamond/Ruby">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Stone Weight</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="stone_carat" id="stoneWeightInput" step="0.01" class="form-control" value="{{ old('stone_carat') }}">
                                    <span class="input-group-text text-xs">cts</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Stone Count</label>
                                <input type="number" name="stone_count" class="form-control form-control-sm" value="{{ old('stone_count') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Certificate #</label>
                                <input type="text" name="certificate_no" class="form-control form-control-sm" value="{{ old('certificate_no') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Dimensions & Stock -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-boxes me-2 text-primary"></i>Dimensions & Stock
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Length (mm)</label>
                                <input type="number" name="length" step="0.01" class="form-control form-control-sm" value="{{ old('length') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Width (mm)</label>
                                <input type="number" name="width" step="0.01" class="form-control form-control-sm" value="{{ old('width') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Height (mm)</label>
                                <input type="number" name="height" step="0.01" class="form-control form-control-sm" value="{{ old('height') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Size</label>
                                <input type="text" name="size" class="form-control form-control-sm" value="{{ old('size') }}" placeholder="Standard Size">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="current_stock" step="0.001" class="form-control form-control-sm" value="{{ old('current_stock', 1) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Current Pieces <span class="text-danger">*</span></label>
                                <input type="number" name="current_pieces" class="form-control form-control-sm" value="{{ old('current_pieces', 1) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Reorder Level <span class="text-danger">*</span></label>
                                <input type="number" name="reorder_level" step="0.001" class="form-control form-control-sm" value="{{ old('reorder_level', 1) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Reorder Qty <span class="text-danger">*</span></label>
                                <input type="number" name="reorder_quantity" step="0.001" class="form-control form-control-sm" value="{{ old('reorder_quantity', 1) }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Storage Location</label>
                                <input type="text" name="location" class="form-control form-control-sm" value="{{ old('location') }}" placeholder="e.g. Safe 1, Drawer A">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Pricing & Valuation -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calculator me-2 text-primary"></i>Pricing & Valuation
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Metal Rate (per g)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" id="metalRateInput" step="0.01" class="form-control" value="{{ $goldRate ? $goldRate->rate_22k : 0 }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Making Charge Type</label>
                                <select name="making_charge_type" id="makingType" class="form-select form-select-sm">
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="per_gram">Per Gram</option>
                                    <option value="percentage">Percentage of Metal</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Making Charge Value</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="making_charge_value" id="makingValue" step="0.01" class="form-control" value="{{ old('making_charge_value', 0) }}">
                                    <span class="input-group-text text-xs" id="makingTypeLabel">Rs.</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Labor / Other Charges</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" name="labor_charge" id="laborCharge" step="0.01" class="form-control" value="{{ old('labor_charge', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-primary">Calculated Cost Price <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-primary text-white border-primary">Rs.</span>
                                    <input type="number" name="cost_price" id="finalCostPrice" step="0.01" class="form-control border-primary fw-bold" value="{{ old('cost_price') }}" required>
                                </div>
                                <small class="text-muted text-xs">Auto-calculated from above</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-success">Selling Price <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-success text-white border-success">Rs.</span>
                                    <input type="number" name="selling_price" id="finalSellingPrice" step="0.01" class="form-control border-success fw-bold" value="{{ old('selling_price') }}" required>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="bg-light p-3 rounded border">
                                    <div class="row text-center">
                                        <div class="col-md-3 border-end">
                                            <p class="text-muted small mb-1">Metal Cost</p>
                                            <h6 class="fw-bold mb-0" id="calcMetalCost">Rs. 0.00</h6>
                                        </div>
                                        <div class="col-md-3 border-end">
                                            <p class="text-muted small mb-1">Wastage Cost</p>
                                            <h6 class="fw-bold mb-0" id="calcWastageCost">Rs. 0.00</h6>
                                        </div>
                                        <div class="col-md-3 border-end">
                                            <p class="text-muted small mb-1">Total Making</p>
                                            <h6 class="fw-bold mb-0" id="calcMakingCost">Rs. 0.00</h6>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="text-muted small mb-1">Expected Profit</p>
                                            <h6 class="fw-bold mb-0 text-success" id="expectedProfit">Rs. 0.00</h6>
                                            <small id="profitMargin" class="text-success fw-bold">0%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Media & Gallery -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-images me-2 text-primary"></i>Product Media
                    </div>
                    <div class="card-body">
                        <div class="upload-zone border border-2 border-dashed rounded p-4 text-center mb-3" onclick="document.getElementById('imageInput').click()">
                            <input type="file" name="images[]" id="imageInput" class="d-none" multiple accept="image/*">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                            <h6 class="fw-bold">Click or drag images to upload</h6>
                            <p class="text-muted small mb-0">Supported formats: JPG, PNG, WEBP (Max 2MB each)</p>
                        </div>
                        <div id="imagePreviewContainer" class="row g-2">
                            <!-- Previews will be injected here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Summary -->
            <div class="col-xl-3 col-lg-4">
                <div class="card sticky-top" style="top: 24px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-slate-800">Valuation Summary</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="bg-light p-3 border-bottom text-center">
                            <div id="summaryImagePreview" class="mb-2 bg-white border rounded" style="height: 120px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image fa-2x text-muted opacity-25"></i>
                            </div>
                            <h6 id="summaryName" class="fw-bold text-truncate mb-0">Product Name</h6>
                            <small id="summarySku" class="text-muted">SKU: ---</small>
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Metal Info:</span>
                                <span id="summaryMetal" class="fw-bold">---</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Total Weight:</span>
                                <span id="summaryWeight" class="fw-bold text-dark">0.000g</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-muted">Stones:</span>
                                <span id="summaryStones" class="fw-bold">None</span>
                            </div>
                            
                            <div class="p-2 rounded bg-success bg-opacity-10 border border-success border-opacity-10 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-success">Total Cost:</span>
                                    <span id="calcTotalCost" class="fw-bold text-success">Rs. 0.00</span>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-muted text-xs mb-1 text-uppercase fw-bold">Selling Price</p>
                                <h3 id="summaryPrice" class="fw-bold text-slate-800 mb-0">Rs. 0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <button type="submit" form="productForm" class="btn btn-gold w-100 shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Confirm & Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .upload-zone:hover { border-color: var(--secondary) !important; background: #fcfbf4 !important; cursor: pointer; }
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .text-xs { font-size: 0.7rem; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // SKU & Serial Generation
    function generateCode(prefix, length) {
        const today = new Date();
        const dateStr = today.getFullYear() + String(today.getMonth() + 1).padStart(2, '0') + String(today.getDate()).padStart(2, '0');
        const randomStr = Math.random().toString(36).substring(2, 2 + length).toUpperCase();
        return prefix + '-' + dateStr + '-' + randomStr;
    }

    if (!$('#skuInput').val()) $('#skuInput').val(generateCode('PRD', 4));
    if (!$('#serialInput').val()) $('#serialInput').val(generateCode('SN', 8));

    $('#regenSku').click(function() {
        $('#skuInput').val(generateCode('PRD', 4));
        updateSummary();
    });

    // Dynamic Price Calculation
    function calculatePrices() {
        const weight = parseFloat($('#weightInput').val()) || 0;
        const metalRate = parseFloat($('#metalRateInput').val()) || 0;
        const wastagePercent = parseFloat($('#wastageInput').val()) || 0;
        const makingType = $('#makingType').val();
        const makingValue = parseFloat($('#makingValue').val()) || 0;
        const laborCharge = parseFloat($('#laborCharge').val()) || 0;

        // Metal Cost
        const metalCost = weight * metalRate;
        const wastageCost = (wastagePercent / 100) * metalCost;
        
        // Making Cost
        let makingCost = 0;
        if (makingType === 'fixed') {
            makingCost = makingValue;
        } else if (makingType === 'per_gram') {
            makingCost = makingValue * weight;
        } else if (makingType === 'percentage') {
            makingCost = (makingValue / 100) * metalCost;
        }

        const totalCost = metalCost + wastageCost + makingCost + laborCharge;
        
        $('#calcMetalCost').text('Rs.' + metalCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#calcWastageCost').text('Rs.' + wastageCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#calcMakingCost').text('Rs.' + (makingCost + laborCharge).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#calcTotalCost').text('Rs.' + totalCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        // Only update final cost if user hasn't manually changed it too much (optional logic)
        $('#finalCostPrice').val(totalCost.toFixed(2));
        updateProfit();
    }

    function updateProfit() {
        const cost = parseFloat($('#finalCostPrice').val()) || 0;
        const selling = parseFloat($('#finalSellingPrice').val()) || 0;
        const profit = selling - cost;
        const margin = cost > 0 ? (profit / cost) * 100 : 0;

        $('#expectedProfit').text('Rs.' + profit.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#profitMargin').text(margin.toFixed(2) + '%');

        if (profit < 0) {
            $('#expectedProfit').removeClass('text-success').addClass('text-danger');
            $('#profitMargin').removeClass('text-success').addClass('text-danger');
        } else {
            $('#expectedProfit').removeClass('text-danger').addClass('text-success');
            $('#profitMargin').removeClass('text-danger').addClass('text-success');
        }
        updateSummary();
    }

    // Event Listeners for Calculation
    $('#weightInput, #metalRateInput, #wastageInput, #makingType, #makingValue, #laborCharge').on('input change', calculatePrices);
    $('#finalCostPrice, #finalSellingPrice').on('input', updateProfit);

    // Purity Selection Changes Rate
    $('#puritySelect').change(function() {
        const selected = $(this).find(':selected');
        const rate = selected.data('rate') || 0;
        $('#metalRateInput').val(rate);
        calculatePrices();
    });

    // Update Making Type Label
    $('#makingType').change(function() {
        const type = $(this).val();
        if (type === 'percentage') {
            $('#makingTypeLabel').text('%');
        } else {
            $('#makingTypeLabel').text('Rs.');
        }
        calculatePrices();
    });

    // Summary Sidebar Updates
    function updateSummary() {
        $('#summaryName').text($('input[name="name"]').val() || 'Product Name');
        $('#summarySku').text('SKU: ' + ($('#skuInput').val() || '---'));
        
        const purityText = $('#puritySelect option:selected').text();
        const color = $('select[name="metal_color"]').val();
        $('#summaryMetal').text(purityText !== 'Select Purity' ? (purityText + ' ' + color) : '---');
        
        $('#summaryWeight').text(($('#weightInput').val() || '0.000') + 'g');
        
        const stones = $('input[name="stone_type"]').val();
        $('#summaryStones').text(stones ? stones : 'None');
        
        const price = parseFloat($('#finalSellingPrice').val()) || 0;
        $('#summaryPrice').text('Rs.' + price.toLocaleString());
    }

    $('input[name="name"], select[name="metal_color"], input[name="stone_type"]').on('input change', updateSummary);

    // Image Upload Handling
    $('#imageInput').change(function() {
        const files = this.files;
        $('#imagePreviewContainer').empty();
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function(e) {
                const html = `
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden">
                            <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                            <div class="card-body p-2 text-center bg-light">
                                <small class="text-truncate d-block">${file.name}</small>
                                ${i === 0 ? '<span class="badge bg-primary">Primary</span>' : ''}
                            </div>
                        </div>
                    </div>
                `;
                $('#imagePreviewContainer').append(html);
                if (i === 0) {
                    $('#summaryImagePreview').html(`<img src="${e.target.result}" class="rounded w-100" style="height: 150px; object-fit: cover;">`);
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush
@endsection
