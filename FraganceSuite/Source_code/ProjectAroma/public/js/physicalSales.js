/* =====================================================
   VENTAS FÍSICAS — physicalSales.js
   Los datos se reciben desde el blade via window.AROMA_PRODUCTS
   ===================================================== */

const ALL_PRODUCTS = window.AROMA_PRODUCTS || [];

let cart = [];
let invoiceCounter = Math.floor(Math.random() * 9000) + 1000;

/* =====================================================
   INICIALIZACIÓN
   ===================================================== */
document.addEventListener('DOMContentLoaded', function () {
    setInvoiceDate();
    populateFilters();
    renderProducts([]);

    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('brandFilter').addEventListener('change', applyFilters);
    document.getElementById('categoryFilter').addEventListener('change', applyFilters);
    document.getElementById('discountInput').addEventListener('input', updateCheckoutTotals);
    document.getElementById('cashReceivedInput').addEventListener('input', () => {
        const safePct = Math.max(0, Math.min(100, parseFloat(document.getElementById('discountInput').value) || 0));
        const total   = getSubtotal() * (1 - safePct / 100);
        updateChange(total);
    });
});

function setInvoiceDate() {
    const opts = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('invoiceDateLabel').textContent =
        new Date().toLocaleDateString('es-CR', opts);
}

/* =====================================================
   FILTROS Y BÚSQUEDA
   ===================================================== */
function populateFilters() {
    const brands     = [...new Set(ALL_PRODUCTS.map(p => p.brand).filter(Boolean))].sort();
    const categories = [...new Set(ALL_PRODUCTS.map(p => p.category).filter(Boolean))].sort();

    const brandSel = document.getElementById('brandFilter');
    brands.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b;
        opt.textContent = b;
        brandSel.appendChild(opt);
    });

    const catSel = document.getElementById('categoryFilter');
    categories.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        catSel.appendChild(opt);
    });
}

function applyFilters() {
    const search = document.getElementById('searchInput').value.trim().toLowerCase();
    const brand  = document.getElementById('brandFilter').value;
    const cat    = document.getElementById('categoryFilter').value;

    if (!search) {
        renderProducts([]);
        return;
    }

    const filtered = ALL_PRODUCTS.filter(p => {
        const matchName  = p.name.toLowerCase().includes(search);
        const matchBrand = !brand || p.brand === brand;
        const matchCat   = !cat   || p.category === cat;
        return matchName && matchBrand && matchCat;
    });

    renderProducts(filtered);
}

/* =====================================================
   RENDERIZADO DE PRODUCTOS
   ===================================================== */
function renderProducts(products) {
    const container    = document.getElementById('productsContainer');
    const noResults    = document.getElementById('noResults');
    const noStock      = document.getElementById('noStock');
    const searchPrompt = document.getElementById('searchPrompt');

    const searching = document.getElementById('searchInput').value.trim().length > 0;

    if (ALL_PRODUCTS.length === 0) {
        container.innerHTML = '';
        noStock.classList.remove('d-none');
        noResults.classList.add('d-none');
        searchPrompt.classList.add('d-none');
        return;
    }

    noStock.classList.add('d-none');

    if (!searching) {
        container.innerHTML = '';
        searchPrompt.classList.remove('d-none');
        noResults.classList.add('d-none');
        return;
    }

    searchPrompt.classList.add('d-none');

    if (products.length === 0) {
        container.innerHTML = '';
        noResults.classList.remove('d-none');
        return;
    }

    noResults.classList.add('d-none');

    const inCartIds = new Set(cart.map(i => i.product.idproduct));

    container.innerHTML = products.map(p => {
        const isInCart = inCartIds.has(p.idproduct);
        return `
        <div class="product-row" id="prow-${p.idproduct}">
            <img class="product-row-img"
                 src="${p.pathimg || ''}"
                 alt="${escHtml(p.name)}"
                 onerror="this.style.visibility='hidden'">
            <div class="product-row-info">
                <div class="product-row-name" title="${escHtml(p.name)}">${escHtml(p.name)}</div>
                <div class="product-row-meta">${escHtml(p.brand || '')} &middot; ${escHtml(p.category || '')} &middot; Stock: ${p.stock}</div>
            </div>
            <div class="product-row-price">&#x20A1;${fmt(p.price)}</div>
            <div class="product-row-actions">
                <input type="number"
                       class="qty-input"
                       id="qty-${p.idproduct}"
                       value="1" min="1" max="${p.stock}"
                       ${isInCart ? 'disabled' : ''}>
                <button class="btn-add ${isInCart ? 'in-cart' : ''}"
                        id="btn-${p.idproduct}"
                        onclick="addToCart(${p.idproduct})"
                        ${isInCart ? 'disabled' : ''}>
                    ${isInCart ? '&#10003; Agregado' : '+ Agregar'}
                </button>
            </div>
        </div>`;
    }).join('');
}

