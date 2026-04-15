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
            @if ($discountedPrice)
                <div class="discount-badge">
                    -{{ $product->discount->value }}%
                </div>
            @endif

            <div class="main-image">
                @if ($product->pathimg)
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
                    @if ($discountedPrice)
                        <span class="current-price">₡{{ number_format($discountedPrice, 2) }}</span>
                        <span class="old-price">₡{{ number_format($product->price, 2) }}</span>
                        <span class="discount-info">{{ $product->discount->value }}% OFF</span>
                    @else
                        <span class="current-price">₡{{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <div class="stock-info">
                    @if ($product->stock > 0)
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
            @if ($product->stock > 0)
                <div class="quantity-selector">
                    <span class="quantity-label">Cantidad:</span>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" id="decrementQty"
                            onclick="updateQuantity(-1)">−</button>
                        <input type="number" class="quantity-input" id="quantity" value="1" min="1"
                            max="{{ $product->stock }}" readonly>
                        <button type="button" class="quantity-btn" id="incrementQty" onclick="updateQuantity(1)">+</button>
                    </div>
                </div>
            @endif

            <!-- Product Actions -->
            <div class="product-actions">
                @if ($product->stock > 0)
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

    <!-- Reviews Section -->
    <section class="reviews-section">
        <div class="reviews-header">
            <h2 class="reviews-title">Reseñas</h2>
            <p class="reviews-subtitle">Contanos tu experiencia con este producto.</p>
        </div>

        <button class="btn-secondary review-toggle" type="button" id="toggleReviewForm">
            Agregar reseña
        </button>

        <form class="review-form is-hidden" id="reviewForm" onsubmit="return false;">
            <div class="review-field">
                <label class="review-label">Calificación</label>
                <div class="star-rating" id="ratingStars" data-max="5" aria-label="Calificación">
                    <input type="hidden" id="rating" name="rating" required>
                    <span class="star" data-star="1" aria-hidden="true"></span>
                    <span class="star" data-star="2" aria-hidden="true"></span>
                    <span class="star" data-star="3" aria-hidden="true"></span>
                    <span class="star" data-star="4" aria-hidden="true"></span>
                    <span class="star" data-star="5" aria-hidden="true"></span>
                    <div class="rating-tooltip" id="ratingTooltip" role="status" aria-live="polite"></div>
                </div>
            </div>

            <div class="review-field">
                <label for="comment" class="review-label">Comentario (opcional)</label>
                <textarea id="comment" name="comment" class="review-input" rows="4" maxlength="250"
                    placeholder="Máximo 250 caracteres"></textarea>
                <div class="review-hint"><span id="commentRemaining">250</span> caracteres disponibles</div>
            </div>

            <button class="btn-primary review-submit" type="submit">Enviar reseña</button>
        </form>

        <div id="reviewsList" class="reviews-list"></div>

    </section>
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
                    body: JSON.stringify({
                        productId,
                        quantity: currentQuantity
                    })
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
                        window.favoritesSystem.showNotification(
                            `Error: ${errorData.error || 'Error al añadir al carrito'}`, 'error');
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
            const notify = (message, type = 'success') => {
                if (window.favoritesSystem && typeof window.favoritesSystem.showNotification === 'function') {
                    window.favoritesSystem.showNotification(message, type);
                    return;
                }
                if (window.cartManager && typeof window.cartManager.showNotification === 'function') {
                    window.cartManager.showNotification(message, type);
                    return;
                }
                console.warn(message);
            };

            if (maxStock > 0) {
                document.getElementById('decrementQty').disabled = true;
            }

            loadFavoriteStatus();
            loadReviews();

            const toggleBtn = document.getElementById('toggleReviewForm');
            const reviewForm = document.getElementById('reviewForm');
            const commentInput = document.getElementById('comment');
            const remainingEl = document.getElementById('commentRemaining');

            if (toggleBtn && reviewForm) {
                toggleBtn.addEventListener('click', () => {
                    reviewForm.classList.toggle('is-hidden');
                    toggleBtn.textContent = reviewForm.classList.contains('is-hidden') ?
                        'Agregar reseña' :
                        'Ocultar reseña';
                });
            }

            if (commentInput && remainingEl) {
                const updateRemaining = () => {
                    const max = Number(commentInput.getAttribute('maxlength')) || 250;
                    const remaining = Math.max(0, max - commentInput.value.length);
                    remainingEl.textContent = remaining;
                };
                commentInput.addEventListener('input', updateRemaining);
                updateRemaining();
            }

            const ratingWrap = document.getElementById('ratingStars');
            const ratingInput = document.getElementById('rating');
            if (ratingWrap && ratingInput) {
                const stars = Array.from(ratingWrap.querySelectorAll('.star'));
                const tooltip = document.getElementById('ratingTooltip');
                let selectedValue = 0;
                let tooltipTimeout = null;

                const formatValue = (value) => value.toFixed(1).replace(/\.0$/, '');

                const renderStars = (value) => {
                    const full = Math.floor(value);
                    const hasHalf = value - full >= 0.5;
                    stars.forEach((star, idx) => {
                        const starIndex = idx + 1;
                        star.classList.remove('full', 'half');
                        if (starIndex <= full) {
                            star.classList.add('full');
                        } else if (starIndex === full + 1 && hasHalf) {
                            star.classList.add('half');
                        }
                    });
                };

                const findStarByX = (clientX) => {
                    const rects = stars.map(star => ({
                        star,
                        rect: star.getBoundingClientRect()
                    }));
                    for (const item of rects) {
                        if (clientX >= item.rect.left && clientX <= item.rect.right) {
                            return item;
                        }
                    }
                    let closest = rects[0];
                    let minDist = Infinity;
                    rects.forEach(item => {
                        const center = item.rect.left + item.rect.width / 2;
                        const dist = Math.abs(clientX - center);
                        if (dist < minDist) {
                            minDist = dist;
                            closest = item;
                        }
                    });
                    return closest;
                };

                const valueFromX = (clientX) => {
                    const item = findStarByX(clientX);
                    if (!item) return null;
                    const {
                        star,
                        rect
                    } = item;
                    const starIndex = Number(star.dataset.star) || 1;
                    const mid = rect.left + rect.width * 0.5;
                    const isLeftHalf = clientX <= mid;
                    return starIndex - (isLeftHalf ? 0.5 : 0);
                };

                const showTooltip = (value) => {
                    if (!tooltip) return;
                    tooltip.textContent = `${formatValue(value)}/5`;
                    tooltip.classList.add('is-visible');
                    if (tooltipTimeout) clearTimeout(tooltipTimeout);
                };

                const hideTooltip = () => {
                    if (!tooltip) return;
                    tooltip.classList.remove('is-visible');
                };

                ratingWrap.addEventListener('mousemove', (event) => {
                    const value = valueFromX(event.clientX);
                    if (!value) return;
                    renderStars(value);
                    showTooltip(value);
                });

                ratingWrap.addEventListener('mouseleave', () => {
                    renderStars(selectedValue);
                    hideTooltip();
                });

                ratingWrap.addEventListener('click', (event) => {
                    const value = valueFromX(event.clientX);
                    if (!value) return;
                    selectedValue = value;
                    ratingInput.value = formatValue(value);
                    renderStars(selectedValue);
                    showTooltip(selectedValue);
                    tooltipTimeout = setTimeout(() => hideTooltip(), 1200);
                });

                ratingWrap.addEventListener('touchstart', (event) => {
                    const touch = event.touches[0];
                    if (!touch) return;
                    const value = valueFromX(touch.clientX);
                    if (!value) return;
                    renderStars(value);
                    showTooltip(value);
                }, {
                    passive: true
                });

                ratingWrap.addEventListener('touchmove', (event) => {
                    const touch = event.touches[0];
                    if (!touch) return;
                    const value = valueFromX(touch.clientX);
                    if (!value) return;
                    renderStars(value);
                    showTooltip(value);
                }, {
                    passive: true
                });

                ratingWrap.addEventListener('touchend', (event) => {
                    const touch = event.changedTouches[0];
                    if (!touch) return;
                    const value = valueFromX(touch.clientX);
                    if (!value) return;
                    selectedValue = value;
                    ratingInput.value = formatValue(value);
                    renderStars(selectedValue);
                    showTooltip(selectedValue);
                    tooltipTimeout = setTimeout(() => hideTooltip(), 1200);
                });

                if (reviewForm) {
                    reviewForm.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        const rating = ratingInput.value;
                        if (!rating) {
                            notify('Seleccioná una calificación', 'error');
                            return;
                        }

                        const commentValue = commentInput ? commentInput.value.trim() : '';
                        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfTokenElement) {
                            notify('Error: CSRF token no encontrado', 'error');
                            return;
                        }

                        try {
                            const response = await fetch('/api/reviews', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
                                },
                                body: JSON.stringify({
                                    productId: {{ $product->idproduct }},
                                    rating,
                                    comment: commentValue || null
                                })
                            });

                            if (response.redirected || response.status === 401) {
                                window.location.href = response.url || '/login';
                                return;
                            }

                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({}));
                                const msg = errorData.error || 'Error al guardar la reseña';
                                notify(msg, 'error');
                                return;
                            }

                            notify('Reseña guardada', 'success');

                            reviewForm.reset();
                            selectedValue = 0;
                            ratingInput.value = '';
                            renderStars(0);
                            if (commentInput && remainingEl) {
                                remainingEl.textContent = commentInput.getAttribute('maxlength') ||
                                    '250';
                            }
                            hideTooltip();
                            reviewForm.classList.add('is-hidden');
                            if (toggleBtn) {
                                toggleBtn.textContent = 'Agregar reseña';
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            notify('Error al guardar la reseña', 'error');
                        }
                    });
                }
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

        async function loadReviews() {
            try {
                const response = await fetch(`/api/reviews/{{ $product->idproduct }}`);
                const reviews = await response.json();

                const container = document.getElementById('reviewsList');
                container.innerHTML = '';

                if (reviews.length === 0) {
                    container.innerHTML = `
                        <div class="no-reviews">
                            <p class="no-reviews-title">Aún no hay reseñas</p>
                            <p class="no-reviews-text">
                                Sé el primero en compartir tu experiencia con este producto.
                            </p>
                        </div>
                    `;
                    return;
                }

                reviews.forEach(review => {
                    const stars = generateStars(review.rating);
                    const formattedDate = formatDate(review.created_at);

                    const reviewHTML = `
                <div class="review-item">
                    <div class="review-header">
                        <div class="review-user">
                            <strong>${review.user?.name ?? 'Usuario'}</strong>
                            <span class="review-date">${formattedDate}</span>
                        </div>
                        <span class="review-stars">${stars}</span>
                    </div>
                    ${review.comment ? `<p class="review-comment">${review.comment}</p>` : ''}
                </div>
            `;

                    container.innerHTML += reviewHTML;
                });

            } catch (error) {
                console.error('Error cargando reseñas:', error);
            }
        }

        function generateStars(rating) {
            let html = '';
            const full = Math.floor(rating);
            const half = rating % 1 !== 0;

            for (let i = 1; i <= 5; i++) {
                if (i <= full) {
                    html += '<span class="star full"></span>';
                } else if (i === full + 1 && half) {
                    html += '<span class="star half"></span>';
                } else {
                    html += '<span class="star"></span>';
                }
            }

            return html;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            return `${day}/${month}/${year}`;
        }
    </script>
@endpush
