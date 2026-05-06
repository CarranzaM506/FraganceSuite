/**
 * PREVIEW DEL CARRITO - DROPDOWN AL PASAR EL MOUSE
 * Maneja la visualización del carrito en miniatura cuando el usuario pasa el mouse
 * Con controles de cantidad y eliminación
 */

class CartPreview {
    constructor() {
        this.listenersAttached = false;
        this.cachedCart = {}; // Cache del carrito para respuesta inmediata
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        this.container = document.getElementById('cartIconContainer');
        this.preview = document.getElementById('cartPreview');
        this.itemsContainer = document.getElementById('cartPreviewItems');
        this.totalElement = document.getElementById('cartPreviewTotal');
        this.hideTimeout = null;
        
        if (!this.container || !this.preview) {
            return;
        }
        
        // Detectar si es móvil (por ancho de pantalla)
        this.esMovil = window.matchMedia('(max-width: 768px)').matches;
        
        // Solo cargar caché y listeners si es desktop
        if (!this.esMovil) {
            this.refreshCache();
            this.attachListeners();
            this.attachItemListeners();
        }
    }

    // Refrescar la caché del carrito
    async refreshCache() {
        try {
            const response = await fetch('/api/cart');
            if (response.ok) {
                const data = await response.json();
                this.cachedCart = data.items.reduce((acc, item) => {
                    acc[item.id] = item;
                    return acc;
                }, {});
            }
        } catch (error) {
            console.error('[CartPreview] Error refreshing cache:', error);
        }
    }

    attachListeners() {
        // SOLO para desktop: usar hover
        this.container.addEventListener('mouseenter', () => this.showPreview());
        this.container.addEventListener('mouseleave', () => this.hidePreview());
        
        window.addEventListener('cartUpdated', () => {
            this.refreshCache();
            this.updatePreview();
        });
    }

    showPreview() {
        clearTimeout(this.hideTimeout);
        this.updatePreview();
        this.preview.style.display = 'block';
        this.preview.style.animation = 'none';
        setTimeout(() => {
            this.preview.style.animation = 'fadeIn 0.2s ease-in-out';
        }, 10);
    }

    hidePreview() {
        this.hideTimeout = setTimeout(() => {
            this.preview.style.animation = 'fadeOut 0.2s ease-in-out';
            setTimeout(() => {
                this.preview.style.display = 'none';
            }, 200);
        }, 200);
    }

