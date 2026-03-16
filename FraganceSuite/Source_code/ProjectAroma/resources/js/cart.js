/**
 * LÓGICA DEL CARRITO CON LOCALSTORAGE
 * Maneja agregar, quitar, actualizar productos en el carrito
 */

class CartManager {
    constructor() {
        this.storageKey = 'aroma_cart';
        this.pendingProduct = null;
        this.init();
    }
    // Inicializar event listeners
    init() {
        // Ejecutar solo una vez cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.attachAddToCartListeners());
        } else {
            this.attachAddToCartListeners();
        }
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
        console.log('Carrito actualizado en DB');
    }

    // Agregar producto al carrito
    async addToCart(productId, productName, productBrand, productCategory, productPrice, productImage, discount = 0) {
        try {
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                this.showNotification('Error: CSRF token no encontrado. Recarga la página.', 'error');
                console.error('CSRF token meta tag not found');
                return;
            }

            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
                },
                body: JSON.stringify({ productId, quantity: 1 })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to add to cart');
            }
            
            // Actualizar el preview si está disponible
            if (window.cartPreview) {
                window.cartPreview.updatePreview();
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification(`Error al añadir al carrito: ${error.message}`, 'error');
        }
    }

    // Remover producto del carrito
    async removeFromCart(productId) {
        try {
            const response = await fetch('/api/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ productId })
            });
            if (response.ok) {
                return true;
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
        }
        return false;
    }

    // Actualizar cantidad
    async updateQuantity(productId, quantity) {
        try {
            const response = await fetch('/api/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ productId, quantity })
            });
            if (response.ok) {
                return true;
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
        }
        return false;
    }

    // Obtener cantidad de items en el carrito
    async getCartCount() {
        try {
            const response = await fetch('/api/cart');
            if (response.ok) {
                const data = await response.json();
                return data.items.reduce((total, item) => total + item.quantity, 0);
            }
        } catch (error) {
            console.error('Error getting cart count:', error);
        }
        return 0;
    }

    // Adjuntar listeners a botones de agregar al carrito
    attachAddToCartListeners() {
        const addCartButtons = document.querySelectorAll('.add-cart-icon');
        console.log('Botones encontrados:', addCartButtons.length);
        
        addCartButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                const productId = button.getAttribute('data-product');
                const productCard = button.closest('.product-card');

                if (productCard && productId) {
                    // Obtener datos del producto de la tarjeta
                    const productImage = productCard.querySelector('img')?.src || '';
                    const productName = productCard.querySelector('.product-name')?.textContent || 'Producto';
                    const productBrand = productCard.querySelector('.product-brand')?.textContent || '';
                    const productCategory = productCard.querySelector('.product-category')?.textContent || '';
                    const productPrice = productCard.querySelector('.product-price')?.textContent?.replace('₡', '').replace(/,/g, '').trim() || '0';

                    console.log('Producto capturado:', { productId, productName, productPrice });

                    // Obtener datos completos del producto incluyendo descuento
                    const productData = await this.fetchProductData(productId);
                    const discount = productData?.discount || 0;

                    // Agregar directamente al carrito
                    await this.addToCart(
                        productId,
                        productName,
                        productBrand,
                        productCategory,
                        productPrice,
                        productImage,
                        discount
                    );

                    // Mostrar notificación
                    this.showNotification(`Producto añadido al carrito`);

                    // Animación visual
                    this.animateAddToCart(button);
                } else {
                    console.warn('No se pudo obtener los datos del producto');
                }
            });
        });
    }

    // Obtener datos del producto desde la API
    async fetchProductData(productId) {
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

    // Animación al agregar al carrito
    animateAddToCart(button) {
        button.style.transform = 'scale(1.2)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 200);
    }

    // Mostrar notificación
    showNotification(message, type = 'success') {
        // Crear notificación si no existe
        let notification = document.getElementById('cartNotification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'cartNotification';
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #000;
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

            // Agregar estilos de animación
            const style = document.createElement('style');
            style.textContent = `
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
            `;
            document.head.appendChild(style);
        }

        notification.textContent = message;
        notification.style.animation = 'slideIn 0.3s ease-in-out';
        notification.style.display = 'block';

        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in-out';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 3000);
    }

    // Actualizar preview del carrito
    updateCartPreview() {
        // Función removida
    }

    // Inicializar eventos del preview
    initCartPreview() {
        // Función removida
    }
}

