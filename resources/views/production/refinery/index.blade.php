@extends('layouts.app')
@section('title','Refinery Batches')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">Refinery Batches</h1><small class="text-muted">Old gold/buyback refining management</small></div>
        <a href="{{ route('production.refinery.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Batch</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-warning mb-0">{{ $stats['pending'] }}</h4><small class="text-muted">Pending</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-primary mb-0">{{ $stats['sent'] }}</h4><small class="text-muted">Sent to Refinery</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-success mb-0">{{ $stats['received'] }}</h4><small class="text-muted">Received</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-info mb-0">{{ number_format($stats['total_sent_weight'],3) }}g</h4><small class="text-muted">Total Sent</small></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>Batch #</th><th>Refiner</th><th>Sent Date</th><th>Gross Sent</th><th>Est. Fine</th><th>Actual Fine</th><th>Loss</th><th>Charges</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($batches as $b)
                    <tr>
                        <td><code>{{ $b->batch_number }}</code></td>
                        <td>{{ $b->refiner->name ?? '—' }}</td>
                        <td>{{ $b->sent_date?->format('d M Y') }}</td>
                        <td>{{ number_format($b->total_gross_weight_sent,3) }}g</td>
                        <td>{{ number_format($b->estimated_fine_weight_sent,4) }}g</td>
                        <td>{{ $b->actual_fine_weight_received ? number_format($b->actual_fine_weight_received,4).'g' : '—' }}</td>
                        <td class="text-danger">{{ $b->refining_loss_weight ? number_format($b->refining_loss_weight,4).'g' : '—' }}</td>
                        <td>{{ $b->refining_charges ? 'Rs. '.number_format($b->refining_charges,0) : '—' }}</td>
                        <td><span class="badge bg-{{ $b->status=='received'?'success':($b->status=='sent_to_refinery'?'primary':'warning text-dark') }}">{{ ucfirst(str_replace('_',' ',$b->status)) }}</span></td>
                        <td><a href="{{ route('production.refinery.show',$b) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No refinery batches yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $batches->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
