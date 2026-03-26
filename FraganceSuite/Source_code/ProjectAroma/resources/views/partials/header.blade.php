<header>
    <div class="header-main">
        <!-- Botón de menú lateral (hamburguesa)  -->
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Logo centrado -->
        <a href="{{ route('mainPage') }}" class="logo">AROMA</a>

        <!-- Iconos de usuario y búsqueda a la derecha -->
        <div class="user-icons">
            <!-- Formulario de búsqueda integrado en iconos -->
            <form action="{{ route('catalog.index') }}" method="GET" class="search-icon-form">
                <input type="text" name="search" class="search-input-mobile" placeholder="Buscar..." value="{{ request('search') }}">
<button type="submit" class="search-icon-btn">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
</button>
            </form>

            <!-- Icono de favoritos -->
            <a href="{{ route('favorites.index') }}" class="icon-link">
                <span class="icon"><i class="far fa-heart"></i></span>
            </a>

            <!-- Icono de carrito con dropdown -->
            <div id="cartIconContainer" class="cart-dropdown-container">
                <a href="{{ route('cart.index') }}" class="icon-link">
                    <span class="icon" id="cartIcon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </span>
                </a>

                <!-- Dropdown Preview del Carrito -->
                <div id="cartPreview" class="cart-preview-dropdown" style="display: none;">
                    <div class="cart-preview-content">
                        <div class="cart-preview-header">
                            <h3>Tu Carrito</h3>
                        </div>
                        <div id="cartPreviewItems" class="cart-preview-items"></div>
                        <div class="cart-preview-footer">
                            <div class="cart-total">
                                <strong>Total:</strong>
                                <span id="cartPreviewTotal">₡0</span>
                            </div>
                            <a href="{{ route('cart.index') }}" class="btn-view-cart">Ver Carrito</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- MENÚ LATERAL -->
<div class="side-menu" id="sideMenu">
    <div class="side-menu-header">
        <span class="side-menu-logo">AROMA</span>
        <button class="side-menu-close" id="closeMenuBtn" aria-label="Cerrar menú">✕</button>
    </div>
    <nav class="side-menu-nav">
        <!-- Enlace Inicio -->
        <a href="{{ route('mainPage') }}" class="side-menu-link">
            <i class="fas fa-home"></i> Inicio
        </a>
        
        <!-- Enlace Catálogo -->
        <a href="{{ route('catalog.index') }}" class="side-menu-link">
            <i class="fas fa-box-open"></i> Catálogo
        </a>
        
        <!-- Sección de Categorías -->
        <div class="side-menu-categories">
            <span class="side-menu-categories-title">Categorías</span>
            <a href="{{ route('catalog.index', ['category' => 'hombre']) }}" class="side-menu-category-link">
                <i class="fas fa-male"></i> Hombre
            </a>
            <a href="{{ route('catalog.index', ['category' => 'mujer']) }}" class="side-menu-category-link">
                <i class="fas fa-female"></i> Mujer
            </a>
            <a href="{{ route('catalog.index', ['category' => 'unisex']) }}" class="side-menu-category-link">
                <i class="fas fa-venus-mars"></i> Unisex
            </a>
            <a href="{{ route('catalog.index', ['category' => 'kids']) }}" class="side-menu-category-link">
                <i class="fas fa-child"></i> Niños
            </a>
        </div>
        
        <!-- Enlace Favoritos -->
        <a href="{{ route('favorites.index') }}" class="side-menu-link">
            <i class="far fa-heart"></i> Favoritos
        </a>
        
        <!-- Enlace Carrito -->
        <a href="{{ route('cart.index') }}" class="side-menu-link">
            <i class="fas fa-shopping-cart"></i> Carrito
        </a>
        
        <!-- Sección de Usuario (Perfil y Logout) -->
        @auth
            <a href="{{ route('profile.index') }}" class="side-menu-link">
                <i class="far fa-user"></i> Mi Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="side-menu-logout-form">
                @csrf
                <button type="submit" class="side-menu-link side-menu-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        @endauth
        @guest
            <a href="{{ route('login') }}" class="side-menu-link">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </a>
            <a href="{{ route('register') }}" class="side-menu-link">
                <i class="fas fa-user-plus"></i> Registrarse
            </a>
        @endguest
    </nav>
</div>

<!-- Overlay para el menú lateral -->
<div class="menu-overlay" id="menuOverlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sideMenu = document.getElementById('sideMenu');
        const closeMenuBtn = document.getElementById('closeMenuBtn');
        const menuOverlay = document.getElementById('menuOverlay');

        function openMenu() {
            sideMenu.classList.add('open');
            menuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            sideMenu.classList.remove('open');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (menuToggle) menuToggle.addEventListener('click', openMenu);
        if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
        if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);

        // Cerrar menú al hacer clic en cualquier enlace
        const menuLinks = document.querySelectorAll('.side-menu-link');
        menuLinks.forEach(link => {
            link.addEventListener('click', closeMenu);
        });
    });
</script>