@extends('layouts.app')

@section('body-class', 'cart-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endsection

@section('content')

    <nav class="black-navbar">
        <div class="container">
            <span class="nav-title">CHECKOUT</span>
        </div>
    </nav>

    <div class="cart-page">
        <div class="cart-container">

            <div class="cart-items-section">

                <!--DIRECCIONES-->
                <h3 class="summary-title">Dirección de envío</h3>

                <div id="addressList">
                    @foreach ($addresses as $address)
                        <label class="address-option">
                            <input type="radio" name="address_id" value="{{ $address->id }}">
                            <div>
                                <strong>{{ $address->province }}, {{ $address->canton }}</strong><br>
                                <small>{{ $address->details }}</small>
                            </div>
                        </label>
                    @endforeach
                </div>

                <hr>

                <!-- NUEVA DIRECCIÓN -->
                <h4 class="summary-title">Nueva dirección</h4>

                <form id="newAddressForm" method="POST">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Provincia</label>
                            <input type="text" name="province" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cantón</label>
                            <input type="text" name="canton" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="district" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Dirección exacta</label>
                            <textarea name="detail" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Código Postal</label>
                            <input type="text" name="zipcode" maxlength="5" class="form-control" required>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn-checkout">
                            Guardar dirección
                        </button>
                    </div>
                </form>

            </div>

            <!-- CARRITO A LA DERECHA -->
            <div class="cart-summary">
                <div class="summary-card">

                    <h3 class="summary-title">Resumen del Pedido</h3>

                    <!-- MINI CARRITO -->
                    <div id="checkoutItems"></div>

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

                    <!-- PAYPAL -->
                    <button id="paypalBtn" class="btn-checkout">
                        Pagar con PayPal
                    </button>

                </div>
            </div>

        </div>
    </div>

@endsection