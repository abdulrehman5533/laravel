@extends('layouts.app')

@section('title', 'Edit Product: ' . $product->name . ' | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">✍️ Edit Masterpiece</h2>
                <p class="text-muted mb-0">Update specifications for <strong>{{ $product->name }}</strong>.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-eye me-2"></i>View Product
                </a>
                <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Gold Rate Alert Bar -->
    @if($goldRate)
    <div class="alert border-0 shadow-sm d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(90deg, #d4af37, #f1d592); color: #000;">
        <div class="d-flex align-items-center">
            <i class="fas fa-chart-line fs-4 me-3"></i>
            <div>
                <span class="fw-bold">Market Rates Today ({{ \Carbon\Carbon::parse($goldRate->date)->format('M d, Y') }}):</span>
                <span class="ms-3">24K: Rs.{{ number_format($goldRate->rate_24k, 2) }}</span>
                <span class="ms-3">22K: Rs.{{ number_format($goldRate->rate_22k, 2) }}</span>
                <span class="ms-3">18K: Rs.{{ number_format($goldRate->rate_18k, 2) }}</span>
                <span class="ms-3">Silver: Rs.{{ number_format($goldRate->silver_rate, 2) }}</span>
            </div>
        </div>
        <a href="{{ route('gold-rate') }}" class="btn btn-sm btn-dark px-3">Update Rates</a>
    </div>
    @endif

    <form action="{{ route('inventory.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <ul class="nav nav-pills card-header-pills" id="productTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                                    <i class="fas fa-info-circle me-2"></i>General Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="specifications-tab" data-bs-toggle="tab" href="#specifications" role="tab">
                                    <i class="fas fa-gem me-2"></i>Specifications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pricing-tab" data-bs-toggle="tab" href="#pricing" role="tab">
                                    <i class="fas fa-tag me-2"></i>Pricing & Charges
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="inventory-tab" data-bs-toggle="tab" href="#inventory" role="tab">
                                    <i class="fas fa-boxes me-2"></i>Inventory
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="media-tab" data-bs-toggle="tab" href="#media" role="tab">
                                    <i class="fas fa-camera me-2"></i>Media
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="productTabsContent">
                            <!-- General Info Tab -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">SKU</label>
                                        <input type="text" class="form-control bg-light" value="{{ $product->sku }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Tag ID / Barcode</label>
                                        <input type="text" name="tag_id" class="form-control @error('tag_id') is-invalid @enderror" value="{{ old('tag_id', $product->tag_id) }}" placeholder="Enter physical tag ID">
                                        @error('tag_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Serial Number</label>
                                        <input type="text" class="form-control bg-light" value="{{ $product->serial_number }}" readonly>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" placeholder="e.g. 22K Gold Bridal Choker Necklace" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Collection</label>
                                        <input type="text" name="collection" class="form-control" value="{{ old('collection', $product->collection) }}" placeholder="e.g. Heritage Bridal">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="Women" {{ old('gender', $product->gender) == 'Women' ? 'selected' : '' }}>Women</option>
                                            <option value="Men" {{ old('gender', $product->gender) == 'Men' ? 'selected' : '' }}>Men</option>
                                            <option value="Unisex" {{ old('gender', $product->gender) == 'Unisex' ? 'selected' : '' }}>Unisex</option>
                                            <option value="Kids" {{ old('gender', $product->gender) == 'Kids' ? 'selected' : '' }}>Kids</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the masterpiece...">{{ old('description', $product->description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Specifications Tab -->
                            <div class="tab-pane fade" id="specifications" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-hammer me-2"></i>Metal Specifications</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Metal Type / Purity</label>
                                                <select name="purity_id" id="puritySelect" class="form-select">
                                                    <option value="">Select Purity</option>
                                                    @foreach($purities as $purity)
                                                        <option value="{{ $purity->id }}" 
                                                            data-karat="{{ $purity->karat }}"
                                                            {{ old('purity_id', $product->purity_id) == $purity->id ? 'selected' : '' }}>
                                                            {{ $purity->name }} ({{ $purity->karat }}K)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Metal Color</label>
                                                <select name="metal_color" class="form-select">
                                                    @foreach(['Yellow Gold', 'White Gold', 'Rose Gold', 'Bicolor', 'Tricolor', 'Silver', 'Platinum'] as $color)
                                                        <option value="{{ $color }}" {{ old('metal_color', $product->metal_color) == $color ? 'selected' : '' }}>{{ $color }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Net Weight (g) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="weight" id="weightInput" step="0.001" class="form-control" value="{{ old('weight', $product->weight) }}" placeholder="0.000" required>
                                                    <span class="input-group-text">g</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Gross Weight (g)</label>
                                                <div class="input-group">
                                                    <input type="number" name="gross_weight" step="0.001" class="form-control" value="{{ old('gross_weight', $product->gross_weight) }}" placeholder="0.000">
                                                    <span class="input-group-text">g</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Net Weight (g)</label>
                                                <div class="input-group">
                                                    <input type="number" name="net_weight" step="0.001" class="form-control" value="{{ old('net_weight', $product->net_weight) }}" placeholder="0.000">
                                                    <span class="input-group-text">g</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Wastage (%)</label>
                                                <div class="input-group">
                                                    <input type="number" name="wastage_percentage" id="wastageInput" step="0.01" class="form-control" value="{{ old('wastage_percentage', $product->wastage_percentage) }}">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-gem me-2"></i>Stone Specifications</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Stone Type</label>
                                                <input type="text" name="stone_type" class="form-control" value="{{ old('stone_type', $product->stone_type) }}" placeholder="e.g. Diamond VVS1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Stone Count</label>
                                                <input type="number" name="stone_count" class="form-control" value="{{ old('stone_count', $product->stone_count) }}" placeholder="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Stone Weight (cts)</label>
                                                <div class="input-group">
                                                    <input type="number" name="stone_carat" id="stoneWeightInput" step="0.01" class="form-control" value="{{ old('stone_carat', $product->stone_carat) }}" placeholder="0.00">
                                                    <span class="input-group-text">cts</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Certificate No.</label>
                                                <input type="text" name="certificate_no" class="form-control" value="{{ old('certificate_no', $product->certificate_no) }}" placeholder="GIA-XXXXXX">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-ruler-combined me-2"></i>Dimensions & Size</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Length</label>
                                                <input type="number" name="length" step="0.01" class="form-control" value="{{ old('length', $product->length) }}" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Width</label>
                                                <input type="number" name="width" step="0.01" class="form-control" value="{{ old('width', $product->width) }}" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Height</label>
                                                <input type="number" name="height" step="0.01" class="form-control" value="{{ old('height', $product->height) }}" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Standard Size</label>
                                                <input type="text" name="size" class="form-control" value="{{ old('size', $product->size) }}" placeholder="e.g. 12 (Ring Size)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Tab -->
                            <div class="tab-pane fade" id="pricing" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-6 border-end">
                                        <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="fas fa-calculator me-2"></i>Pricing Controls</h6>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Metal Rate (per g)</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" id="metalRateInput" step="0.01" class="form-control" value="{{ $goldRate ? ($product->purity && $product->purity->karat == 24 ? $goldRate->rate_24k : ($product->purity && $product->purity->karat == 22 ? $goldRate->rate_22k : ($product->purity && $product->purity->karat == 18 ? $goldRate->rate_18k : $goldRate->rate_22k))) : 0 }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Labor / Making Charges</label>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <select name="making_charge_type" id="makingType" class="form-select">
                                                            <option value="fixed" {{ old('making_charge_type', $product->making_charge_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                            <option value="per_gram" {{ old('making_charge_type', $product->making_charge_type) == 'per_gram' ? 'selected' : '' }}>Per Gram</option>
                                                            <option value="percentage" {{ old('making_charge_type', $product->making_charge_type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <input type="number" name="making_charge_value" id="makingValue" step="0.01" class="form-control" value="{{ old('making_charge_value', $product->making_charge_value) }}">
                                                            <span class="input-group-text text-xs" id="makingTypeLabel">{{ old('making_charge_type', $product->making_charge_type) == 'percentage' ? '%' : 'Rs.' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Additional Labor Charge</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" name="labor_charge" id="laborCharge" step="0.01" class="form-control" value="{{ old('labor_charge', $product->labor_charge) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12 mt-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_hallmarked" id="hallmarkSwitch" {{ old('is_hallmarked', $product->is_hallmarked) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="hallmarkSwitch">BIS Hallmarked Item</label>
                                                </div>
                                                <input type="text" name="hallmark" class="form-control mt-2" placeholder="Hallmark Details / Center" value="{{ old('hallmark', $product->hallmark) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3 text-success"><i class="fas fa-money-bill-wave me-2"></i>Final Pricing</h6>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Cost Price <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" name="cost_price" id="finalCostPrice" step="0.01" class="form-control fw-bold text-primary" value="{{ old('cost_price', $product->cost_price) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Selling Price <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                                    <input type="number" name="selling_price" id="finalSellingPrice" step="0.01" class="form-control form-control-lg fw-bold text-success" value="{{ old('selling_price', $product->selling_price) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <div class="card bg-success bg-opacity-10 border-success border-opacity-25">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Expected Profit:</span>
                                                            <span id="expectedProfit" class="fw-bold text-success">Rs.0.00</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Margin:</span>
                                                            <span id="profitMargin" class="fw-bold text-success">0.00%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory Tab -->
                            <div class="tab-pane fade" id="inventory" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-light p-3 text-center">
                                            <label class="form-label fw-bold">Current Stock</label>
                                            <h3 class="text-primary mb-0">{{ number_format($product->current_stock, 2) }} Units</h3>
                                            <small class="text-muted">Stock updates via movements</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light p-3 text-center">
                                            <label class="form-label fw-bold">Current Pieces</label>
                                            <div class="input-group">
                                                <input type="number" name="current_pieces" class="form-control" value="{{ old('current_pieces', $product->current_pieces) }}" required>
                                                <span class="input-group-text">Pcs</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light p-3">
                                            <label class="form-label fw-bold">Reorder Alert Level</label>
                                            <div class="input-group">
                                                <input type="number" name="reorder_level" step="0.01" class="form-control" value="{{ old('reorder_level', $product->reorder_level) }}" required>
                                                <span class="input-group-text">Units</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light p-3">
                                            <label class="form-label fw-bold">Reorder Quantity</label>
                                            <div class="input-group">
                                                <input type="number" name="reorder_quantity" step="0.01" class="form-control" value="{{ old('reorder_quantity', $product->reorder_quantity) }}" required>
                                                <span class="input-group-text">Units</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Product Status</label>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" {{ old('status', $product->status) == 'active' ? 'checked' : '' }}>
                                                <label class="form-check-label text-success fw-bold" for="statusActive">Active</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive" {{ old('status', $product->status) == 'inactive' ? 'checked' : '' }}>
                                                <label class="form-check-label text-warning fw-bold" for="statusInactive">Inactive</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="statusDiscontinued" value="discontinued" {{ old('status', $product->status) == 'discontinued' ? 'checked' : '' }}>
                                                <label class="form-check-label text-danger fw-bold" for="statusDiscontinued">Discontinued</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Media Tab -->
                            <div class="tab-pane fade" id="media" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <h6>Current Images</h6>
                                        <div class="row g-3 mb-4">
                                            @foreach($product->images as $image)
                                                <div class="col-md-2 position-relative">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="img-fluid rounded border" style="height: 100px; width: 100%; object-fit: cover;">
                                                    @if($image->is_primary)
                                                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary" style="font-size: 0.6rem;">Primary</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="upload-zone border-dashed rounded p-5 text-center bg-light" id="dropZone">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5>Add More Images</h5>
                                            <input type="file" name="images[]" id="imageInput" class="d-none" multiple accept="image/*">
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('imageInput').click()">Browse Files</button>
                                        </div>
                                        <div id="imagePreviewContainer" class="row mt-4 g-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white p-4 border-top">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-light px-4">Discard Changes</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Masterpiece
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar / Summary Box -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white py-3 text-center">
                        <h6 class="mb-0 fw-bold" style="color: #d4af37;">REVISION SUMMARY</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 text-center border-bottom bg-light">
                            <div id="summaryImagePreview" class="mb-3">
                                @if($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" class="rounded w-100 shadow-sm" style="height: 150px; object-fit: cover;">
                                @else
                                    <div class="placeholder-image border rounded d-flex align-items-center justify-content-center" style="height: 150px; background: #eee;">
                                        <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 id="summaryName" class="fw-bold mb-1">{{ $product->name }}</h5>
                            <p class="text-muted small mb-0">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Purity:</span>
                                <span class="fw-bold">{{ $product->purity->name ?? '---' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Weight:</span>
                                <span class="fw-bold">{{ number_format($product->weight, 3) }}g</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Selling Price:</span>
                                <h5 class="fw-bold text-success mb-0">Rs.{{ number_format($product->selling_price, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .border-dashed { border: 2px dashed #dee2e6 !important; }
    .upload-zone:hover { border-color: #d4af37 !important; background: #fffcf0 !important; cursor: pointer; }
    .nav-pills .nav-link { color: #6c757d; font-weight: 600; padding: 10px 20px; }
    .nav-pills .nav-link.active { background-color: #d4af37; color: #000; }
    .form-control:focus, .form-select:focus { border-color: #d4af37; box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25); }
    .card { border-radius: 12px; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
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
    }

    $('#weightInput, #metalRateInput, #wastageInput, #makingType, #makingValue, #laborCharge').on('input change', calculatePrices);
    $('#finalCostPrice, #finalSellingPrice').on('input', updateProfit);

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

    updateProfit();

    $('#imageInput').change(function() {
        const files = this.files;
        $('#imagePreviewContainer').empty();
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreviewContainer').append(`
                    <div class="col-md-2">
                        <div class="card h-100 border-primary">
                            <img src="${e.target.result}" class="card-img-top" style="height: 80px; object-fit: cover;">
                            <div class="card-body p-1 text-center bg-primary text-white" style="font-size: 0.7rem;">New</div>
                        </div>
                    </div>
                `);
            };
            reader.readAsDataURL(files[i]);
        }
    });
});
</script>
@endpush
@endsection
