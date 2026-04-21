<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Jewelry Tags</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 100mm 25mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: white;
            -webkit-print-color-adjust: exact;
        }
        .tag-container {
            width: 100mm;
            height: 25mm;
            display: flex;
            page-break-after: always;
            position: relative;
            overflow: hidden;
            border-bottom: 1px dashed #ddd;
        }
        .tag-wing {
            width: 40mm;
            height: 100%;
            padding: 1.5mm 2mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .tag-bridge {
            width: 20mm;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-left: 0.1mm dashed #eee;
            border-right: 0.1mm dashed #eee;
        }
        .brand-name {
            font-size: 7pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
            margin-bottom: 0.5mm;
        }
        .product-title {
            font-size: 6.5pt;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #333;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            font-size: 6.5pt;
            line-height: 1.2;
            margin-bottom: 0.3mm;
        }
        .data-label {
            font-weight: 400;
            color: #666;
        }
        .data-value {
            font-weight: 700;
            color: #000;
        }
        .price-big {
            font-size: 9pt;
            font-weight: 900;
            margin-top: 1mm;
            border-top: 0.2mm solid #000;
            padding-top: 0.5mm;
            text-align: right;
        }
        .barcode-box {
            text-align: center;
            margin-top: 1mm;
        }
        .barcode-img {
            width: 100%;
            height: 8mm;
        }
        .sku-tag {
            font-size: 5.5pt;
            font-family: monospace;
            margin-top: -1mm;
        }
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 5pt;
            color: #999;
            text-transform: uppercase;
        }
        @media print {
            .tag-container { border-bottom: none; }
            .no-print { display: none !important; }
        }
        .no-print-bar {
            background: #1a1a1a;
            color: #d4af37;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: sans-serif;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .btn-print {
            background: #d4af37;
            color: #000;
            border: none;
            padding: 10px 25px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            text-transform: uppercase;
            transition: all 0.2s;
        }
        .btn-print:hover { background: #f1c40f; }
    </style>
</head>
<body>
    <div class="no-print no-print-bar">
        <div>
            <strong style="font-size: 1.2rem;">{{ config('app.name') }}</strong>
            <span style="margin-left: 15px; color: #888;">Professional Jewelry Tag Printer</span>
        </div>
        <button class="btn-print" onclick="window.print()">Confirm & Print Tags</button>
    </div>

    @foreach($products as $product)
    <div class="tag-container">
        <!-- Left Wing: Brand & Barcode (Front) -->
        <div class="tag-wing">
            <div>
                <div class="brand-name">{{ config('app.name') }}</div>
                <div class="product-title">{{ $product->name }}</div>
            </div>
            
            <div class="barcode-box">
                <svg class="barcode" 
                     jsbarcode-value="{{ $product->sku }}"
                     jsbarcode-format="CODE128"
                     jsbarcode-width="1.2"
                     jsbarcode-height="25"
                     jsbarcode-displayValue="false"
                     jsbarcode-margin="0">
                </svg>
                <div class="sku-tag">{{ $product->sku }}</div>
            </div>

            <div class="price-big">
                <span style="font-size: 6pt; font-weight: normal;">MRP</span> Rs. {{ number_format($product->selling_price, 0) }}
            </div>
        </div>

        <!-- Bridge: Narrow strip for jewelry -->
        <div class="tag-bridge">
            <div class="vertical-text">Certified Quality &bull; Wholesale ERP</div>
        </div>

        <!-- Right Wing: Technical Specs (Back) -->
        <div class="tag-wing">
            <div style="margin-top: 1mm;">
                <div class="data-row">
                    <span class="data-label">Gross Wt:</span>
                    <span class="data-value">{{ number_format($product->gross_weight ?: $product->weight, 3) }}g</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Net Wt:</span>
                    <span class="data-value">{{ number_format($product->net_weight ?: $product->weight, 3) }}g</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Stone Wt:</span>
                    <span class="data-value">{{ number_format($product->stone_carat ?: 0, 3) }}ct</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Purity:</span>
                    <span class="data-value">{{ $product->purity->name ?? 'N/A' }}</span>
                </div>
                @if($product->size)
                <div class="data-row">
                    <span class="data-label">Size:</span>
                    <span class="data-value">{{ $product->size }}</span>
                </div>
                @endif
                @if($product->hallmark)
                <div class="data-row">
                    <span class="data-label">Hallmark:</span>
                    <span class="data-value">{{ $product->hallmark }}</span>
                </div>
                @endif
            </div>

            <div style="text-align: right; font-size: 5pt; border-top: 0.1mm solid #eee; padding-top: 1mm;">
                <strong>ID:</strong> #{{ str_pad($product->id, 6, '0', STR_PAD_LEFT) }} | {{ date('M Y') }}
            </div>
        </div>
    </div>
    @endforeach

    <script>
        JsBarcode(".barcode").init();
        
        // Auto print if requested via query param
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('autoprint')) {
            window.onload = () => {
                setTimeout(() => window.print(), 500);
            };
        }
    </script>
</body>
</html>
