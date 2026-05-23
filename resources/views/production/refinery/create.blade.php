@extends('layouts.app')
@section('title','New Refinery Batch')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Refinery Batch</h1>
        <a href="{{ route('production.refinery.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card shadow-sm" style="max-width:700px">
        <div class="card-body">
            <form action="{{ route('production.refinery.store') }}" method="POST">
                @csrf
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>@foreach($branches as $b)<option value="{{ $b->id }}" {{ auth()->user()->branch_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label">Refiner (Supplier)</label>
                        <select name="refiner_id" class="form-select select2"><option value="">Select Refiner</option>@foreach($refiners as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Gross Weight Sent (g) <span class="text-danger">*</span></label><input type="number" name="total_gross_weight_sent" class="form-control" step="0.001" min="0.001" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Est. Fine Weight (g) <span class="text-danger">*</span></label><input type="number" name="estimated_fine_weight_sent" class="form-control" step="0.0001" min="0.001" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Sent Date <span class="text-danger">*</span></label><input type="date" name="sent_date" class="form-control" required value="{{ date('Y-m-d') }}"></div>
                </div>
                @if($pendingBuybacks->count())
                <div class="mb-3">
                    <label class="form-label">Include Buybacks (Optional)</label>
                    <div class="border rounded p-2" style="max-height:200px;overflow-y:auto">
                        @foreach($pendingBuybacks as $bb)
                        <div class="form-check">
                            <input type="checkbox" name="buyback_ids[]" value="{{ $bb->id }}" class="form-check-input">
                            <label class="form-check-label small">{{ $bb->buyback_number }} — {{ $bb->customer->name ?? '—' }} — {{ number_format($bb->net_fine_weight,4) }}g fine</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <button type="submit" class="btn btn-success">Create Batch</button>
            </form>
        </div>
    </div>
</div>
@endsection
