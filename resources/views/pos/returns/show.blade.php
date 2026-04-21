@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exchange-alt text-danger"></i> Return / Repair Details</h2>
        <div>
            <a href="{{ route('pos.returns.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card p-4">
        <h4>Invoice: <strong>{{ $return->sale->invoice_no ?? 'N/A' }}</strong></h4>
        <p>Type: <span class="badge bg-{{ $return->type === 'return' ? 'danger' : 'warning' }}">{{ ucfirst($return->type) }}</span></p>
        <p>Item: {{ $return->item->description ?? '-' }}</p>
        <p>Reason: {{ $return->reason ?? '-' }}</p>
        <p>Refund Amount: {{ $return->sale->currency ?? 'Rs' }} {{ number_format($return->refund_amount, 2) }}</p>
        <p>Status: <span class="badge bg-{{ $return->status === 'processed' ? 'success' : 'info' }}">{{ ucfirst($return->status) }}</span></p>

        @if($return->status === 'initiated')
        <form method="POST" action="{{ route('pos.returns.process-refund', $return) }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('Process refund and restock item?')">
                <i class="fas fa-undo-alt"></i> Process Refund
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
