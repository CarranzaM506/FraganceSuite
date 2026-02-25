/**
 * PREVIEW DEL CARRITO - DROPDOWN AL PASAR EL MOUSE
 * Maneja la visualización del carrito en miniatura cuando el usuario pasa el mouse
 * Con controles de cantidad y eliminación
 */

class CartPreview {
    constructor() {
        this.container = document.getElementById('cartIconContainer');
        this.preview = document.getElementById('cartPreview');
        this.itemsContainer = document.getElementById('cartPreviewItems');
        this.totalElement = document.getElementById('cartPreviewTotal');
        this.hideTimeout = null;
        
        if (this.container && this.preview) {
            this.init();
        }
    }

    // Inicializar event listeners
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.attachListeners());
        } else {
            this.attachListeners();
        }
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
    updatePreview() {
        const cart = this.getCart();
        
        if (Object.keys(cart).length === 0) {
            this.itemsContainer.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
            this.totalElement.textContent = '₡0';
            return;
        }

        // Construir HTML de los items
        let html = '';
        let total = 0;

        Object.entries(cart).forEach(([productId, item]) => {
            const itemTotal = (item.price * item.quantity) - (item.discount || 0);
            total += itemTotal;

            html += `
                <div class="cart-preview-item" data-product-id="${productId}">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-brand">${item.brand}</div>
                        <div class="item-price">₡${item.price.toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                        <div class="item-bottom">
                            <div class="item-controls">
                                <button class="qty-btn qty-minus" data-product-id="${productId}">−</button>
                                <span class="item-quantity">${item.quantity}</span>
                                <button class="qty-btn qty-plus" data-product-id="${productId}">+</button>
                                <button class="btn-delete" data-product-id="${productId}" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </div>
                            <div class="item-total">₡${(item.price * item.quantity).toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                        </div>
                    </div>
                </div>
            `;
        });

        this.itemsContainer.innerHTML = html;
        this.totalElement.textContent = '₡' + total.toLocaleString('es-CR', {minimumFractionDigits: 0});
        
        // Adjuntar listeners a los botones
        this.attachItemListeners();
    }

    // Adjuntar listeners a los botones de cantidad y eliminación
    attachItemListeners() {
        // Botones de aumentar cantidad
        this.itemsContainer.querySelectorAll('.qty-plus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.increaseQuantity(productId);
            });
        });

        // Botones de disminuir cantidad
        this.itemsContainer.querySelectorAll('.qty-minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.decreaseQuantity(productId);
            });
        });

        // Botones de eliminar
        this.itemsContainer.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.deleteProduct(productId);
            });
        });
    }

    // Aumentar cantidad
    increaseQuantity(productId) {
        const cart = this.getCart();
        if (cart[productId]) {
            cart[productId].quantity += 1;
            this.saveCart(cart);
        }
    }

    // Disminuir cantidad
    async decreaseQuantity(productId) {
        const cart = this.getCart();
        if (cart[productId]) {
            if (cart[productId].quantity <= 1) {
                // Si es la última unidad, pedir confirmación para eliminar
                await this.deleteProduct(productId);
            } else {
                cart[productId].quantity -= 1;
                this.saveCart(cart);
            }
        }
    }

    // Eliminar producto con confirmación
    async deleteProduct(productId) {
        const cart = this.getCart();
        if (cart[productId]) {
            const productName = cart[productId].name;
            const confirmed = await this.showDeleteConfirmationModal(productName);
            
            if (confirmed) {
                delete cart[productId];
                this.saveCart(cart);
                this.showDeleteNotification(`${productName} eliminado del carrito`);
            }
        }
    }

    // Modal de confirmación para eliminar producto
    showDeleteConfirmationModal(productName) {
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
        });
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

    // Obtener carrito desde localStorage
    getCart() {
        try {
            const cart = localStorage.getItem('aroma_cart');
            return cart ? JSON.parse(cart) : {};
        } catch (error) {
            console.error('Error al obtener carrito:', error);
            return {};
        }
    }

    // Guardar carrito en localStorage
    saveCart(cart) {
        try {
            localStorage.setItem('aroma_cart', JSON.stringify(cart));
            // Disparar evento personalizado
            window.dispatchEvent(new CustomEvent('cartUpdated'));
        } catch (error) {
            console.error('Error al guardar carrito:', error);
        }
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
`;
document.head.appendChild(style);