/* =====================================================
   CARRITO
   ===================================================== */
function addToCart(productId) {
    const product = ALL_PRODUCTS.find(p => p.idproduct === productId);
    if (!product) return;

    const qtyInput = document.getElementById('qty-' + productId);
    const qty      = parseInt(qtyInput.value) || 1;

    if (qty < 1 || qty > product.stock) {
        qtyInput.classList.add('is-invalid');
        setTimeout(() => qtyInput.classList.remove('is-invalid'), 1500);
        return;
    }

    const existing = cart.find(i => i.product.idproduct === productId);
    if (existing) {
        existing.qty = Math.min(existing.qty + qty, product.stock);
    } else {
        cart.push({ product, qty });
    }

    const btn = document.getElementById('btn-' + productId);
    if (btn) {
        btn.innerHTML = '&#10003; Agregado';
        btn.classList.add('in-cart');
        btn.disabled  = true;
        qtyInput.disabled = true;
    }

    renderCart();
}

function removeFromCart(productId) {
    cart = cart.filter(i => i.product.idproduct !== productId);
    renderCart();

    const btn = document.getElementById('btn-' + productId);
    if (btn) {
        btn.textContent = '+ Agregar';
        btn.classList.remove('in-cart');
        btn.disabled = false;
        const qi = document.getElementById('qty-' + productId);
        if (qi) qi.disabled = false;
    }
}

function renderCart() {
    const isEmpty  = cart.length === 0;
    const emptyEl  = document.getElementById('cartEmpty');
    const itemsEl  = document.getElementById('cartItems');
    const totalsEl = document.getElementById('cartTotals');
    const badge    = document.getElementById('cartBadge');
    const badgeN   = document.getElementById('cartCount');

    emptyEl.style.display = isEmpty ? '' : 'none';
    itemsEl.classList.toggle('d-none', isEmpty);
    totalsEl.classList.toggle('d-none', isEmpty);
    badge.style.display   = isEmpty ? 'none' : '';
    badgeN.textContent    = cart.length;

    if (isEmpty) return;

    const tbody = document.getElementById('cartTableBody');
    tbody.innerHTML = cart.map(({ product, qty }) => {
        const sub = product.price * qty;
        return `
        <tr>
            <td>
                <span class="cart-item-name" title="${escHtml(product.name)}">${escHtml(product.name)}</span>
                <span class="cart-item-brand">${escHtml(product.brand || '')}</span>
            </td>
            <td class="text-center">${qty}</td>
            <td class="text-end">&#x20A1;${fmt(product.price)}</td>
            <td class="text-end fw-semibold">&#x20A1;${fmt(sub)}</td>
            <td class="text-center">
                <button class="btn-remove-cart" onclick="removeFromCart(${product.idproduct})" title="Eliminar">&times;</button>
            </td>
        </tr>`;
    }).join('');

    const subtotal = getSubtotal();
    document.getElementById('subtotalDisplay').textContent = '&#x20A1;' + fmt(subtotal);
    document.getElementById('totalDisplay').textContent    = '&#x20A1;' + fmt(subtotal);

    // Usar textContent para evitar parseo de HTML entities en estos spans
    document.getElementById('subtotalDisplay').textContent = '₡' + fmt(subtotal);
    document.getElementById('totalDisplay').textContent    = '₡' + fmt(subtotal);
}

