@extends('plantilla')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description)


@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h1 class="fw-bold mb-3">{{ $page->title }}</h1>

                    </div>

                    <article class="card border-0 shadow-sm">
                        @if ($page->imagen_url)
                            <img src="{{ asset($page->imagen_url) }}" class="card-img-top" alt="{{ $page->title }}">
                        @endif
                        <div class="card-body p-4 p-lg-5">
                            <div class="page-content">
                                {!! $page->body !!}
                            </div>
                        </div>
                    </article>

                    <div class="text-center mt-4">
                        <a href="{{ url('/') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
