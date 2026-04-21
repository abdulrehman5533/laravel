@extends('layouts.app')

@section('title', 'Tag Preview - ' . config('app.name'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="fas fa-eye me-2" style="color: var(--primary);"></i>
                Tag Print Preview
            </h2>
            <p class="text-muted">Review the tags before sending them to the printer.</p>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.history.back()" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </button>
            <button id="printBtn" class="btn btn-primary px-4">
                <i class="fas fa-print me-2"></i>Print All ({{ count($products) }})
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-dark text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Live Preview (100mm x 25mm Standard)</h6>
                <div class="badge bg-primary">High Fidelity Mode</div>
            </div>
        </div>
        <div class="card-body bg-light" style="min-height: 400px; padding: 3rem;">
            <div class="d-flex flex-wrap justify-content-center gap-5">
                @foreach($products as $product)
                <div class="tag-preview-wrapper shadow-lg bg-white">
                    <!-- This mirrors the print template style but for web preview -->
                    <div class="tag-container-mock">
                        <div class="tag-wing-mock">
                            <div class="brand-name-mock">{{ config('app.name') }}</div>
                            <div class="product-title-mock">{{ $product->name }}</div>
                            <div class="barcode-box-mock">
                                <div class="barcode-placeholder">|| ||| || ||| |||</div>
                                <div class="sku-tag-mock">{{ $product->sku }}</div>
                            </div>
                            <div class="price-big-mock">Rs. {{ number_format($product->selling_price, 0) }}</div>
                        </div>
                        <div class="tag-bridge-mock">
                            <div class="vertical-text-mock">WHOLESALE ERP</div>
                        </div>
                        <div class="tag-wing-mock">
                            <div class="data-row-mock"><span>Gross:</span> <strong>{{ number_format($product->gross_weight ?: $product->weight, 3) }}g</strong></div>
                            <div class="data-row-mock"><span>Net:</span> <strong>{{ number_format($product->net_weight ?: $product->weight, 3) }}g</strong></div>
                            <div class="data-row-mock"><span>Purity:</span> <strong>{{ $product->purity->name ?? 'N/A' }}</strong></div>
                            <div class="data-row-mock"><span>Size:</span> <strong>{{ $product->size ?: '-' }}</strong></div>
                        </div>
                    </div>
                    <div class="p-2 text-center bg-white border-top">
                        <small class="text-muted fw-bold">{{ $product->sku }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<form id="printForm" action="{{ route('inventory.products.bulk-print-tags') }}" method="POST" target="_blank" class="d-none">
    @csrf
    <input type="hidden" name="product_ids" value="{{ implode(',', $products->pluck('id')->toArray()) }}">
</form>

<style>
    .tag-preview-wrapper {
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.2s;
        border: 1px solid #ddd;
    }
    .tag-preview-wrapper:hover {
        transform: translateY(-5px);
    }
    .tag-container-mock {
        width: 400px; /* Scaled up for screen preview (4x) */
        height: 100px;
        display: flex;
        font-family: 'Segoe UI', sans-serif;
    }
    .tag-wing-mock {
        width: 160px;
        height: 100%;
        padding: 8px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: white;
    }
    .tag-bridge-mock {
        width: 80px;
        height: 100%;
        background: #f8f9fa;
        border-left: 1px dashed #eee;
        border-right: 1px dashed #eee;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .brand-name-mock { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #b8860b; }
    .product-title-mock { font-size: 9px; font-weight: 600; color: #333; margin-top: 2px; }
    .barcode-box-mock { text-align: center; margin: 5px 0; }
    .barcode-placeholder { font-family: monospace; font-size: 16px; letter-spacing: -1px; }
    .sku-tag-mock { font-size: 8px; color: #666; }
    .price-big-mock { font-size: 14px; font-weight: 900; color: #000; border-top: 1px solid #000; text-align: right; }
    .vertical-text-mock { writing-mode: vertical-rl; transform: rotate(180deg); font-size: 8px; color: #ccc; }
    .data-row-mock { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 2px; }
</style>

<script>
document.getElementById('printBtn').addEventListener('click', function() {
    document.getElementById('printForm').submit();
});
</script>
@endsection
