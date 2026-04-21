@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Sale Returns - Invoice {{ $sale->invoice_no }}</h4>
                </div>
                <div class="card-body">
                    @if($returns->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Return Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Reason</th>
                                    <th>Refund Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returns as $return)
                                    <tr>
                                        <td>{{ $return->return_date ? $return->return_date->format('d M, Y') : '-' }}</td>
                                        <td>{{ $return->item ? $return->item->description : 'Full Sale Return' }}</td>
                                        <td><span class="badge badge-info">{{ ucfirst($return->type) }}</span></td>
                                        <td>{{ $return->reason ?? '-' }}</td>
                                        <td>{{ number_format($return->refund_amount, 2) }}</td>
                                        <td>
                                            @if($return->status === 'initiated')
                                                <span class="badge badge-warning">Initiated</span>
                                            @elseif($return->status === 'approved')
                                                <span class="badge badge-info">Approved</span>
                                            @elseif($return->status === 'processed')
                                                <span class="badge badge-success">Processed</span>
                                            @elseif($return->status === 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($return->status === 'initiated')
                                                <button class="btn btn-sm btn-success">Approve</button>
                                                <button class="btn btn-sm btn-danger">Reject</button>
                                            @elseif($return->status === 'approved')
                                                <button class="btn btn-sm btn-primary">Process</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">No returns for this sale.</div>
                    @endif
                    
                    <a href="{{ route('pos.sales.show', $sale) }}" class="btn btn-secondary">Back to Sale</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
