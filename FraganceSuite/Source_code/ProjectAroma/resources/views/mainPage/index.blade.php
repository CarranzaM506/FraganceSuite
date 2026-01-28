@extends('layouts.app')

@section('content')
<!-- Slider Minimalista -->
@if($sliderProducts->isNotEmpty())
<section class="slider-container">
    <button class="slider-arrow arrow-left" id="prevBtn">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <div class="slider" id="slider">
        @foreach($sliderProducts as $index => $item)
        <div class="slide slide-{{ $index + 1 }}">
            @if(isset($item->image_url))
                <img src="{{ asset($item->image_url) }}" alt="{{ $item->title ?? 'Slider image' }}" class="slide-image">
            @elseif(isset($item->pathimg))
                <img src="{{ $item->pathimg }}" alt="{{ $item->name }}" class="slide-image">
            @endif
            
            @if(isset($item->title) && $item->title)
                <h1 class="slide-title">{{ strtoupper($item->title) }}</h1>
            @elseif(isset($item->name))
                <h1 class="slide-title">{{ strtoupper($item->name) }}</h1>
            @endif
        </div>
        @endforeach
    </div>
    
    <button class="slider-arrow arrow-right" id="nextBtn">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <div class="slider-nav" id="sliderNav">
        @foreach($sliderProducts as $index => $item)
        <div class="nav-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></div>
        @endforeach
    </div>
</section>
@endif

<!-- Productos para Mujer -->
<section class="store-section">
    <h2 class="section-title">PARA MUJER</h2>
    <div class="product-grid">
        @foreach($productsForWomen as $product)
        <div class="product-card">
            <div class="product-image">
                @if($product->pathimg)
                    <img src="{{ $product->pathimg }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fas fa-wine-bottle"></i>
                @endif
                <div class="product-hover">
                    <span class="wishlist-icon" data-product="{{ $product->idproduct }}"><i class="far fa-heart"></i></span>
                    <span class="add-cart-icon" data-product="{{ $product->idproduct }}"><i class="fas fa-plus"></i></span>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name">{{ $product->name }}</h3>
                <p class="product-brand">{{ $product->brand }}</p>
                <p class="product-category" style="display: none;">{{ $product->category }}</p>
                <p class="product-price">₡{{ number_format($product->price, 2) }}</p>
                
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Productos para Hombre -->
<section class="store-section">
    <h2 class="section-title">PARA HOMBRE</h2>
    <div class="product-grid">
        @foreach($productsForMen as $product)
        <div class="product-card">
            <div class="product-image">
                @if($product->pathimg)
                    <img src="{{ $product->pathimg }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fas fa-wine-bottle"></i>
                @endif
                <div class="product-hover">
                    <span class="wishlist-icon" data-product="{{ $product->idproduct }}"><i class="far fa-heart"></i></span>
                    <span class="add-cart-icon" data-product="{{ $product->idproduct }}"><i class="fas fa-plus"></i></span>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name">{{ $product->name }}</h3>
                <p class="product-brand">{{ $product->brand }}</p>
                <p class="product-category" style="display: none;">{{ $product->category }}</p>
                <p class="product-price">₡{{ number_format($product->price, 2) }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Promoción Activa -->
@if($activePromotion && $promotionProduct)
<section class="split-promo">
    <div class="split-content">
        <h2 class="promo-title">{{ strtoupper($promotionProduct->name) }}</h2>
        <p class="promo-subtitle">{{ $activePromotion->condition }}</p>
        <p class="promo-description">
            {{ $activePromotion->value }}% de descuento. 
            Válido hasta {{ \Carbon\Carbon::parse($activePromotion->enddate)->format('d/m/Y') }}
        </p>
        
        <div class="promo-price">
            @php
                $oldPrice = $promotionProduct->price;
                $newPrice = $oldPrice * (1 - ($activePromotion->value / 100));
            @endphp
            <span class="old-price">₡{{ number_format($oldPrice, 2) }}</span>
            <span class="new-price">₡{{ number_format($newPrice, 2) }}</span>
            <span class="discount">{{ $activePromotion->value }}% OFF</span>
        </div>
        
        <button class="promo-button" onclick="window.location.href='{{ route('product.show', $promotionProduct->idproduct) }}'">
            VER PRODUCTO
        </button>
    </div>
    <div class="split-image">
        @if($promotionProduct->pathimg)
            <img src="{{ $promotionProduct->pathimg }}" alt="{{ $promotionProduct->name }}">
        @else
            <div class="image-placeholder" style="width: 100%; height: 100%; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-wine-bottle" style="font-size: 80px; color: #927a1b;"></i>
            </div>
        @endif
        <div class="image-overlay"></div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
    // JavaScript para interactividad básica
    document.addEventListener('DOMContentLoaded', function() {
        // Efecto de hover en tarjetas de producto
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.12)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 3px 10px rgba(0,0,0,0.08)';
            });
        });
        
        // Simulación de añadir al carrito
        
        const cartIcons = document.querySelectorAll('.fa-shopping-cart');
        cartIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                alert('Producto añadido al carrito');
            });
        });
        



        // Añadir a favoritos
        const heartIcons = document.querySelectorAll('.fa-heart');
        heartIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                this.classList.toggle('fas');
                this.classList.toggle('far');
                if (window.cartManager) {
                    cartManager.showNotification('Producto añadido a favoritos');
                }
            });
        });
    });
