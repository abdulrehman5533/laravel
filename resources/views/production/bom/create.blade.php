@extends('layouts.app')
@section('title','New BOM')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Bill of Materials</h1>
        <a href="{{ route('production.bom.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <form action="{{ route('production.bom.store') }}" method="POST">
        @csrf
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">BOM Details</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">BOM Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="e.g. Gold Ring 22K Standard"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Linked Product</label><select name="inventory_product_id" class="form-select select2"><option value="">None</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3"><label class="form-label">Gross Weight (g) <span class="text-danger">*</span></label><input type="number" name="expected_gross_weight" class="form-control" step="0.001" min="0" required></div>
                            <div class="col-md-3 mb-3"><label class="form-label">Net Weight (g) <span class="text-danger">*</span></label><input type="number" name="expected_net_weight" class="form-control" step="0.001" min="0" required></div>
                            <div class="col-md-3 mb-3"><label class="form-label">Wastage % <span class="text-danger">*</span></label><input type="number" name="allowed_wastage_percentage" class="form-control" step="0.01" min="0" required value="2"></div>
                            <div class="col-md-3 mb-3"><label class="form-label">Purity</label><select name="purity_id" class="form-select"><option value="">Select</option>@foreach($purities as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Est. Labor Cost (Rs.)</label><input type="number" name="estimated_labor_cost" class="form-control" step="0.01" min="0" value="0"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Labor Type</label><select name="labor_type" class="form-select"><option value="per_gram">Per Gram</option><option value="per_piece">Per Piece</option><option value="fixed">Fixed</option></select></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                        BOM Items (Materials)
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addItem()"><i class="fas fa-plus me-1"></i> Add Item</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0" id="itemsTable">
                            <thead class="table-light"><tr><th>Type</th><th>Item Name</th><th>Qty</th><th>Unit</th><th>Weight (g)</th><th>Est. Cost</th><th></th></tr></thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td><select name="items[0][type]" class="form-select form-select-sm"><option value="metal">Metal</option><option value="stone">Stone</option><option value="other">Other</option></select></td>
                                    <td><input type="text" name="items[0][item_name]" class="form-control form-control-sm" required placeholder="e.g. Gold 22K"></td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm" step="0.001" min="0" required value="1"></td>
                                    <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" value="g"></td>
                                    <td><input type="number" name="items[0][weight]" class="form-control form-control-sm" step="0.001" min="0"></td>
                                    <td><input type="number" name="items[0][estimated_cost]" class="form-control form-control-sm" step="0.01" min="0"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Create BOM</button>
    </form>
</div>
<script>
let idx = 1;
function addItem() {
    const tbody = document.getElementById('itemsBody');
    tbody.insertAdjacentHTML('beforeend', `<tr class="item-row">
        <td><select name="items[${idx}][type]" class="form-select form-select-sm"><option value="metal">Metal</option><option value="stone">Stone</option><option value="other">Other</option></select></td>
        <td><input type="text" name="items[${idx}][item_name]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm" step="0.001" min="0" required value="1"></td>
        <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm" value="g"></td>
        <td><input type="number" name="items[${idx}][weight]" class="form-control form-control-sm" step="0.001" min="0"></td>
        <td><input type="number" name="items[${idx}][estimated_cost]" class="form-control form-control-sm" step="0.01" min="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i></button></td>
    </tr>`);
    idx++;
}
</script>
@endsection
