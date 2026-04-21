<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt #{{ $payment->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .receipt-card { width: 500px; margin: 50px auto; padding: 30px; border: 2px solid #d4af37; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { color: #d4af37; margin-bottom: 5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #eee; }
        .total-row { margin-top: 20px; font-size: 1.4em; font-weight: bold; color: #333; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.8em; color: #777; }
        @media print { .no-print { display: none; } .receipt-card { border: 1px solid #000; margin: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()">Print Receipt</button>
    </div>

    <div class="receipt-card">
        <div class="header">
            <h2>Payment Receipt</h2>
            <p>{{ $payment->girvi->branch->name }}</p>
        </div>

        <div class="row">
            <span>Date:</span>
            <span>{{ $payment->payment_date->format('d M Y') }}</span>
        </div>
        <div class="row">
            <span>Receipt No:</span>
            <span>#PAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row">
            <span>Girvi Number:</span>
            <span>{{ $payment->girvi->girvi_number }}</span>
        </div>
        <div class="row">
            <span>Customer:</span>
            <span>{{ $payment->girvi->customer->name }}</span>
        </div>
        <hr>
        <div class="row">
            <span>Interest Component:</span>
            <span>Rs. {{ number_format($payment->interest_component, 2) }}</span>
        </div>
        <div class="row">
            <span>Principal Component:</span>
            <span>Rs. {{ number_format($payment->principal_component, 2) }}</span>
        </div>
        @if($payment->waiver_amount > 0)
        <div class="row" style="color: green;">
            <span>Waiver/Discount:</span>
            <span>- Rs. {{ number_format($payment->waiver_amount, 2) }}</span>
        </div>
        @endif
        
        <div class="row total-row">
            <span>TOTAL PAID:</span>
            <span>Rs. {{ number_format($payment->amount, 2) }}</span>
        </div>

        <div class="row" style="margin-top: 20px; border: none; font-size: 0.9em;">
            <span>Outstanding Balance:</span>
            <span>Rs. {{ number_format($payment->girvi->outstanding_amount, 2) }}</span>
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>Computer Generated Receipt</p>
        </div>
    </div>
</body>
</html>
