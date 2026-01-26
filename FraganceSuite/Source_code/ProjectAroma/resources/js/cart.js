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
        // Ejecutar tanto en DOMContentLoaded como en window.load para asegurar que funciona
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.attachAddToCartListeners());
        } else {
            this.attachAddToCartListeners();
        }
        
        // También ejecutar cuando todo cargue
        window.addEventListener('load', () => this.attachAddToCartListeners());
    }

    // Obtener carrito desde localStorage
    getCart() {
        try {
            const cart = localStorage.getItem(this.storageKey);
            return cart ? JSON.parse(cart) : {};
        } catch (error) {
            console.error('Error al obtener carrito:', error);
            return {};
        }
    }

    // Guardar carrito en localStorage
    saveCart(cart) {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(cart));
            console.log('Carrito guardado:', cart);
        } catch (error) {
            console.error('Error al guardar carrito:', error);
        }
    }

    // Agregar producto al carrito
    async addToCart(productId, productName, productBrand, productCategory, productPrice, productImage, discount = 0) {
        const cart = this.getCart();

        if (cart[productId]) {
            // Si el producto ya está, aumentar la cantidad
            cart[productId].quantity += 1;
        } else {
            // Si no está, agregarlo
            cart[productId] = {
                id: productId,
                name: productName,
                brand: productBrand,
                category: productCategory,
                price: parseFloat(productPrice),
                image: productImage,
                quantity: 1,
                discount: discount
            };
        }

        this.saveCart(cart);
        return true;
    }

    // Remover producto del carrito
    removeFromCart(productId) {
        const cart = this.getCart();
        delete cart[productId];
        this.saveCart(cart);
    }

    // Actualizar cantidad
    updateQuantity(productId, quantity) {
        const cart = this.getCart();
        if (cart[productId]) {
            if (quantity <= 0) {
                delete cart[productId];
            } else {
                cart[productId].quantity = quantity;
            }
            this.saveCart(cart);
        }
    }

    // Obtener cantidad de items en el carrito
    getCartCount() {
        const cart = this.getCart();
        return Object.values(cart).reduce((total, item) => total + item.quantity, 0);
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

                    // Mostrar modal de confirmación
                    const confirmed = await this.showConfirmationModal(productName, productPrice);
                    
                    if (confirmed) {
                        // Obtener datos completos del producto incluyendo descuento
                        const productData = await this.fetchProductData(productId);
                        const discount = productData?.discount || 0;

                        this.addToCart(
                            productId,
                            productName,
                            productBrand,
                            productCategory,
                            productPrice,
                            productImage,
                            discount
                        );

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

    // Modal de confirmación antes de agregar al carrito
    showConfirmationModal(productName, productPrice) {
        return new Promise((resolve) => {
            // Crear el modal
            const modal = document.createElement('div');
            modal.id = 'confirmAddToCartModal';
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
                    ¿Agregar al carrito?
                </p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button id="modalCancel" style="
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
                    <button id="modalConfirm" style="
                        padding: 10px 20px;
                        background: #000;
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
                        Sí, agregar
                    </button>
                </div>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            // Obtener referencias a los botones DESPUÉS de agregar al DOM (desde el modal)
            const confirmBtn = modal.querySelector('#modalConfirm');
            const cancelBtn = modal.querySelector('#modalCancel');

            console.log('Modal created, buttons found:', { confirmBtn, cancelBtn });

            // Validar que los botones existan
            if (!confirmBtn || !cancelBtn) {
                console.error('No se encontraron los botones del modal');
                return resolve(false);
            }

            // Agregar estilos de animación si no existen
            if (!document.getElementById('cartModalStyles')) {
                const style = document.createElement('style');
                style.id = 'cartModalStyles';
                style.textContent = `
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                        }
                        to {
                            opacity: 1;
                        }
                    }
                    @keyframes slideUp {
                        from {
                            transform: translateY(30px);
                            opacity: 0;
                        }
                        to {
                            transform: translateY(0);
                            opacity: 1;
                        }
                    }
                    @keyframes fadeOut {
                        from {
                            opacity: 1;
                        }
                        to {
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
            }

            // Manejar eventos de botones
            const closeModal = (result) => {
                modal.style.animation = 'fadeOut 0.3s ease-in-out';
                setTimeout(() => {
                    modal.remove();
                    resolve(result);
                }, 300);
            };

            confirmBtn.addEventListener('click', () => {
                this.showNotification(`Producto añadido al carrito`);
                closeModal(true);
            });
            cancelBtn.addEventListener('click', () => closeModal(false));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(false);
            });

            // Efecto hover en botones
            confirmBtn.addEventListener('mouseenter', function() {
                this.style.background = '#333';
            });
            confirmBtn.addEventListener('mouseleave', function() {
                this.style.background = '#000';
            });
            cancelBtn.addEventListener('mouseenter', function() {
                this.style.background = '#eee';
            });
            cancelBtn.addEventListener('mouseleave', function() {
                this.style.background = '#f5f5f5';
            });
        });
    }

    // Mostrar notificación
    showNotification(message) {
        // Crear notificación si no existe
        let notification = document.getElementById('cartNotification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'cartNotification';
            notification.style.cssText = `
                position: fixed;
                top: 100px;
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
}

// Inicializar el CartManager cuando se cargue la página
const cartManager = new CartManager();
