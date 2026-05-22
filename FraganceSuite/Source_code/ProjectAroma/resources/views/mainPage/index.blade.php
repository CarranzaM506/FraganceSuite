@extends('layouts.app')

@section('content')
<!-- HERO ESTÁTICO - SOLO IMAGEN -->
@if(isset($heroImage) && $heroImage->image)
<section class="hero-static">
    <div class="hero-image-wrapper">
        <img src="{{ asset('img-hero/' . $heroImage->image) }}"
             alt="Hero AROMA"
             class="hero-image">
    </div>
</section>
@endif

<!-- Productos para Mujer -->
<section class="store-section">
    <h2 class="section-title">PARA MUJER</h2>
    <div class="product-grid">
        @foreach($productsForWomen as $product)
        <div class="product-card">
            <a href="{{ route('product.show', $product->idproduct) }}" style="text-decoration: none; color: inherit; display: block; height: 100%; width: 100%;">
                <div class="product-image">
                    @if($product->pathimg)
                        <img src="{{ $product->pathimg }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="fas fa-wine-bottle"></i>
                    @endif
                    <div class="product-hover" onclick="event.stopPropagation();">
                        <span class="wishlist-icon" data-product="{{ $product->idproduct }}">
                            <i class="far fa-heart"></i>
                        </span>
                        <span class="add-cart-icon" data-product="{{ $product->idproduct }}"><i class="fas fa-plus"></i></span>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">{{ $product->name }}</h3>
                    <p class="product-brand">{{ $product->brand }}</p>
                    <p class="product-category" style="display: none;">{{ $product->category }}</p>
                    @if($product->discount)
                        @php $discountedPrice = $product->price * (1 - ($product->discount->value / 100)); @endphp
                        <div class="product-price-block">
                            <span class="old-price">₡{{ number_format($product->price, 2) }}</span>
                            <span class="new-price">₡{{ number_format($discountedPrice, 2) }}</span>
                            <span class="discount">{{ $product->discount->value }}% OFF</span>
                        </div>
                    @else
                        <p class="product-price">₡{{ number_format($product->price, 2) }}</p>
                    @endif
                </div>
            </a>
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
            <a href="{{ route('product.show', $product->idproduct) }}" style="text-decoration: none; color: inherit; display: block; height: 100%; width: 100%;">
                <div class="product-image">
                    @if($product->pathimg)
                        <img src="{{ $product->pathimg }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="fas fa-wine-bottle"></i>
                    @endif
                    <div class="product-hover" onclick="event.stopPropagation();">
                        <span class="wishlist-icon" data-product="{{ $product->idproduct }}">
                            <i class="far fa-heart"></i>  
                        </span>     
                        <span class="add-cart-icon" data-product="{{ $product->idproduct }}"><i class="fas fa-plus"></i></span>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">{{ $product->name }}</h3>
                    <p class="product-brand">{{ $product->brand }}</p>
                    <p class="product-category" style="display: none;">{{ $product->category }}</p>
                    @if($product->discount)
                        @php $discountedPrice = $product->price * (1 - ($product->discount->value / 100)); @endphp
                        <div class="product-price-block">
                            <span class="old-price">₡{{ number_format($product->price, 2) }}</span>
                            <span class="new-price">₡{{ number_format($discountedPrice, 2) }}</span>
                            <span class="discount">{{ $product->discount->value }}% OFF</span>
                        </div>
                    @else
                        <p class="product-price">₡{{ number_format($product->price, 2) }}</p>
                    @endif
                </div>
            </a>
        </div>
        @endforeach
    </div>
</section>




