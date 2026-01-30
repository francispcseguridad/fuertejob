<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuertejob :: Portal Empresas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light min-vh-100">
    <div class="d-flex flex-column flex-md-row min-vh-100">
        <aside class="bg-white border-end shadow-sm p-4" style="width: 280px;">
            <div class="mb-4">
                <span class="fw-bold text-primary h4 mb-0">Portal Empresa</span>
                <p class="text-muted small mb-0">Panel de control</p>
            </div>
            <nav class="nav flex-column gap-2">
                <a class="nav-link d-flex align-items-center gap-2 active text-primary" href="#">
                    <i class="bi bi-house-fill"></i> Dashboard
                </a>
                <a class="nav-link d-flex align-items-center gap-2 text-secondary" href="#">
                    <i class="bi bi-plus-circle"></i> Publicar Oferta
                </a>
                <a class="nav-link d-flex align-items-center gap-2 text-secondary" href="#">
                    <i class="bi bi-briefcase"></i> Mis Ofertas
                </a>
                <a class="nav-link d-flex align-items-center gap-2 text-secondary" href="#">
                    <i class="bi bi-receipt"></i> Facturación
                </a>
                <a class="nav-link d-flex align-items-center gap-2 text-secondary" href="#">
                    <i class="bi bi-building"></i> Perfil de Empresa
                </a>
            </nav>
            <div class="mt-4 pt-4 border-top">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">E
                    </div>
                    <div>
                        <p class="mb-0 fw-semibold">Empresa Demo</p>
                        <small class="text-muted">contacto@empresa.es</small>
                    </div>
                </div>
                <a href="/salir" class="btn btn-outline-danger w-100 mt-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </a>
            </div>
        </aside>

        <main class="flex-grow-1">
            <header class="bg-white border-bottom shadow-sm py-3 px-4">
                <h1 class="h4 mb-0">Panel de Control de la Empresa</h1>
                <small class="text-muted">Gestión centralizada de publicaciones y finanzas.</small>
            </header>
            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
