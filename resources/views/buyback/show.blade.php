@extends('layouts.app')
@section('title','Buyback Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">{{ $buyback->buyback_number }}</h1><small class="text-muted">{{ $buyback->created_at->format('d M Y') }}</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('buyback.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            @if($buyback->status=='pending')
            <form action="{{ route('buyback.approve',$buyback) }}" method="POST" class="d-inline">@csrf<button class="btn btn-primary">Approve</button></form>
            @endif
            @if($buyback->status=='approved')
            <form action="{{ route('buyback.complete',$buyback) }}" method="POST" class="d-inline">@csrf<button class="btn btn-success">Mark Complete & Pay</button></form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Buyback Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Customer</th><td>{{ $buyback->customer->name ?? '—' }}</td></tr>
                        <tr><th>Branch</th><td>{{ $buyback->branch->name ?? '—' }}</td></tr>
                        <tr><th>Item</th><td>{{ $buyback->item_description }}</td></tr>
                        <tr><th>Metal Type</th><td><span class="badge bg-warning text-dark">{{ $buyback->metal_type }}</span></td></tr>
                        <tr><th>Gross Weight</th><td>{{ number_format($buyback->gross_weight,3) }}g</td></tr>
                        <tr><th>Stone Weight</th><td>{{ number_format($buyback->stone_weight??0,3) }}g</td></tr>
                        <tr><th>Net Weight</th><td>{{ number_format($buyback->net_weight,3) }}g</td></tr>
                        <tr><th>Purity Reported</th><td>{{ $buyback->purity_reported }}%</td></tr>
                        <tr><th>Purity Tested</th><td>{{ $buyback->purity_tested }}%</td></tr>
                        <tr><th>Net Fine Weight</th><td><strong>{{ number_format($buyback->net_fine_weight,4) }}g</strong></td></tr>
                        <tr><th>Rate Applied</th><td>Rs. {{ number_format($buyback->rate_applied,2) }}/g</td></tr>
                        <tr><th>Total Value</th><td><strong class="text-success fs-5">Rs. {{ number_format($buyback->total_value,2) }}</strong></td></tr>
                        <tr><th>Exchange Type</th><td>{{ ucfirst(str_replace('_',' ',$buyback->exchange_type)) }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-{{ $buyback->status=='completed'?'success':($buyback->status=='approved'?'primary':'warning text-dark') }}">{{ ucfirst($buyback->status) }}</span></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if($buyback->internal_notes)
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Notes</div>
                <div class="card-body">{{ $buyback->internal_notes }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
