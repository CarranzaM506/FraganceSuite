async function loadCheckoutCart() {
    const container = document.getElementById('checkoutItems');
    if (!container) return;

    let data;
    try {
        const response = await fetch('/api/cart');
        data = await response.json();
    } catch (error) {
        console.error('Error cargando checkout cart:', error);
        container.innerHTML = '<p>Error al cargar el carrito.</p>';
        return;
    }

    const items = data.items || [];

    if (items.length === 0) {
        container.innerHTML = '<p>Carrito vacio</p>';
        return;
    }

    let html = '';

    let subtotal = 0;
    let totalDiscount = 0;

    items.forEach(item => {
        const price = parseFloat(item.price);
        const qty = parseInt(item.quantity);
        const discount = parseFloat(item.discount || 0);

        const finalPrice = discount > 0 ? price * (1 - discount / 100) : price;
        const total = finalPrice * qty;

        subtotal += price * qty;
        totalDiscount += (price * qty) * (discount / 100);

        html += `
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <img src="${item.image}" style="width:60px; height:80px; object-fit:cover;">
                <div style="flex:1;">
                    <div style="font-size:13px;">${item.name}</div>
                    <small>Cant: ${qty}</small>
                </div>
                <div style="font-weight:600;">CRC ${total.toFixed(2)}</div>
            </div>
        `;
    });

    const promoPercent = parseFloat(data.promo_value || 0);
    const codeDiscount = (subtotal - totalDiscount) * (promoPercent / 100);
    const total = (subtotal - totalDiscount) - codeDiscount;
    const combinedDiscount = totalDiscount + codeDiscount;

    document.getElementById('subtotalPrice').textContent = 'CRC ' + subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '-CRC ' + combinedDiscount.toFixed(2);
    document.getElementById('totalPrice').textContent = 'CRC ' + total.toFixed(2);

    container.innerHTML = html;
}

function showAddressMessage(message, type = 'success') {
    const container = document.getElementById('addressMessage');
    const text = document.getElementById('addressMessageText');

    if (!container || !text) return;

    container.classList.remove('d-none', 'alert-success', 'alert-danger');
    container.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');

    if (Array.isArray(message)) {
        text.innerHTML = `
            <ul class="mb-0">
                ${message.map(m => `<li>${m}</li>`).join('')}
            </ul>`;
    } else {
        text.textContent = message;
    }
}

function showToast(message, type = 'error') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');

    toast.style.background = type === 'error' ? '#dc3545' : '#28a745';
    toast.style.color = '#fff';
    toast.style.padding = '12px 18px';
    toast.style.marginBottom = '10px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
    toast.style.fontSize = '14px';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';

    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';

        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3500);
}

function getSelectedAddressId() {
    return document.querySelector('input[name="address_id"]:checked');
}

function showPaymentState(message, type = 'info', visible = true) {
    const status = document.getElementById('paymentStatus');
    if (!status) return;

    status.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-danger', 'alert-warning');

    if (!visible) {
        status.classList.add('d-none');
        status.textContent = '';
        return;
    }

    const classMap = {
        info: 'alert-info',
        success: 'alert-success',
        error: 'alert-danger',
        warning: 'alert-warning'
    };

    status.classList.add(classMap[type] || 'alert-info');
    status.textContent = message;
}

function mapPaymentErrorMessage(data, httpStatus) {
    if (data && typeof data.error === 'string' && data.error.trim() !== '') {
        return data.error;
    }

    if (httpStatus === 422) {
        return 'Pago rechazado. Verifica los datos e intenta nuevamente.';
    }

    if (httpStatus === 503 || httpStatus === 502) {
        return 'No hay conexion con la pasarela en este momento. Intenta nuevamente en unos minutos.';
    }

    return 'No se pudo procesar el pago en este momento. Intenta nuevamente.';
}

function setInputsDisabled(disabled) {
    const addressRadios = document.querySelectorAll('input[name="address_id"]');
    addressRadios.forEach(radio => {
        radio.disabled = disabled;
    });

    const addressFormBtn = document.querySelector('#newAddressForm button[type="submit"]');
    if (addressFormBtn) {
        addressFormBtn.disabled = disabled;
    }
}

let isProcessingPayment = false;

function setPaymentProcessing(isProcessing, message = 'Procesando pago...') {
    const container = document.getElementById('paypal-button-container');
    const overlay = document.getElementById('paypalProcessingOverlay');

    isProcessingPayment = isProcessing;

    if (container) {
        container.style.pointerEvents = isProcessing ? 'none' : 'auto';
        container.style.opacity = isProcessing ? '0.65' : '1';
    }

    setInputsDisabled(isProcessing);

    if (overlay) {
        overlay.style.display = isProcessing ? 'flex' : 'none';
        const text = overlay.querySelector('.overlay-text');
        if (text) {
            text.textContent = message;
        }
    }

    if (isProcessing) {
        showPaymentState(message, 'info', true);
    }
}

