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
            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ productId, quantity: 1 })
            });
            if (response.ok) {
                return true;
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
        }
        return false;
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
                    this.addToCart(
                        productId,
                        productName,
                        productBrand,
                        productCategory,
                        productPrice,
                        productImage,
                        discount
                    );

                    // Mostrar notificación
                    //this.showNotification(`Producto añadido al carrito`);

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
    showNotification(message) {
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

// Inicializar el CartManager cuando se cargue la página
window.cartManager = new CartManager();