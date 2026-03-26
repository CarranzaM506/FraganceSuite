// public/js/favorites.js
(function() {
    'use strict';

    const FAVORITES_CONFIG = {
        toastDuration: 3000,
        loginRedirectDelay: 2000
    };

    function initFavorites() {
        const wishlistIcons = document.querySelectorAll('.wishlist-icon');
        
        wishlistIcons.forEach(icon => {
            icon.removeEventListener('click', handleFavoriteClick);
            icon.addEventListener('click', handleFavoriteClick);
            
            const productId = icon.dataset.product;
            if (productId && window.isAuthenticated) {
                checkFavoriteStatus(icon, productId);
            }
        });

        const wishlistBtn = document.getElementById('wishlistBtn');
        if (wishlistBtn) {
            wishlistBtn.removeEventListener('click', handleDetailFavoriteClick);
            wishlistBtn.addEventListener('click', handleDetailFavoriteClick);
            
            const productId = wishlistBtn.dataset.product;
            if (productId && window.isAuthenticated) {
                checkDetailFavoriteStatus(wishlistBtn, productId);
            }
        }
    }

    async function handleFavoriteClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const icon = this;
        const productId = icon.dataset.product;
        const heartIcon = icon.querySelector('i');
        const isFavoritesPage = document.body.classList.contains('favorites-body');
        
        if (!productId) {
            return;
        }

        if (isFavoritesPage && (icon.classList.contains('active') || heartIcon.classList.contains('fas'))) {
            const productName = icon.closest('.product-card')?.querySelector('.product-name')?.textContent?.trim() || 'este producto';
            const confirmed = await showRemoveConfirm(productName);
            if (!confirmed) {
                return;
            }
        }

        icon.style.pointerEvents = 'none';
        
        try {
            const response = await fetch('/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ productId: productId })
            });

            if (response.redirected || response.status === 401 || (response.url && response.url.includes('/login'))) {
                showNotification('Debes iniciar sesión para guardar favoritos', 'info');
                setTimeout(() => {
                    window.location.href = '/login';
                }, FAVORITES_CONFIG.loginRedirectDelay);
                return;
            }

            const data = await response.json();
            
            if (data.success) {
                heartIcon.className = '';
                
                if (data.status === 'added') {
                    heartIcon.classList.add('fas', 'fa-heart');
                    icon.classList.add('active');
                    showNotification('✓ Añadido a favoritos', 'success');
                } else {
                    heartIcon.classList.add('far', 'fa-heart');
                    icon.classList.remove('active');
                    showNotification('Eliminado de favoritos', 'info');
                }
                
                updateAllRelatedElements(productId, data.status === 'added');

                if (isFavoritesPage && data.status === 'removed') {
                    removeFavoriteCard(icon);
                }
            } else {
                showNotification(data.error || 'Error al procesar', 'error');
                restoreIconState(icon, productId);
            }
        } catch (error) {
            showNotification('Debes iniciar sesión para guardar favoritos', 'error');
            setTimeout(() => {
                window.location.href = '/login';
            }, FAVORITES_CONFIG.loginRedirectDelay);
            restoreIconState(icon, productId);
        } finally {
            setTimeout(() => {
                icon.style.pointerEvents = 'auto';
            }, 500);
        }
    }

    async function handleDetailFavoriteClick(e) {
        e.preventDefault();
        
        const btn = this;
        const productId = btn.dataset.product;
        const heartIcon = btn.querySelector('i');

        btn.disabled = true;
        
        try {
            const response = await fetch('/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ productId: productId })
            });

            if (response.redirected || response.status === 401 || (response.url && response.url.includes('/login'))) {
                showNotification('Debes iniciar sesión para guardar favoritos', 'info');
                setTimeout(() => {
                    window.location.href = '/login';
                }, FAVORITES_CONFIG.loginRedirectDelay);
                return;
            }

            const data = await response.json();
            
            if (data.success) {
                heartIcon.className = '';
                
                if (data.status === 'added') {
                    heartIcon.classList.add('fas', 'fa-heart');
                    btn.classList.add('active');
                    showNotification('✓ Añadido a favoritos', 'success');
                } else {
                    heartIcon.classList.add('far', 'fa-heart');
                    btn.classList.remove('active');
                    showNotification('Eliminado de favoritos', 'info');
                }
                
                updateAllIcons(productId, data.status === 'added');
            } else {
                showNotification(data.error || 'Error al procesar', 'error');
            }
        } catch (error) {
            showNotification('Debes iniciar sesión para guardar favoritos', 'error');
            setTimeout(() => {
                window.location.href = '/login';
            }, FAVORITES_CONFIG.loginRedirectDelay);
        } finally {
            btn.disabled = false;
        }
    }

    function updateAllIcons(productId, isFavorite) {
        const icons = document.querySelectorAll(`.wishlist-icon[data-product="${productId}"]`);
        icons.forEach(icon => {
            const heartIcon = icon.querySelector('i');
            heartIcon.className = '';
            
            if (isFavorite) {
                heartIcon.classList.add('fas', 'fa-heart');
                icon.classList.add('active');
            } else {
                heartIcon.classList.add('far', 'fa-heart');
                icon.classList.remove('active');
            }
        });
    }

    function updateAllRelatedElements(productId, isFavorite) {
        updateAllIcons(productId, isFavorite);
        
        const detailBtn = document.getElementById('wishlistBtn');
        if (detailBtn && detailBtn.dataset.product == productId) {
            const heartIcon = detailBtn.querySelector('i');
            heartIcon.className = '';
            
            if (isFavorite) {
                heartIcon.classList.add('fas', 'fa-heart');
                detailBtn.classList.add('active');
            } else {
                heartIcon.classList.add('far', 'fa-heart');
                detailBtn.classList.remove('active');
            }
        }
    }

    function removeFavoriteCard(icon) {
        const card = icon.closest('.product-card');
        if (!card) return;

        card.style.opacity = '0';
        card.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            card.remove();
            updateFavoritesEmptyState();
        }, 300);
    }

    function updateFavoritesEmptyState() {
        const grid = document.getElementById('favoritesGrid');
        const emptyState = document.getElementById('favoritesEmpty');
        if (!grid || !emptyState) return;

        const remaining = grid.querySelectorAll('.product-card').length;
        if (remaining === 0) {
            grid.style.display = 'none';
            emptyState.style.display = 'block';
        }
    }

    async function restoreIconState(icon, productId) {
        try {
            const response = await fetch(`/favorites/check/${productId}`);
            const data = await response.json();
            
            const heartIcon = icon.querySelector('i');
            heartIcon.className = '';
            
            if (data.isFavorite) {
                heartIcon.classList.add('fas', 'fa-heart');
                icon.classList.add('active');
            } else {
                heartIcon.classList.add('far', 'fa-heart');
                icon.classList.remove('active');
            }
        } catch (error) {
            // Silently fail
        }
    }

    async function checkFavoriteStatus(icon, productId) {
        try {
            const response = await fetch(`/favorites/check/${productId}`);
            const data = await response.json();
            
            const heartIcon = icon.querySelector('i');
            heartIcon.className = '';
            
            if (data.isFavorite) {
                heartIcon.classList.add('fas', 'fa-heart');
                icon.classList.add('active');
            } else {
                heartIcon.classList.add('far', 'fa-heart');
                icon.classList.remove('active');
            }
        } catch (error) {
            // Silently fail
        }
    }

    async function checkDetailFavoriteStatus(btn, productId) {
        try {
            const response = await fetch(`/favorites/check/${productId}`);
            const data = await response.json();
            
            const heartIcon = btn.querySelector('i');
            heartIcon.className = '';
            
            if (data.isFavorite) {
                heartIcon.classList.add('fas', 'fa-heart');
                btn.classList.add('active');
            } else {
                heartIcon.classList.add('far', 'fa-heart');
                btn.classList.remove('active');
            }
        } catch (error) {
            // Silently fail
        }
    }

    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    function showNotification(message, type = 'success') {
        const colors = {
            success: '#000',
            error: '#cc0000',
            info: '#cc0000'
        };
        
        const existingNotification = document.querySelector('.aroma-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = 'aroma-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: ${colors[type] || colors.success};
            color: white;
            padding: 12px 20px;
            border-radius: 0;
            z-index: 9999;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideInUp 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }

    function showRemoveConfirm(productName) {
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
                    padding: 28px;
                    max-width: 320px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                ">
                    <p style="margin: 0 0 20px 0; color: #666; font-size: 14px;">
                        ¿Quitar "${productName}" de tus favoritos?
                    </p>
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
                        ">Sí, quitar</button>
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

    function addStyles() {
        if (!document.getElementById('favorites-styles')) {
            const style = document.createElement('style');
            style.id = 'favorites-styles';
            style.textContent = `
                @keyframes slideInUp {
                    from { transform: translateY(100px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                @keyframes slideOutDown {
                    from { transform: translateY(0); opacity: 1; }
                    to { transform: translateY(100px); opacity: 0; }
                }
                .wishlist-icon {
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                .wishlist-icon.active i,
                .wishlist-icon i.fas.fa-heart {
                    color: #ff4444 !important;
                }
                .btn-secondary.active {
                    background-color: #ff4444;
                    color: white;
                    border-color: #ff4444;
                }
                .btn-secondary.active i {
                    color: white;
                }
            `;
            document.head.appendChild(style);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        addStyles();
        initFavorites();
    });
    
})();
