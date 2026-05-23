@extends('layouts.app')
@section('title','New Production Job')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Production Job</h1>
        <a href="{{ route('production.jobs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card shadow-sm" style="max-width:700px">
        <div class="card-body">
            <form action="{{ route('production.jobs.store') }}" method="POST">
                @csrf
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">BOM (Optional)</label>
                        <select name="product_bom_id" class="form-select">
                            <option value="">No BOM / Custom Job</option>
                            @foreach($boms as $b)<option value="{{ $b->id }}">{{ $b->bom_number }} — {{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Karigar Type <span class="text-danger">*</span></label>
                        <select name="karigar_type" class="form-select" required id="karigarType" onchange="toggleKarigar()">
                            <option value="external">External Karigar</option>
                            <option value="internal">Internal Employee</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3" id="karigarDiv">
                    <label class="form-label">Karigar (Supplier)</label>
                    <select name="karigar_id" class="form-select select2">
                        <option value="">Select Karigar</option>
                        @foreach($karigars as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $b)<option value="{{ $b->id }}" {{ auth()->user()->branch_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Expected Delivery <span class="text-danger">*</span></label>
                        <input type="date" name="expected_delivery_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Labor Rate per Gram (Rs.)</label>
                        <input type="number" name="labor_rate_per_gram" class="form-control" step="0.01" min="0" placeholder="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Labor Rate per Piece (Rs.)</label>
                        <input type="number" name="labor_rate_per_piece" class="form-control" step="0.01" min="0" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Create Job</button>
            </form>
        </div>
    </div>
</div>
<script>
function toggleKarigar() {
    document.getElementById('karigarDiv').style.display = document.getElementById('karigarType').value === 'external' ? '' : 'none';
}
</script>
@endsection
