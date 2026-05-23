@extends('layouts.app')
@section('title','BOM Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">{{ $bom->name }}</h1><small class="text-muted">{{ $bom->bom_number }}</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('production.bom.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <a href="{{ route('production.jobs.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> Create Job from BOM</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">BOM Specifications</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>BOM Number</th><td><code>{{ $bom->bom_number }}</code></td></tr>
                        <tr><th>Linked Product</th><td>{{ $bom->product->name ?? '—' }}</td></tr>
                        <tr><th>Purity</th><td>{{ $bom->purity->name ?? '—' }}</td></tr>
                        <tr><th>Gross Weight</th><td>{{ number_format($bom->expected_gross_weight,3) }}g</td></tr>
                        <tr><th>Net Weight</th><td>{{ number_format($bom->expected_net_weight,3) }}g</td></tr>
                        <tr><th>Allowed Wastage</th><td>{{ $bom->allowed_wastage_percentage }}%</td></tr>
                        <tr><th>Est. Labor Cost</th><td>Rs. {{ number_format($bom->estimated_labor_cost,0) }}</td></tr>
                        <tr><th>Total Est. Cost</th><td><strong>Rs. {{ number_format($bom->calculateTotalEstimatedCost(),0) }}</strong></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Materials List</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>Item</th><th>Qty</th><th>Unit</th><th>Weight</th><th class="text-end">Est. Cost</th></tr></thead>
                        <tbody>
                            @forelse($bom->items as $item)
                            <tr>
                                <td><span class="badge bg-{{ $item->type=='metal'?'warning text-dark':($item->type=='stone'?'info':'secondary') }}">{{ ucfirst($item->type) }}</span></td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ $item->weight ? number_format($item->weight,3).'g' : '—' }}</td>
                                <td class="text-end">{{ $item->estimated_cost ? 'Rs. '.number_format($item->estimated_cost,0) : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No items.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Production Jobs using this BOM</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Job #</th><th>Karigar</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($jobs as $j)
                            <tr>
                                <td><a href="{{ route('production.jobs.show',$j) }}"><code>{{ $j->job_number }}</code></a></td>
                                <td>{{ $j->karigar->name ?? 'Internal' }}</td>
                                <td><span class="badge bg-{{ $j->status=='completed'?'success':($j->status=='issued'?'primary':'warning text-dark') }}">{{ ucfirst($j->status) }}</span></td>
                                <td>{{ $j->created_at->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No jobs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
