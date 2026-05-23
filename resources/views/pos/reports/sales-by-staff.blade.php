@extends('layouts.app')
@section('title', 'Sales by Staff')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sales by Staff</h1>
            <small class="text-muted">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</small>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> POS</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Staff Performance</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Staff Name</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">Total Revenue</th>
                        <th class="text-end">Total Discount</th>
                        <th class="text-end">Avg Sale Value</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxRevenue = $staffSales->max('total_revenue') ?: 1; @endphp
                    @forelse($staffSales as $i => $staff)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $staff->name }}</strong>
                            @if($i == 0)<span class="badge bg-warning text-dark ms-1">Top Performer</span>@endif
                        </td>
                        <td class="text-end">{{ $staff->total_sales }}</td>
                        <td class="text-end fw-semibold text-success">Rs. {{ number_format($staff->total_revenue, 0) }}</td>
                        <td class="text-end text-danger">Rs. {{ number_format($staff->total_discount, 0) }}</td>
                        <td class="text-end">Rs. {{ number_format($staff->avg_sale, 0) }}</td>
                        <td style="width:200px">
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-success" style="width:{{ ($staff->total_revenue / $maxRevenue) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format(($staff->total_revenue / $maxRevenue) * 100, 1) }}%</small>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales data for this period.</td></tr>
                    @endforelse
                </tbody>
                @if($staffSales->count())
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="2" class="fw-bold">TOTAL</td>
                        <td class="text-end fw-bold text-warning">{{ $staffSales->sum('total_sales') }}</td>
                        <td class="text-end fw-bold text-warning">Rs. {{ number_format($staffSales->sum('total_revenue'), 0) }}</td>
                        <td class="text-end fw-bold text-danger">Rs. {{ number_format($staffSales->sum('total_discount'), 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
