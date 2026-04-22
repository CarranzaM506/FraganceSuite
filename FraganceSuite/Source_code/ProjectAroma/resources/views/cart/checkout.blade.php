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

                <h3 class="summary-title">Direccion de envio</h3>

                <div class="row g-3" id="addressList">

                    @forelse ($addresses as $address)
                        <div class="col-md-6 col-lg-4">

                            <label class="address-card-select w-100">
                                <input type="radio" name="address_id" value="{{ $address->idlocation }}" class="address-radio">

                                <div class="address-card">
                                    <div class="address-card-body">

                                        <h6 class="fw-bold mb-1">
                                            {{ $address->province }}
                                        </h6>

                                        <h6 class="mb-1 text-muted small">
                                            {{ $address->canton }} -
                                            {{ $address->district }} -
                                            {{ $address->zipcode }}
                                        </h6>

                                        <p class="mb-2">
                                            {{ $address->detail }}
                                        </p>

                                        <span class="select-label">
                                            Seleccionar direccion
                                        </span>

                                    </div>
                                </div>

                            </label>

                        </div>
                    @empty
                        <p class="text-muted">No tienes direcciones registradas.</p>
                    @endforelse

                </div>

                <hr>

                <h4 class="summary-title">Nueva direccion</h4>

                <div id="addressMessage" class="alert d-none" role="alert">
                    <span id="addressMessageText"></span>
                </div>

                <form id="newAddressForm" method="POST">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Provincia</label>
                            <input type="text" name="province" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Canton</label>
                            <input type="text" name="canton" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="district" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Direccion exacta</label>
                            <textarea name="detail" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Codigo Postal</label>
                            <input type="text" name="zipcode" maxlength="5" class="form-control" required>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn-checkout">
                            Guardar direccion
                        </button>
                    </div>
                </form>

            </div>

            <div class="cart-summary">
                <div class="summary-card">

                    <h3 class="summary-title">Resumen del Pedido</h3>

                    <div id="checkoutItems"></div>

                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span id="subtotalPrice">CRC 0.00</span>
                    </div>

                    <div class="summary-item">
                        <span>Descuentos:</span>
                        <span id="discountAmount" class="discount-text">-CRC 0.00</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="totalPrice">CRC 0.00</span>
                    </div>

                    <div id="currencyCompatibilityNotice" class="alert alert-warning" role="alert" style="font-size: 12px; margin-bottom: 12px;">
                        Por compatibilidad de la pasarela, el cobro puede mostrarse en USD.
                        <div id="currencyEquivalence" style="margin-top: 6px; font-weight: 600;">
                            Equivalencia estimada: CRC 0.00 ≈ USD 0.00
                        </div>
                    </div>

                    <div id="paymentStatus" class="alert alert-info d-none" role="alert" style="font-size: 13px; margin-bottom: 12px;"></div>

                    <div style="position: relative;">
                        <div id="paypal-button-container"></div>
                        <div id="paypalProcessingOverlay"
                             style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.75); z-index:10; align-items:center; justify-content:center; text-align:center; padding:12px;">
                            <div style="display:flex; flex-direction:column; gap:8px; align-items:center;">
                                <div class="spinner-border text-dark" role="status" style="width:1.6rem; height:1.6rem;">
                                    <span class="visually-hidden">Procesando</span>
                                </div>
                                <span class="overlay-text" style="font-size:13px; font-weight:600;">Procesando pago...</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
    @php
        $paypalClientId = config('paypal.mode') === 'live'
            ? config('paypal.live.client_id')
            : config('paypal.sandbox.client_id');
    @endphp
    <script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}"></script>
@endsection
