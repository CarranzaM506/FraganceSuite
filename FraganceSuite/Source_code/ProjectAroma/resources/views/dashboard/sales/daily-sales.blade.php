@extends('partsAdmin.header')

@section('title', 'Ventas Diarias')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4">Ventas Diarias</h2>

        <form method="GET" class="mb-4 d-flex gap-2 align-items-center">
            <input type="date" name="date" class="form-control w-auto" value="{{ request('date') }}">
            <button class="btn btn-dark">Filtrar</button>
        </form>

        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered text-center align-middle" style="border-radius: 0;">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Total Ventas</th>
                        <th>Pedidos</th>
                        <th>Promedio</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @if (empty($ventasDiarias) || count($ventasDiarias) === 0)
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <strong>No hay ventas registradas.</strong><br>
                                <small class="text-muted">₡0.</small>
                            </td>
                        </tr>
                    @else
                        @foreach ($ventasDiarias as $venta)
                            @php $fecha = \Carbon\Carbon::parse($venta->day)->format('d/m/Y'); @endphp
                            <tr>
                                <td>{{ $fecha }}</td>
                                <td>₡{{ number_format($venta->totalSales ?? 0, 2) }}</td>
                                <td>{{ $venta->totalOrders ?? 0 }}</td>
                                <td>₡{{ number_format($venta->avgOrder ?? 0, 2) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-dark"
                                            onclick="loadPeriodOrders(null, null, '{{ $venta->day }}', '{{ $fecha }}')">
                                        Ver órdenes
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="d-md-none mobile-records-list">
            <div class="mobile-records-toolbar">
                <input type="text" id="dailySearch" class="form-control" placeholder="Buscar fecha...">
            </div>
            <div id="dailyCards">
                @foreach ($ventasDiarias as $venta)
                    @php $fecha = \Carbon\Carbon::parse($venta->day)->format('d/m/Y'); @endphp
                    <article class="mobile-record-card" data-search="{{ strtolower($fecha) }}">
                        <h5 class="mobile-record-title">{{ $fecha }}</h5>
                        <p class="mobile-record-meta"><strong>Total Ventas:</strong> ₡{{ number_format($venta->totalSales ?? 0, 2) }}</p>
                        <p class="mobile-record-meta"><strong>Pedidos:</strong> {{ $venta->totalOrders ?? 0 }}</p>
                        <p class="mobile-record-meta"><strong>Promedio:</strong> ₡{{ number_format($venta->avgOrder ?? 0, 2) }}</p>
                        <button class="btn btn-sm btn-outline-dark mt-2"
                                onclick="loadPeriodOrders(null, null, '{{ $venta->day }}', '{{ $fecha }}')">
                            Ver órdenes
                        </button>
                    </article>
                @endforeach
            </div>
            <div class="mobile-records-pagination" id="dailyPagination"></div>
        </div>

    </div>

    {{-- Modal de órdenes del período --}}
    <div class="modal fade" id="periodOrdersModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="periodOrdersTitle" style="font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;font-weight:600;"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="periodOrdersLoading" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                        <p class="text-muted small mt-2 mb-0">Cargando...</p>
                    </div>
                    <div id="periodOrdersContent" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:0.83rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:60px">#</th>
                                        <th>Fecha</th>
                                        <th>Canal</th>
                                        <th>Método de pago</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Productos</th>
                                    </tr>
                                </thead>
                                <tbody id="periodOrdersBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="periodOrdersEmpty" class="text-center py-5 d-none">
                        <p class="text-muted mb-0">No hay órdenes para este período.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initMobilePager('dailySearch', 'dailyCards', 'dailyPagination');
});

