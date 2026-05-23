@extends('layouts.app')
@section('title','New Karigar Settlement')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Karigar Settlement</h1>
        <a href="{{ route('production.settlements.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card shadow-sm" style="max-width:700px">
        <div class="card-body">
            <form action="{{ route('production.settlements.store') }}" method="POST">
                @csrf
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Karigar <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select select2" required><option value="">Select Karigar</option>@foreach($karigars as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>@foreach($branches as $b)<option value="{{ $b->id }}" {{ auth()->user()->branch_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Date <span class="text-danger">*</span></label><input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Metal Type <span class="text-danger">*</span></label>
                        <select name="metal_type" class="form-select" required><option value="gold">Gold</option><option value="silver">Silver</option></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Fine Weight (g) <span class="text-danger">*</span></label><input type="number" name="fine_weight_fixed" class="form-control" step="0.0001" min="0" required oninput="calcTotal()"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Rate (Rs./g) <span class="text-danger">*</span></label><input type="number" name="fixed_rate" class="form-control" step="0.01" min="0" required value="{{ $goldRate?->rate_22k??5500 }}" oninput="calcTotal()"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Metal Value</label><input type="text" id="metalValue" class="form-control bg-light" readonly value="Rs. 0"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Labor Amount (Rs.)</label><input type="number" name="labor_amount" class="form-control" step="0.01" min="0" value="0" oninput="calcTotal()"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Other Charges (Rs.)</label><input type="number" name="other_charges" class="form-control" step="0.01" min="0" value="0" oninput="calcTotal()"></div>
                </div>
                <div class="alert alert-success py-2 mb-3"><strong>Total Amount: <span id="totalAmount">Rs. 0</span></strong></div>
                <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                <button type="submit" class="btn btn-success">Create Settlement</button>
            </form>
        </div>
    </div>
</div>
<script>
function calcTotal() {
    const fw = parseFloat(document.querySelector('[name=fine_weight_fixed]').value)||0;
    const rate = parseFloat(document.querySelector('[name=fixed_rate]').value)||0;
    const labor = parseFloat(document.querySelector('[name=labor_amount]').value)||0;
    const other = parseFloat(document.querySelector('[name=other_charges]').value)||0;
    const metal = fw * rate;
    const total = metal + labor + other;
    document.getElementById('metalValue').value = 'Rs. '+Math.round(metal).toLocaleString();
    document.getElementById('totalAmount').textContent = 'Rs. '+Math.round(total).toLocaleString();
}
</script>
@endsection
