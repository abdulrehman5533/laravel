<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Girvi Pledge Document #{{ $girvi->girvi_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .receipt-container { width: 800px; margin: 0 auto; padding: 30px; border: 1px solid #eee; }
        .header { text-align: center; border-bottom: 2px solid #d4af37; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #d4af37; text-transform: uppercase; letter-spacing: 2px; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f9f9f9; text-align: left; padding: 12px; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .financials { background: #fcfaf2; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .financial-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .financial-row.grand-total { font-size: 1.2em; font-weight: bold; color: #d4af37; border-top: 1px solid #d4af37; pt: 10px; }
        .terms { font-size: 0.85em; color: #777; margin-top: 50px; border-top: 1px solid #eee; pt: 20px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 80px; }
        .sig-box { text-align: center; width: 200px; border-top: 1px solid #333; pt: 10px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-container { border: none; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #d4af37; color: white; border: none; border-radius: 5px; cursor: pointer;">Print Document</button>
    </div>

    <div class="receipt-container">
        <div class="header">
            <h1>Girvi Pledge Agreement</h1>
            <p><strong>{{ $girvi->branch->name ?? 'Jewellery Store' }}</strong></p>
            <p>{{ $girvi->branch->address ?? '' }} | {{ $girvi->branch->phone ?? '' }}</p>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Customer Details</h3>
                <p><strong>Name:</strong> {{ $girvi->customer->name }}</p>
                <p><strong>Phone:</strong> {{ $girvi->customer->phone }}</p>
                <p><strong>Address:</strong> {{ $girvi->customer->address }}</p>
            </div>
            <div class="info-box" style="text-align: right;">
                <h3>Pledge Details</h3>
                <p><strong>Number:</strong> {{ $girvi->girvi_number }}</p>
                <p><strong>Date:</strong> {{ $girvi->girvi_date->format('d M Y') }}</p>
                <p><strong>Maturity:</strong> {{ $girvi->maturity_date ? $girvi->maturity_date->format('d M Y') : 'Open' }}</p>
            </div>
        </div>

        <h3>Pledged Items Manifest</h3>
        <table>
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Item Name</th>
                    <th>Metal</th>
                    <th>Purity</th>
                    <th>Gross Wt</th>
                    <th>Stone Wt</th>
                    <th>Net Wt</th>
                    <th>Fine Wt</th>
                    <th>Est. Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($girvi->items as $item)
                <tr>
                    <td><code>{{ $item->barcode }}</code></td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->item_type }}</td>
                    <td>{{ $item->purity }}</td>
                    <td>{{ $item->gross_weight }}g</td>
                    <td>{{ $item->stone_weight }}g</td>
                    <td>{{ $item->net_weight }}g</td>
                    <td class="fw-bold">{{ number_format($item->fine_weight, 3) }}g</td>
                    <td>Rs. {{ number_format($item->estimated_value, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="financials">
            <h3>Financial Agreement</h3>
            <div class="financial-row">
                <span>Principal Loan Amount:</span>
                <span class="fw-bold">Rs. {{ number_format($girvi->loan_amount, 2) }}</span>
            </div>
            <div class="financial-row">
                <span>Interest Rate:</span>
                <span>{{ $girvi->interest_rate }}% per {{ $girvi->interest_cycle }} ({{ ucfirst($girvi->interest_type) }})</span>
            </div>
            <div class="financial-row grand-total">
                <span>Total Principal Outstanding:</span>
                <span>Rs. {{ number_format($girvi->loan_amount, 2) }}</span>
            </div>
        </div>

        <div class="terms">
            <h4>Terms & Conditions</h4>
            <ol>
                <li>The borrower agrees to pay interest at the rate mentioned above.</li>
                <li>Items pledged will be returned only upon full settlement of principal and accrued interest.</li>
                <li>The lender is not responsible for natural wear and tear or minor weight differences during storage.</li>
                <li>If the loan is not settled within the maturity period, the lender reserves the right to auction the pledged items.</li>
            </ol>
        </div>

        <div class="signatures">
            <div class="sig-box">Borrower's Signature</div>
            <div class="sig-box">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>
