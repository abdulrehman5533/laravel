@extends('layouts.app')
@section('title','Refinery Batch')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">{{ $batch->batch_number }}</h1><small class="text-muted">{{ $batch->refiner->name ?? 'No Refiner' }}</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('production.refinery.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            @if($batch->status=='sent_to_refinery')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#receiveModal"><i class="fas fa-arrow-down me-1"></i> Receive Refined Metal</button>
            @endif
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Batch Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Batch #</th><td><code>{{ $batch->batch_number }}</code></td></tr>
                        <tr><th>Refiner</th><td>{{ $batch->refiner->name ?? '—' }}</td></tr>
                        <tr><th>Branch</th><td>{{ $batch->branch->name ?? '—' }}</td></tr>
                        <tr><th>Sent Date</th><td>{{ $batch->sent_date?->format('d M Y') }}</td></tr>
                        <tr><th>Gross Sent</th><td>{{ number_format($batch->total_gross_weight_sent,3) }}g</td></tr>
                        <tr><th>Est. Fine Sent</th><td>{{ number_format($batch->estimated_fine_weight_sent,4) }}g</td></tr>
                        <tr><th>Actual Fine Received</th><td class="text-success fw-semibold">{{ $batch->actual_fine_weight_received ? number_format($batch->actual_fine_weight_received,4).'g' : '—' }}</td></tr>
                        <tr><th>Refining Loss</th><td class="text-danger">{{ $batch->refining_loss_weight ? number_format($batch->refining_loss_weight,4).'g' : '—' }}</td></tr>
                        <tr><th>Refining Charges</th><td>{{ $batch->refining_charges ? 'Rs. '.number_format($batch->refining_charges,2) : '—' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-{{ $batch->status=='received'?'success':($batch->status=='sent_to_refinery'?'primary':'warning text-dark') }}">{{ ucfirst(str_replace('_',' ',$batch->status)) }}</span></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Included Buybacks</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Buyback #</th><th>Customer</th><th>Net Fine Wt</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($batch->buybacks as $bb)
                            <tr>
                                <td><a href="{{ route('buyback.show',$bb) }}"><code>{{ $bb->buyback_number }}</code></a></td>
                                <td>{{ $bb->customer->name ?? '—' }}</td>
                                <td>{{ number_format($bb->net_fine_weight,4) }}g</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($bb->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No buybacks linked.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('production.refinery.receive',$batch) }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Receive Refined Metal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">Est. Fine Weight: <strong>{{ number_format($batch->estimated_fine_weight_sent,4) }}g</strong></div>
                <div class="mb-3"><label class="form-label">Actual Gross Received (g) <span class="text-danger">*</span></label><input type="number" name="actual_gross_weight_received" class="form-control" step="0.001" min="0" required></div>
                <div class="mb-3"><label class="form-label">Actual Fine Received (g) <span class="text-danger">*</span></label><input type="number" name="actual_fine_weight_received" class="form-control" step="0.0001" min="0" required></div>
                <div class="mb-3"><label class="form-label">Refining Charges (Rs.)</label><input type="number" name="refining_charges" class="form-control" step="0.01" min="0" value="0"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Receive & Reconcile</button></div>
        </div>
    </form></div>
</div>
@endsection
