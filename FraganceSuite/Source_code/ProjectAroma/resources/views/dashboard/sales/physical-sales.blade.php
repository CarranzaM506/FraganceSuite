@extends('partsAdmin.header')

@section('title', 'Nueva Venta Física')

@section('content')

{{-- CSS específico de esta página --}}
<link rel="stylesheet" href="{{ asset('css/stylesPhysicalSales.css') }}">

<div class="content-wrap">

    {{-- ===== ENCABEZADO ===== --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Nueva Venta Física</h2>
            <p class="text-muted small mb-0">Registra ventas realizadas fuera de la tienda en línea</p>
        </div>
        <span class="badge bg-dark px-3 py-2 fs-6" id="cartBadge" style="display:none">
            <span id="cartCount">0</span> producto(s)
        </span>
    </div>

    <div class="row g-4">

        {{-- ===== COLUMNA IZQUIERDA: búsqueda + productos ===== --}}
        <div class="col-xl-7 col-lg-6">

            <div class="card mb-3">
                <div class="card-header">Buscar Perfumes</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text"
                                   id="searchInput"
                                   class="form-control"
                                   placeholder="Buscar por nombre..."
                                   autocomplete="off">
                        </div>
                        <div class="col-sm-6">
                            <select id="brandFilter" class="form-select">
                                <option value="">Todas las marcas</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <select id="categoryFilter" class="form-select">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="productsContainer"></div>

            <div id="searchPrompt" class="text-center py-5">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" class="mb-3">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <p class="text-muted mb-0">Escribe en el buscador para ver productos.</p>
            </div>

            <div id="noResults" class="text-center py-5 d-none">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" class="mb-3">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <p class="text-muted mb-0">No hay perfumes que coincidan con la búsqueda.</p>
            </div>

            <div id="noStock" class="text-center py-5 d-none">
                <p class="text-muted mb-0">No hay perfumes disponibles con stock.</p>
            </div>

        </div>

        {{-- ===== COLUMNA DERECHA: factura en tiempo real ===== --}}
        <div class="col-xl-5 col-lg-6">
            <div class="card" style="position: sticky; top: 4.5rem;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Factura</span>
                    <span class="text-muted small" id="invoiceDateLabel"></span>
                </div>

                {{-- Estado vacío --}}
                <div id="cartEmpty" class="text-center py-5 px-3">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" class="mb-2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <p class="text-muted small mb-0">Agrega productos para comenzar</p>
                </div>

                {{-- Tabla de ítems --}}
                <div id="cartItems" class="d-none">
                    <div class="table-responsive">
                        <table class="table mb-0" style="font-size: 0.82rem;">
                            <thead>
                                <tr>
                                    <th style="width:40%">Producto</th>
                                    <th class="text-center" style="width:15%">Cant.</th>
                                    <th class="text-end" style="width:20%">P. Unit.</th>
                                    <th class="text-end" style="width:20%">Subtotal</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Totales y botón pagar --}}
                <div id="cartTotals" class="p-3 d-none" style="border-top: 1px solid var(--aroma-gray-200);">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Subtotal</span>
                        <span id="subtotalDisplay">₡0</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mt-2">
                        <span>TOTAL</span>
                        <span id="totalDisplay" style="font-size: 1.1rem;">₡0</span>
                    </div>
                    <button class="btn btn-dark w-100 mt-3"
                            id="payBtn"
                            onclick="openCheckout()"
                            style="letter-spacing: 1px; font-size: 0.85rem;">
                        PAGAR
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>


{{-- =====================================================
     MODAL: CHECKOUT
     ===================================================== --}}
