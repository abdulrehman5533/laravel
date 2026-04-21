{{-- resources/views/invoices/print.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .invoice-header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 28px; font-weight: bold; color: #8B4513; }
        .invoice-details { margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f8f9fa; }
        .total-section { text-align: right; margin-top: 30px; }
        .signature { margin-top: 50px; border-top: 1px solid #000; width: 200px; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="company-name">ARFI GEMS JEWELLERY SHOP</div>
        <div>123 SADDAR Gold Street, Karachi - 123456</div>
        <div>Phone: +91 9876543210
        @if($invoiceType === 'tax')
            | GSTIN: 27ABCDE1234F1Z5
        @endif
        </div>
        <div style="margin-top:10px;">
            <span class="badge" style="font-size:1.1em; background:{{ $invoiceType==='tax' ? '#198754':'#0dcaf0' }};color:#fff;padding:6px 16px;border-radius:6px;">
                {{ $invoiceType==='tax' ? 'TAX INVOICE' : 'NON-TAX / ESTIMATE / KACHA INVOICE' }}
            </span>
        </div>
    </div>
    
    <div class="invoice-details">
        <div style="float: left; width: 50%;">
            <strong>Customer Details:</strong><br>
            {{ $sale->customer->name ?? 'Walk-in Customer' }}<br>
            {{ $sale->customer->phone ?? '' }}<br>
            {{ $sale->customer->address ?? '' }}
            @if($invoiceType==='tax')
                @if($sale->customer && $sale->customer->gstin)
                    <br><strong>GSTIN:</strong> {{ $sale->customer->gstin }}
                @endif
            @endif
        </div>
        <div style="float: right; width: 50%; text-align: right;">
            <strong>Invoice #:</strong> {{ $sale->invoice_number }}<br>
            <strong>Date:</strong> {{ $sale->created_at->format('d/m/Y') }}<br>
            <strong>Time:</strong> {{ $sale->created_at->format('h:i A') }}
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Weight (g)</th>
                <th>Rate (Rs./g)</th>
                <th>Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->product->weight }}</td>
                <td>{{ number_format($item->unit_price / $item->product->weight, 2) }}</td>
                <td>{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-section">
        <div style="margin-bottom: 10px;">
            <span style="width: 150px; display: inline-block;">Subtotal:</span>
            <span style="width: 100px; display: inline-block; text-align: right;">Rs.{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        @if($invoiceType==='tax' && $sale->tax_amount > 0)
        <div style="margin-bottom: 10px;">
            <span style="width: 150px; display: inline-block;">Tax:</span>
            <span style="width: 100px; display: inline-block; text-align: right;">Rs.{{ number_format($sale->tax_amount, 2) }}</span>
        </div>
        @endif
        @if($sale->discount > 0)
        <div style="margin-bottom: 10px;">
            <span style="width: 150px; display: inline-block;">Discount:</span>
            <span style="width: 100px; display: inline-block; text-align: right;">-Rs.{{ number_format($sale->discount, 2) }}</span>
        </div>
        @endif
        <div style="font-size: 18px; font-weight: bold; margin-top: 20px;">
            <span style="width: 150px; display: inline-block;">Total Amount:</span>
            <span style="width: 100px; display: inline-block; text-align: right;">Rs.{{ number_format($sale->total_amount, 2) }}</span>
        </div>
        <div style="margin-top: 10px; font-style: italic;">
                <span>In Words: {{ \App\Helpers\CurrencyHelper::spellOut($sale->total_amount) }} Rupees Only</span>
        </div>
    </div>
    
    <div style="margin-top: 50px;">
        <div style="float: left; width: 50%;">
            <p>Customer Signature</p>
            <div class="signature"></div>
        </div>
        <div style="float: right; width: 50%; text-align: right;">
            <p>Authorized Signature</p>
            <div class="signature" style="margin-left: auto;"></div>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <div style="text-align: center; margin-top: 50px; font-size: 12px; color: #666;">
        Thank you for your business!<br>
        Goods sold are not returnable or exchangeable<br>
        Subject to City Jurisdiction<br>
        @if($invoiceType==='tax')
            <span style="color:#198754;">This is a computer-generated tax invoice. All taxes as applicable are included. Please retain for GST/VAT compliance.</span>
        @else
            <span style="color:#0dcaf0;">This is a non-tax/estimate/kacha invoice for rate confirmation or internal use. Not valid for GST/VAT claim.</span>
        @endif
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>