function handleAddressSelection(paypalActions = null) {
    const radios = document.querySelectorAll('input[name="address_id"]');
    if (radios.length === 0) return;
    if (paypalActions && radios[0].dataset.paypalBound === '1') return;

    radios.forEach(radio => {
        if (paypalActions) {
            radio.dataset.paypalBound = '1';
        }
    });

    const syncButtonState = () => {
        const selected = !!getSelectedAddressId();
        if (paypalActions) {
            if (selected) {
                paypalActions.enable();
            } else {
                paypalActions.disable();
            }
        }

        if (!selected && !isProcessingPayment) {
            showPaymentState('Selecciona una direccion para habilitar el pago.', 'warning', true);
        }

        if (selected && !isProcessingPayment) {
            showPaymentState('Direccion seleccionada. Ya puedes completar el pago.', 'success', true);
        }
    };

    radios.forEach(radio => {
        radio.addEventListener('change', syncButtonState);
    });

    syncButtonState();
}

document.getElementById('newAddressForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('/api/address', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            let messages = [];

            if (data.errors) {
                Object.values(data.errors).forEach(fieldErrors => {
                    fieldErrors.forEach(error => {
                        messages.push(error);
                    });
                });
            } else {
                messages.push(data.message || 'Ocurrio un error al guardar la direccion.');
            }

            showAddressMessage(messages, 'error');
            return;
        }

        showAddressMessage(data.message || 'Direccion guardada correctamente.', 'success');
        this.reset();

        setTimeout(() => {
            location.reload();
        }, 1000);

    } catch (error) {
        console.error(error);
        showAddressMessage('Error inesperado', 'error');
    }
});

if (typeof paypal !== 'undefined' && document.getElementById('paypal-button-container')) {
    paypal.Buttons({
        onInit: function (_data, actions) {
            handleAddressSelection(actions);
        },

        createOrder: async function () {
            const addressId = getSelectedAddressId();

            if (!addressId) {
                showToast('Selecciona una direccion antes de pagar', 'error');
                showPaymentState('Selecciona una direccion antes de pagar.', 'warning', true);
                return Promise.reject(new Error('No address selected'));
            }

            showPaymentState('Preparando pago...', 'info', true);

            try {
                const res = await fetch('/paypal/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        address_id: addressId.value
                    })
                });

                let data = {};
                try {
                    data = await res.json();
                } catch (_error) {
                    data = {};
                }

                if (!res.ok || !data.id) {
                    const message = mapPaymentErrorMessage(data, res.status);
                    showToast(message, 'error');
                    showPaymentState(message, 'error', true);
                    throw new Error(message);
                }

                return data.id;
            } catch (error) {
                console.error('Error createOrder:', error);
                if (!error.message) {
                    const message = 'No hay conexion con la pasarela. Intenta de nuevo.';
                    showToast(message, 'error');
                    showPaymentState(message, 'error', true);
                    throw new Error(message);
                }
                throw error;
            }
        },

        onApprove: async function (data) {
            const addressId = getSelectedAddressId();

            if (!addressId) {
                showToast('Selecciona una direccion', 'error');
                showPaymentState('Selecciona una direccion antes de confirmar.', 'warning', true);
                return;
            }

            try {
                setPaymentProcessing(true, 'Procesando pago...');

                const res = await fetch('/paypal/capture-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        orderID: data.orderID,
                        address_id: addressId.value
                    })
                });

                let payload = {};
                try {
                    payload = await res.json();
                } catch (_error) {
                    payload = {};
                }

                if (!res.ok || !payload.success) {
                    const message = mapPaymentErrorMessage(payload, res.status);
                    showToast(message, 'error');
                    showPaymentState(message, 'error', true);
                    return;
                }

                showPaymentState('Pago aprobado. Redirigiendo al comprobante...', 'success', true);
                window.location.href = `/order/success/${payload.order_id}`;
            } catch (error) {
                console.error('Error captureOrder:', error);
                const message = 'Error de conexion con la pasarela. Intenta nuevamente.';
                showToast(message, 'error');
                showPaymentState(message, 'error', true);
            } finally {
                setPaymentProcessing(false);
            }
        },

        onCancel: function () {
            showPaymentState('Pago cancelado. Puedes intentarlo nuevamente.', 'warning', true);
        },

        onError: function (err) {
            console.error('PayPal JS SDK error:', err);
            const message = 'Error de conexion con la pasarela. Intenta nuevamente.';
            showToast(message, 'error');
            showPaymentState(message, 'error', true);
            setPaymentProcessing(false);
        }
    }).render('#paypal-button-container');
}

loadCheckoutCart();
