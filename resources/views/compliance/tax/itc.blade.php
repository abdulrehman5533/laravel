@extends('layouts.app')

@section('title', 'Input Tax Credit')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Input Tax Credit (ITC)</h1>
            <small class="text-muted">Tax paid on purchases — claimable against output tax</small>
        </div>
        <a href="{{ route('compliance.tax.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary --}}
    <div class="card border-success shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted mb-1">Total Input Tax Credit Available</p>
                <h3 class="text-success mb-0">Rs. {{ number_format($totalITC, 2) }}</h3>
                <small class="text-muted">For {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</small>
            </div>
            <i class="fas fa-receipt text-success fa-3x opacity-25"></i>
        </div>
    </div>

    {{-- Purchase-wise ITC Table --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Purchase-wise Tax Breakdown</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Tax Paid (ITC)</th>
                        <th>Taxable Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $p)
                    <tr>
                        <td><code>{{ $p->order_number ?? '#'.$p->id }}</code></td>
                        <td>{{ $p->supplier_name ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y') }}</td>
                        <td>Rs. {{ number_format($p->total_amount, 2) }}</td>
                        <td class="text-success fw-semibold">Rs. {{ number_format($p->tax_amount, 2) }}</td>
                        <td>Rs. {{ number_format($p->total_amount - $p->tax_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No purchase records found for this period.</td></tr>
                    @endforelse
                </tbody>
                @if($purchases->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-semibold">Total ITC:</td>
                        <td class="text-success fw-bold">Rs. {{ number_format($totalITC, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
