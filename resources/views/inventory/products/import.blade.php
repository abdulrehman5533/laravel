@extends('layouts.app')
@section('title', 'Import Products')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Import Products (CSV)</h1>
            <small class="text-muted">Bulk upload products via CSV file</small>
        </div>
        <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Upload CSV File</div>
                <div class="card-body">
                    <form action="{{ route('inventory.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Max size: 2MB. Format: CSV with headers.</small>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i> Import Products</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">CSV Format Guide</div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Your CSV must have these column headers (first row):</p>
                    <table class="table table-sm table-bordered small">
                        <thead class="table-dark"><tr><th>Column</th><th>Required</th><th>Example</th></tr></thead>
                        <tbody>
                            <tr><td>name</td><td><span class="badge bg-danger">Yes</span></td><td>Gold Ring 22K</td></tr>
                            <tr><td>sku</td><td><span class="badge bg-secondary">No</span></td><td>PRD-001</td></tr>
                            <tr><td>category</td><td><span class="badge bg-secondary">No</span></td><td>Ring</td></tr>
                            <tr><td>purity</td><td><span class="badge bg-secondary">No</span></td><td>22K</td></tr>
                            <tr><td>weight</td><td><span class="badge bg-secondary">No</span></td><td>5.5</td></tr>
                            <tr><td>net_weight</td><td><span class="badge bg-secondary">No</span></td><td>5.2</td></tr>
                            <tr><td>cost_price</td><td><span class="badge bg-secondary">No</span></td><td>25000</td></tr>
                            <tr><td>selling_price</td><td><span class="badge bg-secondary">No</span></td><td>30000</td></tr>
                            <tr><td>current_stock</td><td><span class="badge bg-secondary">No</span></td><td>10</td></tr>
                            <tr><td>reorder_level</td><td><span class="badge bg-secondary">No</span></td><td>2</td></tr>
                        </tbody>
                    </table>
                    <a href="#" class="btn btn-sm btn-outline-primary" onclick="downloadSample()">
                        <i class="fas fa-download me-1"></i> Download Sample CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadSample() {
    const csv = `name,sku,category,purity,weight,net_weight,cost_price,selling_price,current_stock,reorder_level,reorder_quantity
Gold Ring 22K,PRD-001,Ring,22K,5.5,5.2,25000,30000,10,2,5
Silver Necklace,PRD-002,Necklace,925,15.0,14.5,5000,7000,5,1,3
Diamond Earring,PRD-003,Earring,18K,3.2,3.0,45000,55000,3,1,2`;
    const blob = new Blob([csv], {type: 'text/csv'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'sample-products.csv';
    a.click();
}
</script>
@endsection
