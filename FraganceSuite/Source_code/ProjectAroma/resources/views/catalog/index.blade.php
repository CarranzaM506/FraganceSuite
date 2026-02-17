@extends('layouts.app')

@section('body-class', 'catalog-body') 


@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesCatalog.css') }}">
@endsection

@section('content')
<!-- Barra negra pegada al header -->
<nav class="black-navbar">
    <div class="container">
        <span class="nav-title">CATÁLOGO</span>
    </div>
</nav>

<!-- Modal de filtros -->
<div class="filter-modal" id="filterModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Filtrar Productos</h3>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Ordenar por -->
            <div class="filter-option">
                <label class="filter-label">Ordenar por</label>
                <select id="modalSort" class="filter-select">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                    <option value="price-asc" {{ request('sort') == 'price-asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                    <option value="price-desc" {{ request('sort') == 'price-desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                    <option value="name-asc" {{ request('sort') == 'name-asc' ? 'selected' : '' }}>Nombre: A-Z</option>
                    <option value="name-desc" {{ request('sort') == 'name-desc' ? 'selected' : '' }}>Nombre: Z-A</option>
                </select>
            </div>
            
            <!-- Categoría -->
<div class="filter-option">
    <label class="filter-label">Categoría</label>
    <select id="modalCategory" class="filter-select">
        <option value="all" {{ !request('category') || request('category') == 'all' ? 'selected' : '' }}>
            Todas ({{ $categoryCounts['total'] ?? 0 }} productos)
        </option>
        <option value="women" {{ request('category') == 'women' ? 'selected' : '' }}>
            Mujer ({{ $categoryCounts['women'] ?? 0 }} productos)
        </option>
        <option value="men" {{ request('category') == 'men' ? 'selected' : '' }}>
            Hombre ({{ $categoryCounts['men'] ?? 0 }} productos)
        </option>
        <option value="unisex" {{ request('category') == 'unisex' ? 'selected' : '' }}>
            Unisex ({{ $categoryCounts['unisex'] ?? 0 }} productos)
        </option>
        <option value="kids" {{ request('category') == 'kids' ? 'selected' : '' }}>
            Niños ({{ $categoryCounts['kids'] ?? 0 }} productos)
        </option>
    </select>
</div>
            
            <!-- Marcas -->
            <div class="filter-option">
                <label class="filter-label">Marcas</label>
                <select id="modalBrand" class="filter-select">
                    <option value="all" {{ !request('brand') || request('brand') == 'all' ? 'selected' : '' }}>Todas las marcas</option>
                    @foreach($brands as $brand)
                        @if(!empty($brand))
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                {{ $brand }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            
            
            <!-- Precio -->
<div class="filter-option">
    <label class="filter-label">Rango de Precio</label>
    <select id="modalPrice" class="filter-select">
        <option value="all" {{ !request('price') || request('price') == 'all' ? 'selected' : '' }}>Todos los precios</option>
        <option value="0-5000" {{ request('price') == '0-5000' ? 'selected' : '' }}>₡0 - ₡5,000</option>
        <option value="5000-10000" {{ request('price') == '5000-10000' ? 'selected' : '' }}>₡5,000 - ₡10,000</option>
        <option value="10000-20000" {{ request('price') == '10000-20000' ? 'selected' : '' }}>₡10,000 - ₡20,000</option>
        <option value="20000-50000" {{ request('price') == '20000-50000' ? 'selected' : '' }}>₡20,000 - ₡50,000</option>
        <option value="50000-plus" {{ request('price') == '50000-plus' ? 'selected' : '' }}>₡50,000+</option>
    </select>
</div>
            
            <!-- Buscar por nombre -->
            <div class="filter-option">
                <label class="filter-label">Buscar por nombre</label>
                <input type="text" 
                       id="modalSearch" 
                       class="filter-select" 
                       placeholder="Escribe el nombre del producto..."
                       value="{{ request('search') }}">
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-clear" id="clearFilters">Limpiar Todo</button>
            <button class="btn-apply" id="applyFilters">Aplicar Filtros</button>
        </div>
    </div>
</div>

<div class="catalog-page">
    <!-- Filtros minimalistas -->
    <div class="catalog-filters">
        <div class="filter-left">
            <button class="filter-btn" id="openFilter">
                <i class="fas fa-sliders-h"></i> Filtrar
                @if(request('category') || request('brand') || request('price') || request('search') || (request('sort') && request('sort') != 'newest'))
                @endif
            </button>
            <span class="product-count">{{ $products->total() }} productos</span>
        </div>
    </div>
    
    <!-- Filtros activos -->
    @if(request('category') || request('brand') || request('price') || request('search') || (request('sort') && request('sort') != 'newest'))
    <div style="margin-bottom: 20px; padding: 0 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="font-size: 14px; color: #666;">Filtros aplicados:</span>
            
            @if(request('sort') && request('sort') != 'newest')
                <span class="filter-tag">
                    @switch(request('sort'))
                        @case('price-asc') Menor precio @break
                        @case('price-desc') Mayor precio @break
                        @case('name-asc') A-Z @break
                        @case('name-desc') Z-A @break
                        @default Orden: {{ request('sort') }}
                    @endswitch
                    <button onclick="removeFilter('sort')" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">×</button>
                </span>
            @endif
            
            @if(request('category') && request('category') != 'all')
                <span class="filter-tag">
                    @switch(request('category'))
                        @case('women') Mujer @break
                        @case('men') Hombre @break
                        @case('unisex') Unisex @break
                        @case('kids') Niños @break
                        @default {{ ucfirst(request('category')) }}
                    @endswitch
                    <button onclick="removeFilter('category')" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">×</button>
                </span>
            @endif
            
            @if(request('brand') && request('brand') != 'all')
                <span class="filter-tag">
                    {{ request('brand') }}
                    <button onclick="removeFilter('brand')" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">×</button>
                </span>
            @endif
            
           @if(request('price'))
    <span class="filter-tag">
        @switch(request('price'))
            @case('0-5000') ₡0 - ₡5,000 @break
            @case('5000-10000') ₡5,000 - ₡10,000 @break
            @case('10000-20000') ₡10,000 - ₡20,000 @break
            @case('20000-50000') ₡20,000 - ₡50,000 @break
            @case('50000-plus') ₡50,000+ @break
        @endswitch
        <button onclick="removeFilter('price')" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">×</button>
    </span>
@endif
            
            @if(request('search'))
                <span class="filter-tag">
                    "{{ request('search') }}"
                    <button onclick="removeFilter('search')" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">×</button>
                </span>
            @endif
            
            @if(request('category') || request('brand') || request('price') || request('search') || (request('sort') && request('sort') != 'newest'))
                <button onclick="clearAllFilters()" style="background: none; border: none; color: #666; cursor: pointer; font-size: 14px; margin-left: 10px;">
                    <i class="fas fa-times"></i> Limpiar todos
                </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Grid de productos -->
    <section class="catalog-products">
        @if($products->isEmpty())
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>No hay productos disponibles</h3>
                <p>No se encontraron productos con los filtros aplicados.</p>
                <button onclick="clearAllFilters()" class="filter-btn" style="margin-top: 20px;">
                    <i class="fas fa-times"></i> Limpiar filtros
                </button>
            </div>
        @else
            <div class="product-grid catalog-grid">
                @foreach($products as $product)
                <div class="product-card catalog-card">
                    <div class="product-image">
                        @if($product->pathimg)
                            <img src="{{ $product->pathimg }}" alt="{{ $product->name }}" class="product-img">
                        @else
                            <div class="product-img-placeholder">
                                <i class="fas fa-wine-bottle"></i>
                            </div>
                        @endif
                        
                        <div class="product-hover">
                            <span class="wishlist-icon" data-product="{{ $product->idproduct }}">
                                <i class="far fa-heart"></i>
                            </span>
                            <span class="add-cart-icon" data-product="{{ $product->idproduct }}">
                                <i class="fas fa-plus"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">{{ $product->name }}</h3>
                        <p class="product-brand">{{ $product->brand }}</p>
                        <p class="product-category" style="display: none;">{{ $product->category }}</p>
                        <span class="product-price">₡{{ number_format($product->price, 2) }}</span>                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Flechas de paginación -->
            <div class="pagination-arrows">
                @if($products->onFirstPage())
                    <button class="arrow-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="arrow-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif
                
                <span style="padding: 10px 15px; color: #666; font-size: 14px;">
                    Página {{ $products->currentPage() }} de {{ $products->lastPage() }}
                </span>
                
                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="arrow-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <button class="arrow-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Solo aplicar efectos sticky si estamos en catálogo
    const body = document.body;
    const isCatalog = body.classList.contains('catalog-body');
    
    if (!isCatalog) return;
    
    const header = document.querySelector('header');
    const blackNavbar = document.querySelector('.black-navbar');
    let lastScrollTop = 0;
    
    // Alturas importantes
    const headerHeight = header.offsetHeight;
    const blackNavbarHeight = blackNavbar ? blackNavbar.offsetHeight : 0;
    const totalFixedHeight = headerHeight + blackNavbarHeight;
    
    // Configurar posición inicial
    if (blackNavbar) {
        blackNavbar.style.top = headerHeight + 'px';
        blackNavbar.style.position = 'fixed';
    }
    
    // FUNCIÓN PRINCIPAL DE SCROLL
    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollingDown = scrollTop > lastScrollTop;
        
        // 1. SI ESTAMOS EN LA PARTE SUPERIOR
        if (scrollTop < 50) {
            // Mostrar ambos elementos
            header.classList.remove('hidden');
            header.classList.add('visible');
            
            if (blackNavbar) {
                blackNavbar.classList.remove('hidden');
                blackNavbar.classList.add('visible');
                blackNavbar.style.top = headerHeight + 'px';
                blackNavbar.style.position = 'fixed';
            }
            lastScrollTop = scrollTop;
            return;
        }
        
        // 2. SCROLL HACIA ABAJO (ocultar)
        if (scrollingDown) {
            // Solo ocultar si hemos bajado suficiente
            if (scrollTop - lastScrollTop > 5) {
                header.classList.remove('visible');
                header.classList.add('hidden');
                
                if (blackNavbar) {
                    blackNavbar.classList.remove('visible');
                    blackNavbar.classList.add('hidden');
                }
            }
        } 
        // 3. SCROLL HACIA ARRIBA (mostrar)
        else {
            header.classList.remove('hidden');
            header.classList.add('visible');
            
            if (blackNavbar) {
                blackNavbar.classList.remove('hidden');
                blackNavbar.classList.add('visible');
                blackNavbar.style.top = headerHeight + 'px';
                blackNavbar.style.position = 'fixed';
            }
        }
        
        lastScrollTop = scrollTop;
    }
    
    // FUNCIÓN PARA HEADER STICKY - FIJAR CUANDO HACE SCROLL
    function handleStickyHeader() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Si hemos bajado más allá del header, fijar la barra negra arriba
        if (blackNavbar && scrollTop > headerHeight) {
            // La barra negra se queda fija en top: 0 cuando el header está oculto
            if (header.classList.contains('hidden')) {
                blackNavbar.style.top = '0';
            }
        }
    }
    
    // Combinar ambas funciones
    function combinedScrollHandler() {
        handleScroll();
        handleStickyHeader();
    }
    
    // Inicializar eventos
    window.addEventListener('scroll', combinedScrollHandler);
    
    // Forzar posición inicial
    setTimeout(() => {
        if (blackNavbar) {
            blackNavbar.style.top = headerHeight + 'px';
            blackNavbar.style.position = 'fixed';
        }
        combinedScrollHandler();
    }, 100);
    
    // Redimensionar ventana - recalcular alturas
    window.addEventListener('resize', function() {
        if (blackNavbar) {
            const newHeaderHeight = header.offsetHeight;
            blackNavbar.style.top = newHeaderHeight + 'px';
        }
    });
    
    // ========== SISTEMA DE FILTROS EN TIEMPO REAL ==========
    
    // Modal de filtros
    const filterModal = document.getElementById('filterModal');
    const openFilterBtn = document.getElementById('openFilter');
    const closeModalBtn = document.getElementById('closeModal');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    // Cargar valores actuales en el modal
    const urlParams = new URLSearchParams(window.location.search);
    
    // Abrir modal
    if (openFilterBtn) {
        openFilterBtn.addEventListener('click', function() {
            filterModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Cerrar modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            filterModal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Cerrar modal al hacer clic fuera
    if (filterModal) {
        filterModal.addEventListener('click', function(e) {
            if (e.target === filterModal) {
                filterModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && filterModal && filterModal.classList.contains('active')) {
            filterModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Aplicar filtros
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    // Limpiar filtros dentro del modal
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            document.getElementById('modalSort').value = 'newest';
            document.getElementById('modalCategory').value = 'all';
            document.getElementById('modalBrand').value = 'all';
            document.getElementById('modalPrice').value = 'all';
            document.getElementById('modalSearch').value = '';
        });
    }
    
    // Función para aplicar filtros
    function applyFilters() {
        // Obtener valores del modal
        const sortBy = document.getElementById('modalSort').value;
        const category = document.getElementById('modalCategory').value;
        const brand = document.getElementById('modalBrand').value;
        const price = document.getElementById('modalPrice').value;
        const search = document.getElementById('modalSearch').value;
        
        // Construir URL con parámetros
        const params = new URLSearchParams();
        
        // Solo agregar parámetros si no son valores por defecto
        if (sortBy !== 'newest') params.append('sort', sortBy);
        if (category !== 'all') params.append('category', category);
        if (brand !== 'all') params.append('brand', brand);
        if (price !== 'all') params.append('price', price);
        if (search.trim() !== '') params.append('search', search.trim());
        
        // Quitar página actual al filtrar
        params.delete('page');
        
        const queryString = params.toString();
        const baseUrl = window.location.pathname;
        const url = baseUrl + (queryString ? '?' + queryString : '');
        
        // Cerrar modal y redirigir
        filterModal.classList.remove('active');
        document.body.style.overflow = '';
        window.location.href = url;
    }
    
    // Función para remover un filtro específico
    window.removeFilter = function(filterName) {
        const params = new URLSearchParams(window.location.search);
        params.delete(filterName);
        params.delete('page');
        
        const queryString = params.toString();
        const baseUrl = window.location.pathname;
        const url = baseUrl + (queryString ? '?' + queryString : '');
        
        window.location.href = url;
    }
    
    // Función para limpiar todos los filtros
    window.clearAllFilters = function() {
        window.location.href = window.location.pathname;
    }
    
    // ========== FIN SISTEMA DE FILTROS ==========
    
    // Animación de entrada de productos
    const productCards = document.querySelectorAll('.catalog-card');
    
    function animateProducts() {
        productCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }
    
    // Iniciar animación
    setTimeout(animateProducts, 300);
    
    // Wishlist
    document.querySelectorAll('.wishlist-icon').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const heartIcon = this.querySelector('i');
            heartIcon.classList.toggle('far');
            heartIcon.classList.toggle('fas');
            
            if (heartIcon.classList.contains('fas')) {
                showToast('Añadido a favoritos');
            } else {
                showToast('Quitado de favoritos');
            }
        });
    });
    
    // Carrito
    document.querySelectorAll('.add-cart-icon').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            this.classList.add('adding');
            setTimeout(() => {
                this.classList.remove('adding');
            }, 300);
            
            showToast('Producto añadido al carrito');
        });
    });
    
    // Toast notifications
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: #000;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            z-index: 9999;
            animation: slideInUp 0.3s ease;
            font-size: 14px;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Añadir estilos CSS para toast
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInUp {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideOutDown {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(100px); opacity: 0; }
        }
        
        .toast-notification {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .add-cart-icon.adding {
            animation: pulse 0.3s ease;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        
        /* Mejoras para la animación de la barra negra */
        .black-navbar {
            transition: transform 0.3s ease-in-out, top 0.3s ease-in-out !important;
        }
        
        /* Estilos para los tags de filtros */
        .filter-tag {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            background: #927a1b;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            gap: 5px;
        }
        
        .filter-badge {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #ff6b6b;
            border-radius: 50%;
            margin-left: 5px;
            vertical-align: super;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush