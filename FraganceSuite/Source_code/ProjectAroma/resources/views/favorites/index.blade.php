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
        @media (max-width: 768px) {
            .favorites-body .catalog-products {
                padding: 0 18px;
            }
            .favorites-body .product-image { height: 180px; }
        }
        @media (max-width: 600px) {
            .favorites-body .catalog-products {
                padding: 0 14px;
            }
            .favorites-body .catalog-card { max-width: 100%; }
            .favorites-body .product-image { height: 170px; }
        }
    </style>
@endsection

@section('content')
<nav class="black-navbar">
    <div class="container">
        <span class="nav-title">FAVORITOS</span>
    </div>
</nav>

<section class="catalog-products" style="padding-top: 40px;">
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
