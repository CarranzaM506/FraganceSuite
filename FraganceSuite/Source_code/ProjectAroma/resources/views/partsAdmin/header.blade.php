@yield('header')

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AROMA | Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

</head>

<body>

    <!-- Header negro -->
    <div class="topbar">
        <div class="container-fluid py-2 px-3 d-flex align-items-center justify-content-between text-white">
            <!-- Hamburguesa (solo móvil) -->
            <button class="btn btn-outline-light only-mobile" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Abrir menú">
                ☰
            </button>

            <div class="fw-bold">@yield('title')</div>

            <div class="d-flex align-items-center gap-2">
                <span class="small d-none d-sm-inline">Admin</span>
                <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center"
                    style="width:34px;height:34px;">A</div>
            </div>
        </div>
    </div>

    <div class="layout">

        <!-- Sidebar -->
        <div class="offcanvas offcanvas-start aroma-offcanvas" tabindex="-1" id="sidebar"
            aria-labelledby="sidebarLabel">

            <div class="offcanvas-header p-0">
                <div class="sidebar-logo w-100">
                    <div>
                        <p class="title mb-1">AROMA</p>
                        <p class="subtitle">Administración</p>
                    </div>

                    <button type="button" class="btn-close btn-close-white ms-auto me-2" data-bs-dismiss="offcanvas"
                        aria-label="Cerrar"></button>
                </div>
            </div>

            <div class="offcanvas-body">
                <nav class="nav flex-column gap-1">

                    <!-- Dashboard -->
                    <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5Z" />
                        </svg>
                        Dashboard
                    </a>

                    <!-- Productos -->
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('product') ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenuProductos" role="button" aria-expanded="false" aria-controls="submenuProductos">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M7 7h10v10H7z" />
                                <path d="M4 4h16v16H4z" />
                            </svg>
                            Productos
                        </span>
                        <span class="ms-2" aria-hidden="true">▾</span>
                    </a>

                    <div class="collapse submenu" id="submenuProductos">
                        <a class="nav-link" href="{{ route('product.create') }}">Crear</a>
                        <a class="nav-link" href="{{ route('product.index') }}">Ver</a>
                    </div>

                    <hr class="border-light opacity-25 my-3" />

                    <!-- Promociones -->
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('discount') ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenuPromociones" role="button" aria-expanded="false" aria-controls="submenuPromociones">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 12h-8" />
                                <path d="M12 4v8" />
                                <path d="M4 20h16" />
                            </svg>
                            Promociones
                        </span>
                        <span class="ms-2" aria-hidden="true">▾</span>
                    </a>

                    <div class="collapse submenu" id="submenuPromociones">
                        <a class="nav-link" href="{{ route('discount.create') }}">Crear</a>
                        <a class="nav-link" href="{{ route('discount.index') }}">Ver</a>
                    </div>

                    <a class="nav-link" href="#">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 17l5-5-5-5" />
                            <path d="M4 4h7a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H4" />
                        </svg>
                        Cerrar sesión
                    </a>

                </nav>
            </div>
        </div>

        @yield('content')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>
