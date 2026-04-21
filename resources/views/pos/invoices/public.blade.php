@extends('layouts.app')

@section('title', 'Invoice - ' . $sale->invoice_no)

@section('content')
<div class="container py-5">
    <!-- Invoice Card -->
    <div class="card border-0 shadow-lg mx-auto" style="max-width: 900px;" id="invoiceContainer">
        <!-- Invoice Header -->
        <div class="card-body" style="border-bottom: 3px solid #d4af37; padding: 40px;">
            <div class="row mb-4">
                <div class="col-sm-6 text-center text-sm-start">
                    <h2 style="color: #d4af37; font-weight: 900; font-size: 32px;">
                        {{ config('pos.shop_name', 'JEWELS') }}
                    </h2>
                    <p class="text-muted mb-1">
                        📍 {{ config('pos.shop_address', 'Shop Address') }}
                    </p>
                    <p class="text-muted mb-1">
                        📱 {{ config('pos.shop_phone', '+92-300-000-0000') }}
                    </p>
                </div>
                <div class="col-sm-6 text-center text-sm-end mt-3 mt-sm-0">
                    <h4 class="text-muted mb-3">{{ $sale->is_wholesale ? 'WHOLESALE INVOICE' : 'TAX INVOICE' }}</h4>
                    <p class="mb-1">
                        <strong>Invoice #:</strong> 
                        <span style="font-size: 18px; color: #d4af37; font-weight: bold;">{{ $sale->invoice_no }}</span>
                    </p>
                    <p class="mb-1">
                        <strong>Date:</strong> {{ $sale->sale_time->format('d M, Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card-body" style="padding: 30px; border-bottom: 1px solid #e0e0e0;">
            <div class="row">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <div style="padding: 15px; background-color: #f8f9fa; border-left: 3px solid #d4af37; border-radius: 4px; height: 100%;">
                        <h5 class="mb-3" style="color: #333; font-weight: bold;">Customer Details</h5>
                        @if($sale->customer)
                            <p class="mb-1"><strong>Name:</strong> {{ $sale->customer->name }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $sale->customer->phone ?? 'N/A' }}</p>
                        @else
                            <p class="text-muted mb-0">Walk-in Customer</p>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <div style="padding: 15px; background-color: #f8f9fa; border-left: 3px solid #d4af37; border-radius: 4px; height: 100%;">
                        <h5 class="mb-3" style="color: #333; font-weight: bold;">Payment Information</h5>
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $sale->payment_status === 'Paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($sale->payment_status) }}
                            </span>
                        </p>
                        <p class="mb-0">
                            <strong>Currency:</strong> {{ $sale->currency }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card-body px-0 py-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #d4af37; color: white;">
                        <tr>
                            <th class="ps-4 py-3">Description</th>
                            <th class="text-center py-3">Weights (g)</th>
                            <th class="text-center py-3">Purity</th>
                            <th class="text-end py-3">Rate/Unit</th>
                            <th class="text-end pe-4 py-3">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td class="ps-4 py-3">
                                <strong>{{ $item->description }}</strong>
                                <br><small class="text-muted">SKU: {{ $item->sku }}</small>
                                @if($item->stone_count > 0)
                                    <br><small class="text-muted">Stones: {{ $item->stone_type }} ({{ $item->stone_count }} pcs)</small>
                                @endif
                            </td>
                            <td class="text-center py-3">
                                @php
                                    $gross = $item->gross_weight > 0 ? $item->gross_weight : $item->weight;
                                    $stoneWt = $item->stone_weight ?? 0;
                                    $net = $item->net_weight > 0 ? $item->net_weight : ($gross - $stoneWt);
                                @endphp
                                <div class="small">Gross: {{ number_format($gross, 3) }}g</div>
                                <div class="small text-muted">Stone: {{ number_format($stoneWt, 3) }}g</div>
                                <div class="small fw-bold text-danger">Net: {{ number_format($net, 3) }}g</div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-light text-dark border">{{ $item->gold_purity ?? '—' }}</span>
                            </td>
                            <td class="text-end py-3">
                                {{ number_format($item->gold_rate > 0 ? $item->gold_rate : $item->unit_price, 2) }}
                                @if($item->making_charge_amount > 0)
                                    <br><small class="text-muted">Making: {{ number_format($item->making_charge_amount, 2) }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-4 py-3">
                                <strong>{{ number_format($item->line_total, 2) }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @php
            $totalGross = $sale->items->sum(function($i) { return $i->gross_weight > 0 ? $i->gross_weight : $i->weight; });
            $totalNet = $sale->items->sum(function($i) { return $i->net_weight > 0 ? $i->net_weight : ($i->gross_weight - $i->stone_weight); });
        @endphp

        <div class="px-4 mb-4">
            <div class="row g-2 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block text-uppercase">Total Gross</small>
                        <span class="fw-bold">{{ number_format($totalGross, 3) }}g</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 border rounded bg-light">
                        <small class="text-muted d-block text-uppercase">Total Net</small>
                        <span class="fw-bold">{{ number_format($totalNet, 3) }}g</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totals Section -->
        <div class="card-body" style="padding: 30px;">
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 4px;">
                        <table style="width: 100%; font-size: 14px;">
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px 0;">Subtotal:</td>
                                <td style="text-align: right; padding: 8px 0;">
                                    {{ number_format($sale->subtotal, 2) }}
                                </td>
                            </tr>
                            @if($sale->making_charges > 0)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px 0;">Aggregate Making:</td>
                                <td style="text-align: right; padding: 8px 0;">
                                    {{ number_format($sale->making_charges, 2) }}
                                </td>
                            </tr>
                            @endif
                            @if($sale->wastage_amount > 0)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px 0;">Wastage Value:</td>
                                <td style="text-align: right; padding: 8px 0;">
                                    {{ number_format($sale->wastage_amount, 2) }}
                                </td>
                            </tr>
                            @endif
                            @if($sale->discount > 0)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px 0;">Discount:</td>
                                <td style="text-align: right; padding: 8px 0; color: #dc3545;">
                                    -{{ number_format($sale->discount, 2) }}
                                </td>
                            </tr>
                            @endif
                            @if($sale->tax_amount > 0)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px 0;">Tax:</td>
                                <td style="text-align: right; padding: 8px 0;">
                                    {{ number_format($sale->tax_amount, 2) }}
                                </td>
                            </tr>
                            @endif
                            <tr style="background-color: #d4af37; color: white;">
                                <td style="padding: 12px 10px; font-weight: bold; font-size: 16px;">TOTAL:</td>
                                <td style="text-align: right; padding: 12px 10px; font-weight: bold; font-size: 16px;">
                                    {{ number_format($sale->total, 2) }} {{ $sale->currency }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-body" style="padding: 20px; background-color: #f8f9fa; border-top: 1px solid #d4af37; text-align: center;">
            <p class="text-muted mb-0" style="font-size: 12px;">
                {{ config('app.name') }} © {{ now()->year }} | Official Digital Invoice
            </p>
        </div>
    </div>
    
    <div class="text-center mt-4 no-print">
        <button class="btn btn-gold px-4 py-2 fw-bold shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Print Copy
        </button>
    </div>
</div>

<style>
    .btn-gold {
        background-color: #d4af37;
        color: white;
        border: none;
    }
    .btn-gold:hover {
        background-color: #b8962d;
        color: white;
    }
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .card { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: none !important; }
    }
</style>
@endsection
