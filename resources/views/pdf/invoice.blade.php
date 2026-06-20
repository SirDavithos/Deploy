<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documento #{{ $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 2px 0; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 5px 8px; border: 1px solid #ccc; text-align: left; font-size: 9px; }
        th { background-color: #f2f2f2; }
        .info p { margin: 3px 0; font-size: 9px; }
        .total { text-align: right; font-size: 14px; font-weight: bold; margin-top: 10px; }
        .qr { text-align: center; margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 8px; text-align: center; color: #666; }
    </style>
</head>
<body>
    @php
        $shopTax = $order->shop->taxData->first();
        $isInvoice = $shopTax && $shopTax->tax_regimen === 'Régimen General';
        $docType = $isInvoice ? 'FACTURA' : 'RECIBO';
    @endphp

    <div class="header">
        <h1>PUNTO BOLIVIANO</h1>
        <p>{{ $docType }} DE COMPRA</p>
        <p>Pedido #{{ $order->id }} | Fecha: {{ optional($order->created_at)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Emisor:</strong></td>
                <td>
                    {{ $order->shop->name ?? 'No disponible' }}<br>
                    @if($shopTax)
                        NIT: {{ $shopTax->nit_or_ci }}<br>
                        Razón Social: {{ $shopTax->business_name }}<br>
                        Régimen: {{ $shopTax->tax_regimen }}<br>
                    @else
                        No registrado (recibo simple)<br>
                    @endif
                </td>
                <td><strong>Comprador:</strong></td>
                <td>
                    {{ $order->buyer->first_name ?? '' }} {{ $order->buyer->paternal_last_name ?? '' }}<br>
                    CI: {{ $order->buyer->ci_number ?? '' }} {{ $order->buyer->ci_extension ?? '' }}<br>
                    Email: {{ $order->buyer->email ?? '' }}<br>
                    @if($order->taxData)
                        NIT: {{ $order->taxData->nit_or_ci }}<br>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Producto</th>
                <th>P. Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->product->title ?? 'Producto eliminado' }}</td>
                    <td>{{ number_format($item->price, 2) }} BOB</td>
                    <td>{{ number_format($item->quantity * $item->price, 2) }} BOB</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL: {{ number_format($order->total, 2) }} BOB
    </div>

    <div class="qr">
        <img src="data:image/png;base64,{{ base64_encode($qr) }}" alt="QR de verificación" width="120">
        <p style="font-size: 8px;">Escanee este código para verificar el documento</p>
    </div>

    <div class="footer">
        <p>Este documento digital es válido como comprobante de compra según normativa SIN Bolivia.</p>
        <p>Punto Boliviano – La Paz, Bolivia</p>
    </div>
</body>
</html>