@extends('partsAdmin.header')

@section('title', 'Pedidos Pendientes de Envío')

@section('content')
<div class="container mt-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0">Pedidos Pendientes de Envío</h2>
        @if ($orders->isNotEmpty())
            <span class="badge bg-dark fs-6">{{ $orders->count() }} pendiente{{ $orders->count() !== 1 ? 's' : '' }}</span>
        @endif
    </div>

    {{-- TABLA DESKTOP --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th># Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Destino</th>
                    <th>Total</th>
                    <th>Método de Pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-semibold">#{{ $order->idorder }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</td>
                        <td>{{ $order->user?->name }} {{ $order->user?->lastname }}</td>
                        <td>{{ $order->location?->province }}, {{ $order->location?->canton }}</td>
                        <td>₡{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->purchasemethod }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-dark pending-toggle"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#details-{{ $order->idorder }}"
                                    aria-expanded="false">▼</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="p-0 border-top-0">
                            <div class="collapse" id="details-{{ $order->idorder }}">
                                <div class="p-3 text-start" style="background: var(--aroma-gray-100);">
                                    <p class="mb-2 small">
                                        <strong>Dirección completa:</strong>
                                        {{ $order->location?->province }},
                                        {{ $order->location?->canton }},
                                        {{ $order->location?->district }},
                                        {{ $order->location?->detail }}
                                        @if ($order->location?->zipcode)
                                            &nbsp;(CP: {{ $order->location->zipcode }})
                                        @endif
                                    </p>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th class="text-start">Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->details as $detail)
                                                <tr>
                                                    <td class="text-start">
                                                        {{ $detail->product?->name ?? 'Producto no disponible' }}
                                                    </td>
                                                    <td class="text-center">{{ $detail->quantity }}</td>
                                                    <td class="text-center">₡{{ number_format($detail->price, 2) }}</td>
                                                    <td class="text-center">₡{{ number_format($detail->price * $detail->quantity, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <strong>No hay pedidos pendientes de envío.</strong><br>
                            <small class="text-muted">Todos los pedidos han sido procesados.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CARDS MOBILE --}}
    <div class="d-md-none mobile-records-list">
        <div class="mobile-records-toolbar">
            <input type="text" id="pendingSearch" class="form-control" placeholder="Buscar por cliente o pedido...">
        </div>
        <div id="pendingCards">
            @forelse ($orders as $order)
                <article class="mobile-record-card"
                         data-search="{{ strtolower('#' . $order->idorder . ' ' . $order->user?->name . ' ' . $order->user?->lastname . ' ' . $order->location?->province . ' ' . $order->location?->canton) }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="mobile-record-title mb-1">#{{ $order->idorder }}</h5>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</small>
                    </div>
                    <p class="mobile-record-meta"><strong>Cliente:</strong> {{ $order->user?->name }} {{ $order->user?->lastname }}</p>
                    <p class="mobile-record-meta"><strong>Destino:</strong> {{ $order->location?->province }}, {{ $order->location?->canton }}</p>
                    <p class="mobile-record-meta"><strong>Total:</strong> ₡{{ number_format($order->total, 2) }}</p>
                    <p class="mobile-record-meta"><strong>Método:</strong> {{ $order->purchasemethod }}</p>

                    <button class="btn btn-sm btn-outline-dark w-100 mt-2 mobile-toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#mobile-details-{{ $order->idorder }}"
                            aria-expanded="false">Ver productos ▼</button>

                    <div class="collapse mt-2" id="mobile-details-{{ $order->idorder }}">
                        <div class="p-2" style="background: var(--aroma-gray-100);">
                            <p class="small mb-2">
                                <strong>Dirección:</strong>
                                {{ $order->location?->province }}, {{ $order->location?->canton }},
                                {{ $order->location?->district }}, {{ $order->location?->detail }}
                            </p>
                            @foreach ($order->details as $detail)
                                <div class="d-flex justify-content-between small py-1 border-bottom">
                                    <span>{{ $detail->product?->name ?? 'Producto no disponible' }} × {{ $detail->quantity }}</span>
                                    <span>₡{{ number_format($detail->price * $detail->quantity, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @empty
                <div class="text-center py-5">
                    <strong>No hay pedidos pendientes de envío.</strong><br>
                    <small class="text-muted">Todos los pedidos han sido procesados.</small>
                </div>
            @endforelse
        </div>
        <div class="mobile-records-pagination" id="pendingPagination"></div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle chevron en botones de expandir (desktop y mobile)
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
        var targetEl = document.querySelector(btn.dataset.bsTarget);
        if (!targetEl) return;

        targetEl.addEventListener('show.bs.collapse', function () {
            if (btn.classList.contains('pending-toggle')) {
                btn.textContent = '▲';
            } else if (btn.classList.contains('mobile-toggle')) {
                btn.textContent = 'Ver productos ▲';
            }
        });

        targetEl.addEventListener('hide.bs.collapse', function () {
            if (btn.classList.contains('pending-toggle')) {
                btn.textContent = '▼';
            } else if (btn.classList.contains('mobile-toggle')) {
                btn.textContent = 'Ver productos ▼';
            }
        });
    });

    initMobilePager('pendingSearch', 'pendingCards', 'pendingPagination');
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
</script>
@endsection
