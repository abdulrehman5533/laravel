@extends('layouts.app')
@section('title','Bill of Materials')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">Bill of Materials (BOM)</h1><small class="text-muted">Jewellery design templates</small></div>
        <a href="{{ route('production.bom.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> New BOM</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>BOM #</th><th>Name</th><th>Product</th><th>Purity</th><th>Gross Wt</th><th>Net Wt</th><th>Wastage %</th><th>Est. Labor</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($boms as $b)
                    <tr>
                        <td><code>{{ $b->bom_number }}</code></td>
                        <td>{{ $b->name }}</td>
                        <td>{{ $b->product->name ?? '—' }}</td>
                        <td>{{ $b->purity->name ?? '—' }}</td>
                        <td>{{ number_format($b->expected_gross_weight,3) }}g</td>
                        <td>{{ number_format($b->expected_net_weight,3) }}g</td>
                        <td>{{ $b->allowed_wastage_percentage }}%</td>
                        <td>Rs. {{ number_format($b->estimated_labor_cost,0) }}</td>
                        <td><span class="badge bg-{{ $b->is_active?'success':'secondary' }}">{{ $b->is_active?'Active':'Inactive' }}</span></td>
                        <td><a href="{{ route('production.bom.show',$b) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No BOMs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $boms->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