function initMobilePager(searchId, cardsId, paginationId) {
    const searchInput = document.getElementById(searchId);
    const cardsContainer = document.getElementById(cardsId);
    const paginationContainer = document.getElementById(paginationId);
    if (!searchInput || !cardsContainer || !paginationContainer) return;

    const cards = Array.from(cardsContainer.querySelectorAll('.mobile-record-card'));
    const pageSize = 10;
    let currentPage = 1;
    let filteredCards = cards;

    function renderPagination(totalPages) {
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;
        const prevBtn = document.createElement('button');
        prevBtn.className = 'btn btn-outline-secondary btn-sm';
        prevBtn.textContent = '←';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderCards(); } };
        const label = document.createElement('span');
        label.className = 'mobile-page-label';
        label.textContent = `Página ${currentPage} de ${totalPages}`;
        const nextBtn = document.createElement('button');
        nextBtn.className = 'btn btn-outline-secondary btn-sm';
        nextBtn.textContent = '→';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderCards(); } };
        paginationContainer.append(prevBtn, label, nextBtn);
    }

    function renderCards() {
        const totalPages = Math.max(1, Math.ceil(filteredCards.length / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * pageSize;
        const pageCards = filteredCards.slice(start, start + pageSize);
        cards.forEach(c => c.style.display = 'none');
        pageCards.forEach(c => c.style.display = '');
        renderPagination(totalPages);
    }

    searchInput.addEventListener('input', function () {
        const term = searchInput.value.trim().toLowerCase();
        filteredCards = cards.filter(c => (c.dataset.search || '').includes(term));
        currentPage = 1;
        renderCards();
    });

    renderCards();
}

/* ---- Modal de órdenes por período ---- */
const PAYMENT_LABELS = {
    'PAYPAL': 'PayPal', 'CARD': 'Tarjeta', 'EFECTIVO': 'Efectivo',
    'SINPE': 'SINPE', 'TRANSFERENCIA': 'Transferencia',
};

function paymentLabel(method) {
    return PAYMENT_LABELS[(method || '').toUpperCase()] || (method || '—');
}

function canalLabel(saleType) {
    return saleType === 'physical' ? '<span class="badge bg-dark">Físico</span>' : '<span class="badge bg-secondary">Web</span>';
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmt(n) {
    return Number(n).toLocaleString('es-CR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function loadPeriodOrders(year, month, date, label) {
    document.getElementById('periodOrdersTitle').textContent = 'Órdenes: ' + label;
    document.getElementById('periodOrdersLoading').classList.remove('d-none');
    document.getElementById('periodOrdersContent').classList.add('d-none');
    document.getElementById('periodOrdersEmpty').classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('periodOrdersModal'));
    modal.show();

    const params = new URLSearchParams();
    if (year && month) { params.set('year', year); params.set('month', month); }
    if (date) { params.set('date', date); }

    fetch('/orders/period?' + params.toString())
        .then(r => r.json())
        .then(orders => {
            document.getElementById('periodOrdersLoading').classList.add('d-none');
            if (!orders.length) {
                document.getElementById('periodOrdersEmpty').classList.remove('d-none');
                return;
            }
            const tbody = document.getElementById('periodOrdersBody');
            tbody.innerHTML = orders.map(o => {
                const itemRows = o.items.map(i =>
                    `<tr class="table-light" style="font-size:0.78rem;">
                        <td colspan="6" style="padding-left:2rem;">
                            ${escHtml(i.name)}${i.brand ? ' — ' + escHtml(i.brand) : ''} &nbsp;·&nbsp;
                            ${i.quantity} und. &nbsp;·&nbsp; ₡${fmt(i.price)} c/u
                        </td>
                    </tr>`
                ).join('');
                const dateStr = o.date ? new Date(o.date).toLocaleDateString('es-CR') : '—';
                return `
                <tr class="order-header-row" style="cursor:pointer;" onclick="toggleItems(this)">
                    <td class="text-center text-muted">#${o.idorder}</td>
                    <td>${dateStr}</td>
                    <td>${canalLabel(o.sale_type)}</td>
                    <td>${escHtml(paymentLabel(o.purchasemethod))}</td>
                    <td class="text-end fw-semibold">₡${fmt(o.total)}</td>
                    <td class="text-center text-muted small">▼ ver</td>
                </tr>
                <tr class="items-row d-none"><td colspan="6" class="p-0">
                    <table class="table table-sm mb-0">${itemRows}</table>
                </td></tr>`;
            }).join('');
            document.getElementById('periodOrdersContent').classList.remove('d-none');
        })
        .catch(() => {
            document.getElementById('periodOrdersLoading').classList.add('d-none');
            document.getElementById('periodOrdersEmpty').textContent = 'Error al cargar las órdenes.';
            document.getElementById('periodOrdersEmpty').classList.remove('d-none');
        });
}

function toggleItems(row) {
    const next = row.nextElementSibling;
    if (next && next.classList.contains('items-row')) {
        next.classList.toggle('d-none');
    }
}
</script>
@endsection
