@extends('layouts.app')
@section('title','Settlement Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">{{ $settlement->settlement_number }}</h1><small class="text-muted">{{ $settlement->karigar->name ?? '—' }}</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('production.settlements.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            @if($settlement->payment_status=='pending')
            <form action="{{ route('production.settlements.pay',$settlement) }}" method="POST">@csrf<button class="btn btn-success">Mark as Paid</button></form>
            @endif
        </div>
    </div>
    <div class="card shadow-sm" style="max-width:600px">
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr><th>Settlement #</th><td><code>{{ $settlement->settlement_number }}</code></td></tr>
                <tr><th>Karigar</th><td>{{ $settlement->karigar->name ?? '—' }}</td></tr>
                <tr><th>Branch</th><td>{{ $settlement->branch->name ?? '—' }}</td></tr>
                <tr><th>Date</th><td>{{ $settlement->date?->format('d M Y') }}</td></tr>
                <tr><th>Metal Type</th><td><span class="badge bg-warning text-dark">{{ ucfirst($settlement->metal_type) }}</span></td></tr>
                <tr><th>Fine Weight Fixed</th><td>{{ number_format($settlement->fine_weight_fixed,4) }}g</td></tr>
                <tr><th>Fixed Rate</th><td>Rs. {{ number_format($settlement->fixed_rate,2) }}/g</td></tr>
                <tr><th>Metal Value</th><td>Rs. {{ number_format($settlement->metal_value,2) }}</td></tr>
                <tr><th>Labor Amount</th><td>Rs. {{ number_format($settlement->labor_amount,2) }}</td></tr>
                <tr><th>Other Charges</th><td>Rs. {{ number_format($settlement->other_charges,2) }}</td></tr>
                <tr class="table-success"><th>Total Amount</th><td><strong class="fs-5">Rs. {{ number_format($settlement->total_amount,2) }}</strong></td></tr>
                <tr><th>Payment Status</th><td><span class="badge bg-{{ $settlement->payment_status=='paid'?'success':'warning text-dark' }}">{{ ucfirst($settlement->payment_status) }}</span></td></tr>
                @if($settlement->notes)<tr><th>Notes</th><td>{{ $settlement->notes }}</td></tr>@endif
            </table>
        </div>
    </div>
</div>
@endsection
