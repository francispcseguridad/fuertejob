<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="assets/images/logo-fuertejob-96x96.jpg" type="image/x-icon">
    <meta name="description" content="Tu portal de empleo en Fuerteventura">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">

    <title>FuerteJob - Tu Buscador de Empleo</title>
    <link rel="stylesheet" href="{{ asset('assets/web/assets/mobirise-icons2/mobirise2.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link href="{{ asset('css/chatbot.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/parallax/jarallax.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/animatecss/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dropdown/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/socicon/css/styles.css') }}">


    <link rel="preload" href="https://fonts.googleapis.com/css?family=Montserrat:100,400,500,600,700,800&display=swap"
        as="style" onload="this.onload=null;this.rel='stylesheet'">


    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Syne:400,500,600,700,800&display=swap">
    </noscript>
    <link rel="preload" as="style" href="{{ asset('assets/mobirise/css/mbr-additional.css?v=tX0ioP') }}">
    <link rel="stylesheet" href="{{ asset('assets/mobirise/css/mbr-additional.css?v=tX0ioP') }}" type="text/css">

    @yield('styles')
</head>

<body>
    @php
        $socialNetworks = $socialNetworks ?? collect();
    @endphp

    <!-- MENU -->
    <section data-bs-version="5.1" class="menu menu1 programm5 cid-v3tjftUC4j" once="menu" id="menu1-7">
        <nav class="navbar navbar-dropdown navbar-expand-lg">
            <div class="menu_box container-fluid">
                <div class="navbar-brand d-flex">
                    <span class="navbar-logo">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('/img/logofuertejob.png') }}" alt="FuerteJob"
                                style="width: 10rem;background: white;">
                            <p class="mt-2" style="font-size: 13px;color:#2a84c0;">Tu Buscador de Empleo</p>
                        </a>
                    </span>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-bs-toggle="collapse"
                        data-target="#navbarSupportedContent" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                        <div class="hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav nav-dropdown" data-app-modern-menu="true">
                        @foreach ($menus as $menu)
                            <li class="nav-item {{ $menu->children->count() ? 'dropdown' : '' }}">
                                <a class="nav-link link texto-fuertejob display-4 {{ $menu->children->count() ? 'dropdown-toggle' : '' }}"
                                    href="{{ $menu->children->count() ? '#' : $menu->url ?? '#' }}"
                                    @if ($menu->children->count()) data-toggle="dropdown-submenu" data-bs-toggle="dropdown" aria-expanded="false" @endif>
                                    {{ $menu->title }}
                                </a>
                                @if ($menu->children->count())
                                    <div class="dropdown-menu">
                                        @foreach ($menu->children as $child)
                                            <a class="dropdown-item text-white display-4"
                                                href="{{ $child->url ?? '#' }}">{{ $child->title }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>




                    <div class="mbr-section-btn-main" role="tablist">
                        @auth
                            <div class="d-flex align-items-center">
                                @if (Auth::user()->rol === 'admin')
                                    <a class="btn btn-fuertejob display-4" href="/administracion/dashboard"
                                        style="border-radius: 60%;">
                                        <i class="bi bi-person-circle"></i>
                                    </a>
                                @elseif(Auth::user()->hasCompanyRole())
                                    <a class="btn btn-fuertejob display-4" href="{{ route('empresa.dashboard') }}"
                                        style="border-radius: 60%;">
                                        <i class="bi bi-person-circle"></i>
                                    </a>
                                @else
                                    <a class="btn btn-fuertejob display-4" href="{{ route('worker.dashboard') }}"
                                        style="border-radius: 60%;">
                                        <i class="bi bi-person-circle"></i>
                                    </a>
                                @endif
                                <!-- Formulario de Logout -->
                                <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-fuertejob display-4"
                                        style="border-radius: 60%;">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- Ajustar ruta login empresas si existe -->
                            <a class="btn btn-fuertejob display-4" href="{{ route('login') }}"><i
                                    class="bi bi-box-arrow-in-right"></i>&nbsp;&nbsp;Acceso</a>
                        @endauth
                        <a class="btn btn-fuertejob display-4" href="{{ route('public.social_networks.index') }}"
                            style="font-size: 12px !important;"><i class="bi bi-wifi"></i>&nbsp;&nbsp;Redes
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </section>
    @yield('content')
    <!-- FOOTER -->
    <section data-bs-version="5.1" class="footer1 levelm4_footer1 cid-v3L8fm2dec" once="footers" id="footer01-b">
        <div class="container">
            <div class="row align-left">
                <!-- Footer Col 1 -->
                <div class="col-md-6 col-lg-3">
                    <h2 class="title mbr-bold pb-2 mbr-fonts-style display-7">FuerteJob</h2>
                    <div class="align-wrap">
                        @foreach ($footer1 as $item)
                            <div class="item-wrap">
                                <div class="icons-wrap pb-2">
                                    <a href="{{ $item->url ?? '#' }}"
                                        class="text-white icon-title mbr-regular mbr-fonts-style display-4 text-primary">{{ $item->title }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Col 2 -->
                <div class="col-md-6 col-lg-3">
                    <h2 class="title mbr-bold pb-2 mbr-fonts-style display-7 text-white">Empresas</h2>
                    <div class="align-wrap">

                        <div class="item-wrap">
                            <div class="icons-wrap pb-2">
                                <a href="{{ route('company.register.create') }}"
                                    class="text-white icon-title mbr-regular mbr-fonts-style display-4 text-primary">
                                    Regístrate
                                </a>
                            </div>
                        </div>
                        <div class="item-wrap">
                            <div class="icons-wrap pb-2">
                                <a href="{{ route('login') }}"
                                    class="text-white icon-title mbr-regular mbr-fonts-style display-4 text-primary">
                                    Acceso
                                </a>
                            </div>
                        </div>
                        @foreach ($footer2 as $item)
                            <div class="item-wrap">
                                <div class="icons-wrap pb-2">
                                    <a href="{{ $item->url ?? '#' }}"
                                        class="text-white icon-title mbr-regular mbr-fonts-style display-4 text-primary">{{ $item->title }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Col 3 -->
                <div class="col-md-6 col-lg-3">
                    <h2 class="title mbr-bold pb-2 mbr-fonts-style display-7">Solicitantes</h2>
                    <div class="align-wrap">
                        <div class="item-wrap">
                            <div class="icons-wrap pb-2">
                                <a href="{{ route('worker.register.form') }}"
                                    class="text-white icon-title mbr-regular mbr-fonts-style display-4 text-primary">
                                    Regístrate
                                </a>
                            </div>
                        </div>
                        <div class="icons-wrap pb-2">
                            <a href="{{ route('login') }}"
                                class="text-white icon-title mbr-regular mbr-fonts-style display-4 text-primary">
                                Acceso
                            </a>
                        </div>
                        @foreach ($footer3 as $item)
                            <div class="item-wrap">
                                <div class="icons-wrap pb-2">
                                    <a href="{{ $item->url ?? '#' }}"
                                        class="texto-fuertejob icon-title mbr-regular mbr-fonts-style display-4 text-primary">
                                        {{ $item->title }}
                                    </a>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                <div class="col-md-6 col-lg-2" style="padding:0px !important;">
                    <h2 class="title mbr-bold pb-2 mbr-fonts-style display-5">Social</h2>

                    <a class="iconfont-wrapper text-white" href="{{ route('public.social_networks.index') }}"
                        target="_blank" rel="noopener noreferrer" title="Redes Sociales"
                        aria-label="Redes Sociales">
                        Siguenos en nuestras redes
                    </a>


                </div>
            </div>
            <div class=row align-center">
                <div class="col-md-12 col-lg-12">
                    <div class="container text-center">

                        <p class="mb-0 text-white"><a href="{{ route('contact.create') }}"
                                class="text-white">Contáctanos si tienes
                                dudas o problemas</a></p>
                        <br>
                        <p class="mb-0 text-white">&copy; {{ date('Y') }} FuerteJob. Todos los derechos
                            reservados.</p>
                    </div>
                </div>
    </section>

    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/parallax/jarallax.js') }}"></script>
    <script src="{{ asset('assets/smoothscroll/smooth-scroll.js') }}"></script>
    <script src="{{ asset('assets/ytplayer/index.js') }}"></script>
    <script src="{{ asset('assets/dropdown/js/navbar-dropdown.js') }}"></script>
    <script src="{{ asset('assets/theme/js/script.js') }}"></script>
    <script src="{{ asset('assets/formoid/formoid.min.js') }}"></script>
    @yield('scripts')

    <!-- Cookie Consent by TermsFeed https://www.TermsFeed.com -->
    <script type="text/javascript" src="https://www.termsfeed.com/public/cookie-consent/4.2.0/cookie-consent.js"
        charset="UTF-8"></script>
    <script type="text/javascript" charset="UTF-8">
        document.addEventListener('DOMContentLoaded', function() {
            cookieconsent.run({
                "notice_banner_type": "simple",
                "consent_type": "express",
                "palette": "light",
                "language": "es",
                "page_load_consent_levels": ["strictly-necessary"],
                "notice_banner_reject_button_hide": false,
                "preferences_center_close_button_hide": false,
                "page_refresh_confirmation_buttons": false,
                "website_privacy_policy_url": "https://www.fuertejob.com/info/politica-de-privacidad"
            });
        });
    </script>

    <noscript>
        Free cookie consent management tool by <a href="https://www.termsfeed.com/">TermsFeed
            Generator</a>
    </noscript>
    <!-- End Cookie Consent by TermsFeed https://www.TermsFeed.com -->
    @include('components.chatbot-widget')
    <div id="scrollToTop" class="scrollToTop mbr-arrow-up">
        <a style="text-align: center;">
            <i class="mbr-arrow-up-icon mbr-arrow-up-icon-cm cm-icon cm-icon-smallarrow-up"></i>
        </a>
    </div>
    <input name="animation" type="hidden">
</body>

</html>