    updatePreview() {
        const cart = this.cachedCart;
        
        if (Object.keys(cart).length === 0) {
            this.itemsContainer.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
            this.totalElement.textContent = '₡0';
            return;
        }

        let html = '';
        let total = 0;

        Object.entries(cart).forEach(([productId, item]) => {
            const price = item.price;
            const qty = item.quantity;
            const discountPercent = item.discount || 0;
            const stock = parseInt(item.stock ?? -1);
            const disablePlus = stock > -1 && qty >= stock;
            const finalPrice = discountPercent > 0 ? price * (1 - discountPercent / 100) : price;
            const itemTotal = finalPrice * qty;
            total += itemTotal;

            html += `
                <div class="cart-preview-item" data-product-id="${productId}" data-stock="${stock}">
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
                                <button class="qty-btn qty-plus" data-product-id="${productId}" ${disablePlus ? 'disabled' : ''}>+</button>
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
    }

    attachItemListeners() {
        if (this.listenersAttached) return;
        if (!this.itemsContainer) return;
        
        this.listenersAttached = true;
        
        this.itemsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const productId = parseInt(btn.dataset.productId);
            if (!productId) return;

            if (btn.classList.contains('qty-plus')) {
                this.increaseQuantity(productId);
            } else if (btn.classList.contains('qty-minus')) {
                this.decreaseQuantity(productId);
            } else if (btn.classList.contains('btn-delete')) {
                this.deleteProduct(productId);
            }
        });
    }

    async updateQuantityInCache(productId, newQty) {
        if (this.cachedCart[productId]) {
            if (newQty <= 0) {
                delete this.cachedCart[productId];
            } else {
                this.cachedCart[productId].quantity = newQty;
            }
            this.updatePreview();
        }
    }

    async increaseQuantity(productId) {
        const item = this.itemsContainer?.querySelector(`.cart-preview-item[data-product-id="${productId}"]`);
        if (!item) return;
        
        const stock = parseInt(item.getAttribute('data-stock') || '-1');
        const qtySpan = item.querySelector('.item-quantity');
        const currentQty = parseInt(qtySpan.textContent);
        
        if (stock > -1 && currentQty + 1 > stock) return;
        
        const newQty = currentQty + 1;
        
        qtySpan.textContent = newQty;
        const plusBtn = item.querySelector('.qty-plus');
        if (plusBtn && stock > -1) {
            plusBtn.disabled = newQty >= stock;
        }
        
        await this.updateQuantityInCache(productId, newQty);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const response = await fetch('/api/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ productId, quantity: newQty })
            });
            
            if (!response.ok) {
                qtySpan.textContent = currentQty;
                await this.updateQuantityInCache(productId, currentQty);
            } else {
                window.dispatchEvent(new CustomEvent('cartUpdated'));
            }
        } catch (error) {
            console.error('Error:', error);
            qtySpan.textContent = currentQty;
            await this.updateQuantityInCache(productId, currentQty);
        }
    }

    async decreaseQuantity(productId) {
        const item = this.itemsContainer?.querySelector(`.cart-preview-item[data-product-id="${productId}"]`);
        if (!item) return;
        
        const qtySpan = item.querySelector('.item-quantity');
        const currentQty = parseInt(qtySpan.textContent);
        
        if (currentQty <= 1) {
            await this.deleteProduct(productId);
            return;
        }
        
        const newQty = currentQty - 1;
        
        qtySpan.textContent = newQty;
        const plusBtn = item.querySelector('.qty-plus');
        const stock = parseInt(item.getAttribute('data-stock') || '-1');
        if (plusBtn && stock > -1) {
            plusBtn.disabled = newQty >= stock;
        }
        
        await this.updateQuantityInCache(productId, newQty);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const response = await fetch('/api/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ productId, quantity: newQty })
            });
            
            if (!response.ok) {
                qtySpan.textContent = currentQty;
                await this.updateQuantityInCache(productId, currentQty);
            } else {
                window.dispatchEvent(new CustomEvent('cartUpdated'));
            }
        } catch (error) {
            console.error('Error:', error);
            qtySpan.textContent = currentQty;
            await this.updateQuantityInCache(productId, currentQty);
        }
    }

    async deleteProduct(productId) {
        const item = this.itemsContainer?.querySelector(`.cart-preview-item[data-product-id="${productId}"]`);
        if (!item) return;
        
        item.style.opacity = '0';
        item.style.transition = 'opacity 0.2s';
        
        delete this.cachedCart[productId];
        this.updatePreview();
        
        setTimeout(() => item.remove(), 200);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const response = await fetch('/api/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ productId })
            });
            
            if (response.ok) {
                this.showDeleteNotification('Producto eliminado');
                window.dispatchEvent(new CustomEvent('cartUpdated'));
            }
        } catch (error) {
            console.error('[CartPreview] Exception:', error);
        }
    }

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

    async getCart() {
        return this.cachedCart;
    }
}

// Inicializar el CartPreview solo en desktop
window.cartPreview = new CartPreview();

// Agregar estilos (sin border radius)
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
        border-radius: 0px !important;
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
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        background-color: #f9f9f9;
    }

    .cart-preview-header h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    .cart-preview-items {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
        max-height: 350px;
    }

    .cart-preview-item {
        display: flex;
        gap: 10px;
        padding: 12px;
        border-bottom: 1px solid #f5f5f5;
        transition: background-color 0.2s ease;
    }

    .cart-preview-item:hover {
        background-color: #fafafa;
    }

    .item-image {
        flex-shrink: 0;
        width: 55px;
        height: 55px;
        border-radius: 0px !important;
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
        font-size: 12px;
        font-weight: 600;
        color: #333;
        line-height: 1.3;
        max-height: 36px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .item-brand {
        font-size: 10px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .item-price {
        font-size: 11px;
        color: #666;
    }

    .item-bottom {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 6px;
    }

    .item-quantity {
        font-size: 10px;
        color: #999;
    }

    .item-total {
        font-weight: 600;
        color: #333;
        font-size: 11px;
        text-align: right;
        margin-left: auto;
    }

    .item-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .qty-btn {
        width: 22px;
        height: 22px;
        border: 1px solid #ddd;
        background: #f5f5f5;
        color: #333;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        border-radius: 0px !important;
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

    .qty-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f0f0f0;
        border-color: #eee;
    }

    .qty-btn:disabled:hover {
        background: #f0f0f0;
        border-color: #eee;
    }

    .item-quantity {
        font-size: 12px;
        font-weight: 600;
        color: #333;
        min-width: 18px;
        text-align: center;
    }

    .btn-delete {
        width: 22px;
        height: 22px;
        border: 1px solid #ffcccc;
        background: #ffe6e6;
        cursor: pointer;
        font-size: 10px;
        border-radius: 0px !important;
        transition: all 0.2s ease;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #cc0000;
    }

    .btn-delete i {
        font-size: 10px;
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
        padding: 20px 15px;
        color: #999;
        font-size: 12px;
    }

    .cart-preview-footer {
        padding: 12px;
        border-top: 1px solid #f0f0f0;
        background-color: #f9f9f9;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }

    .btn-view-cart {
        display: block;
        width: 100%;
        padding: 8px;
        background-color: #000;
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 0px !important;
        font-size: 11px;
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
    }

    .cart-preview-items::-webkit-scrollbar-thumb:hover {
        background: #b0b0b0;
    }

    /* Responsive para móviles - ocultar el preview */
    @media (max-width: 768px) {
        .cart-preview-dropdown {
            display: none !important;
        }
    }
`;
document.head.appendChild(style);