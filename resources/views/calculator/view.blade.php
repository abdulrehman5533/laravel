@extends('layouts.app')

@section('title', 'View Calculation - Weight Calculator')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-calculator me-2" style="color: var(--primary);"></i>
                Calculation Details
            </h2>
            <p class="text-muted">{{ $calculation->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('calculator.history') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to History
            </a>
            <a href="?print=1" target="_blank" class="btn btn-success me-2">
                <i class="fas fa-print me-2"></i>Print
            </a>
            <a href="{{ route('calculator.export', $calculation->id) }}" class="btn btn-primary">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </a>
        </div>
    </div>

    <!-- Input Parameters -->
    <div class="row mb-4">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Gross Weight</h6>
                    <h3 class="mb-1">{{ $details['gross_weight_input'] }}</h3>
                    <small class="text-primary">{{ strtoupper($details['input_unit']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">In Grams</h6>
                    <h3 class="mb-1">{{ number_format($details['gross_weight_grams'], 4) }}</h3>
                    <small class="text-primary">g</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Karat</h6>
                    <h3 class="mb-1">{{ $details['karat'] }}K</h3>
                    <small class="text-primary">{{ number_format($details['purity_percentage'], 2) }}% Pure</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Rate/Gram</h6>
                    <h3 class="mb-1">Rs.{{ number_format($details['rate_per_gram'], 0) }}</h3>
                    <small class="text-primary">per gram</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Breakdown -->
        <div class="col-lg-6">
            <!-- Price Breakdown -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0">Price Breakdown</h5>
                </div>
                <div class="card-body">
                    <!-- Metal -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Pure Metal</h6>
                                <small class="text-muted">{{ number_format($details['pure_weight'], 4) }}g @ Rs.{{ number_format($details['rate_per_gram'], 2) }}/g</small>
                            </div>
                            <h5 class="text-success mb-0">Rs.{{ number_format($details['metal_value'], 2) }}</h5>
                        </div>
                    </div>

                    <!-- Wastage -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Wastage</h6>
                                <small class="text-muted">{{ number_format($details['wastage_weight'], 4) }}g ({{ $details['wastage_type'] === 'percentage' ? $details['wastage_input'] . '%' : $details['wastage_input'] . 'g' }})</small>
                            </div>
                            <h5 class="text-warning mb-0">Rs.{{ number_format($details['wastage_value'], 2) }}</h5>
                        </div>
                    </div>

                    <!-- Making Charges -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Making Charges</h6>
                                <small class="text-muted">{{ $details['making_charge_description'] }}</small>
                            </div>
                            <h5 class="text-info mb-0">Rs.{{ number_format($details['making_charges'], 2) }}</h5>
                        </div>
                    </div>

                    <!-- Stones -->
                    @if($details['stone_total_cost'] > 0)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Gemstones</h6>
                                    <small class="text-muted">{{ number_format($details['stone_weight_carats'], 4) }} ct @ Rs.{{ number_format($details['stone_price_per_carat'], 2) }}/ct</small>
                                </div>
                                <h5 class="text-danger mb-0">Rs.{{ number_format($details['stone_total_cost'], 2) }}</h5>
                            </div>
                        </div>
                    @endif

                    <!-- Custom Charges -->
                    @if($details['custom_charges'] > 0)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Custom Charges</h6>
                                    <small class="text-muted">Polish, resizing, certification</small>
                                </div>
                                <h5 class="mb-0">Rs.{{ number_format($details['custom_charges'], 2) }}</h5>
                            </div>
                        </div>
                    @endif

                    <!-- Subtotal -->
                    <div class="mb-3 pb-3 border-bottom" style="background: #f8f9fa; padding: 12px; border-radius: 5px; margin: 0 -12px 0 -12px; padding-left: 12px;">
                        <div class="d-flex justify-content-between">
                            <strong>Subtotal</strong>
                            <strong>Rs.{{ number_format($details['subtotal'], 2) }}</strong>
                        </div>
                    </div>

                    <!-- Tax & Discount -->
                    @if($details['tax_amount'] > 0)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Tax ({{ number_format($details['tax_percentage'], 2) }}%)</small>
                                <small class="text-danger">+ Rs.{{ number_format($details['tax_amount'], 2) }}</small>
                            </div>
                        </div>
                    @endif

                    @if($details['discount_amount'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Discount ({{ number_format($details['discount_percentage'], 2) }}%)</small>
                                <small class="text-success">- Rs.{{ number_format($details['discount_amount'], 2) }}</small>
                            </div>
                        </div>
                    @endif

                    <!-- Final Total -->
                    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <small>FINAL TOTAL</small>
                        <div class="display-5 fw-bold">Rs.{{ number_format($details['final_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Details & Actions -->
        <div class="col-lg-6">
            <!-- Configuration -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Wastage</h6>
                            <p class="mb-3">
                                <strong>{{ number_format($details['wastage_input'], 2) }}{{ $details['wastage_type'] === 'percentage' ? '%' : 'g' }}</strong><br>
                                <small class="text-muted">Type: {{ ucfirst($details['wastage_type']) }}</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Ratti Type</h6>
                            <p class="mb-3">
                                <strong>{{ ucfirst($calculation->ratti_type) }} Ratti</strong><br>
                                <small class="text-muted">Standard used</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversion Table -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Weight Conversions</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td><strong>Grams</strong></td>
                                <td class="text-end"><strong>{{ number_format($details['gross_weight_grams'], 4) }} g</strong></td>
                            </tr>
                            <tr>
                                <td>Milligrams</td>
                                <td class="text-end">{{ number_format($details['gross_weight_grams'] * 1000, 2) }} mg</td>
                            </tr>
                            <tr>
                                <td>Tola</td>
                                <td class="text-end">{{ number_format($details['gross_weight_grams'] / 11.6638038, 4) }} tola</td>
                            </tr>
                            <tr>
                                <td>Ratti ({{ ucfirst($calculation->ratti_type) }})</td>
                                <td class="text-end">
                                    @php
                                        $sunariRatti = $details['gross_weight_grams'] / 0.121497956;
                                        $ratti = $calculation->ratti_type === 'pakki' ? $sunariRatti / 1.5 : $sunariRatti;
                                    @endphp
                                    {{ number_format($ratti, 2) }} ratti
                                </td>
                            </tr>
                            <tr>
                                <td>Carat</td>
                                <td class="text-end">{{ number_format($details['gross_weight_grams'] / 0.2, 2) }} ct</td>
                            </tr>
                            <tr>
                                <td>Troy Ounce</td>
                                <td class="text-end">{{ number_format($details['gross_weight_grams'] / 31.1034768, 4) }} oz</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Product Status -->
            @if($calculation->is_saved_as_product && $calculation->product)
                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #28a745;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>Saved as Product
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Product Name:</strong><br>
                            {{ $calculation->product->name }}
                        </p>
                        <p class="mb-2">
                            <strong>SKU:</strong><br>
                            {{ $calculation->product->sku }}
                        </p>
                        <p class="mb-0">
                            <a href="{{ route('inventory.products.show', $calculation->product->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View in Inventory
                            </a>
                        </p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-4">
                        <p class="text-muted mb-3">Not yet saved as a product</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#saveProductModal">
                            <i class="fas fa-plus-circle me-2"></i>Save as Product
                        </button>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($calculation->notes)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $calculation->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