<div class="modal fade" id="checkoutModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border: none;">

            <div class="modal-header" style="border-bottom: 1px solid var(--aroma-gray-200);">
                <h5 class="modal-title" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                    Confirmar Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding: 1.5rem;">

                {{-- Método de pago --}}
                <div class="mb-4">
                    <label class="ps-label">Método de Pago</label>
                    <div class="d-flex gap-2">
                        <div class="payment-option flex-fill text-center p-3" data-method="efectivo" onclick="selectPayment(this)">
                            <i class="fa-solid fa-money-bill-wave" style="font-size: 1.3rem; margin-bottom: 6px; display: block;"></i>
                            <div style="font-size: 0.78rem; font-weight: 500;">Efectivo</div>
                        </div>
                        <div class="payment-option flex-fill text-center p-3" data-method="sinpe" onclick="selectPayment(this)">
                            <i class="fa-solid fa-mobile-screen-button" style="font-size: 1.3rem; margin-bottom: 6px; display: block;"></i>
                            <div style="font-size: 0.78rem; font-weight: 500;">SINPE</div>
                        </div>
                        <div class="payment-option flex-fill text-center p-3" data-method="transferencia" onclick="selectPayment(this)">
                            <i class="fa-solid fa-building-columns" style="font-size: 1.3rem; margin-bottom: 6px; display: block;"></i>
                            <div style="font-size: 0.78rem; font-weight: 500;">Transferencia</div>
                        </div>
                    </div>

                    {{-- Campo efectivo: solo visible cuando se selecciona "Efectivo" --}}
                    <div id="cashSection" class="mt-3 d-none">
                        <label class="ps-label">Con cuánto paga el cliente</label>
                        <div class="input-group">
                            <span class="input-group-text" style="font-size: 0.85rem;">₡</span>
                            <input type="number"
                                   class="form-control"
                                   id="cashReceivedInput"
                                   min="0"
                                   step="1"
                                   placeholder="0">
                        </div>
                        <div id="changeSection" class="mt-2 d-none" style="background: var(--aroma-gray-100); padding: 0.6rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <span class="text-muted small">Vuelto</span>
                            <span id="changeAmount" class="fw-bold" style="font-size: 1rem;"></span>
                        </div>
                        <div id="cashError" class="text-danger small mt-1 d-none">El monto recibido debe ser mayor o igual al total.</div>
                    </div>
                    <input type="hidden" id="selectedPaymentMethod" value="">
                    <div id="paymentError" class="text-danger small mt-1 d-none">Selecciona un método de pago.</div>
                </div>

                {{-- Descuento --}}
                <div class="mb-4">
                    <label class="ps-label">Descuento</label>
                    <div class="input-group">
                        <input type="number"
                               class="form-control"
                               id="discountInput"
                               min="0"
                               max="100"
                               step="1"
                               value="0"
                               placeholder="0">
                        <span class="input-group-text" style="font-size: 0.85rem;">%</span>
                    </div>
                    <div id="discountError" class="text-danger small mt-1 d-none">El descuento debe estar entre 0% y 100%.</div>
                    <div class="text-muted small mt-1">Ingresa 25 para aplicar un 25% de descuento al total.</div>
                </div>

                {{-- Resumen --}}
                <div style="background: var(--aroma-gray-100); padding: 1rem;">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span id="checkoutSubtotal">₡0</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1" id="discountRow" style="display: none;">
                        <span class="text-muted" id="checkoutDiscountLabel">Descuento (0%)</span>
                        <span class="text-success" id="checkoutDiscountAmount">-₡0</span>
                    </div>
                    <div style="border-top: 1px solid var(--aroma-gray-300); margin: 8px 0;"></div>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>TOTAL A COBRAR</span>
                        <span id="checkoutTotal" style="font-size: 1.05rem;">₡0</span>
                    </div>
                </div>

            </div>

            <div class="modal-footer flex-column align-items-stretch gap-2" style="border-top: 1px solid var(--aroma-gray-200); padding: 1rem 1.5rem;">
                <div id="saleError" class="text-danger small d-none"></div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                            style="font-size: 0.82rem; letter-spacing: 0.5px;">
                        Cancelar
                    </button>
                    <button type="button"
                            class="btn btn-dark"
                            onclick="confirmSale()"
                            style="font-size: 0.82rem; letter-spacing: 1px;">
                        CONFIRMAR VENTA
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- =====================================================
     MODAL: FACTURA GENERADA
     ===================================================== --}}
