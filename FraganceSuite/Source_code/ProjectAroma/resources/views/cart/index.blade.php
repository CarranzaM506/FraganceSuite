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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const subtotalPriceEl = document.getElementById('subtotalPrice');
    const discountAmountEl = document.getElementById('discountAmount');
    const totalPriceEl = document.getElementById('totalPrice');
    const checkoutBtn = document.getElementById('checkoutBtn');

    // Obtener datos del carrito desde localStorage
    function getCart() {
        const cart = localStorage.getItem('aroma_cart');
        return cart ? JSON.parse(cart) : {};
    }

    // Guardar carrito en localStorage
    function saveCart(cart) {
        localStorage.setItem('aroma_cart', JSON.stringify(cart));
    }

    // Cargar datos de un producto desde el servidor
    async function loadProductData(productId) {
        try {
            const response = await fetch(`/api/product/${productId}`);
            if (response.ok) {
                return await response.json();
            }
        } catch (error) {
            console.error('Error cargando datos del producto:', error);
        }
        return null;
    }

    // Renderizar carrito
    async function renderCart() {
        const cart = getCart();
        const cartItems = Object.values(cart);

        if (cartItems.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Tu carrito está vacío</p>
                    <a href="{{ route('catalog.index') }}" class="btn-continue-shopping">Continuar comprando</a>
                </div>
            `;
            checkoutBtn.disabled = true;
            updateTotals();
            return;
        }

        checkoutBtn.disabled = false;
        cartItemsContainer.innerHTML = '';

        for (const item of cartItems) {
            // Crear elemento del carrito
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.setAttribute('data-product-id', item.id);

            // Calcular precio con descuento
            const price = parseFloat(item.price);
            const quantity = parseInt(item.quantity);
            const discount = item.discount ? parseFloat(item.discount) : 0;
            const subtotal = price * quantity;
            const discountAmount = subtotal * (discount / 100);
            const total = subtotal - discountAmount;

            cartItem.innerHTML = `
                <div class="cart-item-image">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : `<div class="placeholder"><i class="fas fa-wine-bottle"></i></div>`}
                </div>

                <div class="cart-item-details">
                    <h3 class="item-name">${item.name}</h3>
                    <p class="item-brand">${item.brand}</p>
                    <p class="item-category">${item.category}</p>
                    
                    <div class="item-quantity">
                        <button class="qty-btn minus-btn" data-product-id="${item.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="qty-value">${quantity}</span>
                        <button class="qty-btn plus-btn" data-product-id="${item.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="cart-item-price">
                    <div class="price-info">
                        ${discount > 0 ? `<span class="unit-price" style="text-decoration: line-through; color: #999;">₡${price.toFixed(2)}</span>` : `<span class="unit-price">₡${price.toFixed(2)}</span>`}
                        <span class="multiplier">x${quantity}</span>
                    </div>
                    ${discount > 0 ? `<span class="discount-badge">${Math.round(discount)}% desc.</span>` : ''}
                    <div class="item-total">₡${total.toFixed(2)}</div>
                </div>

                <div class="cart-item-actions">
                    <button class="remove-btn" data-product-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            cartItemsContainer.appendChild(cartItem);
        }

        // Agregar event listeners
        addCartEventListeners();
        updateTotals();
    }

    // Agregar event listeners a botones del carrito
    function addCartEventListeners() {
        // Botones de cantidad
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const cart = getCart();
                
                if (cart[productId]) {
                    if (this.classList.contains('plus-btn')) {
                        cart[productId].quantity += 1;
                    } else if (this.classList.contains('minus-btn')) {
                        if (cart[productId].quantity > 1) {
                            cart[productId].quantity -= 1;
                        } else {
                            delete cart[productId];
                        }
                    }
                    saveCart(cart);
                    renderCart();
                }
            });
        });

        // Botones de eliminar
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const productId = this.getAttribute('data-product-id');
                const cart = getCart();
                const product = cart[productId];
                
                if (product) {
                    // Mostrar modal de confirmación para eliminar
                    const confirmed = await showDeleteConfirmationModal(product.name);
                    
                    if (confirmed) {
                        delete cart[productId];
                        saveCart(cart);
                        showDeleteNotification(`${product.name} eliminado del carrito`);
                        renderCart();
                    }
                }
            });
        });
    }

    // Actualizar totales
    function updateTotals() {
        const cart = getCart();
        const cartItems = Object.values(cart);

        let subtotal = 0;
        let totalDiscount = 0;

        cartItems.forEach(item => {
            const price = parseFloat(item.price);
            const quantity = parseInt(item.quantity);
            const discount = item.discount ? parseFloat(item.discount) : 0;

            const itemSubtotal = price * quantity;
            subtotal += itemSubtotal;

            const itemDiscountAmount = itemSubtotal * (discount / 100);
            totalDiscount += itemDiscountAmount;
        });

        const total = subtotal - totalDiscount;

        subtotalPriceEl.textContent = '₡' + subtotal.toFixed(2);
        discountAmountEl.textContent = '-₡' + totalDiscount.toFixed(2);
        totalPriceEl.textContent = '₡' + total.toFixed(2);
    }

    // Renderizar carrito al cargar la página
    renderCart();

    // Modal de confirmación para eliminar producto
    function showDeleteConfirmationModal(productName) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease-in-out;
            `;

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 0px;
                max-width: 300px;
                width: 90%;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                animation: slideUp 0.3s ease-in-out;
                text-align: center;
            `;

            modalContent.innerHTML = `
                <p style="margin: 0 0 25px 0; color: #666; font-size: 14px;">
                    ¿Eliminar del carrito?
                </p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button id="deleteCancel" style="
                        padding: 10px 20px;
                        background: #f5f5f5;
                        color: #666;
                        border: 1px solid #ddd;
                        border-radius: 0px;
                        font-size: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        letter-spacing: 0.5px;
                        text-transform: uppercase;
                        transition: all 0.2s;
                    ">
                        No
                    </button>
                    <button id="deleteConfirm" style="
                        padding: 10px 20px;
                        background: #cc0000;
                        color: white;
                        border: none;
                        border-radius: 0px;
                        font-size: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        letter-spacing: 0.5px;
                        text-transform: uppercase;
                        transition: all 0.2s;
                    ">
                        Sí, eliminar
                    </button>
                </div>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            const confirmBtn = modal.querySelector('#deleteConfirm');
            const cancelBtn = modal.querySelector('#deleteCancel');

            const closeModal = (result) => {
                modal.style.animation = 'fadeOut 0.3s ease-in-out';
                setTimeout(() => {
                    modal.remove();
                    resolve(result);
                }, 300);
            };

            confirmBtn.addEventListener('click', () => closeModal(true));
            cancelBtn.addEventListener('click', () => closeModal(false));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(false);
            });

            confirmBtn.addEventListener('mouseenter', function() {
                this.style.background = '#ff0000';
            });
            confirmBtn.addEventListener('mouseleave', function() {
                this.style.background = '#cc0000';
            });
            cancelBtn.addEventListener('mouseenter', function() {
                this.style.background = '#eee';
            });
            cancelBtn.addEventListener('mouseleave', function() {
                this.style.background = '#f5f5f5';
            });
        });
    }

    // Notificación para eliminación
    function showDeleteNotification(message) {
        let notification = document.getElementById('deleteNotification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'deleteNotification';
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: #cc0000;
                color: white;
                padding: 15px 25px;
                border-radius: 0px;
                z-index: 9999;
                font-size: 13px;
                font-weight: 500;
                letter-spacing: 0.5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                animation: slideIn 0.3s ease-in-out;
            `;
            document.body.appendChild(notification);
        }

        notification.textContent = message;
        notification.style.animation = 'slideIn 0.3s ease-in-out';
        notification.style.display = 'block';

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in-out';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 3000);
    }

    // Botón de checkout (por ahora solo muestra un mensaje)
    checkoutBtn.addEventListener('click', function() {
        alert('La función de pago será implementada próximamente.');
    });
});
</script>
@endpush
