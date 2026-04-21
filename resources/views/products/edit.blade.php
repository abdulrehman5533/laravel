@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Edit Product</h3>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to list</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SKU</label>
                        <input type="text" class="form-control" value="{{ $product->sku }}" disabled>
                        <small class="text-muted">SKU cannot be changed</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Material</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material', $product->material) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Weight (g)</label>
                        <input type="number" step="0.001" name="weight" class="form-control" value="{{ old('weight', $product->weight) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Purity (%)</label>
                        <input type="number" step="0.01" name="purity" class="form-control" value="{{ old('purity', $product->purity) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Making Charge</label>
                        <input type="number" step="0.01" name="making_charge" class="form-control" value="{{ old('making_charge', $product->making_charge) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stone Weight (g)</label>
                        <input type="number" step="0.001" name="stone_weight" class="form-control" value="{{ old('stone_weight', $product->stone_weight) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stone Cost</label>
                        <input type="number" step="0.01" name="stone_cost" class="form-control" value="{{ old('stone_cost', $product->stone_cost) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gold Rate (22K per g)</label>
                        <input type="number" step="0.01" name="gold_rate" class="form-control" value="{{ old('gold_rate', $product->gold_rate ?? optional($goldRate)->rate_22k) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hallmark</label>
                        <input type="text" name="hallmark" class="form-control" value="{{ old('hallmark', $product->hallmark) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Certificate No.</label>
                        <input type="text" name="certificate_no" class="form-control" value="{{ old('certificate_no', $product->certificate_no) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Images</label>
                        @if($product->images)
                            <div class="mb-2">
                                <small class="text-muted">Current images:</small>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    @foreach(json_decode($product->images, true) as $image)
                                        <img src="{{ asset('storage/' . $image) }}" alt="Product image" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Upload new images to replace existing ones (optional).</small>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection