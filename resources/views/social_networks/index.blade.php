@extends('plantilla')
@section('title', 'Redes Sociales | FuerteJob')
@section('content')
    <style>
        .social-card {
            transition: all 0.3s ease;
            text-decoration: none !important;
            color: white !important;
        }

        .social-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .social-icon-wrapper {
            transition: all 0.3s ease;
        }



        .social-card:hover .social-icon-wrapper i {
            color: white !important;
        }
    </style>

    @php
        if (!function_exists('social_network_color_map')) {
            function social_network_color_map()
            {
                return [
                    'facebook' => [
                        'color' => '#1877F2',
                        'aliases' => ['facebook', 'fb', 'bi-facebook'],
                    ],
                    'instagram' => [
                        'color' => '#E1306C',
                        'aliases' => ['instagram', 'insta', 'bi-instagram'],
                    ],
                    'linkedin' => [
                        'color' => '#0A66C2',
                        'aliases' => ['linkedin', 'bi-linkedin'],
                    ],
                    'twitter' => [
                        'color' => '#1DA1F2',
                        'aliases' => ['twitter', 'bi-twitter'],
                    ],
                    'youtube' => [
                        'color' => '#FF0000',
                        'aliases' => ['youtube', 'bi-youtube'],
                    ],
                    'tiktok' => [
                        'color' => '#000000',
                        'aliases' => ['tiktok', 'bi-tiktok'],
                    ],
                    'whatsapp' => [
                        'color' => '#25D366',
                        'aliases' => ['whatsapp', 'bi-whatsapp'],
                    ],
                    'pinterest' => [
                        'color' => '#BD081C',
                        'aliases' => ['pinterest', 'bi-pinterest'],
                    ],
                    'telegram' => [
                        'color' => '#0088CC',
                        'aliases' => ['telegram', 'bi-telegram'],
                    ],
                    'snapchat' => [
                        'color' => '#FFFC00',
                        'aliases' => ['snapchat', 'bi-snapchat'],
                    ],
                    'behance' => [
                        'color' => '#1769FF',
                        'aliases' => ['behance', 'bi-behance'],
                    ],
                    'dribbble' => [
                        'color' => '#EA4C89',
                        'aliases' => ['dribbble', 'bi-dribbble'],
                    ],
                    'github' => [
                        'color' => '#181717',
                        'aliases' => ['github', 'bi-github'],
                    ],
                    'discord' => [
                        'color' => '#7289DA',
                        'aliases' => ['discord', 'bi-discord'],
                    ],
                    'twitch' => [
                        'color' => '#9142FF',
                        'aliases' => ['twitch', 'bi-twitch'],
                    ],
                ];
            }

            function social_network_color_key($network)
            {
                $source = strtolower(($network->name ?? '') . ' ' . ($network->icon_class ?? ''));

                foreach (social_network_color_map() as $key => $metadata) {
                    foreach ($metadata['aliases'] as $alias) {
                        if (str_contains($source, strtolower($alias))) {
                            return $key;
                        }
                    }
                }

                return 'default';
            }

            function social_network_color($key)
            {
                $map = social_network_color_map();
                return $map[$key]['color'] ?? '#0D6EFD';
            }

            function social_network_color_rgba($key, $alpha = 0.15)
            {
                $hex = ltrim(social_network_color($key), '#');
                if (strlen($hex) === 3) {
                    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                }
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));

                return "rgba({$r}, {$g}, {$b}, {$alpha})";
            }
        }
    @endphp

    <section class="features07 cid-v3QghnRgfg py-5" id="redes-sociales">
        <div class="container">
            {{-- Encabezado y Filtro --}}
            <div class="row justify-content-center align-items-center mb-5">
                <div class="col-12 col-lg-8 text-center mb-4">
                    <h1 class="display-5 fw-bold mb-3">Nuestras Redes Sociales</h1>
                    <p class="text-muted lead">Conecta con nosotros a través de nuestros canales oficiales organizados por
                        isla.</p>
                </div>

                <div class="col-12 col-lg-7">
                    <form action="{{ route('public.social_networks.index') }}" method="GET"
                        class="card border-0 shadow-sm p-3 rounded-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="bi bi-geo-alt text-white"></i></span>
                                    <select name="island_id" class="form-select border-start-0 ps-0">
                                        <option value="" @selected(!$selectedIsland)>Todas las islas</option>
                                        <option value="0" @selected($selectedIsland === 0)>Canales Generales</option>
                                        @foreach ($islands as $island)
                                            <option value="{{ $island->id }}" @selected($selectedIsland === $island->id)>
                                                {{ $island->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-white w-100 py-2 fw-bold">
                                    <i class="bi bi-funnel-fill me-2"></i>Filtrar Canales
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($groups->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-search fs-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted">No hemos encontrado redes sociales para este filtro.</h4>
                    <a href="{{ route('public.social_networks.index') }}" class="btn btn-outline-primary mt-3">Ver todas las
                        redes</a>
                </div>
            @else
                <style>
                    .social-line {
                        border: 1px solid rgba(13, 110, 253, 0.15);
                        border-radius: 18px;
                        padding: 0.95rem 1.25rem;
                        background: #fff;
                        box-shadow: 0px 8px 18px rgba(15, 23, 42, 0.06);
                    }

                    .social-line+.social-line {
                        margin-top: 0.85rem;
                    }

                    .social-line__label {
                        font-weight: 600;
                        font-size: 0.95rem;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        color: #0f3d6a;
                    }

                    .social-line__icons {
                        display: flex;
                        gap: 0.45rem;
                        flex-wrap: wrap;
                        align-items: center;
                    }

                    .social-line__icon {
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        border: 1px solid transparent;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        transition: transform 0.2s ease, box-shadow 0.2s ease;
                    }

                    .social-line__icon:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 8px 15px rgba(15, 23, 42, 0.15);
                    }

                    .social-line__meta {
                        font-size: 0.78rem;
                        color: #4b5563;
                        margin-left: 0.85rem;
                    }

                    @media (max-width: 767px) {
                        .social-line {
                            flex-direction: column;
                            gap: 0.75rem;
                        }

                        .social-line__icons {
                            justify-content: flex-start;
                        }
                    }
                </style>
                @php
                    $groupCollection = collect($groups);
                    $generalGroups = $groupCollection->filter(function ($group) {
                        return str_contains(strtolower($group['label'] ?? ''), 'general') ||
                            str_contains(strtolower($group['label'] ?? ''), 'canales generales');
                    });
                    $orderedGroups = $generalGroups->concat(
                        $groupCollection->reject(function ($group) {
                            return str_contains(strtolower($group['label'] ?? ''), 'general') ||
                                str_contains(strtolower($group['label'] ?? ''), 'canales generales');
                        }),
                    );
                @endphp
                <div class="d-flex flex-column">
                    @foreach ($orderedGroups as $group)
                        <div class="social-line d-flex align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center gap-1">
                                <span class="social-line__label">{{ $group['label'] }}</span>
                                <span class="social-line__meta">{{ $group['networks']->count() }} canales</span>
                            </div>
                            <div class="social-line__icons">
                                @foreach ($group['networks'] as $network)
                                    @php
                                        $socialKey = social_network_color_key($network);
                                        $primaryColor = social_network_color($socialKey);
                                        $primaryBg = social_network_color_rgba($socialKey, 0.15);
                                        $borderColor = social_network_color_rgba($socialKey, 0.4);
                                    @endphp
                                    <a href="{{ $network->url }}" target="_blank" rel="noopener noreferrer"
                                        class="social-line__icon"
                                        style="background-color: {{ $primaryBg }}; border-color: {{ $borderColor }};"
                                        aria-label="{{ $network->name }}">
                                        <i class="{{ $network->icon_class }}" style="color: {{ $primaryColor }};"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