// ============================================
// GESTOR DE PÁGINA DE CARRITO COMPLETO
// ============================================
class CartPageManager {
    constructor() {
        this.container = document.getElementById('cartItemsContainer');
        this.emptyMessage = document.getElementById('emptyCartMessage');
        this.subtotalEl = document.getElementById('subtotalPrice');
        this.discountEl = document.getElementById('discountAmount');
        this.totalEl = document.getElementById('totalPrice');
        this.checkoutBtn = document.getElementById('checkoutBtn');
        this.listenersAttached = false;
        
        // Solo inicializar si estamos en la página del carrito
        if (this.container) {
            this.init();
        }
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.renderCart();
                this.attachListeners();
            });
        } else {
            this.renderCart();
            this.attachListeners();
        }
    }

    async renderCart() {
        if (!this.container) {
            console.warn('Container not found');
            return;
        }

        try {
            const response = await fetch('/api/cart');
            if (!response.ok) {
                console.error('API error:', response.status);
                throw new Error('Error fetching cart');
            }
            
            const data = await response.json();
            console.log('Cart data:', data);
            const items = data.items || [];

            if (items.length === 0) {
                this.container.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-bag"></i>
                        <p>Tu carrito está vacío</p>
                        <a href="/catalog" class="btn-continue-shopping">Continuar comprando</a>
                    </div>
                `;
                if (this.checkoutBtn) this.checkoutBtn.disabled = true;
                this.updateTotals();
                return;
            }

            if (this.checkoutBtn) this.checkoutBtn.disabled = false;
            this.container.innerHTML = '';

            items.forEach(item => {
                const price = parseFloat(item.price);
                const qty = parseInt(item.quantity);
                const discount = parseFloat(item.discount || 0);
                const subtotal = price * qty;
                const discountAmount = subtotal * (discount / 100);
                const total = subtotal - discountAmount;

                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.setAttribute('data-product-id', item.id);

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
                            <span class="qty-value">${qty}</span>
                            <button class="qty-btn plus-btn" data-product-id="${item.id}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="cart-item-price">
                        ${discount > 0 ? `
                            <div class="price-with-discount">
                                <span class="original-price" style="text-decoration: line-through; color: #999; font-size: 0.9em;">₡${price.toFixed(2)}</span>
                                <div class="final-price" style="color: #333; font-size: 1.2em; font-weight: bold;">₡${(price * (1 - discount / 100)).toFixed(2)}</div>
                            </div>
                        ` : `
                            <div class="regular-price" style="color: #333; font-size: 1.1em; font-weight: bold;">₡${price.toFixed(2)}</div>
                        `}
                        <div class="item-total" data-base-price="${price}" data-discount="${discount}" style="margin-top: 8px; font-size: 0.95em; color: #666;">Total: ₡${total.toFixed(2)}</div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="remove-btn" data-product-id="${item.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                this.container.appendChild(cartItem);
            });

            this.updateTotals();
        } catch (error) {
            console.error('Error rendering cart:', error);
            this.container.innerHTML = '<p style="color: red;">Error al cargar el carrito</p>';
        }
    }

    attachListeners() {
        if (this.listenersAttached || !this.container) return;
        this.listenersAttached = true;

        this.container.addEventListener('click', async (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            const cartItem = button.closest('.cart-item');
            if (!cartItem) return;

            const productId = parseInt(cartItem.getAttribute('data-product-id'));
            const qtySpan = cartItem.querySelector('.qty-value');

            if (button.classList.contains('minus-btn')) {
                await this.decreaseQty(productId, qtySpan, cartItem);
            } else if (button.classList.contains('plus-btn')) {
                await this.increaseQty(productId, qtySpan, cartItem);
            } else if (button.classList.contains('remove-btn')) {
                await this.removeItem(productId, cartItem);
            }
        });
    }

    async decreaseQty(productId, qtySpan, cartItem) {
        const currentQty = parseInt(qtySpan.textContent);
        if (currentQty <= 1) {
            cartItem.querySelector('.remove-btn').click();
            return;
        }

        const newQty = currentQty - 1;
        qtySpan.textContent = newQty;
        this.updateItemTotals(cartItem);
        this.updateTotals();

        const success = await window.cartManager.updateQuantity(productId, newQty);
        if (!success) {
            qtySpan.textContent = currentQty;
            this.updateItemTotals(cartItem);
            this.updateTotals();
        }
    }

    async increaseQty(productId, qtySpan, cartItem) {
        const currentQty = parseInt(qtySpan.textContent);
        const newQty = currentQty + 1;
        
        qtySpan.textContent = newQty;
        this.updateItemTotals(cartItem);
        this.updateTotals();

        const success = await window.cartManager.updateQuantity(productId, newQty);
        if (!success) {
            qtySpan.textContent = currentQty;
            this.updateItemTotals(cartItem);
            this.updateTotals();
        }
    }

    async removeItem(productId, cartItem) {
        const productName = cartItem.querySelector('.item-name').textContent;
        const confirmed = await this.showConfirmModal(productName);
        
        if (!confirmed) return;

        cartItem.style.opacity = '0';
        cartItem.style.transition = 'opacity 0.3s';
        
        setTimeout(() => cartItem.remove(), 300);
        this.updateTotals();

        const success = await window.cartManager.removeFromCart(productId);
        if (success) {
            this.showDeleteNotification(`${productName} eliminado`);
            if (document.querySelectorAll('.cart-item').length === 0) {
                this.renderCart();
            }
            if (window.cartPreview) {
                window.cartPreview.updatePreview();
            }
        }
    }

    updateItemTotals(item) {
        const qtySpan = item.querySelector('.qty-value');
        const qty = parseInt(qtySpan.textContent);
        const itemTotal = item.querySelector('.item-total');
        const basePrice = parseFloat(itemTotal.getAttribute('data-base-price'));
        const discount = parseFloat(itemTotal.getAttribute('data-discount')) || 0;
        
        const finalPrice = discount > 0 ? basePrice * (1 - discount / 100) : basePrice;
        const total = finalPrice * qty;
        
        itemTotal.textContent = `Total: ₡${total.toFixed(2)}`;
        
        if (discount > 0) {
            const finalPriceEl = item.querySelector('.final-price');
            if (finalPriceEl) {
                finalPriceEl.textContent = `₡${finalPrice.toFixed(2)}`;
            }
        }
    }

    updateTotals() {
        let subtotal = 0;
        let totalDiscount = 0;

        document.querySelectorAll('.cart-item').forEach(item => {
            const qty = parseInt(item.querySelector('.qty-value').textContent);
            const itemTotal = item.querySelector('.item-total');
            const basePrice = parseFloat(itemTotal.getAttribute('data-base-price'));
            const discount = parseFloat(itemTotal.getAttribute('data-discount')) || 0;
            
            const itemSubtotal = basePrice * qty;
            subtotal += itemSubtotal;
            totalDiscount += itemSubtotal * (discount / 100);
        });

        const total = subtotal - totalDiscount;
        
        if (this.subtotalEl) this.subtotalEl.textContent = '₡' + subtotal.toFixed(2);
        if (this.discountEl) this.discountEl.textContent = '-₡' + totalDiscount.toFixed(2);
        if (this.totalEl) this.totalEl.textContent = '₡' + total.toFixed(2);
    }

    async showConfirmModal(productName) {
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
            `;

            modal.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    max-width: 300px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                ">
                    <p style="margin: 0 0 25px 0; color: #666; font-size: 14px;">¿Eliminar del carrito?</p>
                    <div style="display: flex; gap: 10px;">
                        <button class="modal-cancel" style="
                            flex: 1;
                            padding: 10px;
                            background: #f5f5f5;
                            color: #666;
                            border: 1px solid #ddd;
                            cursor: pointer;
                            font-weight: 600;
                            font-size: 12px;
                        ">No</button>
                        <button class="modal-confirm" style="
                            flex: 1;
                            padding: 10px;
                            background: #cc0000;
                            color: white;
                            border: none;
                            cursor: pointer;
                            font-weight: 600;
                            font-size: 12px;
                        ">Sí, eliminar</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            modal.querySelector('.modal-confirm').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });

            modal.querySelector('.modal-cancel').addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    resolve(false);
                }
            });
        });
    }

    showDeleteNotification(message) {
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
            `;
            document.body.appendChild(notification);
        }

        notification.textContent = message;
        notification.style.display = 'block';

        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
}

// Inicializar CartPageManager si estamos en la página del carrito
window.cartPageManager = new CartPageManager();

// Inicializar el CartManager cuando se cargue la página
window.cartManager = new CartManager();