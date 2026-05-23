@extends('layouts.app')
@section('title', 'Purity Levels')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Purity Levels</h1>
            <small class="text-muted">Manage gold/silver purity standards (22K, 18K, 916 etc.)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPurityModal"><i class="fas fa-plus me-1"></i> Add Purity</button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Purity Levels ({{ $purities->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Name</th><th>Karat</th><th>Percentage (%)</th><th>Description</th><th>Products</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($purities as $p)
                    <tr>
                        <td><strong>{{ $p->name }}</strong></td>
                        <td><span class="badge bg-warning text-dark">{{ $p->karat }}K</span></td>
                        <td>{{ number_format($p->percentage, 2) }}%</td>
                        <td><small class="text-muted">{{ $p->description ?? '—' }}</small></td>
                        <td><span class="badge bg-primary">{{ $p->products_count }}</span></td>
                        <td><span class="badge bg-{{ $p->is_active ? 'success' : 'secondary' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPurity{{ $p->id }}"><i class="fas fa-edit"></i></button>
                            <form action="{{ route('inventory.purities.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editPurity{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog"><form action="{{ route('inventory.purities.update', $p) }}" method="POST">@csrf @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title">Edit: {{ $p->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $p->name }}" required></div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Karat</label><input type="number" name="karat" class="form-control" step="0.01" value="{{ $p->karat }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Percentage (%)</label><input type="number" name="percentage" class="form-control" step="0.0001" value="{{ $p->percentage }}" required></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{ $p->description }}"></div>
                                    <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $p->is_active ? 'checked' : '' }}><label class="form-check-label">Active</label></div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                            </div>
                        </form></div>
                    </div>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No purity levels yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addPurityModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('inventory.purities.store') }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Purity Level</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="e.g. 22K, 18K, 916"></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Karat <span class="text-danger">*</span></label><input type="number" name="karat" class="form-control" step="0.01" min="0" max="24" required placeholder="22"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Percentage (%) <span class="text-danger">*</span></label><input type="number" name="percentage" class="form-control" step="0.0001" min="0" max="100" required placeholder="91.6"></div>
                </div>
                <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" placeholder="e.g. Standard gold purity"></div>
                <div class="alert alert-info py-2 small mb-0">
                    Common: 24K=99.9% | 22K=91.6% | 21K=87.5% | 18K=75% | 14K=58.5% | Silver 925=92.5%
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Create</button></div>
        </div>
    </form></div>
</div>
@endsection
