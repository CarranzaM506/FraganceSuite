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
            <span class="icon"><i class="far fa-user"></i></span>
            <span class="icon"><i class="far fa-heart"></i></span>
            <span class="icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </span>
        </div>
    </div>
    
    <div class="categories">
        <a href="{{ route('catalog.index', ['category' => 'hombre']) }}" class="category">HOMBRE</a>
        <a href="{{ route('catalog.index', ['category' => 'mujer']) }}" class="category">MUJER</a>
        <a href="{{ route('catalog.index', ['category' => 'unisex']) }}" class="category">UNISEX</a>
        <a href="{{ route('catalog.index', ['category' => 'kids']) }}" class="category">NIÑOS</a>
    </div>
</header>