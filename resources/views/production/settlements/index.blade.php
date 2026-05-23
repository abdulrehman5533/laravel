@extends('layouts.app')
@section('title','Karigar Settlements')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">Karigar Settlements</h1><small class="text-muted">Karigar ko final payment karna</small></div>
        <a href="{{ route('production.settlements.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Settlement</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-warning mb-0">{{ $stats['pending'] }}</h4><small class="text-muted">Pending Settlements</small></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm text-center py-3"><h4 class="text-success mb-0">{{ $stats['paid'] }}</h4><small class="text-muted">Paid</small></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm text-center py-3 border-danger"><h4 class="text-danger mb-0">Rs. {{ number_format($stats['total_pending_amount'],0) }}</h4><small class="text-muted">Pending Amount</small></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>Settlement #</th><th>Karigar</th><th>Date</th><th>Metal</th><th>Fine Wt</th><th>Rate</th><th class="text-end">Metal Value</th><th class="text-end">Labor</th><th class="text-end">Total</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($settlements as $s)
                    <tr>
                        <td><code>{{ $s->settlement_number }}</code></td>
                        <td>{{ $s->karigar->name ?? '—' }}</td>
                        <td>{{ $s->date?->format('d M Y') }}</td>
                        <td><span class="badge bg-warning text-dark">{{ ucfirst($s->metal_type) }}</span></td>
                        <td>{{ number_format($s->fine_weight_fixed,4) }}g</td>
                        <td>Rs. {{ number_format($s->fixed_rate,0) }}</td>
                        <td class="text-end">Rs. {{ number_format($s->metal_value,0) }}</td>
                        <td class="text-end">Rs. {{ number_format($s->labor_amount,0) }}</td>
                        <td class="text-end fw-bold">Rs. {{ number_format($s->total_amount,0) }}</td>
                        <td><span class="badge bg-{{ $s->payment_status=='paid'?'success':'warning text-dark' }}">{{ ucfirst($s->payment_status) }}</span></td>
                        <td>
                            <a href="{{ route('production.settlements.show',$s) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            @if($s->payment_status=='pending')
                            <form action="{{ route('production.settlements.pay',$s) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success" onclick="return confirm('Mark as paid?')"><i class="fas fa-check"></i></button></form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No settlements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $settlements->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