<div class="modal fade" id="invoiceModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border: none;">

            <div class="modal-header d-print-none" style="border-bottom: 1px solid var(--aroma-gray-200);">
                <div>
                    <h5 class="modal-title mb-0" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                        Factura Generada
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Descarga la factura para enviarla al cliente.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetSale()"></button>
            </div>

            <div class="modal-body p-0">
                <div id="invoiceContent" style="padding: 2rem;">

                    {{-- Cabecera --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem;">
                        <div>
                            <div style="font-size:1.8rem; font-weight:800; letter-spacing:2px;">AROMA</div>
                            <div style="font-size:0.78rem; color:#666; letter-spacing:1px;">TIENDA DE PERFUMES</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.72rem; color:#999; text-transform:uppercase; letter-spacing:1px;">Factura</div>
                            <div style="font-size:1.3rem; font-weight:700;" id="inv-number">#0000</div>
                            <div style="font-size:0.8rem; color:#555;" id="inv-date"></div>
                        </div>
                    </div>

                    {{-- Método de pago --}}
                    <div style="background:#f8f8f8; padding:0.75rem 1rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#888;">Método de pago</span>
                        <span style="font-size:0.85rem; font-weight:600;" id="inv-payment"></span>
                    </div>

                    {{-- Tabla de productos --}}
                    <table style="width:100%; border-collapse:collapse; margin-bottom:1.5rem; font-size:0.83rem;">
                        <thead>
                            <tr style="border-bottom:2px solid #000;">
                                <th style="padding:8px 6px; text-align:left; font-weight:600; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Producto</th>
                                <th style="padding:8px 6px; text-align:left; font-weight:600; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Marca</th>
                                <th style="padding:8px 6px; text-align:center; font-weight:600; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Cant.</th>
                                <th style="padding:8px 6px; text-align:right; font-weight:600; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">P. Unit.</th>
                                <th style="padding:8px 6px; text-align:right; font-weight:600; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="inv-products"></tbody>
                    </table>

                    {{-- Totales --}}
                    <div style="display:flex; justify-content:flex-end;">
                        <div style="min-width:240px;">
                            <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:0.83rem; color:#555;">
                                <span>Subtotal</span>
                                <span id="inv-subtotal"></span>
                            </div>
                            <div id="inv-discount-row" style="display:none; justify-content:space-between; padding:4px 0; font-size:0.83rem; color:#2c7a2e;">
                                <span id="inv-discount-label">Descuento (0%)</span>
                                <span id="inv-discount-amount"></span>
                            </div>
                            <div style="border-top:2px solid #000; margin:8px 0;"></div>
                            <div style="display:flex; justify-content:space-between; padding:4px 0; font-weight:700; font-size:1rem;">
                                <span>TOTAL</span>
                                <span id="inv-total"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Vuelto (efectivo) --}}
                    <div id="inv-cash-row" style="display:none; margin-top:0.5rem;">
                        <div style="border-top:1px solid #e0e0e0; margin:8px 0;"></div>
                        <div style="display:flex; justify-content:flex-end;">
                            <div style="min-width:240px;">
                                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:0.83rem; color:#555;">
                                    <span>Recibido</span>
                                    <span id="inv-cash-received"></span>
                                </div>
                                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:0.83rem; font-weight:600; color:#2c7a2e;">
                                    <span>Vuelto</span>
                                    <span id="inv-vuelto"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pie --}}
                    <div style="margin-top:2.5rem; padding-top:1rem; border-top:1px solid #e0e0e0; text-align:center; font-size:0.72rem; color:#999; letter-spacing:0.5px;">
                        Gracias por su compra — AROMA
                    </div>

                </div>
            </div>

            <div class="modal-footer d-print-none" style="border-top: 1px solid var(--aroma-gray-200); padding: 1rem 1.5rem;">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                        onclick="resetSale()"
                        style="font-size: 0.82rem;">
                    Cerrar
                </button>
                <button type="button"
                        class="btn btn-dark"
                        onclick="downloadInvoice()"
                        style="font-size: 0.82rem; letter-spacing: 1px;">
                    &#8595; DESCARGAR FACTURA
                </button>
            </div>

        </div>
    </div>
</div>

@endsection


@section('scripts')
{{-- Pasar datos de productos al JS externo --}}
<script>
    window.AROMA_PRODUCTS = {!! json_encode(
        ($products ?? collect())->map(fn($p) => [
            'idproduct' => $p->idproduct,
            'name'      => $p->name,
            'brand'     => $p->brand,
            'category'  => $p->category,
            'price'     => (float) $p->price,
            'stock'     => (int)   $p->stock,
            'pathimg'   => $p->pathimg,
        ])->values()
    ) !!};
</script>
<script src="{{ asset('js/physicalSales.js') }}"></script>
@endsection
