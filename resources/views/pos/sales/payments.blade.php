@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Payment History - Invoice {{ $sale->invoice_no }}</h4>
                </div>
                <div class="card-body">
                    <!-- Payment Summary -->
                    @if($summary)
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Amount</h6>
                                    <h4>{{ $summary['total_amount'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Paid</h6>
                                    <h4>{{ $summary['total_paid'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Outstanding</h6>
                                    <h4>{{ $summary['outstanding_balance'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Status</h6>
                                    <h4>
                                        @if($summary['payment_status'] === 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($summary['payment_status'] === 'partial')
                                            <span class="badge badge-warning">Partial</span>
                                        @else
                                            <span class="badge badge-danger">Unpaid</span>
                                        @endif
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payments Table -->
                    @if($payments->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('d M, Y H:i') }}</td>
                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                        <td>{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->reference ?? '-' }}</td>
                                        <td>{{ $payment->recordedByUser ? $payment->recordedByUser->name : '-' }}</td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($payment->status === 'failed')
                                                <span class="badge badge-danger">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">No payments recorded for this sale.</div>
                    @endif
                    
                    <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-secondary">Back to Sale</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
