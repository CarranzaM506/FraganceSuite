/**
 * PREVIEW DEL CARRITO - DROPDOWN AL PASAR EL MOUSE
 * Maneja la visualización del carrito en miniatura cuando el usuario pasa el mouse
 * Con controles de cantidad y eliminación
 */

class CartPreview {
    constructor() {
        this.listenersAttached = false; // Bandera para evitar duplicados
        
        // Siempre intentar inicializar en DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    // Inicializar event listeners
    init() {
        // Re-obtener elementos cada vez que se inicializa
        this.container = document.getElementById('cartIconContainer');
        this.preview = document.getElementById('cartPreview');
        this.itemsContainer = document.getElementById('cartPreviewItems');
        this.totalElement = document.getElementById('cartPreviewTotal');
        this.hideTimeout = null;
        
        // Solo proceder si existen los elementos necesarios
        if (!this.container || !this.preview) {
            console.warn('[CartPreview] Elements not found - not initializing preview');
            return;
        }
        
        console.log('[CartPreview] Initializing with container:', this.container);
        this.attachListeners();
        this.attachItemListeners(); // Agregar listeners una sola vez
    }

    // Adjuntar listeners al contenedor del carrito
    attachListeners() {
        this.container.addEventListener('mouseenter', () => this.showPreview());
        this.container.addEventListener('mouseleave', () => this.hidePreview());
        
        // También escuchar cambios en el carrito
        window.addEventListener('cartUpdated', () => this.updatePreview());
    }

    // Mostrar el preview del carrito
    showPreview() {
        clearTimeout(this.hideTimeout);
        this.updatePreview();
        this.preview.style.display = 'block';
        // Pequeña animación
        this.preview.style.animation = 'none';
        setTimeout(() => {
            this.preview.style.animation = 'fadeIn 0.2s ease-in-out';
        }, 10);
    }

    // Ocultar el preview del carrito
    hidePreview() {
        this.hideTimeout = setTimeout(() => {
            this.preview.style.animation = 'fadeOut 0.2s ease-in-out';
            setTimeout(() => {
                this.preview.style.display = 'none';
            }, 200);
        }, 200);
    }

    // Actualizar el contenido del preview
    async updatePreview() {
        const cart = await this.getCart();
        
        if (Object.keys(cart).length === 0) {
            this.itemsContainer.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
            this.totalElement.textContent = '₡0';
            return;
        }

        // Construir HTML de los items
        let html = '';
        let total = 0;

        Object.entries(cart).forEach(([productId, item]) => {
            const price = item.price;
            const qty = item.quantity;
            const discountPercent = item.discount || 0;
            
            // Calcular precio con descuento
            const finalPrice = discountPercent > 0 ? price * (1 - discountPercent / 100) : price;
            const itemTotal = finalPrice * qty;
            total += itemTotal;

            html += `
                <div class="cart-preview-item" data-product-id="${productId}">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-brand">${item.brand}</div>
                        ${discountPercent > 0 ? `
                            <div class="item-price" style="font-size: 0.85em;">
                                <span style="text-decoration: line-through; color: #999;">₡${price.toLocaleString('es-CR', {minimumFractionDigits: 0})}</span>
                            </div>
                            <div class="item-final-price" style="color: #333; font-weight: bold; font-size: 1.05em;">₡${finalPrice.toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                        ` : `
                            <div class="item-price">₡${price.toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                        `}
                        <div class="item-bottom">
                            <div class="item-controls">
                                <button class="qty-btn qty-minus" data-product-id="${productId}">−</button>
                                <span class="item-quantity">${qty}</span>
                                <button class="qty-btn qty-plus" data-product-id="${productId}">+</button>
                                <button class="btn-delete" data-product-id="${productId}" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </div>
                            <div class="item-total" data-product-id="${productId}" data-base-price="${price}" data-discount="${discountPercent}">₡${itemTotal.toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                        </div>
                    </div>
                </div>
            `;
        });

        this.itemsContainer.innerHTML = html;
        this.totalElement.textContent = '₡' + total.toLocaleString('es-CR', {minimumFractionDigits: 0});
        // Los listeners ya están agregados una sola vez en init()
    }

    // Adjuntar listeners a los botones de cantidad y eliminación
    attachItemListeners() {
        // Si ya se agregaron listeners, no hacer nada
        if (this.listenersAttached) return;
        
        if (!this.itemsContainer) {
            console.log('[CartPreview] itemsContainer not found, skipping item listeners');
            return;
        }
        
        this.listenersAttached = true;
        console.log('[CartPreview] Attaching item listeners to container');
        
        // Usar un único listener con event delegation
        this.itemsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const productId = parseInt(btn.dataset.productId);
            if (!productId) {
                console.log('[CartPreview] No product ID found on button');
                return;
            }

            console.log('[CartPreview] Button clicked for product', productId, 'Class:', btn.className);

            // Botón de aumentar cantidad
            if (btn.classList.contains('qty-plus')) {
                console.log('[CartPreview] Increasing quantity for product', productId);
                this.increaseQuantity(productId);
            }
            // Botón de disminuir cantidad
            else if (btn.classList.contains('qty-minus')) {
                console.log('[CartPreview] Decreasing quantity for product', productId);
                this.decreaseQuantity(productId);
            }
            // Botón de eliminar
            else if (btn.classList.contains('btn-delete')) {
                this.deleteProduct(productId);
            }
        });
    }

    // Aumentar cantidad - RÁPIDO Y RESPONSIVO
    async increaseQuantity(productId) {
        try {
            // 1. Actualizar el DOM inmediatamente (feedback visual)
            const item = this.itemsContainer?.querySelector(`.cart-preview-item[data-product-id="${productId}"]`);
            if (!item) {
                console.warn('[CartPreview] Item not found for product', productId);
                return;
            }
            const qtySpan = item.querySelector('.item-quantity');
            const currentQty = parseInt(qtySpan.textContent);
            const newQty = currentQty + 1;
            
            qtySpan.textContent = newQty;
            this.updatePreviewTotal();
            
            // Actualizar total del item
            const itemTotalEl = item.querySelector('.item-total');
            const basePrice = parseFloat(itemTotalEl?.dataset.basePrice) || 0;
            const discount = parseFloat(itemTotalEl?.dataset.discount) || 0;
            const finalPrice = discount > 0 ? basePrice * (1 - discount / 100) : basePrice;
            const newTotal = finalPrice * newQty;
            if (itemTotalEl) {
                itemTotalEl.textContent = '₡' + newTotal.toLocaleString('es-CR', {minimumFractionDigits: 0});
            }
            
            // 2. Ahora actualizar la BD en background
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ productId, quantity: 1 })
            });
            
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return;
            }
            if (!response.ok) {
                // Si falla, revertir el cambio en el DOM
                qtySpan.textContent = currentQty;
                if (itemTotalEl) {
                    itemTotalEl.textContent = '₡' + (finalPrice * currentQty).toLocaleString('es-CR', {minimumFractionDigits: 0});
                }
                console.error('Error al agregar cantidad:', await response.json());
            } else {
                // Actualizar total general
                this.updatePreviewTotal();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Disminuir cantidad - RÁPIDO Y RESPONSIVO
    async decreaseQuantity(productId) {
        try {
            const item = this.itemsContainer?.querySelector(`.cart-preview-item[data-product-id="${productId}"]`);
            if (!item) {
                console.warn('[CartPreview] Item not found for product', productId);
                return;
            }
            const qtySpan = item.querySelector('.item-quantity');
            const currentQty = parseInt(qtySpan.textContent);
            
            if (currentQty <= 1) {
                // Si es 1, eliminar
                await this.deleteProduct(productId);
                return;
            }
            
            // 1. Actualizar el DOM inmediatamente
            const newQty = currentQty - 1;
            qtySpan.textContent = newQty;
            this.updatePreviewTotal();
            
            // Actualizar total del item
            const itemTotalEl = item.querySelector('.item-total');
            const basePrice = parseFloat(itemTotalEl?.dataset.basePrice) || 0;
            const discount = parseFloat(itemTotalEl?.dataset.discount) || 0;
            const finalPrice = discount > 0 ? basePrice * (1 - discount / 100) : basePrice;
            const newTotal = finalPrice * newQty;
            if (itemTotalEl) {
                itemTotalEl.textContent = '₡' + newTotal.toLocaleString('es-CR', {minimumFractionDigits: 0});
            }
            
            // 2. Actualizar la BD en background
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ productId, quantity: newQty })
            });
            
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return;
            }
            if (!response.ok) {
                // Si falla, revertir
                qtySpan.textContent = currentQty;
                if (itemTotalEl) {
                    itemTotalEl.textContent = '₡' + (finalPrice * currentQty).toLocaleString('es-CR', {minimumFractionDigits: 0});
                }
                console.error('Error:', await response.json());
            } else {
                // Actualizar total general
                this.updatePreviewTotal();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Eliminar producto - RÁPIDO Y RESPONSIVO
    async deleteProduct(productId) {
        try {
            // 1. Eliminar del DOM inmediatamente
            const item = this.itemsContainer?.querySelector(`.cart-preview-item[data-product-id="${productId}"]`);
            if (!item) {
                console.warn('[CartPreview] Item not found for product', productId);
                return;
            }
            item.style.opacity = '0';
            item.style.transition = 'opacity 0.2s';
            
            setTimeout(() => item.remove(), 200);
            
            // 2. Actualizar la BD
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/api/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ productId })
            });
            
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return;
            }
            if (response.ok) {
                this.showDeleteNotification('Producto eliminado');
                this.updatePreviewTotal();
            } else {
                // Si falla, volver a mostrar el item
                item.style.opacity = '1';
                console.error('Error:', await response.json());
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Actualizar solo el total sin re-renderizar todo
    updatePreviewTotal() {
        let total = 0;
        const items = this.itemsContainer ? this.itemsContainer.querySelectorAll('.cart-preview-item') : [];
        items.forEach(item => {
            const basePrice = parseFloat(item.querySelector('.item-total').dataset.basePrice) || 0;
            const discount = parseFloat(item.querySelector('.item-total').dataset.discount) || 0;
            const quantity = parseInt(item.querySelector('.item-quantity').textContent) || 0;
            
            const finalPrice = discount > 0 ? basePrice * (1 - discount / 100) : basePrice;
            const itemTotal = finalPrice * quantity;
            
            // Update the item total display
            item.querySelector('.item-total').textContent = `₡${itemTotal.toLocaleString('es-CR', {minimumFractionDigits: 0})}`;
            
            total += itemTotal;
        });
        this.totalElement.textContent = '₡' + total.toLocaleString('es-CR', {minimumFractionDigits: 0});
    }

    // Mostrar notificación de eliminación
    showDeleteNotification(message) {
        let notification = document.getElementById('cartDeleteNotification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'cartDeleteNotification';
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
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
            `;
            document.body.appendChild(notification);
        }

        notification.textContent = message;
        notification.style.display = 'block';
        notification.style.animation = 'slideIn 0.3s ease-in-out';

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in-out';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 3000);
    }

    // Obtener carrito desde la API
    async getCart() {
        try {
            const response = await fetch('/api/cart');
            if (response.ok) {
                const data = await response.json();
                return data.items.reduce((acc, item) => {
                    acc[item.id] = item;
                    return acc;
                }, {});
            }
        } catch (error) {
            console.error('Error al obtener carrito:', error);
        }
        return {};
    }

    // Guardar carrito (no necesario con DB)
    saveCart(cart) {
        // Disparar evento personalizado
        window.dispatchEvent(new CustomEvent('cartUpdated'));
    }
}

// Inicializar cuando el carrito se actualice
if (typeof window.cartManager !== 'undefined') {
    const originalSaveCart = window.cartManager.saveCart.bind(window.cartManager);
    
    window.cartManager.saveCart = function(cart) {
        originalSaveCart(cart);
        // Disparar evento personalizado cuando el carrito se actualiza
        window.dispatchEvent(new CustomEvent('cartUpdated'));
    };
}

// Inicializar el CartPreview cuando se cargue la página
window.cartPreview = new CartPreview();

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .cart-dropdown-container {
        position: relative;
    }

    .cart-preview-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 10px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        width: 420px;
        max-height: 600px;
        display: flex;
        flex-direction: column;
    }

    .cart-preview-content {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .cart-preview-header {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        background-color: #f9f9f9;
    }

    .cart-preview-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .cart-preview-items {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
        max-height: 450px;
    }

    .cart-preview-item {
        display: flex;
        gap: 12px;
        padding: 15px 15px;
        border-bottom: 1px solid #f5f5f5;
        transition: background-color 0.2s ease;
    }

    .cart-preview-item:hover {
        background-color: #fafafa;
    }

    .item-image {
        flex-shrink: 0;
        width: 60px;
        height: 60px;
        border-radius: 6px;
        overflow: hidden;
        background: #f5f5f5;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .item-name {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        line-height: 1.4;
        max-height: 42px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .item-brand {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .item-price {
        font-size: 12px;
        color: #666;
    }

    .item-bottom {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-top: 8px;
    }

    .item-quantity {
        font-size: 11px;
        color: #999;
    }

    .item-total {
        font-weight: 600;
        color: #333;
        font-size: 12px;
        text-align: right;
        margin-left: auto;
    }

    .item-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .qty-btn {
        width: 26px;
        height: 26px;
        border: 1px solid #ddd;
        background: #f5f5f5;
        color: #333;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        border-radius: 3px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .qty-btn:hover {
        background: #e0e0e0;
        border-color: #999;
    }

    .qty-btn:active {
        transform: scale(0.95);
    }

    .item-quantity {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        min-width: 20px;
        text-align: center;
    }

    .btn-delete {
        width: 26px;
        height: 26px;
        border: 1px solid #ffcccc;
        background: #ffe6e6;
        cursor: pointer;
        font-size: 12px;
        border-radius: 3px;
        transition: all 0.2s ease;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #cc0000;
    }

    .btn-delete i {
        font-size: 11px;
        color: #cc0000;
    }

    .btn-delete:hover {
        background: #ff9999;
        border-color: #cc0000;
    }

    .btn-delete:hover i {
        color: white;
    }

    .btn-delete:active {
        transform: scale(0.95);
    }

    .empty-cart {
        text-align: center;
        padding: 30px 15px;
        color: #999;
        font-size: 13px;
    }

    .cart-preview-footer {
        padding: 15px;
        border-top: 1px solid #f0f0f0;
        background-color: #f9f9f9;
        border-radius: 0 0 8px 8px;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    .btn-view-cart {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: #000;
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: background-color 0.3s ease;
    }

    .btn-view-cart:hover {
        background-color: #333;
        text-decoration: none;
        color: white;
    }

    /* Scrollbar personalizado para los items */
    .cart-preview-items::-webkit-scrollbar {
        width: 6px;
    }

    .cart-preview-items::-webkit-scrollbar-track {
        background: #f5f5f5;
    }

    .cart-preview-items::-webkit-scrollbar-thumb {
        background: #d0d0d0;
        border-radius: 3px;
    }

    .cart-preview-items::-webkit-scrollbar-thumb:hover {
        background: #b0b0b0;
    }

    /* Responsive para móviles */
    @media (max-width: 768px) {
        .cart-preview-dropdown {
            width: 90vw;
            max-width: 350px;
        }
    }
`;
document.head.appendChild(style);
