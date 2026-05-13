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
                    </tr>
                </thead>

                <tbody>

                    @if (empty($ventasDiarias) || count($ventasDiarias) === 0)
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <strong>No hay ventas registradas.</strong><br>
                                <small class="text-muted">₡0.</small>
                            </td>
                        </tr>
                    @else
                        @foreach ($ventasDiarias as $venta)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($venta->day)->format('d/m/Y') }}
                                </td>
                                <td>₡{{ number_format($venta->totalSales ?? 0, 2) }}</td>
                                <td>{{ $venta->totalOrders ?? 0 }}</td>
                                <td>₡{{ number_format($venta->avgOrder ?? 0, 2) }}</td>
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
                    </article>
                @endforeach
            </div>
            <div class="mobile-records-pagination" id="dailyPagination"></div>
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
</script>
@endsection
