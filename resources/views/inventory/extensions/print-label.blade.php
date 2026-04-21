<!DOCTYPE html>
<html>
<head>
    <title>Print Barcode Label - {{ $product->sku }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        @page { size: 50mm 25mm; margin: 0; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 2mm; width: 46mm; height: 21mm; overflow: hidden; }
        .label-container { display: flex; flex-direction: row; align-items: center; justify-content: space-between; }
        .details { font-size: 7pt; width: 60%; }
        .barcode-side { width: 35%; text-align: right; }
        .sku { font-weight: bold; font-size: 8pt; margin-bottom: 1mm; }
        .price { font-weight: bold; color: #000; }
        #barcode { width: 100%; height: auto; }
        #qrcode { width: 40px; height: 40px; margin-top: 1mm; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="generateCodes()">
    <div class="no-print" style="background: #eee; padding: 10px; margin-bottom: 10px;">
        <button onclick="window.print()">Print Label</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="label-container">
        <div class="details">
            <div class="sku">{{ $product->sku }}</div>
            <div>{{ $product->name }}</div>
            <div>{{ $product->purity->name ?? '' }} | {{ number_format($product->weight, 3) }}g</div>
            <div class="price">Rs. {{ number_format($product->selling_price, 2) }}</div>
        </div>
        <div class="barcode-side">
            <svg id="barcode"></svg>
            <div id="qrcode"></div>
        </div>
    </div>

    <script>
        function generateCodes() {
            // Generate Barcode
            JsBarcode("#barcode", "{{ $barcodeData['value'] }}", {
                format: "CODE128",
                width: 1,
                height: 30,
                displayValue: false
            });

            // Generate QR Code
            var qr = qrcode(0, 'M');
            qr.addData("{{ $barcodeData['value'] }}");
            qr.make();
            document.getElementById('qrcode').innerHTML = qr.createImgTag(2);
            
            // Auto print if requested
            if (window.location.search.includes('autoprint=1')) {
                setTimeout(() => window.print(), 500);
            }
        }
    </script>
</body>
</html>