function getSubtotal() {
    return cart.reduce((acc, { product, qty }) => acc + product.price * qty, 0);
}

/* =====================================================
   MODAL DE CHECKOUT
   ===================================================== */
function openCheckout() {
    if (cart.length === 0) return;

    document.getElementById('selectedPaymentMethod').value = '';
    document.getElementById('discountInput').value = '0';
    document.getElementById('cashReceivedInput').value = '';
    document.getElementById('cashSection').classList.add('d-none');
    document.getElementById('changeSection').classList.add('d-none');
    document.getElementById('paymentError').classList.add('d-none');
    document.getElementById('discountError').classList.add('d-none');
    document.getElementById('cashError').classList.add('d-none');
    document.getElementById('saleError').classList.add('d-none');
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));

    updateCheckoutTotals();
    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
}

function selectPayment(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedPaymentMethod').value = el.dataset.method;
    document.getElementById('paymentError').classList.add('d-none');

    const isCash = el.dataset.method === 'efectivo';
    document.getElementById('cashSection').classList.toggle('d-none', !isCash);
    if (!isCash) {
        document.getElementById('cashReceivedInput').value = '';
        document.getElementById('changeSection').classList.add('d-none');
        document.getElementById('cashError').classList.add('d-none');
    } else {
        updateCheckoutTotals();
    }
}

function updateCheckoutTotals() {
    const subtotal    = getSubtotal();
    const discountPct = parseFloat(document.getElementById('discountInput').value) || 0;
    const discountErr = document.getElementById('discountError');

    if (discountPct < 0 || discountPct > 100) {
        discountErr.classList.remove('d-none');
    } else {
        discountErr.classList.add('d-none');
    }

    const safePct     = Math.max(0, Math.min(100, discountPct));
    const discountAmt = subtotal * safePct / 100;
    const total       = subtotal - discountAmt;

    document.getElementById('checkoutSubtotal').textContent      = '₡' + fmt(subtotal);
    document.getElementById('checkoutDiscountLabel').textContent  = 'Descuento (' + safePct + '%)';
    document.getElementById('checkoutDiscountAmount').textContent = '-₡' + fmt(discountAmt);
    document.getElementById('checkoutTotal').textContent          = '₡' + fmt(total);

    document.getElementById('discountRow').style.display = safePct > 0 ? 'flex' : 'none';

    updateChange(total);
}

function updateChange(total) {
    const method = document.getElementById('selectedPaymentMethod').value;
    if (method !== 'efectivo') return;

    const received    = parseFloat(document.getElementById('cashReceivedInput').value) || 0;
    const changeEl    = document.getElementById('changeSection');
    const changeAmt   = document.getElementById('changeAmount');
    const cashErr     = document.getElementById('cashError');

    if (received <= 0) {
        changeEl.classList.add('d-none');
        cashErr.classList.add('d-none');
        return;
    }

    if (received < total) {
        cashErr.classList.remove('d-none');
        changeEl.classList.add('d-none');
    } else {
        cashErr.classList.add('d-none');
        changeAmt.textContent = '₡' + fmt(received - total);
        changeEl.style.display = 'flex';
        changeEl.classList.remove('d-none');
    }
}

