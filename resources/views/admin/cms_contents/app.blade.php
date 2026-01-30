<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin CMS')</title>
    <!-- Incluimos Tailwind CSS CDN para simplificar -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .logo {
            height: 48px;
            width: auto;
            object-fit: contain;
        }
    </style>
    <!-- SLIM Image Cropper Styles -->
    @stack('styles')
</head>

<body class="bg-gray-100 antialiased">
    <div class="min-h-screen">
        <header class="bg-white shadow">
            <div class="w-full mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center">
                    <img src="{{ asset('/img/logofuertejob.png') }}" alt="Logo" class="logo mr-3">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Administración de Contenido
                    </h1>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-10xl mx-auto py-12 sm:px-12 lg:px-12">
                <!-- Mensajes de sesión -->
                @if (session('success'))
                    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>

</html>
