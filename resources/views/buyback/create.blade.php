@extends('layouts.app')
@section('title','New Buyback')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Buyback / Old Gold Purchase</h1>
        <a href="{{ route('buyback.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card shadow-sm" style="max-width:800px">
        <div class="card-body">
            <form action="{{ route('buyback.store') }}" method="POST">
                @csrf
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select select2" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} — {{ $c->phone }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $b)<option value="{{ $b->id }}" {{ auth()->user()->branch_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Item Description <span class="text-danger">*</span></label>
                    <input type="text" name="item_description" class="form-control" required placeholder="e.g. Old Gold Necklace 22K, Broken Ring">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Metal Type <span class="text-danger">*</span></label>
                        <select name="metal_type" class="form-select" required>
                            <option value="Gold">Gold</option>
                            <option value="Silver">Silver</option>
                            <option value="Platinum">Platinum</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Gross Weight (g) <span class="text-danger">*</span></label>
                        <input type="number" name="gross_weight" id="grossWeight" class="form-control" step="0.001" min="0.001" required oninput="calcValues()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stone Weight (g)</label>
                        <input type="number" name="stone_weight" id="stoneWeight" class="form-control" step="0.001" min="0" value="0" oninput="calcValues()">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purity Reported (%) <span class="text-danger">*</span></label>
                        <input type="number" name="purity_reported" id="purityReported" class="form-control" step="0.01" min="0" max="100" required placeholder="91.6" oninput="calcValues()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purity Tested (%) <span class="text-danger">*</span></label>
                        <input type="number" name="purity_tested" id="purityTested" class="form-control" step="0.01" min="0" max="100" required placeholder="91.6" oninput="calcValues()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Melting Loss (%)</label>
                        <input type="number" name="melting_loss_expected" id="meltingLoss" class="form-control" step="0.01" min="0" value="2" oninput="calcValues()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Rate per Gram (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" name="rate_applied" id="rateApplied" class="form-control" step="0.01" min="0" required value="{{ $goldRate?->rate_22k ?? 5500 }}" oninput="calcValues()">
                    </div>
                </div>

                {{-- Auto Calculation --}}
                <div class="card bg-light mb-3">
                    <div class="card-body py-2">
                        <div class="row text-center">
                            <div class="col-3"><small class="text-muted d-block">Net Weight</small><strong id="netWt">0.000g</strong></div>
                            <div class="col-3"><small class="text-muted d-block">Fine Weight</small><strong id="fineWt">0.0000g</strong></div>
                            <div class="col-3"><small class="text-muted d-block">After Melting Loss</small><strong id="afterLoss">0.0000g</strong></div>
                            <div class="col-3"><small class="text-muted d-block">Total Value</small><strong id="totalVal" class="text-success">Rs. 0</strong></div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="total_value" id="totalValueInput">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exchange Type <span class="text-danger">*</span></label>
                        <select name="exchange_type" class="form-select" required>
                            <option value="cash">Cash Payment</option>
                            <option value="exchange">Exchange for New Jewellery</option>
                            <option value="account_credit">Account Credit</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="internal_notes" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-success" onclick="document.getElementById('totalValueInput').value=document.getElementById('totalVal').textContent.replace('Rs. ','').replace(/,/g,'')">
                    <i class="fas fa-check me-1"></i> Create Buyback
                </button>
            </form>
        </div>
    </div>
</div>
<script>
function calcValues() {
    const gross = parseFloat(document.getElementById('grossWeight').value)||0;
    const stone = parseFloat(document.getElementById('stoneWeight').value)||0;
    const purity = parseFloat(document.getElementById('purityTested').value)||0;
    const loss = parseFloat(document.getElementById('meltingLoss').value)||0;
    const rate = parseFloat(document.getElementById('rateApplied').value)||0;
    const net = gross - stone;
    const fine = (net * purity) / 100;
    const afterLoss = fine * (1 - loss/100);
    const total = afterLoss * rate;
    document.getElementById('netWt').textContent = net.toFixed(3)+'g';
    document.getElementById('fineWt').textContent = fine.toFixed(4)+'g';
    document.getElementById('afterLoss').textContent = afterLoss.toFixed(4)+'g';
    document.getElementById('totalVal').textContent = 'Rs. '+Math.round(total).toLocaleString();
}
</script>
@endsection
