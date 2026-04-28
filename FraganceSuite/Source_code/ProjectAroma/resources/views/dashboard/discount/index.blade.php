@extends('partsAdmin.header')

@section('title', 'Ver Promociones')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">Listado de Promociones</h4>
                <a href="{{ route('discount.create') }}" class="btn btn-dark btn-sm">+ Nueva Promoción</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <!-- Contenedor con scroll horizontal (como la tabla de marcas) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-hover table-bordered" id="discountsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Descuento</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($discounts as $d)
                        <tr>
                            <td>
                                <span class="badge bg-dark">{{ $d->value }}%</span>
                            </td>
                            <td style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($d->startdate)->format('d/m/Y H:i') }}
                            </td>
                            <td style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($d->enddate)->format('d/m/Y H:i') }}
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $e = \Carbon\Carbon::parse($d->enddate);
                                    $ediff = (int) ceil($now->diffInDays($e, false));
                                @endphp
                                @if($e->lt($now))
                                    <span class="badge bg-danger ms-1">Vencida</span>
                                @elseif($ediff <= 3)
                                    <span class="badge bg-warning ms-1">{{ $ediff }} día(s)</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-outline-secondary btn-sm" data-id="{{ $d->iddiscount }}" onclick="showProducts(this)">
                                    Ver ({{ $d->products->count() ?? 0 }})
                                </button>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('discount.edit', $d->iddiscount) }}" class="btn btn-warning btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('discount.destroy', $d->iddiscount) }}" class="d-inline" onsubmit="return confirmDelete(this, '&iquest;Est&aacute;s seguro de eliminar esta promoci&oacute;n?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-md-none mobile-records-list">
                <div class="mobile-records-toolbar">
                    <input type="text" id="discountSearch" class="form-control" placeholder="Buscar descuento o fecha...">
                </div>
                <div id="discountCards">
                    @foreach ($discounts as $d)
                        @php
                            $startFmt = \Carbon\Carbon::parse($d->startdate)->format('d/m/Y H:i');
                            $endFmt = \Carbon\Carbon::parse($d->enddate)->format('d/m/Y H:i');
                        @endphp
                        <article class="mobile-record-card" data-search="{{ strtolower($d->value . '% ' . $startFmt . ' ' . $endFmt) }}">
                            <h5 class="mobile-record-title">Descuento: {{ $d->value }}%</h5>
                            <p class="mobile-record-meta"><strong>Inicio:</strong> {{ $startFmt }}</p>
                            <p class="mobile-record-meta"><strong>Fin:</strong> {{ $endFmt }}</p>
                            <p class="mobile-record-meta"><strong>Productos:</strong> {{ $d->products->count() ?? 0 }}</p>
                            <div class="product-mobile-footer">
                                <div class="product-mobile-badges">
                                    <button class="btn btn-outline-secondary btn-sm" data-id="{{ $d->iddiscount }}" onclick="showProducts(this)">
                                        Ver productos
                                    </button>
                                </div>
                                <div class="product-mobile-actions">
                                    <a href="{{ route('discount.edit', $d->iddiscount) }}" class="btn btn-success btn-sm icon-btn" aria-label="Editar promoción">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('discount.destroy', $d->iddiscount) }}" class="d-inline" onsubmit="return confirmDelete(this, '&iquest;Est&aacute;s seguro de eliminar esta promoci&oacute;n?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm icon-btn" aria-label="Eliminar promoción">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mobile-records-pagination" id="discountPagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="productsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Productos en promoción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="productsModalBody"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    function isMobile() {
        return window.innerWidth < 768;
    }

    function initDataTable() {
        if (!isMobile()) {
            if ($.fn.DataTable.isDataTable('#discountsTable')) {
                $('#discountsTable').DataTable().destroy();
            }
            $('#discountsTable').DataTable({
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    paginate: {
                        previous: "←",
                        next: "→"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [3, 4] }
                ]
            });
        } else {
            if ($.fn.DataTable.isDataTable('#discountsTable')) {
                $('#discountsTable').DataTable().destroy();
            }
            $('.dataTables_wrapper').contents().unwrap();
        }
    }

    $(document).ready(function() {
        initDataTable();
        $(window).resize(function() {
            initDataTable();
        });
        initMobilePager('discountSearch', 'discountCards', 'discountPagination');
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
            cards.forEach(c => c.style.display = 'none');
            filteredCards.slice(start, start + pageSize).forEach(c => c.style.display = '');
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

    function confirmDelete(form, message) {
        openAdminDeleteConfirm(message, function() {
            form.submit();
        });
        return false;
    }

    function showProducts(btn) {
        const id = btn.getAttribute('data-id');
        fetch(`/discount/${id}/products`)
            .then(r => r.json())
            .then(data => {
                const body = document.getElementById('productsModalBody');
                body.innerHTML = '';
                if (data.products.length === 0) {
                    body.innerHTML = '<p class="text-muted text-center mb-0 py-3">No hay productos con esta promoción.</p>';
                } else {
                    let html = '<div class="row g-3">';
                    data.products.forEach(p => {
                        html += `
                            <div class="col-12 col-md-6 d-flex gap-3 align-items-center border-bottom pb-2">
                                <img src="${p.pathimg}" width="60" style="border: 1px solid #e5e5e5;">
                                <div>
                                    <div class="fw-medium">${p.name}</div>
                                    <div class="text-muted small">${p.brand}</div>
                                    <div class="small">
                                        <del class="text-muted">₡${p.old_price}</del>
                                        <span class="fw-medium ms-1">₡${p.new_price}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    body.innerHTML = html;
                }
                new bootstrap.Modal(document.getElementById('productsModal')).show();
            })
            .catch(() => alert('Error cargando productos'));
    }
</script>
@endsection
