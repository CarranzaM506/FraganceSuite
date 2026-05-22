@extends('layouts.app')

@section('title', 'Mis Favoritos | AROMA')

@section('body-class', 'favorites-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesCatalog.css') }}">
    <style>
        .favorites-body .catalog-products {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .favorites-body .catalog-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 28px;
            justify-items: center;
        }
        
        .favorites-body .catalog-card {
            opacity: 1;
            transform: none;
            max-width: 240px;
            width: 100%;
        }
        
        .favorites-body .product-image {
            height: 200px;
            background-color: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .favorites-body .product-image img,
        .favorites-body .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .favorites-body .product-card:hover .product-image img,
        .favorites-body .product-card:hover .product-img {
            transform: scale(1.03);
        }
        
        /* Desktop */
        @media (min-width: 769px) {
            .favorites-body .catalog-products {
                padding-top: 120px;
            }
        }
        
        /* Tablet */
        @media (max-width: 768px) {
            .favorites-body .catalog-products {
                padding: 0 18px;
                padding-top: 100px !important;
            }
            .favorites-body .product-image { 
                height: 160px; 
            }
            .favorites-body .catalog-grid {
                gap: 20px;
            }
            .favorites-body .catalog-card {
                max-width: 180px;
            }
            .product-name {
                font-size: 12px;
            }
            .product-brand {
                font-size: 10px;
            }
            .product-price {
                font-size: 12px;
            }
        }
        
        /* Móvil */
        @media (max-width: 600px) {
            .favorites-body .catalog-products {
                padding: 0 14px;
                padding-top: 90px !important;
            }
            .favorites-body .catalog-card { 
                max-width: 160px; 
            }
            .favorites-body .product-image { 
                height: 140px; 
            }
            .favorites-body .catalog-grid {
                gap: 15px;
            }
            .product-name {
                font-size: 11px;
            }
            .product-brand {
                font-size: 9px;
            }
            .product-price {
                font-size: 11px;
            }
        }
        
        /* Móvil muy pequeño */
        @media (max-width: 480px) {
            .favorites-body .catalog-products {
                padding-top: 85px !important;
            }
            .favorites-body .product-image { 
                height: 130px; 
            }
            .favorites-body .catalog-card { 
                max-width: 150px; 
            }
        }

.favorites-body .no-products {
    padding-top: 20px;
}

@media (max-width: 768px) {
    .favorites-body .no-products {
        padding-top: 50px;
    }
}

@media (max-width: 600px) {
    .favorites-body .no-products {
        padding-top: 50px;
    }
}
    </style>
@endsection

@section('content')
<nav class="black-navbar">
    <div class="container">
        <span class="nav-title">FAVORITOS</span>
    </div>
</nav>

<section class="catalog-products">
    @if($products->isEmpty())
        <div class="no-products" id="favoritesEmpty">
            <i class="far fa-heart"></i>
            <h3>No tienes productos en favoritos</h3>
            <p>Cuando agregues productos, aparecerán aquí.</p>
            <a href="{{ route('catalog.index') }}" class="filter-btn" style="margin-top: 20px; display: inline-block;">
                <i class="fas fa-store"></i> Ir al catálogo
            </a>
        </div>
    @else
        <div class="no-products" id="favoritesEmpty" style="display: none;">
            <i class="far fa-heart"></i>
            <h3>No tienes productos en favoritos</h3>
            <p>Cuando agregues productos, aparecerán aquí.</p>
            <a href="{{ route('catalog.index') }}" class="filter-btn" style="margin-top: 20px; display: inline-block;">
                <i class="fas fa-store"></i> Ir al catálogo
            </a>
        </div>

        <div class="product-grid catalog-grid" id="favoritesGrid">
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
                            <span class="wishlist-icon active" data-product="{{ $product->idproduct }}">
                                <i class="fas fa-heart"></i>
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
                        <p class="product-price">₡{{ number_format($product->price, 2) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    const blackNavbar = document.querySelector('.black-navbar');
    let lastScrollTop = 0;
    
    if (header && blackNavbar) {
        const headerHeight = header.offsetHeight;
        blackNavbar.style.top = headerHeight + 'px';
        blackNavbar.style.position = 'fixed';
        
        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollingDown = scrollTop > lastScrollTop;
            
            if (scrollTop < 50) {
                header.classList.remove('hidden');
                header.classList.add('visible');
                blackNavbar.classList.remove('hidden');
                blackNavbar.classList.add('visible');
                blackNavbar.style.top = headerHeight + 'px';
                lastScrollTop = scrollTop;
                return;
            }
            
            if (scrollingDown && scrollTop - lastScrollTop > 5) {
                header.classList.remove('visible');
                header.classList.add('hidden');
                blackNavbar.classList.remove('visible');
                blackNavbar.classList.add('hidden');
            } 
            else if (!scrollingDown && lastScrollTop - scrollTop > 5) {
                header.classList.remove('hidden');
                header.classList.add('visible');
                blackNavbar.classList.remove('hidden');
                blackNavbar.classList.add('visible');
                blackNavbar.style.top = headerHeight + 'px';
            }
            
            if (header.classList.contains('hidden') && scrollTop > headerHeight) {
                blackNavbar.style.top = '0';
            } else if (!header.classList.contains('hidden')) {
                blackNavbar.style.top = headerHeight + 'px';
            }
            
            lastScrollTop = scrollTop;
        }
        
        window.addEventListener('scroll', handleScroll);
        
        window.addEventListener('resize', function() {
            blackNavbar.style.top = header.offsetHeight + 'px';
        });
    }
});
</script>
@endpush