<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Menús')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
        }

        .logo {
            height: 48px;
            width: auto;
            object-fit: contain;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-light">
    <div class="min-vh-100 d-flex flex-column">
        <header class="bg-white shadow-sm">
            <div class="container py-3 d-flex align-items-center gap-3 flex-wrap">
                <img src="{{ asset('/img/logofuertejob.png') }}" alt="Logo" class="logo">
                <div>
                    <p class="text-uppercase text-muted mb-0 small">Panel de Administración</p>
                    <h1 class="h4 mb-0 fw-semibold text-dark">Administrador de Menús</h1>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                    class="ms-auto btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">Volver al Dashboard</a>
            </div>
        </header>

        <main class="flex-grow-1 py-5">
            <div class="container">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger rounded-4 shadow-sm" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    @stack('scripts')
</body>

</html>
