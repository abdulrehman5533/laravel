@extends('layouts.app')
@section('title','Scheme Details')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0">{{ $scheme->scheme_name }}</h1><small class="text-muted">{{ $scheme->customer->name ?? '—' }}</small></div>
        <div class="d-flex gap-2">
            <a href="{{ route('gold-savings.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            @if($scheme->status=='active')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fas fa-plus me-1"></i> Record Payment</button>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h5 class="text-primary mb-0">{{ $paidMonths }}/{{ $scheme->duration_months }}</h5><small class="text-muted">Months Paid</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h5 class="text-success mb-0">Rs. {{ number_format($scheme->accumulated_amount,0) }}</h5><small class="text-muted">Total Accumulated</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h5 class="text-warning mb-0">{{ number_format($scheme->accumulated_weight,4) }}g</h5><small class="text-muted">Gold Weight Earned</small></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm text-center py-3"><h5 class="text-danger mb-0">{{ $remainingMonths }}</h5><small class="text-muted">Months Remaining</small></div></div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Scheme Info</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Customer</th><td>{{ $scheme->customer->name ?? '—' }}</td></tr>
                        <tr><th>Monthly Amount</th><td>Rs. {{ number_format($scheme->monthly_amount,0) }}</td></tr>
                        <tr><th>Duration</th><td>{{ $scheme->duration_months }} months</td></tr>
                        <tr><th>Start Date</th><td>{{ $scheme->start_date?->format('d M Y') }}</td></tr>
                        <tr><th>End Date</th><td>{{ $scheme->end_date?->format('d M Y') }}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-{{ $scheme->status=='active'?'success':'primary' }}">{{ ucfirst($scheme->status) }}</span></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Payment History</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark"><tr><th>#</th><th>Date</th><th class="text-end">Amount</th><th class="text-end">Gold Rate</th><th class="text-end">Gold Weight</th></tr></thead>
                        <tbody>
                            @forelse($scheme->payments as $i => $p)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
                                <td class="text-end">Rs. {{ number_format($p->amount,0) }}</td>
                                <td class="text-end">Rs. {{ number_format($p->gold_rate,0) }}/g</td>
                                <td class="text-end text-warning fw-semibold">{{ number_format($p->gold_weight,4) }}g</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('gold-savings.payment',$scheme) }}" method="POST">@csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Record Monthly Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Amount (Rs.)</label><input type="number" name="amount" class="form-control" value="{{ $scheme->monthly_amount }}" required></div>
                <div class="mb-3"><label class="form-label">Gold Rate (Rs./g)</label><input type="number" name="gold_rate" class="form-control" value="{{ $goldRate?->rate_22k??5500 }}" required></div>
                <div class="mb-3"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="mb-3"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Record Payment</button></div>
        </div>
    </form></div>
</div>
@endsection
