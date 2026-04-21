@extends('layouts.app')

@section('title', $product->name . ' | ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">{{ $product->category->name }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-1">
                <i class="fas fa-gem me-2 text-warning"></i>{{ $product->name }}
            </h2>
            <div class="d-flex gap-2 mt-2">
                <span class="badge bg-dark px-3 py-2"><i class="fas fa-barcode me-1"></i>{{ $product->sku }}</span>
                @if($product->tag_id)
                    <span class="badge bg-secondary px-3 py-2"><i class="fas fa-tag me-1"></i>Tag: {{ $product->tag_id }}</span>
                @endif
                <span class="badge bg-{{ $product->status === 'active' ? 'success' : 'danger' }} px-3 py-2">
                    {{ ucfirst($product->status) }}
                </span>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group shadow-sm">
                <button type="button" class="btn btn-dark px-4 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-sync me-2"></i>Stock Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#restockModal">
                        <i class="fas fa-plus-circle me-2 text-success"></i>Replenish / Adjust Stock
                    </a></li>
                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#wastageModal">
                        <i class="fas fa-trash-alt me-2 text-danger"></i>Record Wastage
                    </a></li>
                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#damageModal">
                        <i class="fas fa-heart-broken me-2 text-warning"></i>Record Damage
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2" href="{{ route('inventory.products.edit', $product) }}">
                        <i class="fas fa-edit me-2 text-primary"></i>Edit Product
                    </a></li>
                </ul>
                <a href="{{ route('inventory.products.print-tag', $product) }}" target="_blank" class="btn btn-outline-dark px-4">
                    <i class="fas fa-tag me-2"></i>Tag
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

    <div class="row g-4">
        <!-- Left Section: Gallery & Quick Stats -->
        <div class="col-lg-4">
            <!-- Image Gallery -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @forelse($product->images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="d-block w-100" style="height: 400px; object-fit: cover;">
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                    <i class="fas fa-image fa-5x text-muted opacity-25"></i>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    @endif
                </div>
                <div class="card-body bg-light p-2">
                    <div class="d-flex gap-2 overflow-auto pb-1">
                        @foreach($product->images as $index => $image)
                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                 class="rounded border shadow-sm" 
                                 style="height: 60px; width: 60px; object-fit: cover; cursor: pointer;"
                                 data-bs-target="#productCarousel" data-bs-slide-to="{{ $index }}">
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card border-0 shadow-sm bg-dark text-white mb-4">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase small mb-3" style="color: #d4af37; letter-spacing: 2px;">Retail Value</h6>
                    <h2 class="fw-bold mb-0">Rs.{{ number_format($product->selling_price, 2) }}</h2>
                    <hr style="background-color: #d4af37; height: 2px; opacity: 0.5;">
                    <div class="row">
                        <div class="col-6 border-end">
                            <small class="d-block text-muted mb-1">Cost</small>
                            <h5 class="mb-0">Rs.{{ number_format($product->cost_price, 2) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="d-block text-muted mb-1">Margin</small>
                            <h5 class="mb-0 text-success">{{ number_format($product->profit_margin, 2) }}%</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pledge Info -->
            @php
                $activeGirviItem = $product->girviItems()->whereHas('girvi', function($q) {
                    $q->where('status', 'active');
                })->with('girvi')->first();
            @endphp
            @if($activeGirviItem)
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-warning mb-3"><i class="fas fa-lock me-2"></i>Pledged Item</h6>
                    <p class="small text-muted mb-2">This product is currently held as collateral in an active Girvi loan.</p>
                    <div class="d-grid">
                        <a href="{{ route('girvi.loans.show', $activeGirviItem->girvi_id) }}" class="btn btn-warning fw-bold text-dark">
                            <i class="fas fa-file-invoice-dollar me-2"></i>View Girvi #{{ $activeGirviItem->girvi->girvi_number }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Middle Section: Detailed Specifications -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <ul class="nav nav-tabs card-header-tabs" id="detailTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#specs">Specifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#stock">Stock & Movements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#accounting">Pricing Analysis</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- Specifications Tab -->
                        <div class="tab-pane fade show active" id="specs">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-hammer me-2"></i>Metal Details</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted w-50">Metal Type:</td><td class="fw-bold">{{ $product->purity->name ?? 'N/A' }} ({{ $product->purity->karat ?? 'N/A' }}K)</td></tr>
                                        <tr><td class="text-muted">Metal Color:</td><td class="fw-bold">{{ $product->metal_color ?? 'N/A' }}</td></tr>
                                        <tr><td class="text-muted">Net Weight:</td><td class="fw-bold">{{ number_format($product->weight, 3) }} g</td></tr>
                                        <tr><td class="text-muted text-primary">Fine Weight:</td><td class="fw-bold text-primary">{{ number_format($product->fine_weight, 3) }} g</td></tr>
                                        <tr><td class="text-muted">Wastage:</td><td class="fw-bold">{{ $product->wastage_percentage }}%</td></tr>
                                        <tr><td class="text-muted">Hallmarked:</td><td>{!! $product->is_hallmarked ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td></tr>
                                        @if($product->hallmark)<tr><td class="text-muted">Hallmark Details:</td><td class="small">{{ $product->hallmark }}</td></tr>@endif
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-gem me-2"></i>Stone Details</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted w-50">Stone Type:</td><td class="fw-bold">{{ $product->stone_type ?? 'None' }}</td></tr>
                                        <tr><td class="text-muted">Stone Count:</td><td class="fw-bold">{{ $product->stone_count ?? 0 }} pcs</td></tr>
                                        <tr><td class="text-muted">Total Weight:</td><td class="fw-bold">{{ number_format($product->stone_carat, 2) }} cts</td></tr>
                                        <tr><td class="text-muted">Certificate:</td><td class="fw-bold">{{ $product->certificate_no ?? 'None' }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-ruler-combined me-2"></i>Product Info</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted w-50">Category:</td><td class="fw-bold">{{ $product->category->name }}</td></tr>
                                        <tr><td class="text-muted">Collection:</td><td class="fw-bold">{{ $product->collection ?? 'N/A' }}</td></tr>
                                        <tr><td class="text-muted">Gender:</td><td class="fw-bold">{{ $product->gender ?? 'Unisex' }}</td></tr>
                                        <tr><td class="text-muted">Dimensions:</td><td class="fw-bold">
                                            @if($product->length || $product->width || $product->height)
                                                {{ $product->length ?? 0 }}x{{ $product->width ?? 0 }}x{{ $product->height ?? 0 }} (LWH)
                                            @else
                                                N/A
                                            @endif
                                        </td></tr>
                                        <tr><td class="text-muted">Size:</td><td class="fw-bold">{{ $product->size ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-12 mt-3">
                                    <h6 class="text-primary fw-bold mb-2">Description</h6>
                                    <p class="text-muted small">{{ $product->description ?? 'No description provided.' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Tab -->
                        <div class="tab-pane fade" id="stock">
                            <div class="row g-4 text-center mb-4">
                                <div class="col-md-3">
                                    <div class="p-3 border rounded bg-light shadow-sm">
                                        <h6 class="text-muted small mb-1">Current Stock</h6>
                                        <h3 class="fw-bold text-primary mb-0">{{ number_format($product->current_stock, 2) }}</h3>
                                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Grams</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded bg-light shadow-sm">
                                        <h6 class="text-muted small mb-1">Available Pieces</h6>
                                        <h3 class="fw-bold text-info mb-0">{{ number_format($product->current_pieces, 0) }}</h3>
                                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Items</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded bg-light shadow-sm">
                                        <h6 class="text-muted small mb-1">Reorder Level</h6>
                                        <h3 class="fw-bold text-warning mb-0">{{ number_format($product->reorder_level, 2) }}</h3>
                                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Grams</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded bg-light shadow-sm">
                                        <h6 class="text-muted small mb-1">Total Sold</h6>
                                        <h3 class="fw-bold text-success mb-0">{{ number_format($movementStats['total_sold'], 2) }}</h3>
                                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Grams</small>
                                    </div>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-3">Recent Stock History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover border">
                                    <thead class="table-light">
                                        <tr><th>Date</th><th>Type</th><th>Qty</th><th>Ref</th><th>Staff</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stockMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                            <td><span class="badge bg-{{ $movement->type === 'add' ? 'success' : 'danger' }}">{{ ucfirst($movement->type) }}</span></td>
                                            <td>{{ number_format($movement->quantity, 2) }}</td>
                                            <td class="small text-muted">{{ $movement->reference }}</td>
                                            <td>{{ $movement->createdBy->name ?? 'System' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($stockMovements->hasPages())
                                <div class="mt-3">
                                    {{ $stockMovements->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Accounting Tab -->
                        <div class="tab-pane fade" id="accounting">
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold mb-3">Cost Analysis</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted w-50">Making Charge Type:</td><td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $product->making_charge_type)) }}</td></tr>
                                        <tr><td class="text-muted">Making Charge Value:</td><td class="fw-bold">Rs.{{ number_format($product->making_charge_value, 2) }}</td></tr>
                                        <tr><td class="text-muted">Labor Charge:</td><td class="fw-bold">Rs.{{ number_format($product->labor_charge, 2) }}</td></tr>
                                        <tr class="border-top"><td class="text-muted fw-bold">Total Cost (Unit):</td><td class="fw-bold text-primary">Rs.{{ number_format($product->cost_price, 2) }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Revenue Potential</h6>
                                    <div class="p-3 border rounded bg-light">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Stock Value (Cost):</span>
                                            <span class="fw-bold text-dark">Rs.{{ number_format($financialMetrics['total_cost'], 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Market Value (Retail):</span>
                                            <span class="fw-bold text-success">Rs.{{ number_format($financialMetrics['total_retail_value'], 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between border-top pt-2">
                                            <span class="fw-bold">Profit Potential:</span>
                                            <span class="fw-bold text-primary">Rs.{{ number_format($financialMetrics['profit_potential'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .breadcrumb-item + .breadcrumb-item::before { content: "›"; font-size: 1.2rem; line-height: 1; }
    .nav-tabs .nav-link { color: #6c757d; border: none; padding: 1rem 1.5rem; }
    .nav-tabs .nav-link.active { color: #000; border-bottom: 3px solid #d4af37; background: transparent; }
    .card { border-radius: 12px; }
</style>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="restockModalLabel">
                    <i class="fas fa-sync me-2"></i>Stock Adjustment: {{ $product->name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventory.products.add-stock', $product->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <p class="text-muted mb-1 small text-uppercase tracking-wider">Current Stock Balance</p>
                        <h2 class="fw-bold text-dark">{{ number_format($product->current_stock, 2) }} <span class="small text-muted fs-6">units</span></h2>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Adjustment Type *</label>
                        <div class="d-flex gap-3">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="type" id="typeAdd" value="add" checked required>
                                <label class="btn btn-outline-success w-100 py-2 fw-bold" for="typeAdd">
                                    <i class="fas fa-plus-circle me-1"></i> Add Stock
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="type" id="typeSubtract" value="subtract" required>
                                <label class="btn btn-outline-danger w-100 py-2 fw-bold" for="typeSubtract">
                                    <i class="fas fa-minus-circle me-1"></i> Subtract Stock
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase">Grams / Qty *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-balance-scale text-dark"></i></span>
                                <input type="number" step="0.001" name="quantity" class="form-control border-start-0 bg-light" placeholder="0.000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase">Pieces (optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-th text-dark"></i></span>
                                <input type="number" name="pieces" class="form-control border-start-0 bg-light" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Supplier (Optional)</label>
                        <select name="supplier_id" class="form-select bg-light">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Reference / Invoice #</label>
                        <input type="text" name="reference" class="form-control bg-light" placeholder="e.g. INV-2024-001">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Internal Notes</label>
                        <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Reason for restocking..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-5 py-2 fw-bold shadow-sm">
                        Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Wastage Modal -->
<div class="modal fade" id="wastageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-trash-alt me-2"></i>Record Wastage: {{ $product->name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventory.products.record-wastage', $product->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Wastage Amount (Grams) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-balance-scale text-danger"></i></span>
                            <input type="number" step="0.001" name="quantity" class="form-control form-control-lg border-start-0 bg-light" placeholder="0.000" max="{{ $product->current_stock }}" required>
                        </div>
                        <div class="form-text text-danger small">Current stock: {{ number_format($product->current_stock, 3) }}g</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Reason for Wastage *</label>
                        <select name="reason" class="form-select bg-light" required>
                            <option value="">-- Select Reason --</option>
                            <option value="Manufacturing Loss">Manufacturing Loss</option>
                            <option value="Melting Loss">Melting Loss</option>
                            <option value="Stone Setting Loss">Stone Setting Loss</option>
                            <option value="Chemical/Polishing Loss">Chemical/Polishing Loss</option>
                            <option value="Theft/Missing">Theft/Missing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Internal Notes</label>
                        <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-5 py-2 fw-bold shadow-sm">
                        Record Wastage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Damage Modal -->
<div class="modal fade" id="damageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-heart-broken me-2"></i>Record Damage: {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventory.products.record-damage', $product->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Damage Quantity *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-boxes text-warning"></i></span>
                            <input type="number" step="0.001" name="quantity" class="form-control form-control-lg border-start-0 bg-light" placeholder="0.000" max="{{ $product->current_stock }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Damage Status *</label>
                        <select name="status" class="form-select bg-light" required>
                            <option value="damaged">Damaged (In Stock)</option>
                            <option value="repairing">Sent for Repair</option>
                            <option value="discarded">Discarded (Removed from Stock)</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Internal Notes</label>
                        <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Describe the damage..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-5 py-2 fw-bold shadow-sm">
                        Record Damage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
