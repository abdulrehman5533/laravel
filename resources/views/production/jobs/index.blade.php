@extends('layouts.app')
@section('title','Production Jobs')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">Production Jobs</h1><small class="text-muted">Karigar ko metal issue aur receive karna</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('production.bom.index') }}" class="btn btn-outline-secondary"><i class="fas fa-list me-1"></i> BOM</a>
            <a href="{{ route('production.settlements.index') }}" class="btn btn-outline-warning"><i class="fas fa-handshake me-1"></i> Settlements</a>
            <a href="{{ route('production.jobs.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Job</a>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-warning mb-0">{{ $stats['pending'] }}</h4><small class="text-muted">Pending</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-primary mb-0">{{ $stats['issued'] }}</h4><small class="text-muted">Issued to Karigar</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-success mb-0">{{ $stats['completed'] }}</h4><small class="text-muted">Completed</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-danger mb-0">{{ number_format($stats['total_issued_weight'],3) }}g</h4><small class="text-muted">Metal with Karigar</small></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>Job #</th><th>Karigar</th><th>Branch</th><th>Issued Wt</th><th>Received Wt</th><th>Wastage</th><th>Labor</th><th>Expected</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($jobs as $j)
                    <tr>
                        <td><code>{{ $j->job_number }}</code></td>
                        <td>{{ $j->karigar->name ?? 'Internal' }}</td>
                        <td>{{ $j->branch->name ?? '—' }}</td>
                        <td>{{ $j->metal_weight_issued ? number_format($j->metal_weight_issued,3).'g' : '—' }}</td>
                        <td>{{ $j->metal_weight_received ? number_format($j->metal_weight_received,3).'g' : '—' }}</td>
                        <td>{{ $j->wastage_actual ? number_format($j->wastage_actual,4).'g' : '—' }}</td>
                        <td>{{ $j->labor_charges ? 'Rs. '.number_format($j->labor_charges,0) : '—' }}</td>
                        <td>{{ $j->expected_delivery_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $j->status=='completed'?'success':($j->status=='issued'?'primary':($j->status=='pending'?'warning text-dark':'secondary')) }}">{{ ucfirst($j->status) }}</span></td>
                        <td><a href="{{ route('production.jobs.show',$j) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No production jobs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $jobs->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
