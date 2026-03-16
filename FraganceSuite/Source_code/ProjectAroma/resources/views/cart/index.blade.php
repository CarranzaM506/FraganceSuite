@extends('layouts.app')

@section('body-class', 'cart-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endsection

@section('content')
<!-- Barra negra pegada al header -->
<nav class="black-navbar">
    <div class="container">
        <span class="nav-title">MI CARRITO</span>
    </div>
</nav>

<div class="cart-page">
    <div class="cart-container">
        <div class="cart-items-section">
            <div id="cartItemsContainer">
                <!-- Los items del carrito se cargarán aquí con JavaScript -->
                <div class="empty-cart" id="emptyCartMessage">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Tu carrito está vacío</p>
                    <a href="{{ route('catalog.index') }}" class="btn-continue-shopping">Continuar comprando</a>
                </div>
            </div>
        </div>

        <div class="cart-summary">
            <div class="summary-card">
                <h3 class="summary-title">Resumen del Pedido</h3>
                
                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span id="subtotalPrice">₡0.00</span>
                </div>
                
                <div class="summary-item">
                    <span>Descuentos:</span>
                    <span id="discountAmount" class="discount-text">-₡0.00</span>
                </div>
                
                <div class="summary-divider"></div>
                
                <div class="summary-total">
                    <span>Total:</span>
                    <span id="totalPrice">₡0.00</span>
                </div>
                
                <button class="btn-checkout" id="checkoutBtn">Proceder al Pago</button>
                <a href="{{ route('catalog.index') }}" class="btn-continue">Continuar comprando</a>
            </div>
        </div>
    </div>
</div>

@endsection

