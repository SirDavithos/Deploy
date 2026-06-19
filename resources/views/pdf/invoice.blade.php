<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0; }
        .info { margin-bottom: 15px; }
        .info p { margin: 3px 0; }
        .total { text-align: right; font-size: 14px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PUNTO BOLIVIANO - Factura de Compra</h1>
        <p>Fecha: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <p><strong>Comprador:</strong> {{ $order->buyer->first_name }} {{ $order->buyer->paternal_last_name }} (CI: {{ $order->buyer->ci_number }} {{ $order->buyer->ci_extension }})</p>
        <p><strong>Email:</strong> {{ $order->buyer->email }}</p>
        @if($order->taxData)
        <p><strong>Facturación:</strong> {{ $order->taxData->nit_or_ci }} - {{ $order->taxData->business_name }}</p>
        @endif
        <p><strong>Tienda:</strong> {{ $order->shop->name ?? 'No disponible' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->title ?? 'Producto eliminado' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }} BOB</td>
                    <td>{{ number_format($item->quantity * $item->price, 2) }} BOB</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL: {{ number_format($order->total, 2) }} BOB
    </div>
</body>
</html>