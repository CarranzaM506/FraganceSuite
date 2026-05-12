@yield('header')

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>AROMA | Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body class="{{ request()->is('promotionCode*') ? 'promo-codes-admin-mode' : '' }}">

    <!-- Header negro -->
    <div class="topbar">
        <div class="container-fluid py-2 px-3 d-flex align-items-center justify-content-between text-white">
            <!-- Hamburguesa (solo móvil) -->
            <button class="btn btn-outline-light only-mobile" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sidebar" aria-controls="sidebar">
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

        <!-- Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start aroma-offcanvas" tabindex="-1" id="sidebar"
            aria-labelledby="sidebarLabel" data-bs-backdrop="true" data-bs-keyboard="true">

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
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('product*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenuProductos" role="button" aria-expanded="false"
                        aria-controls="submenuProductos">
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

                    <!-- Videos -->
                    <a class="nav-link {{ request()->is('video*') ? 'active' : '' }}" href="{{ route('video.index') }}">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                        Videos
                    </a>

                    <!-- Hero -->
                    <a class="nav-link {{ request()->is('hero*') ? 'active' : '' }}" href="{{ route('hero.index') }}">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="2.18" />
                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor" />
                            <path d="m21 15-5-4-3 3-5-4-6 5" />
                        </svg>
                        Hero Principal
                    </a>

                    <!-- Promociones -->
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('discount*') || request()->is('promotionCode*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenuPromociones" role="button" aria-expanded="false"
                        aria-controls="submenuPromociones">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M20 12h-8" />
                                <path d="M12 4v8" />
                                <path d="M4 20h16" />
                            </svg>
                            Promociones
                        </span>
                        <span class="ms-2" aria-hidden="true">▾</span>
                    </a>

                    <div class="collapse submenu {{ request()->is('discount*') || request()->is('promotionCode*') ? 'show' : '' }}" id="submenuPromociones">
                        <a class="nav-link {{ request()->routeIs('discount.create') ? 'active' : '' }}" href="{{ route('discount.create') }}">Crear promoción</a>
                        <a class="nav-link {{ request()->routeIs('discount.index') ? 'active' : '' }}" href="{{ route('discount.index') }}">Ver promoción</a>

                        <hr class="border-light opacity-25 my-3 mx-4" />

                        <a class="nav-link {{ request()->routeIs('promotionCode.create') ? 'active' : '' }}" href="{{ route('promotionCode.create') }}">Crear código</a>
                        <a class="nav-link {{ request()->routeIs('promotionCode.index') ? 'active' : '' }}" href="{{ route('promotionCode.index') }}">Ver código</a>
                    </div>

                    <!-- Marcas -->
                    <a class="nav-link {{ request()->is('brands*') ? 'active' : '' }}" href="{{ route('brands.index') }}">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                        Logos de Marcas
                    </a>

                    <!-- Ventas -->
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('sales*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenuVentas" role="button" aria-expanded="false"
                        aria-controls="submenuVentas">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M3 3v18h18" />
                                <path d="M7 14l4-4 4 4 5-5" />
                            </svg>
                            Ventas
                        </span>
                        <span class="ms-2">▾</span>
                    </a>

                    <div class="collapse submenu" id="submenuVentas">
                        <a class="nav-link" href="{{ route('orders.index') }}">
                            Ventas Mensuales
                        </a>
                        <a class="nav-link" href="{{route('dailySales')}}">
                            Ventas Diarias
                        </a>
                    </div>

                    <a class="nav-link {{ request()->is('dashboard/reviews*') ? 'active' : '' }}"
                        href="{{ route('dashboard.reviews.index') }}">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                        Moderar Reseñas
                    </a>

                    <hr class="border-light opacity-25 my-3" />

                    @if (Auth::check())
                        <a class="nav-link" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M10 17l5-5-5-5" />
                                <path d="M4 4h7a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H4" />
                            </svg>
                            Cerrar sesión
                        </a>

                        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                            @csrf
                        </form>
                    @endif

                </nav>
            </div>
        </div>

        @yield('content')

    </div>

    <div class="modal fade" id="adminDeleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <p class="mb-0" id="adminDeleteConfirmMessage">&iquest;Est&aacute;s seguro de eliminar este registro?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-2 border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger btn-sm px-4" id="adminDeleteConfirmBtn">S&iacute;, eliminar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="adminUpdateConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <p class="mb-0" id="adminUpdateConfirmMessage">&iquest;Deseas guardar los cambios?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-2 border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-dark btn-sm px-4" id="adminUpdateConfirmBtn">S&iacute;, actualizar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Forzar que el offcanvas se cierre correctamente en móvil
        document.addEventListener('DOMContentLoaded', function() {
            var offcanvasElement = document.getElementById('sidebar');
            if (offcanvasElement) {
                var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (!bsOffcanvas) {
                    bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement, {
                        backdrop: true,
                        keyboard: true
                    });
                }
            }
        });
        window.openAdminDeleteConfirm = function(message, onConfirm) {
            const modalEl = document.getElementById('adminDeleteConfirmModal');
            const messageEl = document.getElementById('adminDeleteConfirmMessage');
            const confirmBtn = document.getElementById('adminDeleteConfirmBtn');
            if (!modalEl || !messageEl || !confirmBtn || typeof bootstrap === 'undefined') {
                const fallback = window.confirm('Estas seguro de eliminar este registro?');
                if (fallback && typeof onConfirm === 'function') onConfirm();
                return;
            }

            messageEl.innerHTML = message || '&iquest;Est&aacute;s seguro de eliminar este registro?';
            const modal = new bootstrap.Modal(modalEl);
            const previousActive = document.activeElement;

            const onHidden = function() {
                const focused = document.activeElement;
                if (focused && modalEl.contains(focused) && typeof focused.blur === 'function') {
                    focused.blur();
                }
                if (previousActive && typeof previousActive.focus === 'function') {
                    previousActive.focus();
                }
            };

            modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });
            confirmBtn.onclick = function() {
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
                modal.hide();
                if (typeof onConfirm === 'function') onConfirm();
            };
            modal.show();
        };

        window.openAdminUpdateConfirm = function(message, onConfirm) {
            const modalEl = document.getElementById('adminUpdateConfirmModal');
            const messageEl = document.getElementById('adminUpdateConfirmMessage');
            const confirmBtn = document.getElementById('adminUpdateConfirmBtn');
            if (!modalEl || !messageEl || !confirmBtn || typeof bootstrap === 'undefined') {
                const fallback = window.confirm('Deseas guardar los cambios?');
                if (fallback && typeof onConfirm === 'function') onConfirm();
                return;
            }

            messageEl.innerHTML = message || '&iquest;Deseas guardar los cambios?';
            const modal = new bootstrap.Modal(modalEl);
            const previousActive = document.activeElement;

            const onHidden = function() {
                const focused = document.activeElement;
                if (focused && modalEl.contains(focused) && typeof focused.blur === 'function') {
                    focused.blur();
                }
                if (previousActive && typeof previousActive.focus === 'function') {
                    previousActive.focus();
                }
            };

            modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });
            confirmBtn.onclick = function() {
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
                modal.hide();
                if (typeof onConfirm === 'function') onConfirm();
            };
            modal.show();
        };

        document.addEventListener('submit', function(event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            const message = form.dataset.confirmMessage;
            if (!message) return;

            if (form.dataset.confirmed === 'true') {
                form.dataset.confirmed = 'false';
                return;
            }

            event.preventDefault();
            openAdminUpdateConfirm(message, function() {
                form.dataset.confirmed = 'true';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
