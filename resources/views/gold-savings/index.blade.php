@extends('layouts.app')
@section('title','Gold Savings Schemes')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">Gold Savings Schemes</h1><small class="text-muted">Monthly installment gold saving plans</small></div>
        <a href="{{ route('gold-savings.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Scheme</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-success mb-0">{{ $stats['active'] }}</h4><small class="text-muted">Active Schemes</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-secondary mb-0">{{ $stats['matured'] }}</h4><small class="text-muted">Matured</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-primary mb-0">Rs. {{ number_format($stats['total_accumulated'],0) }}</h4><small class="text-muted">Total Accumulated</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-warning mb-0">{{ number_format($stats['total_gold_weight'],3) }}g</h4><small class="text-muted">Total Gold Weight</small></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">All Schemes</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>Customer</th><th>Scheme</th><th class="text-end">Monthly</th><th>Duration</th><th>Start</th><th>End</th><th class="text-end">Accumulated</th><th class="text-end">Gold Wt</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($schemes as $s)
                    <tr>
                        <td>{{ $s->customer->name ?? '—' }}</td>
                        <td>{{ $s->scheme_name }}</td>
                        <td class="text-end">Rs. {{ number_format($s->monthly_amount,0) }}</td>
                        <td>{{ $s->duration_months }} months</td>
                        <td>{{ $s->start_date?->format('d M Y') }}</td>
                        <td>{{ $s->end_date?->format('d M Y') }}</td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($s->accumulated_amount,0) }}</td>
                        <td class="text-end text-warning fw-semibold">{{ number_format($s->accumulated_weight,4) }}g</td>
                        <td><span class="badge bg-{{ $s->status=='active'?'success':($s->status=='matured'?'primary':'secondary') }}">{{ ucfirst($s->status) }}</span></td>
                        <td><a href="{{ route('gold-savings.show',$s) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No schemes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $schemes->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
