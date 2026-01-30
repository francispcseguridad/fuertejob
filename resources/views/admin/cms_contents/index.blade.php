@extends('layouts.app')

@section('title', 'Gestión de Contenido CMS')

@section('content')
    <div class="container py-4">
        <div class="rounded-4 bg-gradient shadow-sm p-4 p-md-5 mb-4 position-relative overflow-hidden"
            style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
            <div class="position-absolute top-0 end-0 opacity-25 pe-4 pt-4">
                <i class="bi bi-journal-text text-white" style="font-size: 7rem;"></i>
            </div>
            <div class="position-relative text-white">
                <p class="text-uppercase small fw-semibold text-white-50 tracking-wider mb-2">Portada y Blog</p>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h1 class="display-6 fw-bold mb-3">Gestión de Contenido CMS & Blog</h1>
                        <p class="mb-0 text-white-50" style="max-width: 620px;">
                            Administra las páginas legales, secciones informativas y artículos destacados del portal en un
                            solo panel con filtros inteligentes y acciones rápidas.
                        </p>
                    </div>
                    <a href="{{ route('admin.cms_contents.create') }}"
                        class="btn btn-light btn-lg rounded-pill fw-semibold shadow-lg">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Nuevo Contenido
                    </a>
                </div>
            </div>
        </div>


        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4">Filtros avanzados</h5>
                <form method="GET" action="{{ route('admin.cms_contents.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label for="search" class="form-label text-muted small text-uppercase fw-semibold">Buscar</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            class="form-control form-control-lg rounded-3" placeholder="Título o slug">
                    </div>
                    <div class="col-lg-3">
                        <label for="type" class="form-label text-muted small text-uppercase fw-semibold">Tipo</label>
                        <select name="type" id="type" class="form-select form-select-lg rounded-3">
                            <option value="">Todos</option>
                            @foreach ($types as $key => $name)
                                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="status" class="form-label text-muted small text-uppercase fw-semibold">Estado</label>
                        <select name="status" id="status" class="form-select form-select-lg rounded-3">
                            <option value="">Todos</option>
                            <option value="published" @selected(request('status') === 'published')>Publicado</option>
                            <option value="draft" @selected(request('status') === 'draft')>Borrador</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 w-100">
                            <i class="bi bi-funnel me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.cms_contents.index') }}"
                            class="btn btn-outline-secondary btn-lg rounded-3 w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if ($contents->count() > 0)
            <div class="row g-4">
                @foreach ($contents as $content)
                    <div class="col-xl-4 col-lg-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 pe-3 pt-3 d-flex gap-2">
                                <span class="badge rounded-pill bg-light text-dark text-uppercase fw-semibold">
                                    #{{ $content->id }}
                                </span>
                                <span
                                    class="badge rounded-pill {{ $content->is_published ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $content->is_published ? 'Publicado' : 'Borrador' }}
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase fw-semibold">
                                        {{ $types[$content->type] ?? ucfirst($content->type) }}
                                    </span>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">{{ $content->title }}</h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    <code class="text-secondary">{{ $content->slug }}</code>
                                </p>
                                <p class="text-muted mb-4 flex-grow-1">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($content->body), 140) }}
                                </p>
                                <div class="mb-4">
                                    <div class="d-flex flex-wrap gap-2 text-muted small">
                                        @if ($content->published_at)
                                            <span class="badge rounded-pill bg-light text-muted border">
                                                <i class="bi bi-calendar-week me-1 text-primary"></i>
                                                {{ optional($content->published_at)->format('d/m/Y') }}
                                            </span>
                                        @endif
                                        <span class="badge rounded-pill bg-light text-muted border">
                                            <i class="bi bi-clock-history me-1 text-primary"></i>
                                            Actualizado {{ $content->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <a href="{{ route('admin.cms_contents.edit', ['contenido' => $content->id]) }}"
                                        class="btn btn-outline-primary rounded-pill px-4">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                    <form action="{{ route('admin.cms_contents.destroy', ['contenido' => $content->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Estás seguro de eliminar este contenido?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash me-1"></i>Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="position-absolute bottom-0 start-0 w-100 h-1 bg-gradient"
                                style="background: linear-gradient(90deg, #6366f1, #3b82f6);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 border border-dashed rounded-4 bg-light">
                <div class="mb-3 text-muted">
                    <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold text-muted">No se encontraron contenidos que coincidan con los filtros.</h5>
                <p class="text-muted mb-4">Crea el primer contenido o reajusta los filtros seleccionados.</p>
                <a href="{{ route('admin.cms_contents.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-circle me-1"></i>Crear contenido
                </a>
            </div>
        @endif

        <div class="d-flex justify-content-end mt-4">
            {{ $contents->withQueryString()->links() }}
        </div>
    </div>
@endsection
