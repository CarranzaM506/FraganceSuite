@extends('layouts.app')

@section('body-class', 'cart-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
    <style>
        /* Ocultar el contenido hasta que cargue - SOLO AGREGADO */
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


<div class="cart-page">
    <div class="cart-container">
        <div class="cart-items-section">
            <div id="cartItemsContainer">
                <div class="cart-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando tu carrito...</p>
                </div>
            </div>
        </div>

        <div class="cart-summary">
            <div class="summary-card">
                <h3 class="summary-title">RESUMEN DEL PEDIDO</h3>
                
                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span id="subtotalPrice">₡0</span>
                </div>
                
                <div class="summary-item">
                    <span>Descuentos:</span>
                    <span id="discountAmount" class="discount-text">-₡0</span>
                </div>

                <div class="summary-item promo-code-row">
                    <input id="promoCodeInput" class="promo-code-input" type="text" placeholder="CÓDIGO DE DESCUENTO">
                    <button id="applyPromoBtn" class="promo-code-btn">APLICAR</button>
                </div>
                <p id="promoCodeMessage" class="promo-code-message"></p>
                
                <div class="summary-divider"></div>
                
                <div class="summary-total">
                    <span>Total:</span>
                    <span id="totalPrice">₡0</span>
                </div>
                
                <button class="btn-checkout" id="checkoutBtn">PROCEDER AL PAGO</button>
                <a href="{{ route('catalog.index') }}" class="btn-continue">CONTINUAR COMPRANDO</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div id="confirmModal" class="confirm-modal" style="display: none;">
    <div class="confirm-modal-content">
        <h4>¿Eliminar producto?</h4>
        <p>¿Estás seguro de que deseas eliminar este producto de tu carrito?</p>
        <div class="confirm-modal-buttons">
            <button class="confirm-cancel" id="confirmCancelBtn">CANCELAR</button>
            <button class="confirm-confirm" id="confirmDeleteBtn">ELIMINAR</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
 // ========== MANEJO DEL SCROLL ==========
const header = document.querySelector('header');
const blackNavbar = document.querySelector('.black-navbar');
let lastScroll = 0;

if (header && blackNavbar) {
    const headerHeight = header.offsetHeight;
    
    // Forzar estilos inline con !important
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
    
    // ========== RESTO DEL CÓDIGO DEL CARRITO ==========
    const cartContainer = document.getElementById('cartItemsContainer');
    const subtotalSpan = document.getElementById('subtotalPrice');
    const discountSpan = document.getElementById('discountAmount');
    const totalSpan = document.getElementById('totalPrice');
    let currentItems = [];
    let pendingDeleteProductId = null;
    
    const confirmModal = document.getElementById('confirmModal');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    function showConfirmModal(productId) {
        pendingDeleteProductId = productId;
        confirmModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function hideConfirmModal() {
        confirmModal.style.display = 'none';
        pendingDeleteProductId = null;
        document.body.style.overflow = '';
    }
    
    if (confirmCancelBtn) confirmCancelBtn.addEventListener('click', hideConfirmModal);
    confirmModal.addEventListener('click', function(e) {
        if (e.target === confirmModal) hideConfirmModal();
    });
    
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
    
    function getDiscountedPrice(price, discount) {
        return price * (1 - (discount / 100));
    }
    
    async function loadCart() {
        try {
            cartContainer.innerHTML = `<div class="cart-loading"><i class="fas fa-spinner fa-spin"></i><p>Cargando tu carrito...</p></div>`;
            const response = await fetch('/api/cart', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
            
            if (response.status === 401) {
                cartContainer.innerHTML = `<div class="empty-cart"><i class="fas fa-exclamation-triangle"></i><p>Debes iniciar sesión para ver tu carrito</p><a href="/login" class="btn-continue-shopping">INICIAR SESIÓN</a></div>`;
                // Mostrar página aunque haya error - SOLO AGREGADO
                document.querySelector('.cart-page').classList.add('loaded');
                return;
            }
            if (!response.ok) throw new Error('Error al cargar');
            
            const data = await response.json();
            currentItems = data.items || [];
            
            if (currentItems.length === 0) {
                cartContainer.innerHTML = `<div class="empty-cart"><i class="fas fa-shopping-bag"></i><p>Tu carrito está vacío</p><a href="{{ route('catalog.index') }}" class="btn-continue-shopping">CONTINUAR COMPRANDO</a></div>`;
                updateTotals(0, 0, 0);
                // Mostrar página - SOLO AGREGADO
                document.querySelector('.cart-page').classList.add('loaded');
                return;
            }
            
            renderCartItems();
            calculateTotals();
            
            // Mostrar página después de cargar TODO - SOLO AGREGADO
            document.querySelector('.cart-page').classList.add('loaded');
            
        } catch (error) {
            cartContainer.innerHTML = `<div class="empty-cart"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar el carrito</p><button onclick="location.reload()" class="btn-continue-shopping">REINTENTAR</button></div>`;
            // Mostrar página aunque haya error - SOLO AGREGADO
            document.querySelector('.cart-page').classList.add('loaded');
        }
    }
    
    function renderCartItems() {
    let html = `
        <div class="cart-header">
            <span>Producto</span>
            <span>Precio</span>
            <span>Cantidad</span>
            <span>Total</span>
            <span></span>
        </div>
    `;
    
    currentItems.forEach((item, index) => {
        const discountedPrice = getDiscountedPrice(item.price, item.discount);
        const itemTotal = discountedPrice * item.quantity;
        html += `<div class="cart-item-row" data-product-id="${item.id}">
            <div class="cart-item-product">
                <img src="${item.image || '/images/placeholder.jpg'}" onerror="this.src='/images/placeholder.jpg'">
                <div class="cart-item-info">
                    <div class="cart-item-name">${escapeHtml(item.name)}</div>
                    <div class="cart-item-brand">${escapeHtml(item.brand || '')}</div>
                </div>
            </div>
            <div class="cart-item-price">
                ${item.discount > 0 ? `<span class="original-price">₡${formatCurrency(item.price)}</span><br>` : ''}
                <span class="final-price">₡${formatCurrency(discountedPrice)}</span>
                ${item.discount > 0 ? `<span class="discount-badge">-${item.discount}%</span>` : ''}
            </div>
            <div class="cart-item-quantity">
                <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity - 1})">-</button>
                <span>${item.quantity}</span>
                <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity + 1})" ${item.quantity >= item.stock ? 'disabled' : ''}>+</button>
            </div>
            <div class="cart-item-total">₡${formatCurrency(itemTotal)}</div>
            <div class="cart-item-remove">
                <button class="remove-btn" onclick="showRemoveConfirm(${item.id})"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>`;
    });
    cartContainer.innerHTML = html;
}
    
    window.showRemoveConfirm = function(productId) { showConfirmModal(productId); };
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async function() {
            if (!pendingDeleteProductId) return;
            try {
                await fetch('/api/cart/remove', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify({ productId: pendingDeleteProductId }) });
                loadCart();
            } catch (error) { alert('Error al eliminar'); }
            finally { hideConfirmModal(); }
        });
    }
    
    function calculateTotals() {
        let subtotal = 0, totalDiscount = 0;
        currentItems.forEach(item => {
            subtotal += item.price * item.quantity;
            totalDiscount += item.price * (item.discount / 100) * item.quantity;
        });
        updateTotals(subtotal, totalDiscount, subtotal - totalDiscount);
    }
    
    function updateTotals(subtotal, discount, total) {
        subtotalSpan.textContent = `₡${formatCurrency(subtotal)}`;
        discountSpan.textContent = `-₡${formatCurrency(discount)}`;
        totalSpan.textContent = `₡${formatCurrency(total)}`;
    }
    
    window.updateQty = async function(productId, newQty) {
        if (newQty < 0) return;
        const product = currentItems.find(p => p.id === productId);
        if (product && newQty > product.stock) { alert(`Solo hay ${product.stock} unidades disponibles`); return; }
        try {
            const response = await fetch('/api/cart/update', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify({ productId, quantity: newQty }) });
            if (response.ok) loadCart();
            else { const error = await response.json(); alert(error.error || 'Error al actualizar'); }
        } catch (error) { alert('Error al actualizar la cantidad'); }
    };
    
    const applyBtn = document.getElementById('applyPromoBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', async function() {
            const code = document.getElementById('promoCodeInput').value.trim();
            if (!code) { alert('Ingresa un código'); return; }
            applyBtn.disabled = true; applyBtn.textContent = 'APLICANDO...';
            try {
                const response = await fetch('/api/cart/apply-code', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify({ code }) });
                const result = await response.json();
                if (result.valid) { document.getElementById('promoCodeMessage').textContent = `✅ Código ${result.code} aplicado - ${result.value}% de descuento`; document.getElementById('promoCodeMessage').style.color = '#1b7f3c'; loadCart(); }
                else { document.getElementById('promoCodeMessage').textContent = result.error || 'Código inválido'; document.getElementById('promoCodeMessage').style.color = '#cc0000'; }
            } catch (error) { alert('Error al aplicar el código'); }
            finally { applyBtn.disabled = false; applyBtn.textContent = 'APLICAR'; }
        });
    }
    
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) checkoutBtn.addEventListener('click', function() { if (currentItems.length === 0) alert('Tu carrito está vacío'); else window.location.href = '/checkout'; });
    
    loadCart();
});
</script>
@endsection