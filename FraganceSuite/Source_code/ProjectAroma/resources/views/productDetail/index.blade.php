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
                
                <button class="btn-secondary" id="wishlistBtn" onclick="toggleWishlist({{ $product->idproduct }})">
                    Favoritos
                </button>
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
    function addToCart(productId) {
        // Aquí implementarías la lógica para añadir al carrito
        // Por ahora mostraremos una notificación
        showNotification('Producto añadido al carrito', 'success');
        
        // Aquí puedes hacer una petición AJAX para añadir al carrito
        console.log('Añadiendo al carrito:', productId, 'Cantidad:', currentQuantity);
        
        // Ejemplo de petición fetch:
        /*
        fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: currentQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Producto añadido al carrito', 'success');
                // Actualizar contador del carrito
                updateCartCount();
            }
        });
        */
    }

    // Función para toggle favoritos
    function toggleWishlist(productId) {
        const wishlistBtn = document.getElementById('wishlistBtn');
        const heartIcon = wishlistBtn.querySelector('i');
        
        heartIcon.classList.toggle('far');
        heartIcon.classList.toggle('fas');
        wishlistBtn.classList.toggle('active');
        
        if (heartIcon.classList.contains('fas')) {
            showNotification('Añadido a favoritos', 'success');
        } else {
            showNotification('Eliminado de favoritos', 'info');
        }
        
        // Aquí puedes hacer una petición AJAX para guardar en favoritos
        console.log('Toggle wishlist:', productId);
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        // Eliminar notificaciones existentes
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Crear nueva notificación
        const notification = document.createElement('div');
        notification.className = 'notification';
        
        // Color según tipo
        const colors = {
            success: '#1a1a1a',
            error: '#ff4444',
            info: '#927a1b'
        };
        
        notification.style.backgroundColor = colors[type] || colors.success;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Eliminar después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }

    // Función para actualizar contador del carrito (si existe)
    function updateCartCount() {
        // Aquí implementarías la lógica para actualizar el contador del carrito en el header
        console.log('Actualizando contador del carrito');
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar si el producto está en favoritos
        // Esto sería una petición para verificar el estado
        const wishlistBtn = document.getElementById('wishlistBtn');
        if (wishlistBtn) {
            // Ejemplo: verificar si está en favoritos
            const isInWishlist = false; // Esto vendría de una API
            if (isInWishlist) {
                const heartIcon = wishlistBtn.querySelector('i');
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
                wishlistBtn.classList.add('active');
            }
        }

        // Inicializar botones de cantidad
        if (maxStock > 0) {
            document.getElementById('decrementQty').disabled = true;
        }
    });

    // Manejar zoom de imagen (opcional)
    const mainImage = document.getElementById('mainProductImage');
    if (mainImage) {
        mainImage.addEventListener('mousemove', function(e) {
            // Aquí puedes implementar efecto zoom
        });
    }

    // Efecto de scroll en la imagen
window.addEventListener('scroll', function() {
    const mainImage = document.querySelector('.main-image img');
    if (mainImage) {
        const scrollPosition = window.scrollY;
        const scale = 1 + (scrollPosition * 0.0005); // Efecto muy sutil
        mainImage.style.transform = `scale(${Math.min(scale, 1.03)})`;
    }
});
</script>
@endpush