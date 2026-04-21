@extends('layouts.app')

@section('title', 'Sale ' . $sale->invoice_number)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Invoice: {{ $sale->invoice_number }}
            @php $invoiceType = strpos($sale->invoice_number, 'KACHA') === 0 ? 'non-tax' : 'tax'; @endphp
            <span class="badge" style="font-size:1em; background:{{ $invoiceType==='tax' ? '#198754':'#0dcaf0' }};color:#fff;margin-left:10px;">
                {{ $invoiceType==='tax' ? 'TAX INVOICE' : 'NON-TAX / ESTIMATE' }}
            </span>
        </h3>
        <div>
            <a href="{{ route('pos.sales.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('pos.sales.invoice-a4', $sale->id) }}" target="_blank" class="btn btn-outline-primary">Print</a>
            <a href="{{ route('pos.sales.invoice-pdf', $sale->id) }}" target="_blank" class="btn btn-primary">Download PDF</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Customer</h5>
                    <p>{{ optional($sale->customer)->name ?? 'Walk-in' }}<br>
                    {{ optional($sale->customer)->phone }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <h5>Details</h5>
                    <p>Invoice: <strong>{{ $sale->invoice_number }}</strong><br>
                    Date: {{ $sale->created_at->format('Y-m-d H:i') }}<br>
                    Payment: {{ $sale->payment_status }} / {{ $sale->payment_method }}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Making Charge</th>
                            <th>Stone Cost</th>
                            <th>Gold Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>{{ optional($item->product)->name ?? 'Product #' . $item->product_id }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td>Rs. {{ number_format($item->total_price, 2) }}</td>
                            <td>Rs. {{ number_format($item->making_charge ?? 0, 2) }}</td>
                            <td>Rs. {{ number_format($item->stone_cost ?? 0, 2) }}</td>
                            <td>Rs. {{ number_format($item->gold_value ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    @if($sale->notes)
                        <h6>Notes</h6>
                        <p>{{ $sale->notes }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    <p>Subtotal: <strong>Rs. {{ number_format($sale->subtotal, 2) }}</strong></p>
                    @if($invoiceType==='tax')
                        <p>Tax: <strong>Rs. {{ number_format($sale->tax_amount ?? 0, 2) }}</strong></p>
                    @else
                        <p>Tax: <strong>--</strong></p>
                    @endif
                    <p>Discount: <strong>Rs. {{ number_format($sale->discount ?? 0, 2) }}</strong></p>
                    <h4>Total: <strong>Rs. {{ number_format($sale->total_amount, 2) }}</strong></h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
