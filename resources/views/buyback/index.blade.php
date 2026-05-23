@extends('layouts.app')
@section('title','Buyback Management')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">Buyback / Old Gold Purchase</h1><small class="text-muted">Customer se purana gold/silver kharidna</small></div>
        <a href="{{ route('buyback.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Buyback</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-primary mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Total Buybacks</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-warning mb-0">{{ $stats['pending'] }}</h4><small class="text-muted">Pending</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-success mb-0">{{ $stats['completed'] }}</h4><small class="text-muted">Completed</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-info mb-0">Rs. {{ number_format($stats['total_value'],0) }}</h4><small class="text-muted">Total Value Paid</small></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Buybacks</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>Buyback #</th><th>Customer</th><th>Metal</th><th>Gross Wt</th><th>Net Fine Wt</th><th>Purity Tested</th><th class="text-end">Rate</th><th class="text-end">Value</th><th>Exchange</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($buybacks as $b)
                    <tr>
                        <td><code>{{ $b->buyback_number }}</code></td>
                        <td>{{ $b->customer->name ?? '—' }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $b->metal_type }}</span></td>
                        <td>{{ number_format($b->gross_weight,3) }}g</td>
                        <td>{{ number_format($b->net_fine_weight,4) }}g</td>
                        <td>{{ $b->purity_tested }}%</td>
                        <td class="text-end">Rs. {{ number_format($b->rate_applied,0) }}</td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($b->total_value,0) }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($b->exchange_type) }}</span></td>
                        <td><span class="badge bg-{{ $b->status=='completed'?'success':($b->status=='approved'?'primary':'warning text-dark') }}">{{ ucfirst($b->status) }}</span></td>
                        <td><a href="{{ route('buyback.show',$b) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No buybacks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $buybacks->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
