@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tax Compliance Reports</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export PDF
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('compliance.tax.reports') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period Type</label>
                    <select name="period" class="form-select">
                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ $period == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if($period == 'monthly')
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @elseif($period == 'quarterly')
                <div class="col-md-2">
                    <label class="form-label">Quarter</label>
                    <select name="quarter" class="form-select">
                        <option value="1" {{ $quarter == 1 ? 'selected' : '' }}>Q1 (Jan-Mar)</option>
                        <option value="2" {{ $quarter == 2 ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                        <option value="3" {{ $quarter == 3 ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                        <option value="4" {{ $quarter == 4 ? 'selected' : '' }}>Q4 (Oct-Dec)</option>
                    </select>
                </div>
                @endif
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    @if($report)
    <div class="row">
        <!-- Summary Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales (Taxable)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($report['summary']->total_subtotal, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tax Collected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($report['summary']->total_tax, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Gross Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($report['summary']->total_sales, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $report['summary']->transaction_count }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tax Breakdown (by Rate)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tax Rate (%)</th>
                            <th>Total Tax Amount Collected</th>
                            <th>Calculated Taxable Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['tax_breakdown'] as $tax)
                        <tr>
                            <td>{{ number_format($tax->tax_percent, 2) }}%</td>
                            <td>{{ number_format($tax->total_tax_at_rate, 2) }}</td>
                            <td>{{ number_format($tax->tax_percent > 0 ? ($tax->total_tax_at_rate / ($tax->tax_percent / 100)) : 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No tax data found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
