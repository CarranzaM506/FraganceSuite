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
    renderProducts(ALL_PRODUCTS);

    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('brandFilter').addEventListener('change', applyFilters);
    document.getElementById('categoryFilter').addEventListener('change', applyFilters);
    document.getElementById('discountInput').addEventListener('input', updateCheckoutTotals);
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

    const filtered = ALL_PRODUCTS.filter(p => {
        const matchName  = !search || p.name.toLowerCase().includes(search);
        const matchBrand = !brand  || p.brand === brand;
        const matchCat   = !cat   || p.category === cat;
        return matchName && matchBrand && matchCat;
    });

    renderProducts(filtered);
}

/* =====================================================
   RENDERIZADO DE PRODUCTOS
   ===================================================== */
function renderProducts(products) {
    const container = document.getElementById('productsContainer');
    const noResults = document.getElementById('noResults');
    const noStock   = document.getElementById('noStock');

    if (ALL_PRODUCTS.length === 0) {
        container.innerHTML = '';
        noStock.classList.remove('d-none');
        noResults.classList.add('d-none');
        return;
    }

    noStock.classList.add('d-none');

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
    document.getElementById('paymentError').classList.add('d-none');
    document.getElementById('discountError').classList.add('d-none');
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));

    updateCheckoutTotals();
    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
}

function selectPayment(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedPaymentMethod').value = el.dataset.method;
    document.getElementById('paymentError').classList.add('d-none');
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
}

function confirmSale() {
    const method   = document.getElementById('selectedPaymentMethod').value;
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    let valid = true;

    if (!method) {
        document.getElementById('paymentError').classList.remove('d-none');
        valid = false;
    }
    if (discount < 0 || discount > 100) {
        document.getElementById('discountError').classList.remove('d-none');
        valid = false;
    }
    if (!valid) return;

    bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
    setTimeout(() => buildInvoice(method, discount), 300);
}

/* =====================================================
   FACTURA
   ===================================================== */
function buildInvoice(method, discountPct) {
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
    renderProducts(ALL_PRODUCTS);
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
