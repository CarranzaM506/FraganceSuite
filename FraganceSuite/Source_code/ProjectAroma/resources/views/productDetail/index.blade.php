@extends('layouts.app')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesProductDetail.css') }}">  
@endsection

@section('content')
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('mainPage') }}">Inicio</a> / 
        <a href="{{ route('catalog.index') }}">Catálogo</a> / 
        <span>{{ $product->name }}</span>
    </div>

    <!-- Product Detail -->
    <div class="product-detail-container">
        <!-- Product Gallery -->
        <div class="product-gallery">
            @if($discountedPrice)
                <div class="discount-badge">
                    -{{ $product->discount->value }}%
                </div>
            @endif
            
            <div class="main-image">
                @if($product->pathimg)
                    <img src="{{ $product->pathimg }}" alt="{{ $product->name }}" id="mainProductImage">
                @else
                    <div class="image-placeholder">
                        <i class="fas fa-wine-bottle"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <div class="product-category">{{ $product->category ?? 'Sin categoría' }}</div>
            <h1 class="product-title">{{ $product->name }}</h1>
            <div class="product-brand">{{ $product->brand }}</div>

            <div class="product-price-section">
                <div class="price-container">
                    @if($discountedPrice)
                        <span class="current-price">₡{{ number_format($discountedPrice, 2) }}</span>
                        <span class="old-price">₡{{ number_format($product->price, 2) }}</span>
                        <span class="discount-info">{{ $product->discount->value }}% OFF</span>
                    @else
                        <span class="current-price">₡{{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <div class="stock-info">
                    @if($product->stock > 0)
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i> En stock
                        </span>
                        <span class="stock-badge">{{ $product->stock }} unidades disponibles</span>
                    @else
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i> Agotado
                        </span>
                    @endif
                </div>
            </div>

            <!-- Quantity Selector -->
            @if($product->stock > 0)
                <div class="quantity-selector">
                    <span class="quantity-label">Cantidad:</span>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" id="decrementQty" onclick="updateQuantity(-1)">−</button>
                        <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="{{ $product->stock }}" readonly>
                        <button type="button" class="quantity-btn" id="incrementQty" onclick="updateQuantity(1)">+</button>
                    </div>
                </div>
            @endif

            <!-- Product Actions -->
            <div class="product-actions">
                @if($product->stock > 0)
                    <button class="btn-primary" onclick="addToCart({{ $product->idproduct }})">
                        Añadir al Carrito
                    </button>
                @else
                    <button class="btn-primary" disabled>
                        Producto Agotado
                    </button>
                @endif
                
                <button class="btn-secondary" id="wishlistBtn" data-product="{{ $product->idproduct }}">
    <i class="far fa-heart"></i> Favoritos
</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let currentQuantity = 1;
    const maxStock = {{ $product->stock }};

    // Función para actualizar cantidad
    function updateQuantity(change) {
        const newQuantity = currentQuantity + change;
        
        if (newQuantity >= 1 && newQuantity <= maxStock) {
            currentQuantity = newQuantity;
            document.getElementById('quantity').value = currentQuantity;
            
            document.getElementById('decrementQty').disabled = currentQuantity <= 1;
            document.getElementById('incrementQty').disabled = currentQuantity >= maxStock;
        }
    }

    // Función para añadir al carrito
    async function addToCart(productId) {
        try {
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                if (window.favoritesSystem) {
                    window.favoritesSystem.showNotification('Error: CSRF token no encontrado', 'error');
                }
                return;
            }

            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
                },
                body: JSON.stringify({ productId, quantity: currentQuantity })
            });
            
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return;
            }
            
            if (response.ok) {
                if (window.favoritesSystem) {
                    window.favoritesSystem.showNotification('Producto añadido al carrito', 'success');
                }
                if (window.cartPreview) {
                    setTimeout(() => {
                        window.cartPreview.updatePreview();
                    }, 100);
                }
            } else {
                const errorData = await response.json();
                if (window.favoritesSystem) {
                    window.favoritesSystem.showNotification(`Error: ${errorData.error || 'Error al añadir al carrito'}`, 'error');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.favoritesSystem) {
                window.favoritesSystem.showNotification('Error al añadir al carrito', 'error');
            }
        }
    }

    // Cargar estado inicial de favoritos
    function loadFavoriteStatus() {
        if (!window.isAuthenticated) return;
        
        const productId = {{ $product->idproduct }};
        fetch(`/favorites/check/${productId}`)
            .then(response => response.json())
            .then(data => {
                const wishlistBtn = document.getElementById('wishlistBtn');
                const heartIcon = wishlistBtn.querySelector('i');
                if (data.isFavorite) {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    wishlistBtn.classList.add('active');
                }
            })
            .catch(error => console.error('Error loading favorite status:', error));
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        if (maxStock > 0) {
            document.getElementById('decrementQty').disabled = true;
        }
        
        loadFavoriteStatus();
    });

    // Efecto de scroll en la imagen
    window.addEventListener('scroll', function() {
        const mainImage = document.querySelector('.main-image img');
        if (mainImage) {
            const scrollPosition = window.scrollY;
            const scale = 1 + (scrollPosition * 0.0005);
            mainImage.style.transform = `scale(${Math.min(scale, 1.03)})`;
        }
    });
</script>
@endpush