</script>
<script>
// Slider automático con flechas
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.nav-dot');
    const prevBtn = document.querySelector('.arrow-left');
    const nextBtn = document.querySelector('.arrow-right');
    
    let currentSlide = 0;
    const totalSlides = slides.length; 
    
    // Validar que haya slides
    if (totalSlides === 0) {
        console.error('No se encontraron slides');
        return;
    }
    
    // Configurar el ancho del slider dinámicamente
    slider.style.width = `${totalSlides * 100}%`;
    
    // Cambiar slide con validación de límites
    function goToSlide(n) {
        // Validar que esté dentro de los límites
        if (n < 0 || n >= totalSlides) {
            console.warn(`Índice de slide fuera de rango: ${n}`);
            return;
        }
        
        currentSlide = n;
        const translateX = -(currentSlide * 100);
        slider.style.transform = `translateX(${translateX}%)`;
        
        // Actualizar dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
        
        // Actualizar estado de flechas
        updateArrowButtons();
    }
    
    // Actualizar estado de flechas (deshabilitar en extremos)
    function updateArrowButtons() {
        if (prevBtn) {
            prevBtn.classList.toggle('disabled', currentSlide === 0);
        }
        
        if (nextBtn) {
            nextBtn.classList.toggle('disabled', currentSlide === totalSlides - 1);
        }
    }
    
    // Navegación por dots
    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            const slideIndex = this.getAttribute('data-slide') || 
                              Array.from(dots).indexOf(this);
            goToSlide(parseInt(slideIndex));
        });
    });
    
    // Flecha anterior con validación
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentSlide > 0) {
                currentSlide--;
                goToSlide(currentSlide);
            }
        });
    }
    
    // Flecha siguiente con validación
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (currentSlide < totalSlides - 1) {
                currentSlide++;
                goToSlide(currentSlide);
            }
        });
    }
    
    // Slider automático (cada 5 segundos) - solo si hay más de 1 slide
    let slideInterval;
    
    function startAutoSlide() {
        if (totalSlides > 1) {
            slideInterval = setInterval(() => {
                // Si está en el último slide, volver al primero
                if (currentSlide === totalSlides - 1) {
                    currentSlide = 0;
                } else {
                    currentSlide++;
                }
                goToSlide(currentSlide);
            }, 5000);
        }
    }
    
    function stopAutoSlide() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
    }
    
    // Iniciar slider automático
    startAutoSlide();
    
    // Pausar slider al pasar el mouse
    const sliderContainer = document.querySelector('.slider-container');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', stopAutoSlide);
        sliderContainer.addEventListener('mouseleave', startAutoSlide);
    }
    
    // También pausar al interactuar con controles
    if (prevBtn) prevBtn.addEventListener('mouseenter', stopAutoSlide);
    if (nextBtn) nextBtn.addEventListener('mouseenter', stopAutoSlide);
    dots.forEach(dot => dot.addEventListener('mouseenter', stopAutoSlide));
    
    if (prevBtn) prevBtn.addEventListener('mouseleave', startAutoSlide);
    if (nextBtn) nextBtn.addEventListener('mouseleave', startAutoSlide);
    dots.forEach(dot => dot.addEventListener('mouseleave', startAutoSlide));
    
    // Actualizar estado inicial de flechas
    updateArrowButtons();
    
    // Botones VER MÁS
    const verMasButtons = document.querySelectorAll('.slide-button');
    verMasButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Redirigir o mostrar contenido según el slide actual
            switch(currentSlide) {
                case 0:
                    window.location.href = '#coleccion-1';
                    break;
                case 1:
                    window.location.href = '#coleccion-2';
                    break;
                case 2:
                    window.location.href = '#coleccion-3';
                    break;
                default:
                    alert('Explorando colección...');
            }
        });
    });
    
    // Manejar cambios de tamaño de ventana
    window.addEventListener('resize', function() {
        // Recalcular la transformación al redimensionar
        const translateX = -(currentSlide * 100);
        slider.style.transform = `translateX(${translateX}%)`;
    });
});
</script>
<script>
// Animación de productos al hacer scroll
document.addEventListener('DOMContentLoaded', function() {
    const storeSections = document.querySelectorAll('.store-section');
    
    // Función para verificar si un elemento es visible
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.85 &&
            rect.bottom >= 0
        );
    }
    
    // Función para mostrar elementos cuando son visibles
    function checkScroll() {
        storeSections.forEach(section => {
            if (isElementInViewport(section)) {
                section.classList.add('visible');
                
                // Mostrar productos individualmente con delay
                const cards = section.querySelectorAll('.product-card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, index * 100); // Delay escalonado
                });
            }
        });
    }
    
    // Ejecutar al cargar y al hacer scroll
    checkScroll();
    window.addEventListener('scroll', checkScroll);
    
    // Interacción con iconos de productos
    const wishlistIcons = document.querySelectorAll('.wishlist-icon');
    wishlistIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.stopPropagation();
            const heartIcon = this.querySelector('i');
            heartIcon.classList.toggle('far');
            heartIcon.classList.toggle('fas');
            this.classList.toggle('active');
        });
    });
});
</script>
<script>
// Efecto de header sticky que se esconde al hacer scroll
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    let lastScrollTop = 0;
    const headerHeight = header.offsetHeight;
    
    // Ajusta el padding del body dinámicamente
    document.body.style.paddingTop = headerHeight + 'px';
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Si el scroll es menor a 100px, siempre mostrar header
        if (scrollTop < 100) {
            header.classList.remove('hidden');
            header.classList.add('visible');
            return;
        }
        
        // Si se está haciendo scroll hacia abajo
        if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
            // Scroll down - esconder header
            header.classList.remove('visible');
            header.classList.add('hidden');
        } 
        // Si se está haciendo scroll hacia arriba
        else if (scrollTop < lastScrollTop) {
            // Scroll up - mostrar header
            header.classList.remove('hidden');
            header.classList.add('visible');
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Ajustar padding en resize
    window.addEventListener('resize', function() {
        document.body.style.paddingTop = header.offsetHeight + 'px';
    });
});

// Script para animación del split al hacer scroll
document.addEventListener('DOMContentLoaded', function() {
    const splitPromo = document.querySelector('.split-promo');
    
    if (splitPromo) {
        function checkSplitScroll() {
            const rect = splitPromo.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            // Cuando el elemento está en el 85% de la pantalla
            if (rect.top < windowHeight * 0.85) {
                splitPromo.classList.add('visible');
                // Opcional: remover el listener después de activar
                // window.removeEventListener('scroll', checkSplitScroll);
            }
        }
        
        // Verificar al cargar
        setTimeout(checkSplitScroll, 100);
        
        // Verificar al hacer scroll
        window.addEventListener('scroll', checkSplitScroll);
    }
});
</script>
@endpush