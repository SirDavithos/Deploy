<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de Tiendas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Listado de Tiendas - Punto Boliviano</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Dueño</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shops as $shop)
                <tr>
                    <td>{{ $shop->id }}</td>
                    <td>{{ $shop->name }}</td>
                    <td>{{ $shop->owner->first_name }} {{ $shop->owner->paternal_last_name }}</td>
                    <td>{{ $shop->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>