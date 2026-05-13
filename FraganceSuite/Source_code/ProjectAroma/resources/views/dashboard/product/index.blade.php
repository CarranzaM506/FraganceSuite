@extends('partsAdmin.header')

@section('title', 'Ver Productos')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="mb-4">Listado de Productos</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <!-- Desktop / Tablet: tabla -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-striped table-hover table-bordered" id="productsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Decant</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <img src="{{ $product->pathimg }}" alt="Imagen" width="45" class="rounded">
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->brand }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>₡{{ number_format($product->price, 0) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        <span class="badge {{ $product->decant ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $product->decant ? 'Sí' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $product->active ? 'bg-primary' : 'bg-danger' }}">
                                            {{ $product->active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('product.edit', $product->idproduct) }}" class="btn btn-sm btn-warning">Editar</a>
                                            <form method="POST" action="{{ route('product.destroy', $product->idproduct) }}" class="delete-form d-inline-block" onsubmit="return confirmDelete(this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                    <span class="btn-text">Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile: cards -->
                <div class="d-md-none product-mobile-list">
                    <div class="product-mobile-toolbar">
                        <input type="text" id="mobileProductSearch" class="form-control" placeholder="Buscar producto, marca o categoría...">
                    </div>

                    <div id="mobileProductsCards">
                    @foreach ($products as $product)
                        <article class="product-mobile-card"
                            data-search="{{ strtolower($product->name . ' ' . $product->brand . ' ' . $product->category) }}">
                            <div class="product-mobile-top">
                                <img src="{{ $product->pathimg }}" alt="Imagen de {{ $product->name }}" class="product-mobile-image">
                                <div class="product-mobile-main">
                                    <h5 class="product-mobile-title">{{ $product->name }}</h5>
                                    <p class="product-mobile-meta"><strong>Marca:</strong> {{ $product->brand }}</p>
                                    <p class="product-mobile-meta"><strong>Categoría:</strong> {{ $product->category }}</p>
                                    <p class="product-mobile-meta"><strong>Precio:</strong> ₡{{ number_format($product->price, 0) }}</p>
                                    <p class="product-mobile-meta"><strong>Stock:</strong> {{ $product->stock }}</p>
                                </div>
                            </div>

                            <div class="product-mobile-footer">
                                <div class="product-mobile-badges">
                                <span class="badge {{ $product->decant ? 'bg-success' : 'bg-secondary' }}">
                                    Decant: {{ $product->decant ? 'Sí' : 'No' }}
                                </span>
                                <span class="badge {{ $product->active ? 'bg-primary' : 'bg-danger' }}">
                                    {{ $product->active ? 'Activo' : 'Inactivo' }}
                                </span>
                                </div>

                                <div class="product-mobile-actions">
                                    <a href="{{ route('product.edit', $product->idproduct) }}" class="btn btn-success btn-sm icon-btn" aria-label="Editar producto">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('product.destroy', $product->idproduct) }}" class="delete-form d-inline-block" onsubmit="return confirmDelete(this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm delete-btn icon-btn" aria-label="Eliminar producto">
                                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                            <span class="btn-text d-inline-flex">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                    <path d="M10 11v6"/>
                                                    <path d="M14 11v6"/>
                                                    <path d="M9 6V4h6v2"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                    </div>

                    <div class="product-mobile-pagination" id="mobileProductsPagination"></div>
                </div>
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
            // Solo en desktop: activar DataTables
            if ($.fn.DataTable.isDataTable('#productsTable')) {
                $('#productsTable').DataTable().destroy();
            }
            $('#productsTable').DataTable({
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    paginate: {
                        previous: "←",
                        next: "→"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [0, 8] }
                ]
            });
        } else {
            // En móvil: destruir DataTables completamente
            if ($.fn.DataTable.isDataTable('#productsTable')) {
                $('#productsTable').DataTable().destroy();
            }
            // Eliminar cualquier wrapper que DataTables haya dejado
            $('.dataTables_wrapper').contents().unwrap();
        }
    }

    $(document).ready(function() {
        initDataTable();
        $(window).resize(function() {
            initDataTable();
        });

        initMobileCards();
    });

    function initMobileCards() {
        const searchInput = document.getElementById('mobileProductSearch');
        const cardsContainer = document.getElementById('mobileProductsCards');
        const paginationContainer = document.getElementById('mobileProductsPagination');
        if (!searchInput || !cardsContainer || !paginationContainer) return;

        const cards = Array.from(cardsContainer.querySelectorAll('.product-mobile-card'));
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
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderCards();
                }
            });
            paginationContainer.appendChild(prevBtn);

            const pageLabel = document.createElement('span');
            pageLabel.className = 'mobile-page-label';
            pageLabel.textContent = `Página ${currentPage} de ${totalPages}`;
            paginationContainer.appendChild(pageLabel);

            const nextBtn = document.createElement('button');
            nextBtn.className = 'btn btn-outline-secondary btn-sm';
            nextBtn.textContent = '→';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderCards();
                }
            });
            paginationContainer.appendChild(nextBtn);
        }

        function renderCards() {
            const totalPages = Math.max(1, Math.ceil(filteredCards.length / pageSize));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;
            const pageCards = filteredCards.slice(start, end);

            cards.forEach(card => card.style.display = 'none');
            pageCards.forEach(card => card.style.display = '');

            renderPagination(totalPages);
        }

        function applySearch() {
            const term = searchInput.value.trim().toLowerCase();
            filteredCards = cards.filter(card => (card.dataset.search || '').includes(term));
            currentPage = 1;
            renderCards();
        }

        searchInput.addEventListener('input', applySearch);
        renderCards();
    }

    function confirmDelete(form) {
        openAdminDeleteConfirm('&iquest;Est&aacute;s seguro de que deseas eliminar este producto?', function() {
            const button = form.querySelector('.delete-btn');
            const spinner = button ? button.querySelector('.spinner-border') : null;
            const btnText = button ? button.querySelector('.btn-text') : null;

            if (spinner) spinner.classList.remove('d-none');
            if (btnText) btnText.textContent = ' Eliminando...';
            if (button) button.disabled = true;

            form.submit();
        });
        return false;
    }
</script>
@endsection

