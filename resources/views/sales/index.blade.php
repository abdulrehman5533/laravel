@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Sales</h3>
        <a href="{{ route('pos.sales.create') }}" class="btn btn-primary">New Sale</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($sales->count())
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ optional($sale->customer)->name ?? 'Walk-in' }}</td>
                                <td>Rs.{{ number_format($sale->subtotal, 2) }}</td>
                                <td>Rs.{{ number_format($sale->tax_amount ?? 0, 2) }}</td>
                                <td>Rs.{{ number_format($sale->discount ?? 0, 2) }}</td>
                                <td>Rs.{{ number_format($sale->total_amount, 2) }}</td>
                                <td>{{ $sale->payment_status }} / {{ $sale->payment_method }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('pos.sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $sales->links() }}
                </div>
            @else
                <p class="text-muted">No sales found. <a href="{{ route('pos.sales.create') }}">Create the first sale</a>.</p>
            @endif
        </div>
    </div>
</div>
@endsection
