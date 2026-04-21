<!DOCTYPE html>
<html>
<head>
    <title>Stock Alert</title>
</head>
<body>
    <h1>Stock Alert: {{ $alert->severity == 'critical' ? 'CRITICAL' : 'WARNING' }}</h1>
    <p>{{ $alert->message }}</p>
    <hr>
    <h3>Product Details:</h3>
    <ul>
        <li><strong>Name:</strong> {{ $alert->product->name }}</li>
        <li><strong>SKU:</strong> {{ $alert->product->sku }}</li>
        <li><strong>Current Stock:</strong> {{ number_format($alert->product->current_stock, 2) }} {{ $alert->product->unit }}</li>
        <li><strong>Purity:</strong> {{ $alert->product->purity->name ?? 'N/A' }}</li>
        <li><strong>Branch:</strong> {{ $alert->product->branch->name ?? 'N/A' }}</li>
    </ul>
    <p>Please take necessary action to restock this item.</p>
</body>
</html>
