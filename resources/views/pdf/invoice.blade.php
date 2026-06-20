<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo #{{ $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Punto Boliviano - Recibo de Compra</h1>
    <p><strong>Pedido #{{ $order->id }}</strong></p>
    <p>Fecha: {{ optional($order->created_at)->format('d/m/Y H:i') }}</p>
    <p>Tienda: {{ $order->shop->name ?? 'No disponible' }}</p>
    <p>Comprador: {{ $order->buyer->first_name ?? '' }} {{ $order->buyer->paternal_last_name ?? '' }}</p>
    @if($order->taxData)
        <p>NIT/CI: {{ $order->taxData->nit_or_ci }} - {{ $order->taxData->business_name }}</p>
    @endif

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

    <h2 style="text-align: right; margin-top: 10px;">Total: {{ number_format($order->total, 2) }} BOB</h2>
</body>
</html>