function confirmSale() {
    const method   = document.getElementById('selectedPaymentMethod').value;
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const safePct  = Math.max(0, Math.min(100, discount));
    const total    = getSubtotal() * (1 - safePct / 100);
    let valid = true;

    if (!method) {
        document.getElementById('paymentError').classList.remove('d-none');
        valid = false;
    }
    if (discount < 0 || discount > 100) {
        document.getElementById('discountError').classList.remove('d-none');
        valid = false;
    }
    if (method === 'efectivo') {
        const received = parseFloat(document.getElementById('cashReceivedInput').value) || 0;
        if (received < total) {
            document.getElementById('cashError').classList.remove('d-none');
            valid = false;
        }
    }
    if (!valid) return;

    const cashReceived = method === 'efectivo'
        ? parseFloat(document.getElementById('cashReceivedInput').value) || 0
        : 0;

    const items = cart.map(({ product, qty }) => ({
        idproduct: product.idproduct,
        qty,
        price: product.price,
    }));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const checkoutModal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
    const saleError = document.getElementById('saleError');

    fetch('/physical-sales', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ items, payment_method: method, discount, total }),
    })
    .then(res => res.json().then(data => ({ ok: res.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            saleError.textContent = data.error || 'Error al procesar la venta. Intenta nuevamente.';
            saleError.classList.remove('d-none');
            return;
        }
        saleError.classList.add('d-none');
        checkoutModal.hide();
        setTimeout(() => buildInvoice(method, discount, cashReceived), 300);
    })
    .catch(() => {
        saleError.textContent = 'Error de conexión. Intenta nuevamente.';
        saleError.classList.remove('d-none');
    });
}

/* =====================================================
   FACTURA
   ===================================================== */
function buildInvoice(method, discountPct, cashReceived = 0) {
    const subtotal    = getSubtotal();
    const discountAmt = subtotal * discountPct / 100;
    const total       = subtotal - discountAmt;
    const dateStr     = new Date().toLocaleDateString('es-CR', { year: 'numeric', month: 'long', day: 'numeric' });
    const methodLabels = {
        efectivo:      'Efectivo',
        sinpe:         'SINPE Móvil',
        transferencia: 'Transferencia Bancaria'
    };

    document.getElementById('inv-number').textContent  = '#' + invoiceCounter;
    document.getElementById('inv-date').textContent    = dateStr;
    document.getElementById('inv-payment').textContent = methodLabels[method] || method;

    const tbody = document.getElementById('inv-products');
    tbody.innerHTML = cart.map(({ product, qty }) => {
        const sub = product.price * qty;
        return `
        <tr style="border-bottom:1px solid #e8e8e8;">
            <td style="padding:8px 6px;">${escHtml(product.name)}</td>
            <td style="padding:8px 6px;">${escHtml(product.brand || '')}</td>
            <td style="padding:8px 6px;text-align:center;">${qty}</td>
            <td style="padding:8px 6px;text-align:right;">₡${fmt(product.price)}</td>
            <td style="padding:8px 6px;text-align:right;font-weight:600;">₡${fmt(sub)}</td>
        </tr>`;
    }).join('');

    document.getElementById('inv-subtotal').textContent        = '₡' + fmt(subtotal);
    document.getElementById('inv-discount-label').textContent  = 'Descuento (' + discountPct + '%)';
    document.getElementById('inv-discount-amount').textContent = '-₡' + fmt(discountAmt);
    document.getElementById('inv-total').textContent           = '₡' + fmt(total);
    document.getElementById('inv-discount-row').style.display  = discountPct > 0 ? 'flex' : 'none';

    const cashRow = document.getElementById('inv-cash-row');
    if (method === 'efectivo' && cashReceived > 0) {
        document.getElementById('inv-cash-received').textContent = '₡' + fmt(cashReceived);
        document.getElementById('inv-vuelto').textContent        = '₡' + fmt(cashReceived - total);
        cashRow.style.display = 'block';
    } else {
        cashRow.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('invoiceModal')).show();
}

function downloadInvoice() {
    window.print();
}

function resetSale() {
    cart = [];
    invoiceCounter = Math.floor(Math.random() * 9000) + 1000;
    document.getElementById('searchInput').value    = '';
    document.getElementById('brandFilter').value    = '';
    document.getElementById('categoryFilter').value = '';
    renderProducts([]);
    renderCart();
}

/* =====================================================
   UTILIDADES
   ===================================================== */
function fmt(n) {
    return Number(n).toLocaleString('es-CR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
