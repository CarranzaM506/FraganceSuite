async function loadCheckoutCart() {
    const container = document.getElementById('checkoutItems');

    const response = await fetch('/api/cart');
    const data = await response.json();

    const items = data.items || [];

    if (items.length === 0) {
        container.innerHTML = "<p>Carrito vacío</p>";
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
                <div style="font-weight:600;">₡${total.toFixed(2)}</div>
            </div>
        `;
    });

    const promoPercent = parseFloat(data.promo_value || 0);

    const codeDiscount = (subtotal - totalDiscount) * (promoPercent / 100);

    const total = (subtotal - totalDiscount) - codeDiscount;

    const combinedDiscount = totalDiscount + codeDiscount;

    // mostrar en pantalla
    document.getElementById('subtotalPrice').textContent = '₡' + subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '-₡' + combinedDiscount.toFixed(2);
    document.getElementById('totalPrice').textContent = '₡' + total.toFixed(2);

    container.innerHTML = html;
}

//mensaje en el form de aviso
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

function closeAddressMessage() {
    const container = document.getElementById('addressMessage');
    if (container) {
        container.classList.add('d-none');
    }
}

// GUARDAR DIRECCIÓN (API)
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
                messages.push(data.message || 'Ocurrió un error al guardar la dirección.');
            }

            showAddressMessage(messages, 'error');
            return;
        }

        //SUCCESS
        showAddressMessage(data.message, 'success');
        this.reset();

        // opcional: ocultar después de unos segundos
        setTimeout(() => {
            location.reload();
        }, 1000);

    } catch (error) {
        console.error(error);
        showAddressMessage('Error inesperado', 'error');
    }
});

function showToast(message, type = 'error') {
    const container = document.getElementById('toast-container');

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

    // animación entrada
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);

    // desaparecer
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';

        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// CONTROL BOTÓN PAYPAL
function handleAddressSelection() {
    const radios = document.querySelectorAll('input[name="address_id"]');
    const container = document.getElementById('paypal-button-container');

    if (!container) return;

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        });
    });
}

if (typeof paypal !== 'undefined') {
    paypal.Buttons({

        createOrder: function () {
            const addressId = document.querySelector('input[name="address_id"]:checked');

            if (!addressId) {
                showToast('Selecciona una dirección antes de pagar', 'error');
                return;
            }

            return fetch('/paypal/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    address_id: addressId.value
                })
            })
                .then(async res => {

                    const data = await res.json();

                    if (!res.ok) {
                        console.error('🔥 ERROR BACKEND PAYPAL:', data);
                        throw new Error(data.error || 'Error creando orden');
                    }

                    console.log('ORDER CREADA:', data);

                    return data.id;
                });
        },

        onApprove: function (data) {
            return fetch('/paypal/capture-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    orderID: data.orderID
                })
            })
                .then(res => res.json())
                .then(details => {
                    alert('Pago completado');
                    window.location.href = "/success";
                });
        }

    }).render('#paypal-button-container');
}

/*INIT*/
loadCheckoutCart();
handleAddressSelection();