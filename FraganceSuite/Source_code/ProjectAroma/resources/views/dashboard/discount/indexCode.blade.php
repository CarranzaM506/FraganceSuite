@extends('partsAdmin.header')

@section('title', 'Ver codigos promocionales')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h4 class="mb-0">Listado de codigos promocionales</h4>
                    <a href="{{ route('promotionCode.create') }}" class="btn btn-dark btn-sm">Crear codigo</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <div class="table-responsive d-none d-md-block">
                    <table id="promotionCodesTable" class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Codigo</th>
                                <th>Descuento</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($codes as $code)
                                @php
                                    $start = $code->startdate ?? $code->start_date;
                                    $end = $code->enddate ?? $code->end_date;
                                    $now = \Carbon\Carbon::now();
                                    $startDate = $start ? \Carbon\Carbon::parse($start) : null;
                                    $endDate = $end ? \Carbon\Carbon::parse($end) : null;
                                @endphp
                                <tr>
                                    <td class="fw-medium">{{ $code->code_promotion }}</td>
                                    <td>{{ number_format($code->value, 2) }}%</td>
                                    <td>{{ $startDate ? $startDate->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $endDate ? $endDate->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if ($startDate && $startDate->gt($now))
                                            <span class="badge bg-warning text-dark">Proximo</span>
                                        @elseif ($endDate && $endDate->lt($now))
                                            <span class="badge bg-danger">Vencido</span>
                                        @else
                                            <span class="badge bg-success">Activo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 flex-wrap justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning">Editar</button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDeleteById('{{ $code->idcode_promotion }}', '{{ $code->code_promotion }}')">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay codigos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none mobile-records-list">
                    <div class="mobile-records-toolbar">
                        <input type="text" id="codeSearch" class="form-control" placeholder="Buscar código o fecha...">
                    </div>
                    <div id="codeCards">
                        @foreach ($codes as $code)
                            @php
                                $start = $code->startdate ?? $code->start_date;
                                $end = $code->enddate ?? $code->end_date;
                                $now = \Carbon\Carbon::now();
                                $startDate = $start ? \Carbon\Carbon::parse($start) : null;
                                $endDate = $end ? \Carbon\Carbon::parse($end) : null;
                                $status = ($startDate && $startDate->gt($now)) ? 'Próximo' : (($endDate && $endDate->lt($now)) ? 'Vencido' : 'Activo');
                            @endphp
                            <article class="mobile-record-card" data-search="{{ strtolower($code->code_promotion . ' ' . ($startDate ? $startDate->format('d/m/Y') : '') . ' ' . ($endDate ? $endDate->format('d/m/Y') : '')) }}">
                                <h5 class="mobile-record-title">{{ $code->code_promotion }}</h5>
                                <p class="mobile-record-meta"><strong>Descuento:</strong> {{ number_format($code->value, 2) }}%</p>
                                <p class="mobile-record-meta"><strong>Inicio:</strong> {{ $startDate ? $startDate->format('d/m/Y') : 'N/A' }}</p>
                                <p class="mobile-record-meta"><strong>Fin:</strong> {{ $endDate ? $endDate->format('d/m/Y') : 'N/A' }}</p>
                                <div class="product-mobile-footer">
                                    <div class="product-mobile-badges">
                                        <span class="badge {{ $status === 'Activo' ? 'bg-success' : ($status === 'Vencido' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $status }}</span>
                                    </div>
                                    <div class="product-mobile-actions">
                                        <button type="button" class="btn btn-success btn-sm icon-btn" aria-label="Editar código">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm icon-btn"
                                            onclick="confirmDeleteById('{{ $code->idcode_promotion }}', '{{ $code->code_promotion }}')"
                                            aria-label="Eliminar código">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mobile-records-pagination" id="codePagination"></div>
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
$.fn.dataTable.ext.errMode = 'none';
        function initDataTable() {
            if (!isMobile()) {
                if ($.fn.DataTable.isDataTable('#promotionCodesTable')) {
                    $('#promotionCodesTable').DataTable().destroy();
                }
                $('#promotionCodesTable').DataTable({
                    pageLength: 10,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                        paginate: {
                            previous: "←",
                            next: "→"
                        }
                    },
                    columnDefs: [
                        { orderable: false, targets: [5] }
                    ]
                });
            } else {
                if ($.fn.DataTable.isDataTable('#promotionCodesTable')) {
                    $('#promotionCodesTable').DataTable().destroy();
                }
                $('.dataTables_wrapper').contents().unwrap();
            }
        }

        $(document).ready(function() {
            initDataTable();
            $(window).resize(function() {
                initDataTable();
            });
            initMobilePager('codeSearch', 'codeCards', 'codePagination');
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

        function confirmDeleteById(id, code) {
            const safeCode = String(code || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            openAdminDeleteConfirm(`&iquest;Eliminar \"${safeCode}\" de los c&oacute;digos promocionales?`, function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/promotionCode/${id}`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            });
        }
    </script>
@endsection
