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
                return false;
            }

            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
                },
                body: JSON.stringify({ productId, quantity: 1 })
            });
            
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return false;
            }
            if (!response.ok) {
                const errorData = await response.json();
                const errorMessage = (errorData?.error || '').toLowerCase();
                if (errorMessage.includes('no hay') && errorMessage.includes('unidades')) {
                    return false;
                }
                throw new Error(errorData.error || 'Failed to add to cart');
            }
            
            // Actualizar el preview si está disponible
            if (window.cartPreview) {
                window.cartPreview.updatePreview();
            }
            return true;
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification(`Error al añadir al carrito: ${error.message}`, 'error');
            return false;
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
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return false;
            }
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
            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return false;
            }
            if (response.ok) return true;
            const errorData = await response.json();
            if (errorData?.error) {
                this.showNotification(errorData.error, 'error');
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
                    const stock = parseInt(productData?.stock ?? -1);

                    if (stock === 0) {
                        return;
                    }

                    if (stock > -1) {
                        const cart = await this.getCart();
                        const currentQty = cart[productId]?.quantity || 0;
                        if (currentQty + 1 > stock) {
                            return;
                        }
                    }

                    // Agregar directamente al carrito
                    const added = await this.addToCart(
                        productId,
                        productName,
                        productBrand,
                        productCategory,
                        productPrice,
                        productImage,
                        discount
                    );

                    if (added) {
                        // Mostrar notificación
                        this.showNotification(`Producto añadido al carrito`);

                        // Animación visual
                        this.animateAddToCart(button);
                    }
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
        this.listenersAttached = false;
        this.codeDiscountPercent = 0;
        this.appliedCode = null;
        
        // Siempre intentar inicializar en DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            // Si el DOM ya está listo cuando se carga el script
            this.init();
        }
    }

    init() {
        // Re-obtener elementos cada vez que se inicializa
        this.container = document.getElementById('cartItemsContainer');
        this.emptyMessage = document.getElementById('emptyCartMessage');
        this.subtotalEl = document.getElementById('subtotalPrice');
        this.discountEl = document.getElementById('discountAmount');
        this.totalEl = document.getElementById('totalPrice');
        this.checkoutBtn = document.getElementById('checkoutBtn');
        this.promoInput = document.getElementById('promoCodeInput');
        this.promoBtn = document.getElementById('applyPromoBtn');
        this.promoMessage = document.getElementById('promoCodeMessage');
        
        // Solo proceder si estamos en la página del carrito
        if (!this.container) {
            console.log('[CartPageManager] No cart page detected - skipping initialization');
            return;
        }
        
        console.log('[CartPageManager] Cart page detected - initializing');
        console.log('[CartPageManager] Elements:', {
            container: !!this.container,
            subtotal: !!this.subtotalEl,
            discount: !!this.discountEl,
            total: !!this.totalEl,
            checkout: !!this.checkoutBtn
        });
        this.renderCart();
        this.attachListeners();
    }

    async renderCart() {
        if (!this.container) {
            console.log('[renderCart] Container not found, aborting');
            return;
        }

        console.log('[renderCart] Starting to fetch cart data...');
        try {
            const response = await fetch('/api/cart');
            console.log('[renderCart] Response status:', response.status, response.statusText);
            
            const data = await response.json();
            console.log('[renderCart] Cart API response:', data);
            
            const items = data.items || [];
            console.log('[renderCart] Number of items:', items.length);

            if (items.length === 0) {
                console.log('[renderCart] Cart is empty, showing empty message');
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

            console.log('[renderCart] Rendering', items.length, 'items');
            if (this.checkoutBtn) this.checkoutBtn.disabled = false;
            
            let itemsHTML = `
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Precio</th>
                            <th>Descuento</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            items.forEach((item, index) => {
                const price = parseFloat(item.price);
                const qty = parseInt(item.quantity);
                const discount = parseFloat(item.discount || 0);
                console.log(`[renderCart] Item ${index + 1}:`, {
                    id: item.id,
                    name: item.name,
                    price,
                    qty,
                    discount
                });

                const finalPrice = discount > 0 ? price * (1 - discount / 100) : price;
                const lineTotal = finalPrice * qty;

                itemsHTML += `
                    <tr class="cart-item" data-product-id="${item.id}" data-stock="${item.stock ?? ''}">
                        <td class="product-cell">
                            <div class="product-wrapper">
                                ${item.image ? `<img src="${item.image}" alt="${item.name}" class="product-image">` : `<div class="placeholder"><i class="fas fa-wine-bottle"></i></div>`}
                                <div class="product-info">
                                    <h4 class="item-name">${item.name}</h4>
                                    <p class="item-category">${item.category || ''}</p>
                                </div>
                            </div>
                        </td>
                        <td class="brand-cell">${item.brand || '-'}</td>
                        <td class="price-cell">
                            ${discount > 0 ? `
                                <div class="price-wrapper">
                                    <span class="original-price">₡${price.toFixed(2)}</span>
                                    <span class="final-price">₡${finalPrice.toFixed(2)}</span>
                                </div>
                            ` : `
                                <span class="final-price">₡${price.toFixed(2)}</span>
                            `}
                        </td>
                        <td class="discount-cell">
                            ${discount > 0 ? `<span class="discount-badge">${discount}%</span>` : '<span class="no-discount">-</span>'}
                        </td>
                        <td class="quantity-cell">
                            <div class="item-quantity">
                                <button class="qty-btn minus-btn" data-product-id="${item.id}" title="Disminuir cantidad">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="qty-value">${qty}</span>
                                <button class="qty-btn plus-btn" data-product-id="${item.id}" title="Aumentar cantidad">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </td>
                        <td class="total-cell" data-base-price="${price}" data-discount="${discount}">
                            <strong>₡${lineTotal.toFixed(2)}</strong>
                        </td>
                        <td class="actions-cell">
                            <button class="remove-btn" data-product-id="${item.id}" title="Eliminar del carrito">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            itemsHTML += `
                    </tbody>
                </table>
            `;

            this.container.innerHTML = itemsHTML;
            console.log('[renderCart] All items rendered successfully');
            this.updateTotals();
        } catch (error) {
            console.error('[renderCart] Error:', error);
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

        if (this.promoBtn) {
            this.promoBtn.addEventListener('click', () => this.applyPromoCode());
        }
        if (this.promoInput) {
            this.promoInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.applyPromoCode();
                }
            });
        }
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
        const stock = parseInt(cartItem.getAttribute('data-stock') || '-1');
        if (stock > -1 && currentQty + 1 > stock) {
            if (window.cartManager) {
                window.cartManager.showNotification('No hay suficientes unidades disponibles', 'error');
            }
            return;
        }
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

    async applyPromoCode() {
        if (!this.promoInput || !this.promoBtn) return;

        const code = this.promoInput.value.trim();
        if (!code) {
            this.setPromoMessage('Ingresa un código válido', 'error');
            return;
        }

        try {
            this.promoBtn.disabled = true;
            const response = await fetch('/api/cart/apply-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code })
            });

            if (response.redirected || response.status === 401) {
                window.location.href = response.url || '/login';
                return;
            }

            const data = await response.json();
            if (!response.ok) {
                this.codeDiscountPercent = 0;
                this.appliedCode = null;
                this.setPromoMessage(data.error || 'Código inválido', 'error');
                this.updateTotals();
                return;
            }

            this.codeDiscountPercent = parseFloat(data.value || 0);
            this.appliedCode = data.code || code;
            this.setPromoMessage(`Código aplicado: ${this.appliedCode} (-${this.codeDiscountPercent}%)`, 'success');
            this.updateTotals();
        } catch (error) {
            console.error('Error applying promo code:', error);
            this.setPromoMessage('Error al validar el código', 'error');
        } finally {
            this.promoBtn.disabled = false;
        }
    }

    setPromoMessage(message, type = '') {
        if (!this.promoMessage) return;
        this.promoMessage.textContent = message;
        this.promoMessage.classList.remove('success', 'error');
        if (type) {
            this.promoMessage.classList.add(type);
        }
    }

    updateItemTotals(item) {
        const qtySpan = item.querySelector('.qty-value');
        const qty = parseInt(qtySpan.textContent);
        const itemTotal = item.querySelector('.total-cell');
        if (!itemTotal) {
            return;
        }
        const basePrice = parseFloat(itemTotal.getAttribute('data-base-price'));
        const discount = parseFloat(itemTotal.getAttribute('data-discount')) || 0;
        
        const finalPrice = discount > 0 ? basePrice * (1 - discount / 100) : basePrice;
        const total = finalPrice * qty;
        
        const totalText = `₡${total.toFixed(2)}`;
        const totalStrong = itemTotal.querySelector('strong');
        if (totalStrong) {
            totalStrong.textContent = totalText;
        } else {
            itemTotal.textContent = totalText;
        }
        
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
            const itemTotal = item.querySelector('.total-cell');
            if (!itemTotal) {
                return;
            }
            const basePrice = parseFloat(itemTotal.getAttribute('data-base-price'));
            const discount = parseFloat(itemTotal.getAttribute('data-discount')) || 0;
            
            const itemSubtotal = basePrice * qty;
            subtotal += itemSubtotal;
            totalDiscount += itemSubtotal * (discount / 100);
        });

        const totalAfterProductDiscount = subtotal - totalDiscount;
        const codeDiscountPercent = this.codeDiscountPercent || 0;
        const codeDiscountAmount = totalAfterProductDiscount * (codeDiscountPercent / 100);
        const total = totalAfterProductDiscount - codeDiscountAmount;
        const combinedDiscount = totalDiscount + codeDiscountAmount;
        
        if (this.subtotalEl) this.subtotalEl.textContent = '₡' + subtotal.toFixed(2);
        if (this.discountEl) this.discountEl.textContent = '-₡' + combinedDiscount.toFixed(2);
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