@if(isset($activeBrands) && $activeBrands->count() > 0)
<section class="brands-section">
    <div class="brands-container">
        <!-- Fila 1 - se mueve hacia la IZQUIERDA -->
        <div class="brands-row row-left">
            <div class="brands-track">
                @foreach($activeBrands as $brand)
                <div class="brand-item" 
                     onclick="window.location.href='{{ route('catalog', ['brand' => $brand->brand_name]) }}'"
                     style="cursor: pointer;">
                    <img src="{{ asset('img-brands/' . $brand->logo) }}" alt="{{ $brand->brand_name }}">
                </div>
                @endforeach
                @foreach($activeBrands as $brand)
                <div class="brand-item" 
                     onclick="window.location.href='{{ route('catalog', ['brand' => $brand->brand_name]) }}'"
                     style="cursor: pointer;">
                    <img src="{{ asset('img-brands/' . $brand->logo) }}" alt="{{ $brand->brand_name }}">
                </div>
                @endforeach
            </div>
        </div>

        <!-- Fila 2 - se mueve hacia la DERECHA -->
        <div class="brands-row row-right">
            <div class="brands-track">
                @foreach($activeBrands as $brand)
                <div class="brand-item" 
                     onclick="window.location.href='{{ route('catalog', ['brand' => $brand->brand_name]) }}'"
                     style="cursor: pointer;">
                    <img src="{{ asset('img-brands/' . $brand->logo) }}" alt="{{ $brand->brand_name }}">
                </div>
                @endforeach
                @foreach($activeBrands as $brand)
                <div class="brand-item" 
                     onclick="window.location.href='{{ route('catalog', ['brand' => $brand->brand_name]) }}'"
                     style="cursor: pointer;">
                    <img src="{{ asset('img-brands/' . $brand->logo) }}" alt="{{ $brand->brand_name }}">
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- ========== MÁS VENDIDOS DEL MES ========== -->
@if(isset($bestSellers) && $bestSellers->count() > 0)
<section class="bestsellers-section">
    <div class="bestsellers-header">
        <div class="bestsellers-badge">
            <span class="badge-text">TOP VENTAS</span>
        </div>
        <h2 class="bestsellers-title">MÁS VENDIDOS DEL MES</h2>
    </div>

    <div class="bestsellers-container">
        <div class="bestsellers-grid">
            @foreach($bestSellers as $index => $product)
            <div class="bestseller-card" data-rank="{{ $index + 1 }}">
                <div class="rank-badge">
                    @if($index == 0)
                    @elseif($index == 1)
                    @elseif($index == 2)
                    @else
                        <span class="rank-number">{{ $index + 1 }}</span>
                    @endif
                </div>
                
                <a href="{{ route('product.show', $product->idproduct) }}" class="bestseller-link">
                    <div class="bestseller-image">
                        @if($product->pathimg)
                            <img src="{{ $product->pathimg }}" alt="{{ $product->name }}">
                        @else
                            <div class="image-placeholder">
                                <i class="fas fa-wine-bottle"></i>
                            </div>
                        @endif
                        <div class="bestseller-overlay">
                            <span class="quick-view">VER DETALLES</span>
                        </div>
                    </div>
                    <div class="bestseller-info">
                        <h3 class="bestseller-name">{{ $product->name }}</h3>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ========== SECCIÓN DE VIDEOS ========== -->
@if(isset($activeVideos) && ($activeVideos->count() == 3 || $activeVideos->count() == 4))
<section class="videos-section">
    <h2 class="section-title">VIDEOS</h2>
    
    <!-- Desktop: Grid normal -->
    <div class="videos-grid" data-count="{{ $activeVideos->count() }}">
        @foreach($activeVideos as $video)
        <div class="video-card">
            <div class="video-wrapper">
                <video class="info-video" preload="metadata" playsinline loop muted>
                    <source src="{{ asset('videos/' . $video->route) }}" type="video/mp4">
                </video>
                <div class="video-overlay">
                    <i class="fas fa-volume-up"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Mobile/Tablet: Slider con 2 videos visibles -->
    <div class="videos-slider-mobile">
        <div class="videos-slider-container">
            <div class="videos-slider-track">
                @foreach($activeVideos as $video)
                <div class="video-slide">
                    <div class="video-card">
                        <div class="video-wrapper">
                            <video class="info-video" preload="metadata" playsinline loop muted>
                                <source src="{{ asset('videos/' . $video->route) }}" type="video/mp4">
                            </video>
                            <div class="video-overlay">
                                <i class="fas fa-volume-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <button class="video-arrow video-arrow-left"><i class="fas fa-chevron-left"></i></button>
        <button class="video-arrow video-arrow-right"><i class="fas fa-chevron-right"></i></button>
        <div class="video-dots">
            @foreach($activeVideos as $index => $video)
            <span class="video-dot {{ $index == 0 ? 'active' : '' }}" data-index="{{ $index }}"></span>
            @endforeach
        </div>
    </div>
