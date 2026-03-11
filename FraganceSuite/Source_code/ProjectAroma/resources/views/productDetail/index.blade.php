@extends('layouts.app')

@section('styles')
    @parent
    <style>
        :root {
            --gold: #927a1b;
            --gold-light: #b89e3a;
            --gold-dark: #6d5a14;
            --dark: #1a1a1a;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #ffffff;
            color: #333;
        }

        /* Breadcrumb */
        .breadcrumb {
            padding: 20px 5%;
            background: var(--light-gray);
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--gold);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #666;
        }

        /* Product Detail Container */
        .product-detail-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            padding: 40px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Product Gallery */
        .product-gallery {
            position: relative;
        }

        .main-image {
            width: 100%;
            aspect-ratio: 1;
            background: var(--light-gray);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .main-image:hover img {
            transform: scale(1.05);
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--medium-gray) 100%);
        }

        .image-placeholder i {
            font-size: 80px;
            color: var(--gold);
        }

        .discount-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--gold);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 18px;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(146, 122, 27, 0.3);
        }

        /* Product Info */
        .product-info {
            padding: 20px 0;
        }

        .product-category {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            margin-bottom: 10px;
        }

        .product-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
            line-height: 1.2;
        }

        .product-brand {
            font-size: 20px;
            color: #666;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .product-price-section {
            margin: 30px 0;
            padding: 25px 0;
            border-top: 2px solid var(--medium-gray);
            border-bottom: 2px solid var(--medium-gray);
        }

        .price-container {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .current-price {
            font-size: 42px;
            font-weight: 700;
            color: var(--gold);
        }

        .old-price {
            font-size: 24px;
            color: #999;
            text-decoration: line-through;
        }

        .discount-info {
            background: #ff4444;
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 16px;
        }

        .stock-info {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .in-stock {
            color: #00a650;
            font-weight: 600;
            font-size: 16px;
        }

        .out-of-stock {
            color: #ff4444;
            font-weight: 600;
            font-size: 16px;
        }

        .stock-badge {
            background: var(--medium-gray);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }

        /* Product Actions */
        .product-actions {
            margin: 30px 0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-primary {
            flex: 1;
            min-width: 200px;
            padding: 15px 30px;
            background: var(--gold);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(146, 122, 27, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            padding: 15px 25px;
            background: white;
            color: var(--gold);
            border: 2px solid var(--gold);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background: var(--gold);
            color: white;
            transform: translateY(-2px);
        }

        .btn-secondary.active {
            background: var(--gold);
            color: white;
        }

        .btn-secondary.active i {
            color: white;
        }

        /* Quantity Selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }

        .quantity-label {
            font-weight: 600;
            color: #666;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid var(--medium-gray);
            border-radius: 10px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            background: white;
            border: none;
            font-size: 18px;
            font-weight: 600;
            color: var(--gold);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--gold);
            color: white;
        }

        .quantity-btn:disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .quantity-btn:disabled:hover {
            background: white;
            color: #ccc;
        }

        .quantity-input {
            width: 60px;
            height: 40px;
            border: none;
            border-left: 2px solid var(--medium-gray);
            border-right: 2px solid var(--medium-gray);
            text-align: center;
            font-weight: 600;
            font-size: 16px;
        }

        .quantity-input:focus {
            outline: none;
        }

        /* Product Description */
        .product-description {
            margin-top: 40px;
        }

        .description-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }

        .description-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--gold);
        }

        .description-content {
            line-height: 1.8;
            color: #666;
        }

        .short-description {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: var(--gold);
        }

        /* Related Products */
        .related-products {
            padding: 60px 5%;
            background: var(--light-gray);
        }

        .related-title {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 40px;
            color: var(--dark);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .related-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .related-image {
            aspect-ratio: 1;
            overflow: hidden;
        }

        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .related-card:hover .related-image img {
            transform: scale(1.1);
        }

        .related-info {
            padding: 20px;
        }

        .related-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .related-price {
            color: var(--gold);
            font-weight: 700;
            font-size: 18px;
        }

        .related-old-price {
            color: #999;
            text-decoration: line-through;
            font-size: 14px;
            margin-left: 10px;
        }

        /* Notifications */
        .notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--dark);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .product-detail-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .product-title {
                font-size: 28px;
            }

            .current-price {
                font-size: 32px;
            }
        }

        @media (max-width: 480px) {
            .product-actions {
                flex-direction: column;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
            }

            .price-container {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
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
                        <i class="fas fa-shopping-cart"></i>
                        Añadir al Carrito
                    </button>
                @else
                    <button class="btn-primary" disabled>
                        <i class="fas fa-times-circle"></i>
                        Producto Agotado
                    </button>
                @endif
                
                <button class="btn-secondary" id="wishlistBtn" onclick="toggleWishlist({{ $product->idproduct }})">
                    <i class="far fa-heart"></i>
                    Favoritos
                </button>
            </div>

            <!-- Product Description -->
            @if($product->shortDescription || $product->description)
                <div class="product-description">
                    <h2 class="description-title">Descripción del Producto</h2>
                    <div class="description-content">
                        @if($product->shortDescription)
                            <p class="short-description">{{ $product->shortDescription }}</p>
                        @endif
                        
                        @if($product->description)
                            <p>{{ $product->description }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Additional Info -->
            @if($product->decant)
                <div style="margin-top: 20px; padding: 15px; background: var(--light-gray); border-radius: 10px;">
                    <i class="fas fa-flask" style="color: var(--gold); margin-right: 10px;"></i>
                    <span>Este producto es un decant (fracción)</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <section class="related-products">
            <h2 class="related-title">Productos Relacionados</h2>
            <div class="related-grid">
                @foreach($relatedProducts as $related)
                    @php
                        $relatedDiscountedPrice = null;
                        if ($related->discount && $related->discount->active) {
                            $relatedDiscountedPrice = $related->price * (1 - ($related->discount->value / 100));
                        }
                    @endphp
                    
                    <a href="{{ route('product.show', $related->idproduct) }}" class="related-card">
                        <div class="related-image">
                            @if($related->pathimg)
                                <img src="{{ $related->pathimg }}" alt="{{ $related->name }}">
                            @else
                                <div class="image-placeholder" style="height: 100%;">
                                    <i class="fas fa-wine-bottle"></i>
                                </div>
                            @endif
                        </div>
                        <div class="related-info">
                            <div class="related-name">{{ $related->name }}</div>
                            <div>
                                @if($relatedDiscountedPrice)
                                    <span class="related-price">₡{{ number_format($relatedDiscountedPrice, 2) }}</span>
                                    <span class="related-old-price">₡{{ number_format($related->price, 2) }}</span>
                                @else
                                    <span class="related-price">₡{{ number_format($related->price, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
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
</script>
@endpush