@extends('layouts.app')
@section('title', 'Product Categories')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Product Categories</h1>
            <small class="text-muted">Manage jewellery product categories</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCatModal"><i class="fas fa-plus me-1"></i> Add Category</button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Categories ({{ $categories->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Code</th><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td><code>{{ $cat->code ?? '—' }}</code></td>
                        <td><i class="fas {{ $cat->icon ?? 'fa-tag' }} me-2 text-warning"></i>{{ $cat->name }}</td>
                        <td><small class="text-muted">{{ $cat->description ?? '—' }}</small></td>
                        <td><span class="badge bg-primary">{{ $cat->products_count }}</span></td>
                        <td><span class="badge bg-{{ $cat->is_active ? 'success' : 'secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCat{{ $cat->id }}"><i class="fas fa-edit"></i></button>
                            <form action="{{ route('inventory.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editCat{{ $cat->id }}" tabindex="-1">
                        <div class="modal-dialog"><form action="{{ route('inventory.categories.update', $cat) }}" method="POST">@csrf @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title">Edit: {{ $cat->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $cat->name }}" required></div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="{{ $cat->code }}"></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Icon (FA class)</label><input type="text" name="icon" class="form-control" value="{{ $cat->icon }}" placeholder="fa-ring"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{ $cat->description }}"></div>
                                    <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $cat->is_active ? 'checked' : '' }}><label class="form-check-label">Active</label></div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                            </div>
                        </form></div>
                    </div>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('inventory.categories.store') }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="e.g. Necklace, Ring, Bangle"></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" placeholder="e.g. NECK"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Icon (FA class)</label><input type="text" name="icon" class="form-control" placeholder="fa-ring"></div>
                </div>
                <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
                <div class="alert alert-info py-2 small mb-0">Common: Ring, Necklace, Bangle, Earring, Bracelet, Pendant, Chain, Anklet, Brooch, Tikka</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Create</button></div>
        </div>
    </form></div>
</div>
@endsection