</section>
@endif
    

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
    document.addEventListener('DOMContentLoaded', function() {
        // ===== EFECTOS HOVER =====
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.12)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 3px 10px rgba(0,0,0,0.08)';
            });
        });

        // Carrito icons
        document.querySelectorAll('.add-cart-icon').forEach(icon => {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                this.classList.add('adding');
                setTimeout(() => {
                    this.classList.remove('adding');
                }, 300);
                
                if (typeof showToast === 'function') {
                    showToast('Producto añadido al carrito');
                } else {
                    console.log('Producto añadido al carrito');
                }
                
                const productId = this.getAttribute('data-product');
                console.log('Añadir al carrito producto:', productId);
                
                return false;
            });
        });
    });

    // ===== SLIDER AUTOMÁTICO CON FLECHAS =====
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.querySelector('.slider');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.nav-dot');
        const prevBtn = document.querySelector('.arrow-left');
        const nextBtn = document.querySelector('.arrow-right');
        
        if (!slider || slides.length === 0) return;
        
        let currentSlide = 0;
        const totalSlides = slides.length; 
        
        slider.style.width = `${totalSlides * 100}%`;
        
        function goToSlide(n) {
            if (n < 0 || n >= totalSlides) return;
            
            currentSlide = n;
            const translateX = -(currentSlide * 100) / totalSlides;
            slider.style.transform = `translateX(${translateX}%)`;
            
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
            
            updateArrowButtons();
        }
        
        function updateArrowButtons() {
            if (prevBtn) {
                prevBtn.classList.toggle('disabled', currentSlide === 0);
            }
            if (nextBtn) {
                nextBtn.classList.toggle('disabled', currentSlide === totalSlides - 1);
            }
        }
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentSlide > 0) goToSlide(currentSlide - 1);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (currentSlide < totalSlides - 1) goToSlide(currentSlide + 1);
            });
        }
        
        let slideInterval;
        
        function startAutoSlide() {
            if (totalSlides > 1) {
                slideInterval = setInterval(() => {
                    if (currentSlide === totalSlides - 1) {
                        goToSlide(0);
                    } else {
                        goToSlide(currentSlide + 1);
                    }
                }, 5000);
            }
        }
        
        function stopAutoSlide() {
            if (slideInterval) clearInterval(slideInterval);
        }
        
        startAutoSlide();
        
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoSlide);
            sliderContainer.addEventListener('mouseleave', startAutoSlide);
        }
    });

    // ===== ANIMACIÓN DE PRODUCTOS AL HACER SCROLL =====
    document.addEventListener('DOMContentLoaded', function() {
        const storeSections = document.querySelectorAll('.store-section');
        
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.85 &&
                rect.bottom >= 0
            );
        }
        
        function checkScroll() {
            storeSections.forEach(section => {
                if (isElementInViewport(section)) {
                    section.classList.add('visible');
                    
                    const cards = section.querySelectorAll('.product-card');
                    cards.forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('visible');
                        }, index * 100);
                    });
                }
            });
        }
        
        checkScroll();
        window.addEventListener('scroll', checkScroll);
    });

    // ===== EFECTO DE HEADER STICKY =====
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('header');
        if (!header) return;
        
        let lastScrollTop = 0;
        const headerHeight = header.offsetHeight;
        
        document.body.style.paddingTop = headerHeight + 'px';
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop < 100) {
                header.classList.remove('hidden');
                header.classList.add('visible');
                return;
            }
            
            if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
                header.classList.remove('visible');
                header.classList.add('hidden');
            } else if (scrollTop < lastScrollTop) {
                header.classList.remove('hidden');
                header.classList.add('visible');
            }
            
            lastScrollTop = scrollTop;
        });
        
        window.addEventListener('resize', function() {
            document.body.style.paddingTop = header.offsetHeight + 'px';
        });
    });

    // ===== ANIMACIÓN DEL SPLIT PROMO =====
    document.addEventListener('DOMContentLoaded', function() {
        const splitPromo = document.querySelector('.split-promo');
        
        if (splitPromo) {
            function checkSplitScroll() {
                const rect = splitPromo.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                
                if (rect.top < windowHeight * 0.85) {
                    splitPromo.classList.add('visible');
                }
            }
            
            setTimeout(checkSplitScroll, 100);
            window.addEventListener('scroll', checkSplitScroll);
        }
    });

    // ===== ANIMACIÓN DE MÁS VENDIDOS AL HACER SCROLL =====
    document.addEventListener('DOMContentLoaded', function() {
        const bestsellerCards = document.querySelectorAll('.bestseller-card');
        
        if (bestsellerCards.length === 0) return;
        
        function checkBestsellerScroll() {
            bestsellerCards.forEach((card, index) => {
                const rect = card.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                
                if (rect.top < windowHeight - 100 && rect.bottom > 0) {
                    setTimeout(() => {
                        card.classList.add('animate-in');
                    }, index * 150);
                }
            });
        }
        
        setTimeout(checkBestsellerScroll, 100);
        window.addEventListener('scroll', checkBestsellerScroll);
    });

