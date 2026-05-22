@extends('layouts.app')

@section('body-class', 'cart-body checkout-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
    <style>
    /* Estilos específicos para checkout */
    .checkout-body .cart-page {
        padding-top: 180px !important;
    }
    
    .checkout-body .cart-items-section {
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    
    .checkout-body .summary-card {
        box-shadow: none !important;
        border: 1px solid #eee !important;
        padding: 20px !important;  /* ← AGREGADO: separar contenido de los bordes */
    }
    
    /* Tarjetas de direcciones - SIN border radius */
    .address-card {
        border: 1px solid #eee;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        border-radius: 0 !important;  /* ← SIN bordes redondeados */
    }
    
    .address-card:hover {
        border-color: #000;
    }
    
    .address-radio:checked + .address-card {
        border-color: #000;
        background: #fafafa;
    }
    
    .address-card-select {
        display: block;
        cursor: pointer;
    }
    
    .address-radio {
        display: none;
    }
    
    .select-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        margin-top: 8px;
        display: inline-block;
    }
    
    .address-radio:checked + .address-card .select-label {
        color: #000;
        font-weight: 600;
    }
    
    /* Campos de formulario - SIN border radius */
    .form-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        margin-bottom: 5px;
        display: block;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        font-size: 13px;
        transition: all 0.2s;
        border-radius: 0 !important;  /* ← SIN bordes redondeados */
    }
    
    .form-control:focus {
        outline: none;
        border-color: #000;
    }
    
    textarea.form-control {
        resize: vertical;
    }
    
    hr {
        margin: 25px 0;
        border-color: #eee;
    }
    
    /* Botón guardar dirección - SIN border radius */
    .btn-checkout {
        border-radius: 0 !important;
    }
    
    /* Tamaño del título del nav */
    .black-navbar .nav-title {
        font-size: 14px;
        letter-spacing: 2px;
    }
    
    /* Item del resumen - con imagen */
    .checkout-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }
    
    .checkout-item-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 2;
    }
    
    .checkout-item-img {
        width: 50px;
        height: 60px;
        object-fit: cover;
        background: #f5f5f5;
    }
    
    .checkout-item-details {
        flex: 1;
    }
    
    .checkout-item-name {
        font-weight: 500;
        font-size: 13px;
        margin-bottom: 2px;
    }
    
    .checkout-item-brand {
        font-size: 11px;
        color: #666;
        margin-bottom: 2px;
    }
    
    .checkout-item-quantity {
        font-size: 11px;
        color: #666;
    }
    
    .checkout-item-price {
        font-weight: 600;
        font-size: 13px;
    }
    
    /* Separar contenido del summary card */
    .summary-item {
        margin-bottom: 12px;
    }
    
    .summary-total {
        margin-top: 5px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .checkout-body .cart-page {
            padding-top: 130px !important;
        }
        
        .address-card {
            padding: 12px;
        }
        
        .form-control {
            padding: 8px;
            font-size: 12px;
        }
        
        .black-navbar .nav-title {
            font-size: 12px !important;
            letter-spacing: 1.5px !important;
        }
        
        .checkout-item-img {
            width: 40px;
            height: 50px;
        }
        
        .checkout-body .summary-card {
            padding: 15px !important;
        }
    }
    
    @media (max-width: 480px) {
        .checkout-body .cart-page {
            padding-top: 130px !important;
        }
        
        .address-card {
            padding: 10px;
        }
        
        .summary-title {
            font-size: 12px;
            margin-bottom: 12px;
        }
        
        .black-navbar .nav-title {
            font-size: 12px !important;
            letter-spacing: 1px !important;
        }
        
        .checkout-body .summary-card {
            padding: 12px !important;
        }
    }
    
    /* Ocultar loading hasta cargar */
    .cart-page {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .cart-page.loaded {
        opacity: 1;
    }

 
</style>
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
            <h3 class="summary-title">DIRECCIÓN DE ENVÍO</h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                @forelse ($addresses as $address)
                    <div>
                        <label class="address-card-select">
                            <input type="radio" name="address_id" value="{{ $address->idlocation }}" class="address-radio">
                            <div class="address-card">
                                <div>
                                    <h6 style="font-weight: 600; margin-bottom: 5px; font-size: 14px;">
                                        {{ $address->province }}
                                    </h6>
                                    <h6 style="margin-bottom: 8px; color: #666; font-size: 12px;">
                                        {{ $address->canton }} - {{ $address->district }} - {{ $address->zipcode }}
                                    </h6>
                                    <p style="margin-bottom: 8px; font-size: 13px; color: #444;">
                                        {{ $address->detail }}
                                    </p>
                                    <span class="select-label">SELECCIONAR DIRECCIÓN</span>
                                </div>
                            </div>
                        </label>
                    </div>
                @empty
                    <p style="color: #666; grid-column: 1 / -1; text-align: center; padding: 40px;">No tienes direcciones registradas.</p>
                @endforelse
            </div>

            <hr>

            <h4 class="summary-title" style="margin-top: 20px;">NUEVA DIRECCIÓN</h4>

            <div id="addressMessage" style="display: none; padding: 12px; margin-bottom: 20px; font-size: 13px;"></div>

            <form id="newAddressForm" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label class="form-label">PROVINCIA</label>
                        <input type="text" name="province" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">CANTÓN</label>
                        <input type="text" name="canton" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">DISTRITO</label>
                        <input type="text" name="district" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">CÓDIGO POSTAL</label>
                        <input type="text" name="zipcode" maxlength="5" class="form-control" required>
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="form-label">DIRECCIÓN EXACTA</label>
                        <textarea name="detail" class="form-control" rows="2" required></textarea>
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn-checkout" style="width: auto; padding: 12px 30px;">GUARDAR DIRECCIÓN</button>
                </div>
            </form>
        </div>

        <div class="cart-summary">
            <div class="summary-card">
                <h3 class="summary-title">RESUMEN DEL PEDIDO</h3>

                <div id="checkoutItems" style="margin-bottom: 15px;"></div>

                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span id="subtotalPrice">₡0</span>
                </div>

                <div class="summary-item">
                    <span>Descuentos:</span>
                    <span id="discountAmount" class="discount-text">-₡0</span>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-total">
                    <span>Total:</span>
                    <span id="totalPrice">₡0</span>
                </div>

                <div id="currencyCompatibilityNotice" style="background: #f5f5f5; padding: 12px; font-size: 11px; margin-bottom: 15px; text-align: center;">
                    Por compatibilidad de la pasarela, el cobro puede mostrarse en USD.
                    <div id="currencyEquivalence" style="margin-top: 5px; font-weight: 600;">
                        Equivalencia estimada: ₡0 ≈ $0
                    </div>
                </div>

                <div id="paymentStatus" style="display: none; padding: 12px; margin-bottom: 15px; background: #cce5ff; color: #004085; font-size: 12px;"></div>

                <div style="position: relative;">
                    <div id="paypal-button-container"></div>
                    <div id="paypalProcessingOverlay" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.9); z-index:10; align-items:center; justify-content:center; text-align:center; padding:12px;">
                        <div style="display:flex; flex-direction:column; gap:8px; align-items:center;">
                            <div class="spinner-border text-dark" role="status" style="width:1.6rem; height:1.6rem;">
                                <span class="visually-hidden">Procesando</span>
                            </div>
                            <span style="font-size:13px; font-weight:600;">Procesando pago...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $paypalClientId = config('paypal.mode') === 'live'
        ? config('paypal.live.client_id')
        : config('paypal.sandbox.client_id');
