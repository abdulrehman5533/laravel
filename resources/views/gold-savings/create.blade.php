@extends('layouts.app')
@section('title','New Gold Savings Scheme')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Gold Savings Scheme</h1>
        <a href="{{ route('gold-savings.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card shadow-sm" style="max-width:600px">
        <div class="card-body">
            <form action="{{ route('gold-savings.store') }}" method="POST">
                @csrf
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="mb-3">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select select2" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} — {{ $c->phone }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Scheme Name <span class="text-danger">*</span></label>
                    <input type="text" name="scheme_name" class="form-control" required placeholder="e.g. 12 Month Gold Plan">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Monthly Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" name="monthly_amount" class="form-control" step="0.01" min="1" required placeholder="5000">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duration (Months) <span class="text-danger">*</span></label>
                        <select name="duration_months" class="form-select" required>
                            @foreach([3,6,9,11,12,18,24,36] as $m)
                            <option value="{{ $m }}" {{ $m==12?'selected':'' }}>{{ $m }} Months</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
                <div class="alert alert-info py-2 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Current Gold Rate: <strong>Rs. {{ number_format($goldRate?->rate_22k??5500,0) }}/g (22K)</strong>
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Create Scheme</button>
            </form>
        </div>
    </div>
</div>
@endsection