// ===== CONTROL DE VIDEOS =====
document.addEventListener('DOMContentLoaded', function() {
    const videoWrappers = document.querySelectorAll('.video-wrapper');
    
    // Iniciar reproducción automática silenciosa
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        if (video) {
            video.muted = true;
            video.play().catch(e => console.log('Autoplay prevented:', e));
        }
    });
    
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const overlay = wrapper.querySelector('.video-overlay');
        
        if (!video || !overlay) return;
        
        const pauseAllOthers = () => {
            videoWrappers.forEach(w => {
                const otherVideo = w.querySelector('video');
                if (otherVideo && otherVideo !== video && !otherVideo.paused) {
                    otherVideo.pause();
                    otherVideo.muted = true;
                    otherVideo.currentTime = 0;
                    otherVideo.play().catch(e => console.log('Error:', e));
                    w.classList.remove('playing');
                }
            });
        };
        
        const toggleSound = (e) => {
            e.stopPropagation();
            pauseAllOthers();
            
            if (video.muted) {
                // Activar sonido y reproducir
                video.muted = false;
                overlay.innerHTML = '<i class="fas fa-pause"></i>';
                wrapper.classList.add('playing');
                video.play();
            } else if (!video.paused) {
                // Pausar video
                video.pause();
                overlay.innerHTML = '<i class="fas fa-volume-up"></i>';
                wrapper.classList.remove('playing');
            } else {
                // Reanudar con sonido
                video.play();
                overlay.innerHTML = '<i class="fas fa-pause"></i>';
                wrapper.classList.add('playing');
            }
        };
        
        // Tocar en cualquier parte del video reproduce/pausa
        wrapper.addEventListener('click', toggleSound);
        
        // El overlay también (por si tocan el botón)
        overlay.addEventListener('click', toggleSound);
        
        video.addEventListener('ended', function() {
            if (video.muted) {
                video.play();
            } else {
                overlay.innerHTML = '<i class="fas fa-volume-up"></i>';
                wrapper.classList.remove('playing');
                video.muted = true;
                video.play();
            }
        });
    });

    // ===== SLIDER PARA MÓVIL (2 videos visibles) =====
    const sliderTrack = document.querySelector('.videos-slider-track');
    const prevBtn = document.querySelector('.video-arrow-left');
    const nextBtn = document.querySelector('.video-arrow-right');
    const dots = document.querySelectorAll('.video-dot');
    
    if (sliderTrack && sliderTrack.children.length > 0) {
        let currentIndex = 0;
        const totalSlides = sliderTrack.children.length;
        let slidesPerView = 2;
        
        if (window.innerWidth >= 769) {
            slidesPerView = totalSlides;
        } else {
            slidesPerView = 2;
        }
        
        const maxIndex = Math.max(0, totalSlides - slidesPerView);
        
        function updateSlider() {
            const translateX = -(currentIndex * (100 / slidesPerView));
            sliderTrack.style.transform = `translateX(${translateX}%)`;
            const currentDotIndex = Math.floor(currentIndex / slidesPerView);
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentDotIndex);
            });
        }
        
        function updateArrows() {
            if (prevBtn) prevBtn.style.opacity = currentIndex <= 0 ? '0.3' : '1';
            if (nextBtn) nextBtn.style.opacity = currentIndex >= maxIndex ? '0.3' : '1';
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (currentIndex < maxIndex) {
                    currentIndex++;
                    updateSlider();
                    updateArrows();
                }
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateSlider();
                    updateArrows();
                }
            });
        }
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentIndex = index * slidesPerView;
                if (currentIndex > maxIndex) currentIndex = maxIndex;
                updateSlider();
                updateArrows();
            });
        });
        
        updateSlider();
        updateArrows();
        
        window.addEventListener('resize', () => {
            let newSlidesPerView = 2;
            if (window.innerWidth >= 769) {
                newSlidesPerView = totalSlides;
            } else {
                newSlidesPerView = 2;
            }
            
            if (newSlidesPerView !== slidesPerView) {
                slidesPerView = newSlidesPerView;
                currentIndex = 0;
                updateSlider();
                updateArrows();
            }
        });
    }
});
</script>
@endpush