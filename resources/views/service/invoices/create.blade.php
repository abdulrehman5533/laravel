@extends('layouts.app')

@section('title', 'Generate Karigar Invoice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Generate Karigar Invoice</h1>
        <a href="{{ route('service.invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('service.invoices.create') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Select Karigar</label>
                    <select name="karigar_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Select Karigar</option>
                        @foreach($karigars as $karigar)
                            <option value="{{ $karigar->id }}" {{ ($selectedKarigar && $selectedKarigar->id == $karigar->id) ? 'selected' : '' }}>
                                {{ $karigar->name }} ({{ $karigar->company_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-0">Select a Karigar to see pending job items for billing.</p>
                </div>
            </form>
        </div>
    </div>

    @if($selectedKarigar)
    <form action="{{ route('service.invoices.store') }}" method="POST">
        @csrf
        <input type="hidden" name="karigar_id" value="{{ $selectedKarigar->id }}">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Pending Job Items for {{ $selectedKarigar->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="select-all"></th>
                                <th>Job #</th>
                                <th>Ornament</th>
                                <th>Received Weight</th>
                                <th>Wastage</th>
                                <th>Labor Charge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingItems as $item)
                            <tr>
                                <td>
                                    <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" class="item-checkbox">
                                </td>
                                <td>{{ $item->serviceJob->job_number }}</td>
                                <td>{{ $item->ornament_name }}</td>
                                <td>{{ number_format($item->weight_received, 3) }}g</td>
                                <td>{{ number_format($item->wastage_actual, 3) }}g</td>
                                <td>Rs. {{ number_format($item->labor_charge, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No pending items found for this Karigar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pendingItems->count() > 0)
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Generate Invoice
                </button>
            </div>
            @endif
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#select-all').click(function() {
            $('.item-checkbox').prop('checked', this.checked);
        });
    });
</script>
@endpush
@endsection
