<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <link rel="manifest" href="{{ asset('favicons/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('favicons/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">



    <title>{{ config('app.name', 'FuerteJobs') }}</title>
    @yield('meta')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="{{ asset('css/fuertejob.css') }}" rel="stylesheet">
    <link href="{{ asset('css/chatbot.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}

    @yield('styles')

</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary"
                    href="@auth
@if (Auth::user()->rol === 'admin')
                        {{ route('admin.dashboard') }}
                    @elseif(Auth::user()->hasCompanyRole())
                        {{ route('empresa.dashboard') }}
                    @else
                        {{ route('worker.dashboard') }}
                    @endif
                @else
                    {{ url('/') }} @endauth">
                    <img src="{{ asset('/img/logofuertejob.png') }}" alt="Logo" class="logo">
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-2">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link fw-medium" href="{{ route('home') }}">
                                    <i class="bi bi-house me-1"></i> Inicio
                                </a>
                            </li>
                            @if (Auth::user()->rol === 'admin')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle fw-medium" href="#" id="adminHomeDropdown"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Configuracion
                                    </a>
                                    <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="adminSettingsDropdown">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.configuracion.index') }}">
                                                <i class="bi bi-sliders me-2"></i> General
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.ai_prompts.index') }}">
                                                <i class="bi bi-robot me-2"></i> Chatbot
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>

                                        <li>

                                            <a class="dropdown-item" href="{{ route('admin.email-templates.index') }}">
                                                <i class="bi bi-mailbox"></i> Plantillas
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>

                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.faq_items.index') }}">
                                                <i class="bi bi-question me-2"></i> FAQ
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.sectores.index') }}">
                                                <i class="bi bi-award-fill"></i> Sectores
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.security.password') }}">
                                                <i class="bi bi-shield-lock-fill"></i> Cambiar contraseña
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.localidades.index') }}">
                                                <i class="bi bi-shield-lock-fill"></i> Localidades
                                            </a>
                                        </li>

                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('admin.empresas.index') }}">
                                        Empresas
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('admin.candidatos.index') }}">
                                        Candidatos
                                    </a>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle fw-medium" href="#" id="adminOffersDropdown"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Ofertas
                                    </a>
                                    <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="adminOffersDropdown">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.ofertas.index') }}">
                                                <i class="bi bi-pencil-square"></i> Ofertas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.ofertas.pendientes') }}">
                                                <i class="bi bi-wifi"></i> Ofertas Pendientes de Publicar
                                            </a>
                                        </li>


                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('admin.facturas.index') }}">
                                        Facturas
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle fw-medium" href="#"
                                        id="adminSettingsDropdown" role="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        Portada
                                    </a>
                                    <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="adminHomeDropdown">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.cms_contents.index') }}">
                                                <i class="bi bi-pencil-square"></i> Contenidos
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.social_networks.index') }}">
                                                <i class="bi bi-wifi"></i> Redes sociales
                                            </a>
                                        </li>

                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.menus.index') }}">
                                                <i class="bi bi-list me-2"></i> Menús
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.home_heroes.index') }}">
                                                <i class="bi bi-image me-2"></i> Banner Hero
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.home_search_sections.index') }}">
                                                <i class="bi bi-search me-2"></i> Sección Buscador
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.home_locations.index') }}">
                                                <i class="bi bi-geo-alt me-2"></i> Ubicaciones
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.home_parallax_images.index') }}">
                                                <i class="bi bi-card-image me-2"></i> Imagen Parallax
                                            </a>
                                        </li>


                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.home_sectors.index') }}">
                                                <i class="bi bi-briefcase me-2"></i> Sectores
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.home_loop_texts.index') }}">
                                                <i class="bi bi-type me-2"></i> Texto Loop
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle fw-medium" href="#"
                                        id="adminOtherServicesDropdown" role="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        Otros Servicios
                                    </a>
                                    <ul class="dropdown-menu shadow-sm border-0"
                                        aria-labelledby="adminOtherServicesDropdown">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.academias.index') }}">
                                                <i class="bi bi-pencil-square"></i> Academias
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.inmobiliarias.index') }}">
                                                <i class="bi bi-pencil-square"></i> Inmobiliarias
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @elseif(Auth::user()->hasCompanyRole())
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('empresa.ofertas.create') }}">
                                        <i class="bi bi-plus-circle me-1"></i> Publicar Oferta
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('empresa.ofertas.index') }}">
                                        <i class="bi bi-briefcase me-1"></i> Mis Ofertas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('empresa.invoices.index') }}">
                                        <i class="bi bi-receipt me-1"></i> Facturas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('empresa.comprar') }}">
                                        <i class="bi bi-credit-card me-1"></i> Comprar Bono
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('empresa.usuarios.index') }}">
                                        <i class="bi bi-people me-1"></i> Usuarios
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link fw-medium" href="{{ route('worker.jobs.index') }}">
                                        <i class="bi bi-search me-1"></i> Buscar Ofertas
                                    </a>
                                </li>
                            @endif
                        @else
                        @endauth

                    </ul>

                    <ul class="navbar-nav align-items-center gap-2">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                                    </a>
                                </li>
                            @endif
                        @else
                            {{-- Icono de Mensajería --}}
                            <li class="nav-item me-2">
                                <a href="{{ route('messaging.inbox') }}"
                                    class="nav-link position-relative icon-hover-effect text-secondary" title="Mensajes">
                                    <svg width="64px" height="64px" viewBox="0 0 32 32" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path
                                                d="M2 11.6C2 8.23969 2 6.55953 2.65396 5.27606C3.2292 4.14708 4.14708 3.2292 5.27606 2.65396C6.55953 2 8.23969 2 11.6 2H20.4C23.7603 2 25.4405 2 26.7239 2.65396C27.8529 3.2292 28.7708 4.14708 29.346 5.27606C30 6.55953 30 8.23969 30 11.6V20.4C30 23.7603 30 25.4405 29.346 26.7239C28.7708 27.8529 27.8529 28.7708 26.7239 29.346C25.4405 30 23.7603 30 20.4 30H11.6C8.23969 30 6.55953 30 5.27606 29.346C4.14708 28.7708 3.2292 27.8529 2.65396 26.7239C2 25.4405 2 23.7603 2 20.4V11.6Z"
                                                fill="url(#paint0_linear_87_7269)"></path>
                                            <path
                                                d="M16 23C20.9706 23 25 19.6421 25 15.5C25 11.3579 20.9706 8 16 8C11.0294 8 7 11.3579 7 15.5C7 18.1255 8.61889 20.4359 11.0702 21.7758C10.9881 22.4427 10.7415 23.3327 10 24C11.4021 23.7476 12.5211 23.2405 13.3571 22.6714C14.1928 22.885 15.0803 23 16 23Z"
                                                fill="white"></path>
                                            <defs>
                                                <linearGradient id="paint0_linear_87_7269" x1="16" y1="2"
                                                    x2="16" y2="30" gradientUnits="userSpaceOnUse">
                                                    <stop stop-color="#6fb0e7 "></stop>
                                                    <stop offset="1" stop-color="#1c476b"></stop>
                                                </linearGradient>
                                            </defs>
                                        </g>
                                    </svg>
                                    @php
                                        $unreadCount = Auth::user()->unreadMessagesCount();
                                    @endphp
                                    @if ($unreadCount > 0)
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white shadow-sm"
                                            style="font-size: 0.65rem;">
                                            {{ $unreadCount }}
                                            <span class="visually-hidden">mensajes nuevos</span>
                                        </span>
                                    @endif
                                </a>
                            </li>

                            {{-- Si el usuario está autenticado, muestra el menú de sesión --}}
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle fw-medium" href="#"
                                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    v-pre>
                                    @if (Auth::user()->hasCompanyRole() && optional(Auth::user()->companyProfile)->logo_url)
                                        <img src="{{ asset(Auth::user()->companyProfile->logo_url) }}" alt="Logo Company"
                                            class="rounded-circle me-1 border"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @elseif(Auth::user()->rol === 'trabajador')
                                        <img src="{{ asset(Auth::user()->workerProfile->profile_image_url) }}"
                                            alt="Foto Worker" class="rounded-circle me-1 border"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('/img/logofuertejob.png') }}" alt="Logo FuerteJob"
                                            class="rounded-circle me-1 border"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif

                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                    aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item"
                                        href="@if (Auth::user()->rol === 'admin') {{ route('admin.dashboard') }}
                                    @elseif(Auth::user()->hasCompanyRole())
                                        {{ route('empresa.profile.index') }}
                                    @else
                                        {{ route('worker.profile.edit') }} @endif">
                                        <i class="bi bi-speedometer2 me-2"></i> Mi Ficha
                                    </a>

                                    @if (Auth::user()->rol === 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                            <i class="bi bi-person-gear me-2"></i> Editar Perfil
                                        </a>
                                    @endif

                                    <div class="dropdown-divider"></div>

                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif
            </div>

            @yield('content')
        </main>

        <footer class="bg-light mt-auto py-3">
            <div class="container text-center">
                <p class="mb-0"><a href="{{ route('contact.create') }}" class="text-muted">Contáctanos si
                        tienes
                        dudas o problemas</a></p>
                <br>

                <p class="mb-0 text-muted">&copy; {{ date('Y') }} FuerteJob. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    {{-- SweetAlert2 para diálogos --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}

    @yield('scripts')

    <script>
        document.addEventListener('click', function(event) {
            const toggle = event.target.closest('.btn-password-toggle');
            if (!toggle) return;
            const targetId = toggle.dataset.target;
            if (!targetId) return;
            const input = document.getElementById(targetId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                toggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
                toggle.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                input.type = 'password';
                toggle.innerHTML = '<i class="bi bi-eye"></i>';
                toggle.setAttribute('aria-label', 'Mostrar contraseña');
            }
        });
    </script>

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
    @include('components.messaging-modal')
    @stack('scripts')
</body>

</html>
