<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Analíticas {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            color: #2c3e50;
            margin: 0;
            padding: 40px;
        }

        h1 {
            font-size: 1.6rem;
            margin-bottom: 5px;
        }

        h5 {
            font-size: 1rem;
            margin-bottom: .5rem;
            color: #4a5568;
        }

        .summary-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1 1 200px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            text-transform: uppercase;
            font-size: .75rem;
            letter-spacing: .4px;
            color: #4a5568;
        }

        .summary-card strong {
            display: block;
            font-size: 1.4rem;
            color: #111;
            margin-top: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            font-size: .85rem;
        }

        th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: .75rem;
            letter-spacing: .4px;
        }

        .text-right {
            text-align: right;
        }

        .section-title {
            margin-top: 30px;
            margin-bottom: 8px;
            font-size: 1rem;
            color: #1d3557;
            letter-spacing: .5px;
        }

        .tag {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 16px;
            background: #edf2ff;
            color: #4338ca;
            font-size: 0.75rem;
            margin-right: 4px;
        }

        @media print {
            body {
                padding: 10px;
            }

            .summary-card {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Analíticas del Portal</h1>
        <p>Rango: <strong>{{ $startDate->format('d/m/Y') }}</strong> — <strong>{{ $endDate->format('d/m/Y') }}</strong>
        </p>
        @if (!empty($selectedIsland))
            <p class="small mb-0">Isla: <span class="tag">{{ $selectedIsland->name }}</span></p>
        @endif
        @if (!empty($searchTerm))
            <p class="small mb-0">Filtro: <span class="tag">{{ $searchTerm }}</span></p>
        @endif
    </header>

    <div class="summary-grid">
        @foreach ($summary as $label => $value)
            <div class="summary-card">
                {{ ucwords(str_replace('_', ' ', $label)) }}
                <strong>{{ number_format($value) }}</strong>
            </div>
        @endforeach
    </div>

    <section>
        <div class="section-title">Top Ofertas (visitas)</div>
        <table>
            <thead>
                <tr>
                    <th>Oferta</th>
                    <th>Empresa</th>
                    <th class="text-right">Visitas</th>
                    <th class="text-right">Visitantes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobOfferPerformance as $offer)
                    <tr>
                        <td>{{ $offer->title }}</td>
                        <td>{{ $offer->company }}</td>
                        <td class="text-right">{{ number_format($offer->views) }}</td>
                        <td class="text-right">{{ number_format($offer->visitors) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Sin datos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section>
        <div class="section-title">Perfiles de trabajadores más visitados</div>
        <table>
            <thead>
                <tr>
                    <th>Perfil</th>
                    <th class="text-right">Visitas</th>
                    <th class="text-right">Visitantes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workerProfileViews as $profile)
                    <tr>
                        <td>{{ $profile->name }}</td>
                        <td class="text-right">{{ number_format($profile->views) }}</td>
                        <td class="text-right">{{ number_format($profile->visitors) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Sin datos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section>
        <div class="section-title">Compañías más vistas</div>
        <table>
            <thead>
                <tr>
                    <th>Compañía</th>
                    <th class="text-right">Visitas</th>
                    <th class="text-right">Visitantes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companyViews as $company)
                    <tr>
                        <td>{{ $company->company_name }}</td>
                        <td class="text-right">{{ number_format($company->views) }}</td>
                        <td class="text-right">{{ number_format($company->visitors) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Sin datos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section>
        <div class="section-title">Top páginas</div>
        <ul style="list-style:none; padding:0;">
            @forelse ($topPages as $page)
                <li style="margin-bottom:6px; display:flex; justify-content:space-between;">
                    <span>{{ \Illuminate\Support\Str::limit($page->url, 90) }}</span>
                    <span>{{ number_format($page->views) }} visitas</span>
                </li>
            @empty
                <li class="text-muted">Sin registros</li>
            @endforelse
        </ul>
    </section>

    <section>
        <div class="section-title">Distribución por tipo de usuario</div>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            @forelse ($userTypeStats as $type)
                <div style="border:1px solid #e2e8f0; border-radius:6px; padding:10px; flex:1 1 160px;">
                    <div class="text-muted small">{{ ucfirst($type->role ?? 'anónimo') }}</div>
                    <div class="fw-bold">{{ number_format($type->views) }} visitas</div>
                    <div class="text-muted small">{{ number_format($type->unique_sessions) }} sesiones únicas</div>
                </div>
            @empty
                <div class="text-muted">Sin datos registrados</div>
            @endforelse
        </div>
    </section>

    <script>
        window.onload = () => window.print();
    </script>
</body>

</html>
