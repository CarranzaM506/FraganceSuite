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
            
            // Actualizar botones
            document.getElementById('decrementQty').disabled = currentQuantity <= 1;
            document.getElementById('incrementQty').disabled = currentQuantity >= maxStock;
        }
    }

    // Función para añadir al carrito
    async function addToCart(productId) {
        try {
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                showNotification('Error: CSRF token no encontrado. Recarga la página.', 'error');
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
                showNotification('Producto añadido al carrito', 'success');
                if (window.cartPreview) {
                    setTimeout(() => {
                        window.cartPreview.updatePreview();
                    }, 100);
                }
            } else {
                const errorData = await response.json();
                showNotification(`Error: ${errorData.error || 'Error al añadir al carrito'}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al añadir al carrito', 'error');
        }
    }

    // Función para toggle favoritos - Versión corregida con mensaje de login
    function toggleWishlist(productId) {
        const wishlistBtn = document.getElementById('wishlistBtn');
        const heartIcon = wishlistBtn.querySelector('i');
        
        // Hacer petición AJAX al servidor
        fetch('/favorites/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ productId: productId })
        })
        .then(response => {
            if (response.status === 401) {
                // Mostrar mensaje de que debe iniciar sesión
                showNotification('Debes iniciar sesión para guardar favoritos', 'info');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                if (data.status === 'added') {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    wishlistBtn.classList.add('active');
                    showNotification('Añadido a favoritos', 'success');
                } else {
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                    wishlistBtn.classList.remove('active');
                    showNotification('Eliminado de favoritos', 'info');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar la solicitud', 'error');
        });
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        // Eliminar notificaciones existentes
        const existingNotification = document.querySelector('.product-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Crear nueva notificación
        const notification = document.createElement('div');
        notification.className = 'product-notification';
        
        // Color según tipo
        const colors = {
            success: '#000',
            error: '#cc0000',
            info: '#927a1b'
        };
        
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: ${colors[type] || colors.success};
            color: white;
            padding: 15px 25px;
            border-radius: 0px;
            z-index: 9999;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideInUp 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Agregar estilos de animación si no existen
        if (!document.querySelector('style[data-notification-styles]')) {
            const style = document.createElement('style');
            style.setAttribute('data-notification-styles', 'true');
            style.textContent = `
                @keyframes slideInUp {
                    from {
                        transform: translateY(100px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutDown {
                    from {
                        transform: translateY(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateY(100px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Eliminar después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }

    // Función para cargar estado inicial de favoritos
    function loadFavoriteStatus() {
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
        // Cargar estado de favoritos
        loadFavoriteStatus();

        // Inicializar botones de cantidad
        if (maxStock > 0) {
            document.getElementById('decrementQty').disabled = true;
        }

        // Agregar event listener al botón de favoritos
        const wishlistBtn = document.getElementById('wishlistBtn');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleWishlist({{ $product->idproduct }});
            });
        }
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