@extends('layouts.app')
@section('title', 'Estadísticas')
@section('content')
    <style>
        :root {
            --fj-primary: #4e73df;
            --fj-success: #1cc88a;
            --fj-info: #36b9cc;
            --fj-warning: #f6c23e;
            --fj-danger: #e74a3b;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .analytics-container {
            background-color: #f8f9fc;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-custom thead th {
            background-color: #f8f9fc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #4e73df;
            border-top: none;
        }

        .chart-container {
            min-height: 300px;
        }

        .filter-bar {
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #eaecf4;
        }

        .badge-soft-primary {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--fj-primary);
        }

        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 0.75rem 1.25rem;
        }

        .list-group-item:first-child {
            border-top: none;
        }
    </style>

    <div class="container-fluid analytics-container py-4">
        {{-- Header & Filters --}}
        <div class="row mb-4 align-items-center">
            <div class="col-xl-4 col-12 mb-3 mb-xl-0">
                <h1 class="h3 mb-1 fw-bold text-gray-800">Analíticas del Portal</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Admin</a></li>
                        <li class="breadcrumb-item active">Métricas Reales</li>
                    </ol>
                </nav>
            </div>
            <div class="col-xl-8 col-12">
                <div class="filter-bar p-3 d-flex flex-wrap gap-2 justify-content-xl-end align-items-center">
                    <form method="GET" action="{{ route('admin.analytics.index') }}"
                        class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-calendar-alt text-muted"></i></span>
                            <input type="date" name="start_date" class="form-control border-start-0"
                                value="{{ $startDate->format('Y-m-d') }}">
                            <input type="date" name="end_date" class="form-control"
                                value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <select name="island_id" class="form-select form-select-sm w-auto">
                            <option value="">Todas las islas</option>
                            @foreach ($islands as $island)
                                <option value="{{ $island->id }}" @selected(optional($selectedIsland)->id === $island->id)>{{ $island->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-search text-muted"></i></span>
                            <input type="text" name="query" class="form-control border-start-0" placeholder="Buscar..."
                                value="{{ $searchTerm }}">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">
                            <i class="fas fa-sync-alt me-1"></i> Filtrar
                        </button>
                    </form>
                    <div class="vr mx-2 d-none d-md-block"></div>
                    <div class="dropdown">
                        <button class="btn btn-outline-dark btn-sm rounded-pill dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Exportar
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item"
                                    href="{{ route('admin.analytics.export', ['format' => 'pdf', ...request()->query()]) }}"><i
                                        class="fas fa-file-pdf text-danger me-2"></i>PDF / Imprimir</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('admin.analytics.export', ['format' => 'excel', ...request()->query()]) }}"><i
                                        class="fas fa-file-excel text-success me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.analytics.job_offers.daily', request()->all()) }}"
                        class="btn btn-outline-info btn-sm rounded-pill">
                        <i class="fas fa-chart-bar me-1"></i> Ver ofertas diarias
                    </a>
                </div>
            </div>
        </div>

        {{-- Status Badges if filtered --}}
        @if ($selectedIsland || $searchTerm)
            <div class="mb-4">
                @if ($selectedIsland)
                    <span
                        class="badge bg-white shadow-sm text-dark p-2 px-3 border-start border-primary border-4 rounded-0 me-2">Isla:
                        <strong>{{ $selectedIsland->name }}</strong></span>
                @endif
                @if ($searchTerm)
                    <span
                        class="badge bg-white shadow-sm text-dark p-2 px-3 border-start border-info border-4 rounded-0">Búsqueda:
                        <strong>{{ $searchTerm }}</strong></span>
                @endif
            </div>
        @endif

        {{-- Key Metrics Cards --}}
        <div class="row g-3 mb-4">
            @php
                $cards = [
                    [
                        'label' => 'Visitas Totales',
                        'value' => $summary['total_views'],
                        'icon' => 'eye',
                        'color' => 'primary',
                    ],
                    [
                        'label' => 'Visitantes Únicos',
                        'value' => $summary['total_visitors'],
                        'icon' => 'users',
                        'color' => 'success',
                    ],
                    [
                        'label' => 'Promedio Diario',
                        'value' => $summary['avg_daily_views'],
                        'icon' => 'chart-line',
                        'color' => 'info',
                    ],
                    [
                        'label' => 'Nuevos Trabajadores',
                        'value' => $summary['new_workers'],
                        'icon' => 'user-plus',
                        'color' => 'warning',
                    ],
                    [
                        'label' => 'Nuevas Empresas',
                        'value' => $summary['new_companies'],
                        'icon' => 'building',
                        'color' => 'danger',
                    ],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="col-xl-2 col-md-4 col-sm-6 flex-grow-1">
                    <div class="card stat-card shadow-sm h-100 p-2">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="card-icon bg-primary bg-opacity-10 text-{{ $card['color'] }} me-3 d-flex align-items-center justify-content-center"
                                    style="width:36px;height:36px;border-radius:10px;">
                                    @switch($card['icon'])
                                        @case('chart-line')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                                viewBox="0 0 24 24">
                                                <path d="M3 3h2v16h16v2H3z" />
                                                <path d="M19.5 6.5 17 9l-4-4-6 6 1.5 1.5L13 8.5l4 4 3.5-3.5z" />
                                            </svg>
                                        @break

                                        @case('users')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M16 11c1.7 0 3-1.3 3-3s-1.3-3-3-3-3 1.3-3 3 1.3 3 3 3zm-8 0c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm0 2c-2.3 0-7 1.2-7 3.5V19h14v-2.5C15 14.2 10.3 13 8 13zm8 0c-.3 0-.6 0-.9.1 1.1.8 1.9 2 1.9 3.4V19h6v-2.5c0-2.3-4.7-3.5-7-3.5z" />
                                            </svg>
                                        @break

                                        @case('building')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M4 22h16v-2H4zm14-4V2H6v16h12zm-8-5H8V9h2zm0-4H8V5h2zm6 4h-2V9h2zm0-4h-2V5h2z" />
                                            </svg>
                                        @break

                                        @default
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                                viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" />
                                            </svg>
                                    @endswitch
                                </div>
                                <span class="text-xs text-uppercase fw-bold text-muted">{{ $card['label'] }}</span>
                            </div>
                            <h3 class="fw-bold mb-0">{{ number_format($card['value']) }}</h3>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            {{-- Left Column: Charts --}}
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-area me-2"></i>Tráfico Diario (Últimos
                            30 días)</h6>
                        <a href="{{ route('admin.analytics.job_offers.daily', request()->query()) }}"
                            class="btn btn-link btn-sm text-decoration-none fw-bold">Detalle por oferta <i
                                class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="trafficChartDaily"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-success"><i class="fas fa-calendar-check me-2"></i>Rendimiento
                                    Mensual</h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px;">
                                    <canvas id="trafficChartMonthly"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-warning"><i class="fas fa-history me-2"></i>Histórico Anual
                                </h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px;">
                                    <canvas id="trafficChartYearly"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top Content Tables --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-trophy me-2"></i>Top Ofertas más
                            Visualizadas
                        </h6>
                        <button class="btn btn-light btn-sm rounded-pill" data-toggle="modal"
                            data-target="#modalTopRutas">Ver todas las rutas <i
                                class="fas fa-external-link-alt ms-1"></i></button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Posición / Oferta</th>
                                        <th>Empresa</th>
                                        <th class="text-center">Visitas</th>
                                        <th class="text-center">Visitantes</th>
                                        <th class="pe-4 text-center">CTR Est.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($jobOfferPerformance as $index => $offer)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="badge bg-light text-dark me-2">#{{ $index + 1 }}</span>
                                                    <span
                                                        class="fw-bold text-gray-800">{{ \Illuminate\Support\Str::limit($offer->title, 40) }}</span>
                                                </div>
                                            </td>
                                            <td><span class="text-muted small">{{ $offer->company }}</span></td>
                                            <td class="text-center fw-bold">{{ number_format($offer->views) }}</td>
                                            <td class="text-center text-muted">{{ number_format($offer->visitors) }}</td>
                                            <td class="pe-4 text-center">
                                                @php $ctr = $offer->views > 0 ? ($offer->visitors / $offer->views) * 100 : 0; @endphp
                                                <span
                                                    class="text-{{ $ctr > 80 ? 'success' : 'primary' }} small fw-bold">{{ number_format($ctr, 1) }}%</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No hay datos
                                                suficientes para generar el ranking</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Side Metrics --}}
            <div class="col-xl-4">
                {{-- Efficiency Metrics --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
                    <div class="card-body bg-gradient-primary text-white p-4"
                        style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <h6 class="text-uppercase small fw-bold opacity-75 mb-4">Rendimiento del Proceso</h6>
                        <div class="row align-items-center mb-4">
                            <div class="col-8">
                                <p class="mb-1 small">Tiempo medio 1er CV</p>
                                <h2 class="mb-0 fw-bold">{{ $timeMetrics['avg_hours_to_first_cv'] }} <small
                                        class="fs-6">horas</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-hourglass-start fa-2x opacity-25"></i>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <p class="mb-1 small">Cierre de Oferta</p>
                                <h2 class="mb-0 fw-bold">{{ $timeMetrics['avg_days_to_close'] }} <small
                                        class="fs-6">días</small></h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-check-double fa-2x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Geo Stats --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">Distribución Geográfica</h6>
                    </div>
                    <div class="card-body">
                        @php $totalOffers = $geoStats->sum('offers_count'); @endphp
                        @foreach ($geoStats as $stat)
                            @php $percent = $totalOffers > 0 ? ($stat['offers_count'] / $totalOffers) * 100 : 0; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-bold">{{ $stat['island'] }}</span>
                                    <span class="small text-muted">{{ $stat['offers_count'] }} ofertas</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Companies Ranking --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">Top Empresas</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach ($companyViews->take(5) as $company)
                                <div class="list-group-item d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded p-2 me-3">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 small fw-bold">{{ $company->company_name }}</h6>
                                        <span class="text-muted text-xs">{{ number_format($company->visitors) }}
                                            visitantes</span>
                                    </div>
                                    <span
                                        class="badge badge-soft-primary rounded-pill">{{ number_format($company->views) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- User Types --}}
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">Tipos de Usuario</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach ($userTypeStats as $type)
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3 text-center">
                                        <div class="text-xs text-uppercase fw-bold text-muted mb-1">
                                            {{ $type->role ?? 'Invitado' }}</div>
                                        <div class="h5 mb-0 fw-bold">{{ number_format($type->views) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mantengo el modal y los scripts pero con retoques de UI para Bootstrap 5 --}}
    <div class="modal fade" id="modalTopRutas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold text-primary">Detalle de Rutas Visitadas</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ruta / URL</th>
                                    <th class="text-end">Visitas</th>
                                    <th class="text-end">Unicos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topRoutes as $route)
                                    <tr>
                                        <td class="small text-muted">{{ $route->label }}</td>
                                        <td class="text-end fw-bold">{{ number_format($route->views) }}</td>
                                        <td class="text-end">{{ number_format($route->visitors) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">No hay datos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Chart.defaults.font.family = "'Inter', 'Nunito', sans-serif";
                Chart.defaults.color = '#858796';

                // Estilo común para los tooltips
                const tooltipStyle = {
                    backgroundColor: '#fff',
                    titleColor: '#6e707e',
                    bodyColor: '#858796',
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10
                };

                // Gráfico Diario
                new Chart(document.getElementById("trafficChartDaily"), {
                    type: 'line',
                    data: {
                        labels: @json($daily30Labels),
                        datasets: [{
                            label: "Visitas",
                            tension: 0.4,
                            fill: true,
                            backgroundColor: "rgba(78, 115, 223, 0.05)",
                            borderColor: "#4e73df",
                            pointRadius: 4,
                            pointBackgroundColor: "#4e73df",
                            pointBorderColor: "#fff",
                            pointHoverRadius: 6,
                            data: @json($daily30Views),
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: tooltipStyle
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "#f0f0f0",
                                    borderDash: [5, 5]
                                }
                            }
                        }
                    }
                });

                // Gráfico Mensual
                new Chart(document.getElementById("trafficChartMonthly"), {
                    type: 'bar',
                    data: {
                        labels: @json($monthly12Labels),
                        datasets: [{
                            label: "Visitas",
                            backgroundColor: "#1cc88a",
                            borderRadius: 5,
                            data: @json($monthly12Views),
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "#f0f0f0"
                                }
                            }
                        }
                    }
                });

                // Gráfico Anual
                new Chart(document.getElementById("trafficChartYearly"), {
                    type: 'bar',
                    data: {
                        labels: @json($yearly5Labels),
                        datasets: [{
                            label: "Visitas",
                            backgroundColor: "#f6c23e",
                            borderRadius: 5,
                            data: @json($yearly5Views),
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "#f0f0f0"
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endsection
@endsection
