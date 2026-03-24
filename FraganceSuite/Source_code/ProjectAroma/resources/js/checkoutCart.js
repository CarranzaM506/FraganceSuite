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

/* GUARDAR DIRECCIÓN (API)*/
document.getElementById('newAddressForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    const response = await fetch('/api/address', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    });

    if (response.ok) {
        location.reload(); // recargar para ver nueva dirección
    }
});

/* PAYPAL BUTTON */
document.getElementById('paypalBtn').addEventListener('click', async () => {

    const addressId = document.querySelector('input[name="address_id"]:checked');

    if (!addressId) {
        alert('Seleccione una dirección');
        return;
    }

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