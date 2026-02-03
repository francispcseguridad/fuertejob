@extends('plantilla')
@section('title', 'FuerteJob - Portal de Empleo')
@section('styles')
    <style>
        .loop-scroller {
            overflow: hidden;
            padding: 2rem 0 1rem;
            position: relative;
        }

        .loop-track {
            display: flex;
            gap: 0.8rem;
            width: max-content;
            align-items: stretch;
            animation: loopScroll 120s linear infinite;
        }

        .loop-card {
            flex: 0 0 11%;
            min-width: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .loop-card img {
            width: 250px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        }

        .loop-card-title {
            margin-top: 1rem;
            font-weight: 600;
            line-height: 1.3;
            color: #fff;
        }

        @media (max-width: 1200px) {
            .loop-card {
                flex: 0 0 18%;
                min-width: 140px;
            }
        }

        @media (max-width: 768px) {
            .loop-card {
                flex: 0 0 30%;
            }

            .loop-card img {
                aspect-ratio: 1 / 1;
            }
        }

        @media (max-width: 576px) {
            .loop-card {
                flex: 0 0 60%;
            }

            .loop-track {
                gap: 0.8rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .loop-track {
                animation: none;
            }
        }

        @keyframes loopScroll {
            0% {
                transform: translateX(-50%);
            }

            100% {
                transform: translateX(0);
            }
        }
    </style>
@endsection
@section('content')
    <!-- HERO MAIN -->
    @if ($hero)
        <section data-bs-version="5.1" class="header2 structurem5 cid-tmYWN3vmKg mbr-fullscreen mbr-parallax-background"
            id="aheader2-e" style="background-image: url('{{ $hero->background_image ?? 'assets/images/background.jpg' }}');">
            <div class="mbr-overlay" style="opacity: 0.8; background-color: rgb(255, 255, 255);"></div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-12">
                        <h1 class="mbr-section-title mbr-fonts-style mb-0 display-1">
                            <strong>{{ $hero->title }}</strong><br>
                        </h1>
                        <p class="mbr-text mbr-fonts-style mb-0 display-2">
                            {{ $hero->subtitle }}
                        </p>
                        @if ($hero->button1_text || $hero->button2_text)
                            <div class="mbr-section-btn mt-3">
                                @if ($hero->button1_text)
                                    <a class="btn btn-primary display-7"
                                        href="{{ $hero->button1_url ?? '#' }}">{{ $hero->button1_text }}</a>
                                @endif
                                @if ($hero->button2_text)
                                    <a class="btn btn-white display-7"
                                        href="{{ $hero->button2_url ?? '#' }}">{{ $hero->button2_text }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif
    <section data-bs-version="5.1" class="features06 divem5 cid-v3Lzh8cTdN" id="features06-h">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title-wrapper">
                        <h2 class="mbr-section-title mbr-fonts-style display-2 text-center mt-4">
                            <strong>
                                Encuentra Trabajo en...
                            </strong>
                        </h2>
                    </div>
                </div>
                <div id="canarias-wrap">
                    <style>
                        #canarias-wrap {
                            display: flex;
                            flex-wrap: nowrap;
                            gap: 15px;
                            padding: 30px 15px;
                            background: #ffffff;
                            border-radius: 5px;
                            overflow-x: auto;
                            scrollbar-width: none;
                            /* Firefox */
                            -ms-overflow-style: none;
                            /* IE and Edge */
                            justify-content: space-between;
                            align-items: center;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                        }

                        #canarias-wrap::-webkit-scrollbar {
                            display: none;
                            /* Hide scrollbar for Chrome, Safari and Opera */
                        }

                        #canarias-wrap .isla {
                            cursor: pointer;
                            background: #fff;
                            border-radius: 5px;
                            padding: ;
                            flex: 0 0 calc(12.5% - 15px);
                            min-width: 100px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            border: 1px solid #edf2f7;
                            gap: 10px;
                        }

                        #canarias-wrap .isla:hover {
                            transform: translateY(-5px);
                            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08);
                            border-color: #0d6fb9;
                            background: #fff;
                        }

                        #canarias-wrap .isla img {
                            width: 100%;
                            height: 100%;
                            max-height: 80px;
                            object-fit: contain;
                            display: block;
                            transition: transform 0.3s ease;
                        }

                        #canarias-wrap .isla:hover img {
                            transform: scale(1.05);
                        }

                        #canarias-wrap .isla span {
                            font-family: 'Inter', sans-serif;
                            font-size: 0.85rem;
                            font-weight: 500;
                            color: #1c486c;
                            text-align: center;
                            transition: color 0.3s ease;
                        }

                        #canarias-wrap .isla:hover span {
                            color: #2280be;
                        }

                        @media (max-width: 992px) {
                            #canarias-wrap {
                                justify-content: flex-start;
                            }

                            #canarias-wrap .isla {
                                flex: 0 0 120px;
                            }
                        }

                        /* Special Button Style */
                        .btn-special-canarias {
                            background-color: #1c486c !important;
                            border-color: #1c486c !important;
                            color: #ffffff !important;
                            padding: ;
                            border-radius: 5px;
                            font-size: 1.1rem;
                            font-weight: 700;
                            box-shadow: 0 10px 25px rgba(28, 72, 108, 0.3);
                            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                            letter-spacing: 1px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                        }

                        .btn-special-canarias:hover {
                            transform: translateY(-3px) scale(1.02);
                            box-shadow: 0 15px 35px rgba(28, 72, 108, 0.5);
                            background-color: #143652 !important;
                            border-color: #143652 !important;
                            color: #ffffff !important;
                        }
                    </style>

                    <!-- El Hierro -->
                    <div id="el-hierro" class="isla">
                        <img src="{{ asset('img/islas/elhierro.png') }}" alt="FuerteJob El Hierro">
                        <span>El Hierro</span>
                    </div>

                    <!-- La Palma -->
                    <div id="la-palma" class="isla">
                        <img src="{{ asset('img/islas/lapalma.png') }}" alt="FuerteJob La Palma">
                        <span>La Palma</span>
                    </div>

                    <!-- La Gomera -->
                    <div id="la-gomera" class="isla">
                        <img src="{{ asset('img/islas/lagomera.png') }}" alt="FuerteJob La Gomera">
                        <span>La Gomera</span>
                    </div>

                    <!-- Tenerife -->
                    <div id="tenerife" class="isla">
                        <img src="{{ asset('img/islas/tenerife.png') }}" alt="FuerteJob Tenerife">
                        <span>Tenerife</span>
                    </div>
                    <!-- Gran Canaria -->
                    <div id="gran-canaria" class="isla">
                        <img src="{{ asset('img/islas/grancanaria.png') }}" alt="FuerteJob Gran Canaria">
                        <span>Gran Canaria</span>
                    </div>
                    <!-- Fuerteventura -->
                    <div id="fuerteventura" class="isla">
                        <img src="{{ asset('img/islas/fuerteventura.png') }}" alt="FuerteJob Fuerteventura">
                        <span>Fuerteventura</span>
                    </div>
                    <!-- Lanzarote -->
                    <div id="lanzarote" class="isla">
                        <img src="{{ asset('img/islas/lanzarote.png') }}" alt="FuerteJob Lanzarote">
                        <span>Lanzarote</span>
                    </div>
                    <!-- La Graciosa -->
                    <div id="lagraciosa" class="isla">
                        <img src="{{ asset('img/islas/lagraciosa.png') }}" alt="FuerteJob La Graciosa">
                        <span>La Graciosa</span>
                    </div>
                </div>

                <div class="col-12 text-center mt-4 mb-4">
                    <a href="{{ route('public.jobs.index') }}" class="btn btn-special-canarias">
                        <span class="mobi-mbri mobi-mbri-search mbr-iconfont mbr-iconfont-btn me-2"></span>
                        Ver ofertas en todas las islas
                    </a>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('#canarias-wrap .isla').forEach((isla) => {
                    isla.addEventListener('click', () => {
                        const name = isla.querySelector('span').textContent.trim();
                        const url = new URL("{{ route('public.jobs.index') }}", window.location
                            .origin);
                        url.searchParams.set('island', name);
                        window.location.href = url.toString();
                    });
                });
            });
        </script>
    </section>
    <!-- SEARCH SECTION -->
    @if ($searchSection)
        <section data-bs-version="5.1" class="form1 structurem5 cid-tmYSCJm7Xf" id="aform1-4">
            <div class="section-border-item section-border-item_top"></div>
            <div class="section-border-item section-border-item_bottom"></div>
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-6">
                        <div class="mbr-section-head">
                            <h2 class="mbr-section-title mbr-fonts-style display-2">{{ $searchSection->title }}</h2>
                            <h4 class="mbr-section-subtitle mbr-fonts-style display-5">{{ $searchSection->subtitle }}
                            </h4>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-wrap">
                            <div class="mbr-form">
                                <!-- Search Form pointing to jobs index -->
                                <form action="{{ route('public.jobs.index') }}" method="GET"
                                    class="mbr-form form-with-styler mx-auto">
                                    <div class="dragArea row">
                                        <div class="col-12 col-sm-6 form-group mb-3" data-for="query">
                                            <input type="text" name="search" id="search"
                                                placeholder="Puesto, empresa o palabra clave" class="form-control display-7"
                                                value="{{ request('search') }}">
                                        </div>
                                        <div class="col-12 col-sm-6 form-group mb-3" data-for="location">
                                            <input type="text" name="island" id="island" placeholder="Isla"
                                                class="form-control display-7" value="{{ request('island') }}">
                                        </div>
                                        <div class="col-12 mbr-section-btn"><button type="submit"
                                                class="w-100 w-100 btn btn-primary display-7">BUSCAR</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <section data-bs-version="5.1" class="article03 fabm5 cid-v3LJsi9XnC mbr-parallax-background" id="article03-o">

        <div class="mbr-overlay"></div>

        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 card">
                    <div class="title-wrapper">
                        <h2 class="mbr-section-title mbr-fonts-style display-2">
                            <strong>¿Eres una EMPRESA?</strong>
                        </h2>

                    </div>
                </div>
                <div class="col-12 col-lg-4 card">
                    <div class="mbr-section-btn">
                        <a class="btn btn-white display-4" href="{{ route('company.register.create') }}">
                            <span class="mobi-mbri mobi-mbri-right mbr-iconfont mbr-iconfont-btn"></span>
                            PUBLICA TU OFERTA
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PARALLAX IMAGE -->
    @if ($parallaxImage)
        <section data-bs-version="5.1" class="image02 standm5 cid-v3Q5SvPADz mbr-parallax-background" id="image02-r"
            style="background-image: url('{{ $parallaxImage->image ?? 'assets/images/background2.jpg' }}');">
            <div class="mbr-overlay" style="opacity: 0.8; background-color: rgb(255, 255, 255);"></div>
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="image-wrapper"></div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- SECTORS -->
    @if ($sectors->count() > 0)
        <section data-bs-version="5.1" class="features7 cid-v3QghnRgfg" id="features7-u">
            <div class="container-fluid">
                <div class="features-header">
                    <h2 class="mbr-section-title mbr-fonts-style display-2"><strong>Explora Ofertas en el Sector
                            de...</strong></h2>
                </div>
                <div class="row">
                    @foreach ($sectors as $sector)
                        <div class="col-12 col-lg-4 col-md-6 card item features-image">
                            <div class="item-wrapper">
                                <div class="card-wrapper">
                                    <div class="card-image">
                                        <a href="{{ $sector->url }}">
                                            <img src="{{ $sector->image ?? 'assets/images/default_sector.png' }}"
                                                alt="{{ $sector->name }}">
                                        </a>
                                    </div>
                                    <div class="card-text">
                                        <p class="mbr-card-title mbr-fonts-style display-7">
                                            <a href="{{ $sector->url }}">
                                                <strong>{{ strtoupper($sector->name) }}</strong>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="card-border"></div>
                                    @if ($sector->url)
                                        <a href="{{ $sector->url }}" class="link-overlay"></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mbr-section-btn"><a class="btn btn-primary display-7"
                        href="{{ route('worker.jobs.index') }}">VER TODAS LAS OFERTAS</a></div>
            </div>
        </section>
    @endif

    <!-- LOOP TEXTS SCROLLER -->
    @if ($loopTexts->count() > 0)
        @php
            $displayItems = $loopTexts;
            $targetCount = max(8, $loopTexts->count());
            while ($displayItems->count() < $targetCount) {
                $displayItems = $displayItems->concat($loopTexts);
            }
            $displayItems = $displayItems->take($targetCount);
        @endphp
        <section data-bs-version="5.1" class="features08 supportm5 cid-v3QngGimZV" id="features08-w">
            <div class="container-fluid">
                <div class="loop-scroller">
                    <div class="loop-track" aria-live="polite">
                        @for ($iteration = 0; $iteration < 2; $iteration++)
                            @foreach ($displayItems as $text)
                                @php
                                    $imageUrl = $text->image
                                        ? asset($text->image)
                                        : 'https://via.placeholder.com/800x600?text=FuerteJob';
                                @endphp
                                <div class="loop-card" aria-hidden="{{ $iteration ? 'true' : 'false' }}">
                                    <img src="{{ $imageUrl }}" alt="{{ $text->content }}">
                                    <p class="loop-card-title mbr-fonts-style display-7">{!! $text->content !!}</p>
                                </div>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section data-bs-version="5.1" class="features13 photom4_features13 cid-v3QKHXw27n" id="features13-11">
        <div class="container-fluid">
            <div class="text-center">
                <h1>Otros Servicios</h1>
            </div>
            <br><br>
            <div class="row">

                <div class="col-12 col-lg-6 card text-center">
                    <div class="content-wrapper">
                        <h2 class="mbr-section-title mbr-fonts-style display-2">

                            <a href="{{ route('public.academias.index') }}">

                                <img src="{{ !empty($portalSettings->imagen_academias) ? asset($portalSettings->imagen_academias) : 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}"
                                    alt="Academias" class="img-fluid mx-auto d-block" style="width: 60% !important;">
                                <br>Formación
                            </a>
                        </h2>
                    </div>
                </div>
                <div class="col-12 col-lg-6 card text-center">
                    <div class="content-wrapper">
                        <h2 class="mbr-section-title mbr-fonts-style display-2">
                            <a href="{{ route('public.inmobiliarias.index') }}">

                                <img src="{{ !empty($portalSettings->imagen_inmobiliarias) ? asset($portalSettings->imagen_inmobiliarias) : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}"
                                    alt="Inmobiliarias" class="img-fluid mx-auto d-block" style="width: 60% !important;">
                                <br> Inmobiliarias
                            </a>
                        </h2>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- NEWS (CMS) -->
    <section data-bs-version="5.1" class="features13 photom4_features13 cid-v3QKHXw27n" id="features13-11">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-6 card">
                    <div class="content-wrapper">
                        <h2 class="mbr-section-title mbr-fonts-style display-2">
                            <a href="{{ route('blog.index') }}">
                                Últimas Tendencias de Empleo
                            </a>
                        </h2>
                        <p class="mbr-text mbr-fonts-style display-7">Descubre las últimas noticias relacionadas con el
                            mundo laboral</p>
                    </div>
                </div>
                <div class="col-12 col-lg-6 card"></div> <!-- Spacer -->
            </div>

            @if (count($news) > 0)
                <div class="row">
                    @foreach ($news as $item)
                        <div class="card item features-image col-12 col-md-6 col-lg-3">
                            <div class="item-wrapper">
                                <div class="card-img">
                                    <a href="{{ route('blog.show', $item->slug) }}">
                                        <img src="{{ $item->imagen_url ?? 'assets/images/default_news.png' }}"
                                            alt="{{ $item->title }}">
                                    </a>
                                </div>
                                <div class="card-box pt-4">
                                    <h5 class="card-subtitle align-center pb-4 mbr-fonts-style display-7">
                                        {{ $item->created_at->format('d M, Y') }}</h5>
                                    <h4 class="card-title align-center pb-1 mbr-bold mbr-fonts-style display-5">
                                        <a href="{{ route('blog.show', $item->slug) }}">{{ $item->title }}</a>
                                    </h4>
                                    <p class="mbr-text align-center pb-3 mbr-fonts-style display-7">
                                        {{ Str::limit(strip_tags($item->body), 100) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="row">
                    <div class="col-12 text-center p-5">No hay noticias publicadas aún.</div>
                </div>
            @endif
        </div>
    </section>
@endsection
