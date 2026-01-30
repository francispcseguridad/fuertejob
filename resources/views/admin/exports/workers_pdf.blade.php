<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Candidatos</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 0.5rem;
        }

        .filters {
            font-size: 10px;
            margin-bottom: 1rem;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f2f4f7;
            font-size: 11px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <h1>Listado de Candidatos</h1>
    <div class="filters">
        @foreach ($filters as $label => $value)
            @if ($value)
                {{ ucfirst($label) }}: {{ $value }} |
            @endif
        @endforeach
        Fecha: {{ now()->format('d/m/Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Ciudad</th>
                <th>País</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($workers as $worker)
                <tr>
                    <td>{{ $worker['id'] }}</td>
                    <td>{{ $worker['name'] }}</td>
                    <td>{{ $worker['email'] }}</td>
                    <td>{{ $worker['city'] }}</td>
                    <td>{{ $worker['country'] }}</td>
                    <td>{{ $worker['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">Sin registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
