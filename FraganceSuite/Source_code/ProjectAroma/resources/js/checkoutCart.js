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

    const total = subtotal - totalDiscount;

    document.getElementById('subtotalPrice').textContent = '₡' + subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '-₡' + totalDiscount.toFixed(2);
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

// CONTROL BOTÓN PAYPAL
function handleAddressSelection() {
    const radios = document.querySelectorAll('input[name="address_id"]');
    const paypalBtn = document.getElementById('paypalBtn');

    if (!paypalBtn) return;

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            paypalBtn.disabled = false;
        });
    });
}

//PAYPAL BUTTON 
document.getElementById('paypalBtn').addEventListener('click', async () => {

    const addressId = document.querySelector('input[name="address_id"]:checked');

    const response = await fetch('/api/paypal/create-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            address_id: addressId.value
        })
    });

    const data = await response.json();

    if (data.url) {
        window.location.href = data.url; // redirige a PayPal
    }
});

/*INIT*/
loadCheckoutCart();
handleAddressSelection();