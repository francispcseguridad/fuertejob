@extends('layouts.app')
@section('title', 'Visitas por Oferta y Día')
@section('content')
    <style>
        .report-container {
            background-color: #f8f9fc;
            min-height: 100vh;
        }

        .filter-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .metric-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: white;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        }

        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .table-report thead th {
            background-color: #f1f4f8;
            color: #4e73df;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 800;
            padding: 12px 15px;
            border: none;
        }

        .table-report tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            color: #5a5c69;
            border-bottom: 1px solid #edf0f5;
        }

        .date-badge {
            background: #f8f9fc;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.85rem;
        }

        .company-tag {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #a0aec0;
            font-weight: 600;
            display: block;
        }

        .num-data {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
        }
    </style>

    <div class="container-fluid report-container py-4">
        {{-- Header Area --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h1 class="h3 mb-1 fw-bold text-gray-800">Rendimiento Diario</h1>
                <p class="text-muted small mb-0">Explora la evolución de impacto de tus ofertas publicadas.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="{{ route('admin.analytics.index') }}"
                    class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold">
                    <i class="fas fa-chevron-left me-2"></i> Dashboard Global
                </a>
            </div>
        </div>

        {{-- Filter Panel --}}
        <div class="filter-card p-3 mb-4">
            <form method="GET" action="{{ route('admin.analytics.job_offers.daily') }}" class="row g-2 align-items-end">
                <div class="col-xl-3 col-md-4">
                    <label class="small fw-bold text-muted mb-1 ms-1">Rango de fechas</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control"
                            value="{{ $startDate->format('Y-m-d') }}">
                        <span class="input-group-text bg-light">a</span>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="small fw-bold text-muted mb-1 ms-1">Isla</label>
                    <select name="island_id" class="form-select form-select-sm">
                        <option value="">Todas las islas</option>
                        @foreach ($islands as $island)
                            <option value="{{ $island->id }}" @selected($islandId === $island->id)>{{ $island->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label class="small fw-bold text-muted mb-1 ms-1">Oferta específica</label>
                    <select name="job_offer_id" class="form-select form-select-sm">
                        <option value="" @selected(!$jobOfferId)>Todas las ofertas</option>
                        @foreach ($availableOffers as $offer)
                            <option value="{{ $offer->id }}" @selected($jobOfferId === $offer->id)>
                                {{ \Illuminate\Support\Str::limit($offer->title, 45) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-6">
                    <label class="small fw-bold text-muted mb-1 ms-1">Título / Empresa</label>
                    <input type="text" name="query" class="form-control form-control-sm" placeholder="Buscar..."
                        value="{{ $searchTerm }}">
                </div>
                <div class="col-xl-2 col-md-6">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        {{-- Status Info --}}
        @if ($selectedIsland || $searchTerm || $selectedOffer)
            <div class="d-flex gap-2 mb-4 flex-wrap">
                @if ($selectedIsland)
                    <span class="badge bg-white text-primary border shadow-sm px-3 py-2 rounded-pill"><i
                            class="fas fa-map-marker-alt me-2"></i>{{ $selectedIsland->name }}</span>
                @endif
                @if ($searchTerm)
                    <span class="badge bg-white text-primary border shadow-sm px-3 py-2 rounded-pill"><i
                            class="fas fa-search me-2"></i>"{{ $searchTerm }}"</span>
                @endif
                @if ($selectedOffer)
                    <span class="badge bg-primary text-white shadow-sm px-3 py-2 rounded-pill"><i
                            class="fas fa-briefcase me-2"></i>{{ \Illuminate\Support\Str::limit($selectedOffer->title, 30) }}</span>
                @endif
            </div>
        @endif

        @php
            $totalViews = $dailyViews->sum('views');
            $totalVisitors = $dailyViews->sum('visitors');
            $daysCovered = $dailyViews->pluck('date')->unique()->count();
        @endphp

        {{-- Statistics Summary --}}
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card metric-card border-left-primary shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-xs fw-bold text-primary text-uppercase mb-1">Vistas en el Periodo</p>
                                <h3 class="mb-0 fw-bold text-gray-800">{{ number_format($totalViews) }}</h3>
                                <p class="text-muted small mb-0 mt-1">Total de impactos acumulados</p>
                            </div>
                            <i class="fas fa-eye fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card metric-card border-left-success shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-xs fw-bold text-success text-uppercase mb-1">Visitantes Únicos</p>
                                <h3 class="mb-0 fw-bold text-gray-800">{{ number_format($totalVisitors) }}</h3>
                                <p class="text-muted small mb-0 mt-1">Actividad en {{ $daysCovered }} días naturales</p>
                            </div>
                            <i class="fas fa-user-check fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card metric-card border-left-info shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-xs fw-bold text-info text-uppercase mb-1">Impacto Diario Medio</p>
                                <h3 class="mb-0 fw-bold text-gray-800">
                                    {{ $daysCovered > 0 ? number_format($totalViews / $daysCovered, 1) : 0 }}</h3>
                                <p class="text-muted small mb-0 mt-1">Promedio de visualizaciones/día</p>
                            </div>
                            <i class="fas fa-chart-line fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ul me-2 text-primary"></i>Desglose Cronológico</h5>
                <span class="badge bg-light text-muted fw-normal">{{ $dailyViews->count() }} registros encontrados</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-report mb-0 table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Oferta Laboral</th>
                                <th class="text-center">Métricas de Alcance</th>
                                <th class="text-end pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dailyViews as $row)
                                <tr>
                                    <td style="width: 140px;">
                                        <span class="date-badge fw-bold">
                                            {{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="company-tag">{{ $row->company }}</span>
                                        <span class="fw-bold d-block text-dark">{{ $row->title }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-4">
                                            <div class="text-center">
                                                <small class="text-muted d-block text-uppercase"
                                                    style="font-size: 0.6rem;">Visitantes</small>
                                                <span class="num-data">{{ number_format($row->visitors) }}</span>
                                            </div>
                                            <div class="text-center border-start ps-4">
                                                <small class="text-muted d-block text-uppercase"
                                                    style="font-size: 0.6rem;">Engagement</small>
                                                @php $rate = $row->views > 0 ? ($row->visitors / $row->views) * 100 : 0; @endphp
                                                <span class="num-data text-primary">{{ number_format($rate, 0) }}%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-block text-end">
                                            <small class="text-muted d-block text-uppercase"
                                                style="font-size: 0.6rem;">Vistas</small>
                                            <h5 class="num-data mb-0 text-dark">{{ number_format($row->views) }}</h5>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                            <h5 class="text-muted">No se encontraron registros para estos filtros</h5>
                                            <p class="small text-muted">Prueba a ampliar el rango de fechas o cambiar la
                                                isla seleccionada.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
