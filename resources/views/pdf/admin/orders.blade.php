<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedidos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Listado de Pedidos - Punto Boliviano</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tienda</th>
                <th>Comprador</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->shop->name ?? 'Sin tienda' }}</td>
                    <td>{{ $order->buyer->first_name ?? '' }} {{ $order->buyer->paternal_last_name ?? '' }}</td>
                    <td>{{ $order->total }} BOB</td>
                    <td>{{ $order->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>