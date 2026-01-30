<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Empresas</title>
    <style>
        @page {
            size: landscape;
            margin: 15mm 10mm 15mm 10mm;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
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
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f2f4f7;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <h1>Listado de Empresas</h1>
    <div class="filters">
        @foreach ($filters as $label => $value)
            @if ($value)
                {{ ucfirst(str_replace('_', ' ', $label)) }}: {{ $value }} |
            @endif
        @endforeach
        Fecha: {{ now()->format('d/m/Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Web</th>
                <th>NIF</th>
                <th>Dirección</th>
                <th>Contacto</th>
                <th>Tel Contacto</th>
                <th>Email Contacto</th>
                <th>Activo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($companies as $company)
                <tr>
                    <td>{{ $company['company_name'] }}</td>
                    <td>{{ $company['website_url'] }}</td>
                    <td>{{ $company['vat_id'] }}</td>
                    <td>{{ $company['fiscal_address'] }}</td>
                    <td>{{ $company['contact'] }}</td>
                    <td>{{ $company['contact_phone'] }}</td>
                    <td>{{ $company['contact_email'] }}</td>
                    <td>{{ $company['island_id'] }}</td>
                    <td>{{ $company['activo'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;">Sin registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