@endphp
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartPage = document.querySelector('.cart-page');
    
    // ========== MANEJO DEL SCROLL ==========
    const header = document.querySelector('header');
    const blackNavbar = document.querySelector('.black-navbar');
    let lastScroll = 0;
    
    if (header && blackNavbar) {
        const headerHeight = header.offsetHeight;
        
        header.style.setProperty('position', 'fixed', 'important');
        header.style.setProperty('top', '0', 'important');
        header.style.setProperty('width', '100%', 'important');
        header.style.setProperty('zIndex', '1000', 'important');
        header.style.setProperty('transition', 'top 0.3s ease-in-out', 'important');
        
        blackNavbar.style.setProperty('position', 'fixed', 'important');
        blackNavbar.style.setProperty('top', headerHeight + 'px', 'important');
        blackNavbar.style.setProperty('width', '100%', 'important');
        blackNavbar.style.setProperty('zIndex', '999', 'important');
        blackNavbar.style.setProperty('backgroundColor', '#000000', 'important');
        blackNavbar.style.setProperty('transition', 'top 0.3s ease-in-out', 'important');
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll < 30) {
                header.style.setProperty('top', '0', 'important');
                blackNavbar.style.setProperty('top', header.offsetHeight + 'px', 'important');
            } 
            else if (currentScroll > lastScroll && currentScroll > 50) {
                const hideValue = '-' + header.offsetHeight + 'px';
                header.style.setProperty('top', hideValue, 'important');
                blackNavbar.style.setProperty('top', hideValue, 'important');
            } 
            else if (currentScroll < lastScroll) {
                header.style.setProperty('top', '0', 'important');
                blackNavbar.style.setProperty('top', header.offsetHeight + 'px', 'important');
            }
            
            lastScroll = currentScroll;
        });
    }
    
    // ========== CARGAR RESUMEN DEL CARRITO ==========
    async function loadCheckoutSummary() {
        try {
            const response = await fetch('/api/cart', {
                headers: { 'Accept': 'application/json' },
                credentials: 'include'
            });
            
            if (!response.ok) throw new Error('Error al cargar');
            
            const data = await response.json();
            const items = data.items || [];
            
            if (items.length === 0) {
                window.location.href = '/cart';
                return;
            }
            
            let itemsHtml = '';
            let subtotal = 0, discountTotal = 0;
            
            items.forEach(item => {
                const discountedPrice = item.price * (1 - (item.discount / 100));
                const itemTotal = discountedPrice * item.quantity;
                subtotal += item.price * item.quantity;
                discountTotal += item.price * (item.discount / 100) * item.quantity;
                
                itemsHtml += `
                    <div class="checkout-item">
                        <div class="checkout-item-info">
                            <img src="${item.image || '/images/placeholder.jpg'}" class="checkout-item-img" onerror="this.src='/images/placeholder.jpg'">
                            <div class="checkout-item-details">
                                <div class="checkout-item-name">${escapeHtml(item.name)}</div>
                                <div class="checkout-item-brand">${escapeHtml(item.brand || '')}</div>
                                <div class="checkout-item-quantity">Cantidad: ${item.quantity}</div>
                            </div>
                        </div>
                        <div class="checkout-item-price">₡${formatCurrency(itemTotal)}</div>
                    </div>
                `;
            });
            
            document.getElementById('checkoutItems').innerHTML = itemsHtml;
            
            const finalTotal = subtotal - discountTotal;
            
            document.getElementById('subtotalPrice').textContent = `₡${formatCurrency(subtotal)}`;
            document.getElementById('discountAmount').textContent = `-₡${formatCurrency(discountTotal)}`;
            document.getElementById('totalPrice').textContent = `₡${formatCurrency(finalTotal)}`;
            
            const usdTotal = (finalTotal / 500).toFixed(2);
            document.getElementById('currencyEquivalence').innerHTML = `Equivalencia estimada: ₡${formatCurrency(finalTotal)} ≈ $${usdTotal}`;
            
            cartPage.classList.add('loaded');
            
        } catch (error) {
            console.error('Error:', error);
            cartPage.classList.add('loaded');
        }
    }
    
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-CR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // ========== NUEVA DIRECCIÓN CON CLONADO Y { once: true } ==========
    const addressForm = document.getElementById('newAddressForm');
    
    if (addressForm) {
        // Eliminar event listeners anteriores clonando el formulario
        const newForm = addressForm.cloneNode(true);
        addressForm.parentNode.replaceChild(newForm, addressForm);
        
        const finalForm = document.getElementById('newAddressForm');
        const submitBtn = finalForm.querySelector('button[type="submit"]');
        let formSubmitted = false;
        
        finalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (formSubmitted) {
                console.log('Formulario ya enviado, ignorando...');
                return;
            }
            formSubmitted = true;
            
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'GUARDANDO...';
            
            const formData = new FormData(finalForm);
            
            try {
                const response = await fetch('/api/address', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('✓ Dirección guardada correctamente', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage(result.message || 'Error al guardar dirección', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    formSubmitted = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Error de conexión', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                formSubmitted = false;
            }
        }, { once: true });
    }
    
    function showMessage(msg, type) {
        const msgDiv = document.getElementById('addressMessage');
        msgDiv.textContent = msg;
        msgDiv.style.display = 'block';
        
        if (type === 'error') {
            msgDiv.style.background = '#f8d7da';
            msgDiv.style.color = '#721c24';
        } else {
            msgDiv.style.background = '#d4edda';
            msgDiv.style.color = '#155724';
        }
        
        setTimeout(() => { 
            msgDiv.style.display = 'none'; 
        }, 3000);
    }
    
    // ========== SELECCIONAR DIRECCIÓN ==========
    document.querySelectorAll('.address-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.address-card').forEach(card => {
                card.style.borderColor = '#eee';
            });
            if (this.checked) {
                this.closest('.address-card-select').querySelector('.address-card').style.borderColor = '#000';
            }
        });
    });
    
    loadCheckoutSummary();
});
</script>
@endsection