@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Add New Product</h3>
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
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Material</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Weight (g)</label>
                        <input type="number" step="0.001" name="weight" class="form-control" value="{{ old('weight') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Purity (%)</label>
                        <input type="number" step="0.01" name="purity" class="form-control" value="{{ old('purity', 22) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Making Charge</label>
                        <input type="number" step="0.01" name="making_charge" class="form-control" value="{{ old('making_charge', 0) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stone Weight (g)</label>
                        <input type="number" step="0.001" name="stone_weight" class="form-control" value="{{ old('stone_weight') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stone Cost</label>
                        <input type="number" step="0.01" name="stone_cost" class="form-control" value="{{ old('stone_cost', 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gold Rate (22K per g)</label>
                        <input type="number" step="0.01" name="gold_rate" class="form-control" value="{{ old('gold_rate', optional($goldRate)->rate_22k) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', 1) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hallmark</label>
                        <input type="text" name="hallmark" class="form-control" value="{{ old('hallmark') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Certificate No.</label>
                        <input type="text" name="certificate_no" class="form-control" value="{{ old('certificate_no') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">You can upload multiple images (optional).</small>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
