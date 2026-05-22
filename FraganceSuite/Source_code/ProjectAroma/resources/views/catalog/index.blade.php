@extends('layouts.app')

@section('body-class', 'catalog-body') 


@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesCatalog.css') }}">
@endsection

@section('content')


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
                @if(request('offers') || request('category') || request('brand') || request('price') || request('search') || (request('sort') && request('sort') != 'newest'))
                @endif
            </button>
            <span class="product-count">{{ $products->total() }} productos</span>
        </div>
    </div>
    
    <!-- Filtros activos -->
    @if(request('offers') || request('category') || request('brand') || request('price') || request('search') || (request('sort') && request('sort') != 'newest'))
    <div style="margin-bottom: 20px; padding: 0 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="font-size: 14px; color: #666;">Filtros aplicados:</span>

            @if(request('offers'))
                <span class="filter-tag">
                    Ofertas especiales
                    <button onclick="removeFilter('offers')" style="background: none; border: none; color: white; cursor: pointer; margin-left: 5px;">Ã—</button>
                </span>
            @endif
            
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
            
            @if(request('offers') || request('category') || request('brand') || request('price') || request('search') || (request('sort') && request('sort') != 'newest'))
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
            </div>
        @else
            <div class="product-grid catalog-grid">
               <!-- En el grid de productos, modifica el div del producto para que sea un enlace -->
@foreach($products as $product)
<a href="{{ route('product.show', $product->idproduct) }}" class="product-card catalog-card" style="text-decoration: none; color: inherit;">
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
            <span class="add-cart-icon" onclick="event.preventDefault(); event.stopPropagation();" data-product="{{ $product->idproduct }}">
                <i class="fas fa-plus"></i>
            </span>
        </div>
    </div>
    
    <div class="product-info">
        <h3 class="product-name">{{ $product->name }}</h3>
        <p class="product-brand">{{ $product->brand }}</p>
        <p class="product-price">₡{{ number_format($product->price, 2) }}</p>
    </div>
</a>
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
    // ========== MODAL DE FILTROS ==========
    const filterModal = document.getElementById('filterModal');
    const openFilterBtn = document.getElementById('openFilter');
    const closeModalBtn = document.getElementById('closeModal');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    function applyFilters() {
        const sortBy = document.getElementById('modalSort').value;
        const category = document.getElementById('modalCategory').value;
        const brand = document.getElementById('modalBrand').value;
        const price = document.getElementById('modalPrice').value;
        const search = document.getElementById('modalSearch').value;
        
        const params = new URLSearchParams();
        if (sortBy !== 'newest') params.append('sort', sortBy);
        if (category !== 'all') params.append('category', category);
        if (brand !== 'all') params.append('brand', brand);
        if (price !== 'all') params.append('price', price);
        if (search.trim() !== '') params.append('search', search.trim());
        
        params.delete('page');
        
        const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        filterModal.classList.remove('active');
        document.body.style.overflow = '';
        window.location.href = url;
    }
    
    if (openFilterBtn) {
        openFilterBtn.addEventListener('click', function() {
            filterModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            filterModal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    if (filterModal) {
        filterModal.addEventListener('click', function(e) {
            if (e.target === filterModal) {
                filterModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    if (applyFiltersBtn) applyFiltersBtn.addEventListener('click', applyFilters);
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            document.getElementById('modalSort').value = 'newest';
            document.getElementById('modalCategory').value = 'all';
            document.getElementById('modalBrand').value = 'all';
            document.getElementById('modalPrice').value = 'all';
            document.getElementById('modalSearch').value = '';
        });
    }
    
    window.removeFilter = function(filterName) {
        const params = new URLSearchParams(window.location.search);
        params.delete(filterName);
        params.delete('page');
        const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }
    
    window.clearAllFilters = function() {
        window.location.href = window.location.pathname;
    }
    
    // Animación de productos
    const productCards = document.querySelectorAll('.catalog-card');
    productCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
