<header>
    <div class="header-main">
        <!-- Formulario de búsqueda -->
        <form action="{{ route('catalog.index') }}" method="GET" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Buscar por nombre o marca..."
                value="{{ request('search') }}">
            <button type="submit" class="search-icon" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <a href="{{ route('mainPage') }}" class="logo">AROMA</a>

        <div class="user-icons">
            @auth
                <a href="{{ route('profile.index') }}">
                    <span class="icon"><i class="far fa-user"></i></span>
                </a>
            @endauth
            @guest
                <a href="{{ route('login') }}">
                    <span class="icon"><i class="far fa-user"></i></span>
                </a>
            @endguest

            <span class="icon"><i class="far fa-heart"></i></span>
            <div id="cartIconContainer" style="position: relative;" class="cart-dropdown-container">
                <a href="{{ route('cart.index') }}" style="text-decoration: none; color: inherit;">
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
                        <div id="cartPreviewItems" class="cart-preview-items">
                            <!-- Los items se cargarán aquí con AJAX -->
                        </div>
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
            @auth
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn p-0 border-0 bg-transparent">
                        <span class="icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                    </button>
                </form>
            @endauth

        </div>
    </div>

    <div class="categories">
        <a href="{{ route('catalog.index', ['category' => 'hombre']) }}" class="category">HOMBRE</a>
        <a href="{{ route('catalog.index', ['category' => 'mujer']) }}" class="category">MUJER</a>
        <a href="{{ route('catalog.index', ['category' => 'unisex']) }}" class="category">UNISEX</a>
        <a href="{{ route('catalog.index', ['category' => 'kids']) }}" class="category">NIÑOS</a>
    </div>
</header>
