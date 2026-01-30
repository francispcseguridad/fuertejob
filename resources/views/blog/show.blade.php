@extends('plantilla')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description)

@section('meta')
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }} | FuerteJob">
    <meta property="og:description" content="{{ $post->meta_description ?? Str::limit(strip_tags($post->body), 160) }}">
    <meta property="og:image" content="{{ asset('img/logofacebook.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image" content="{{ asset('img/logowhatsapp.png') }}">
    <meta property="og:image:width" content="630">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="FuerteJob">
    <meta property="og:locale" content="es_ES">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->meta_title ?? $post->title }} | FuerteJob">
    <meta name="twitter:description" content="{{ $post->meta_description ?? Str::limit(strip_tags($post->body), 160) }}">
    <meta name="twitter:image" content="{{ asset('img/logofacebook.png') }}">
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <article class="card border-0 shadow-sm">
                        <img src="{{ $post->imagen_url ?? asset('assets/images/default_news.png') }}" class="card-img-top"
                            alt="{{ $post->title }}">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap text-muted small mb-3">
                                <span><i
                                        class="bi bi-calendar-event me-1"></i>{{ optional($post->published_at)->translatedFormat('d M, Y') }}</span>
                                @if ($post->author)
                                    <span class="ms-3"><i
                                            class="bi bi-person-circle me-1"></i>{{ $post->author->name }}</span>
                                @endif
                            </div>
                            <h1 class="mb-3">{{ $post->title }}</h1>
                            <div class="mb-4">{!! $post->body !!}</div>
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                @foreach ($postTags as $tag)
                                    <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                                        class="badge bg-light text-primary border">#{{ $tag }}</a>
                                @endforeach
                            </div>

                            <hr class="my-4">
                            @include('components.share_buttons', [
                                'url' => url()->current(),
                                'title' => $post->title,
                                'text' => 'FuerteJob Blog: ' . $post->title,
                            ])
                        </div>
                    </article>

                    @if ($relatedPosts->isNotEmpty())
                        <div class="mt-5">
                            <h4 class="mb-3">También te puede interesar</h4>
                            <div class="row g-4">
                                @foreach ($relatedPosts as $related)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <a href="{{ route('blog.show', $related->slug) }}" class="d-block">
                                                <img src="{{ $related->imagen_url ?? asset('assets/images/default_news.png') }}"
                                                    class="card-img-top" alt="{{ $related->title }}">
                                            </a>
                                            <div class="card-body">
                                                <p class="small text-muted mb-2">
                                                    {{ optional($related->published_at)->translatedFormat('d M, Y') }}</p>
                                                <h6><a href="{{ route('blog.show', $related->slug) }}"
                                                        class="text-decoration-none text-dark">{{ Str::limit($related->title, 70) }}</a>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="text-uppercase small fw-semibold text-muted mb-3">Sobre el autor</h5>
                            @if ($post->author)
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        {{ strtoupper(mb_substr($post->author->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ $post->author->name }}</p>
                                        <p class="mb-0 text-muted small">Redactor en FuerteJob</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">Autor anónimo</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="text-uppercase small fw-semibold text-muted mb-3">Últimas entradas</h5>
                            <div class="list-group list-group-flush">
                                @foreach ($relatedPosts as $related)
                                    <a href="{{ route('blog.show', $related->slug) }}" class="list-group-item px-0">
                                        {{ $related->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
<p class="text-muted mb-0">Autor anónimo</p>
@endif
</div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h5 class="text-uppercase small fw-semibold text-muted mb-3">Últimas entradas</h5>
        <div class="list-group list-group-flush">
            @foreach ($relatedPosts as $related)
                <a href="{{ route('blog.show', $related->slug) }}" class="list-group-item px-0">
                    {{ $related->title }}
                </a>
            @endforeach
        </div>
    </div>
</div>
</div>
</div>
</div>
</section>
@endsection
