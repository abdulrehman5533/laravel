@extends('layouts.app')
@section('title','Job Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">{{ $job->job_number }}</h1><small class="text-muted">{{ $job->karigar->name ?? 'Internal' }}</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('production.jobs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            @if($job->status=='pending')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueModal"><i class="fas fa-arrow-right me-1"></i> Issue Metal</button>
            @elseif($job->status=='issued')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#receiveModal"><i class="fas fa-arrow-left me-1"></i> Receive Job</button>
            @endif
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Job Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Karigar</th><td>{{ $job->karigar->name ?? 'Internal' }}</td></tr>
                        <tr><th>Type</th><td>{{ ucfirst($job->karigar_type) }}</td></tr>
                        <tr><th>Branch</th><td>{{ $job->branch->name ?? '—' }}</td></tr>
                        <tr><th>Expected Delivery</th><td>{{ $job->expected_delivery_date?->format('d M Y') }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-{{ $job->status=='completed'?'success':($job->status=='issued'?'primary':'warning text-dark') }}">{{ ucfirst($job->status) }}</span></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Metal Tracking</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Issued Weight</th><td>{{ $job->metal_weight_issued ? number_format($job->metal_weight_issued,3).'g' : '—' }}</td></tr>
                        <tr><th>Issued Purity</th><td>{{ $job->issuedPurity->name ?? '—' }}</td></tr>
                        <tr><th>Fine Weight Issued</th><td>{{ $job->fine_weight_issued ? number_format($job->fine_weight_issued,4).'g' : '—' }}</td></tr>
                        <tr><th>Received Weight</th><td>{{ $job->metal_weight_received ? number_format($job->metal_weight_received,3).'g' : '—' }}</td></tr>
                        <tr><th>Wastage Actual</th><td class="{{ $job->wastage_actual > $job->wastage_allowed ? 'text-danger' : 'text-success' }}">{{ $job->wastage_actual ? number_format($job->wastage_actual,4).'g' : '—' }}</td></tr>
                        <tr><th>Labor Charges</th><td>{{ $job->labor_charges ? 'Rs. '.number_format($job->labor_charges,2) : '—' }}</td></tr>
                        <tr><th>Total Job Cost</th><td><strong>{{ $job->total_job_cost ? 'Rs. '.number_format($job->total_job_cost,2) : '—' }}</strong></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Issue Modal --}}
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('production.jobs.issue',$job) }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Issue Metal to Karigar</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Metal Weight (g) <span class="text-danger">*</span></label><input type="number" name="metal_weight_issued" class="form-control" step="0.001" min="0.001" required></div>
                <div class="mb-3"><label class="form-label">Purity <span class="text-danger">*</span></label>
                    <select name="issued_purity_id" class="form-select" required>
                        @foreach(\App\Models\PurityLevel::active()->get() as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->percentage }}%)</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Issue Metal</button></div>
        </div>
    </form></div>
</div>

{{-- Receive Modal --}}
<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('production.jobs.receive',$job) }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Receive Finished Job</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Received Weight (g) <span class="text-danger">*</span></label><input type="number" name="metal_weight_received" class="form-control" step="0.001" min="0.001" required></div>
                <div class="mb-3"><label class="form-label">Received Purity <span class="text-danger">*</span></label>
                    <select name="received_purity_id" class="form-select" required>
                        @foreach(\App\Models\PurityLevel::active()->get() as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Actual Wastage (g) <span class="text-danger">*</span></label><input type="number" name="wastage_actual" class="form-control" step="0.0001" min="0" required value="0"></div>
                <div class="mb-3"><label class="form-label">Other Charges (Rs.)</label><input type="number" name="other_charges" class="form-control" step="0.01" min="0" value="0"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Receive & Complete</button></div>
        </div>
    </form></div>
</div>
@endsection
