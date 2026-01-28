/**
 * PREVIEW DEL CARRITO - DROPDOWN AL PASAR EL MOUSE
 * Maneja la visualización del carrito en miniatura cuando el usuario pasa el mouse
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

        Object.values(cart).forEach(item => {
            const itemTotal = (item.price * item.quantity) - (item.discount || 0);
            total += itemTotal;

            html += `
                <div class="cart-preview-item">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-brand">${item.brand}</div>
                        <div class="item-price">₡${item.price.toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                        <div class="item-quantity">Qty: ${item.quantity}</div>
                    </div>
                    <div class="item-total">₡${(item.price * item.quantity).toLocaleString('es-CR', {minimumFractionDigits: 0})}</div>
                </div>
            `;
        });

        this.itemsContainer.innerHTML = html;
        this.totalElement.textContent = '₡' + total.toLocaleString('es-CR', {minimumFractionDigits: 0});
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
        width: 350px;
        max-height: 500px;
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
        max-height: 350px;
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

    .item-quantity {
        font-size: 11px;
        color: #999;
    }

    .item-total {
        flex-shrink: 0;
        font-weight: 600;
        color: #333;
        font-size: 12px;
        text-align: right;
        display: flex;
        align-items: center;
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
