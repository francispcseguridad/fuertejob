@extends('plantilla')

@section('title', 'Blog - FuerteJob')

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8 order-2 order-lg-1">


                    @forelse ($posts as $post)
                        <article class="card border-0 shadow-sm mb-4">
                            <div class="row g-0 align-items-stretch">
                                <div class="col-md-5">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="d-block h-100">
                                        <img src="{{ $post->imagen_url ?? asset('assets/images/default_news.png') }}"
                                            class="img-fluid w-100 h-100 object-fit-cover rounded-start"
                                            alt="{{ $post->title }}">
                                    </a>
                                </div>
                                <div class="col-md-7">
                                    <div class="card-body d-flex flex-column h-100">
                                        <div class="mb-2 text-muted small">
                                            <span><i
                                                    class="bi bi-calendar-event me-1"></i>{{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
                                            @if ($post->author)
                                                <span class="ms-3"><i
                                                        class="bi bi-person-circle me-1"></i>{{ $post->author->name }}</span>
                                            @endif
                                        </div>
                                        <h3 class="h5"><a href="{{ route('blog.show', $post->slug) }}"
                                                class="text-dark text-decoration-none">{{ $post->title }}</a></h3>
                                        <p class="text-muted flex-grow-1">{{ Str::limit(strip_tags($post->body), 180) }}
                                        </p>
                                        <div>
                                            @foreach ($post->tag_list as $tag)
                                                <a href="{{ route('blog.index', array_merge(request()->query(), ['tag' => $tag])) }}"
                                                    class="badge bg-light text-primary border">#{{ $tag }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-emoji-neutral display-4 text-muted"></i>
                                <p class="mt-3 mb-0">No encontramos artículos que coincidan con tu búsqueda.</p>
                            </div>
                        </div>
                    @endforelse

                    <div class="d-flex justify-content-center mt-4">
                        {{ $posts->links('pagination::bootstrap-4') }}
                    </div>
                </div>

                <div class="col-lg-4 order-1 order-lg-2">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="text-uppercase small fw-semibold text-muted mb-3">Entradas recientes</h5>
                            <div class="list-group list-group-flush">
                                @foreach ($recentPosts as $recent)
                                    <a href="{{ route('blog.show', $recent->slug) }}"
                                        class="list-group-item list-group-item-action px-0">
                                        <div class="d-flex gap-3">
                                            <div style="width: 70px; height: 70px;">
                                                <img src="{{ $recent->imagen_url ?? asset('assets/images/default_news.png') }}"
                                                    class="img-fluid rounded" alt="{{ $recent->title }}">
                                            </div>
                                            <div>
                                                <p class="small text-muted mb-1">
                                                    {{ optional($recent->published_at)->translatedFormat('d M, Y') }}</p>
                                                <p class="mb-0 fw-semibold">{{ Str::limit($recent->title, 70) }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="text-uppercase small fw-semibold text-muted mb-3">Filtrar artículos</h5>
                            <form method="GET" action="{{ route('blog.index') }}" class="row g-3">
                                <div class="col-12">
                                    <label for="search"
                                        class="form-label text-uppercase small fw-semibold text-muted">Buscar</label>
                                    <input type="text" name="search" id="search" value="{{ $search }}"
                                        class="form-control" placeholder="Título o contenido">
                                </div>
                                <div class="col-6">
                                    <label for="month"
                                        class="form-label text-uppercase small fw-semibold text-muted">Mes</label>
                                    <select name="month" id="month" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach (range(1, 12) as $monthNumber)
                                            <option value="{{ $monthNumber }}" @selected($selectedMonth == $monthNumber)>
                                                {{ ucfirst(\Carbon\Carbon::create()->month($monthNumber)->locale(app()->getLocale())->translatedFormat('F')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="year"
                                        class="form-label text-uppercase small fw-semibold text-muted">Año</label>
                                    <select name="year" id="year" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach ($archives->pluck('year')->unique() as $archiveYear)
                                            <option value="{{ $archiveYear }}" @selected($selectedYear == $archiveYear)>
                                                {{ $archiveYear }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="author"
                                        class="form-label text-uppercase small fw-semibold text-muted">Autor</label>
                                    <select name="author" id="author" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach ($authors as $author)
                                            <option value="{{ $author->id }}" @selected($selectedAuthor == $author->id)>
                                                {{ $author->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="tag"
                                        class="form-label text-uppercase small fw-semibold text-muted">Tag</label>
                                    <input type="text" name="tag" id="tag" value="{{ $selectedTag }}"
                                        class="form-control" placeholder="Ej: talento">
                                </div>
                                <div class="col-12 d-grid gap-2">
                                    <button type="submit" class="btn btn-primary text-white">Aplicar filtros</button>
                                    <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="text-uppercase small fw-semibold text-muted mb-3">Nube de tags</h5>
                            @if ($tagCloud->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($tagCloud as $tagItem)
                                        <a href="{{ route('blog.index', array_merge(request()->query(), ['tag' => $tagItem['tag']])) }}"
                                            class="badge text-bg-light border">
                                            #{{ $tagItem['tag'] }} ({{ $tagItem['count'] }})
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">Aún no hay tags registrados.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="text-uppercase small fw-semibold text-muted mb-3">Archivo</h5>
                            <ul class="list-unstyled mb-0">
                                @foreach ($archives as $item)
                                    <li class="mb-2">
                                        <a href="{{ route('blog.index', array_merge(request()->query(), ['month' => $item->month, 'year' => $item->year])) }}"
                                            class="text-decoration-none">
                                            {{ ucfirst(\Carbon\Carbon::create()->month($item->month)->locale(app()->getLocale())->translatedFormat('F')) }}
                                            {{ $item->year }} ({{ $item->total }})
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
