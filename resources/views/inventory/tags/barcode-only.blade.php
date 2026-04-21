<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode - {{ $product->sku }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .barcode-container { padding: 40px; border: 1px solid #eee; border-radius: 10px; text-align: center; }
        button { margin-top: 20px; padding: 10px 20px; background: #d4af37; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        @media print { button { display: none; } .barcode-container { border: none; } }
    </style>
</head>
<body>
    <div class="barcode-container">
        <h3>{{ $product->name }}</h3>
        <svg id="barcode"></svg>
        <p>{{ $product->sku }}</p>
        <button onclick="window.print()">Print Barcode</button>
    </div>
    <script>
        JsBarcode("#barcode", "{{ $product->sku }}", {
            format: "CODE128",
            width: 2,
            height: 100,
            displayValue: true
        });
    </script>
</body>
</html>
