@extends('partsAdmin.header')

@section('title', 'Agregar promoción')

@section('content')
    <div class="container-fluid py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">

                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                            <div>
                                <h3 class="mb-1">Agregar promoción</h3>
                                <p class="text-muted mb-0">Crea una promoción y asigna productos desde la lista.</p>
                            </div>

                            <a href="{{ route('discount.index') }}" class="btn btn-outline-secondary">Volver</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('conflicts'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <div>Algunos productos ya tienen otra promoción asignada:</div>
                                <ul>
                                    @foreach (session('conflicts') as $c)
                                        <li>{{ $c['name'] }} ({{ $c['brand'] }})</li>
                                    @endforeach
                                </ul>
                                <div>Si confirmas, estos productos serán reasignados a la nueva promoción.</div>
                                <form method="POST" action="{{ route('discount.store') }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="force_replace" value="1">
                                    {{-- Re-supply old inputs --}}
                                    <input type="hidden" name="value" value="{{ old('value') }}">
                                    <input type="hidden" name="startdate" value="{{ old('startdate') }}">
                                    <input type="hidden" name="enddate" value="{{ old('enddate') }}">
                                    <input type="hidden" name="condition" value="{{ old('condition') }}">
                                    @if(old('products'))
                                        @foreach(old('products') as $pid)
                                            <input type="hidden" name="products[]" value="{{ $pid }}">
                                        @endforeach
                                    @endif
                                    <button class="btn btn-danger">Confirmar reemplazo y crear promoción</button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" data-bs-dismiss="alert">Cancelar</button>
                                </form>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('discount.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Valor (% )</label>
                                    <input type="number" step="0.01" min="0" name="value" class="form-control" value="{{ old('value') }}" required>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Fecha inicio</label>
                                    <input type="datetime-local" name="startdate" class="form-control" value="{{ old('startdate') }}" required>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Fecha fin</label>
                                    <input type="datetime-local" name="enddate" class="form-control" value="{{ old('enddate') }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Condición</label>
                                    <input type="text" name="condition" class="form-control" maxlength="100" value="{{ old('condition') }}" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5>Seleccionar productos</h5>
                            <p class="text-muted">Busca y selecciona los productos que formarán parte de la promoción.</p>

                                <div class="row mb-3">
                                    <div class="col-12 col-md-6 mb-2">
                                        <input id="productSearch" class="form-control" placeholder="Buscar productos por nombre, marca o categoría">
                                    </div>
                                    <div class="col-12 col-md-6 d-flex gap-2">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="catDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Categorías
                                            </button>
                                            <div class="dropdown-menu p-3" style="max-height:220px;overflow:auto;">
                                                @foreach($categories as $cat)
                                                    <label class="form-check d-block">
                                                        <input class="form-check-input category-filter" type="checkbox" value="{{ $cat }}">
                                                        <span class="form-check-label">{{ $cat }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="brandDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Marcas
                                            </button>
                                            <div class="dropdown-menu p-3" style="max-height:220px;overflow:auto;">
                                                @foreach($brands as $b)
                                                    <label class="form-check d-block">
                                                        <input class="form-check-input brand-filter" type="checkbox" value="{{ $b }}">
                                                        <span class="form-check-label">{{ $b }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="productsContainer" class="row g-3 mb-4">
                                    <!-- Products loaded via AJAX -->
                                    <div id="productsLoading" class="text-center text-muted">Cargando productos...</div>
                                </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('discount.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-dark px-4">Crear promoción</button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const search = document.getElementById('productSearch');
            const productsContainer = document.getElementById('productsContainer');
            const loading = document.getElementById('productsLoading');

            let debounceTimer;

            function renderProducts(items) {
                loading.style.display = 'none';
                productsContainer.innerHTML = '';
                if (!items.length) {
                    productsContainer.innerHTML = '<div class="text-muted">No se encontraron productos.</div>';
                    return;
                }
                items.forEach(p => {
                    const col = document.createElement('div');
                    col.className = 'col-12 col-md-6 col-lg-4 product-card';
                    col.innerHTML = `
                        <div class="card h-100">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <input type="checkbox" name="products[]" value="${p.id}" class="form-check-input me-2 product-checkbox ${p.iddiscount ? 'has-discount' : ''}" ${(Array.isArray(oldProducts) && oldProducts.includes(String(p.id))) ? 'checked' : ''}>
                                <img src="${p.pathimg}" alt="" width="64" class="rounded">
                                <div>
                                    <div class="fw-bold">${p.name}</div>
                                    <div class="text-muted">${p.brand}</div>
                                    <div>₡${p.price}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    productsContainer.appendChild(col);
                });
            }

            // Pre-populate old products from server-side old() if present
            const oldProducts = @json(old('products', []));

            async function fetchProducts() {
                loading.style.display = '';
                const q = search.value.trim();
                const categories = Array.from(document.querySelectorAll('.category-filter:checked')).map(i => i.value);
                const brands = Array.from(document.querySelectorAll('.brand-filter:checked')).map(i => i.value);

                const params = new URLSearchParams();
                if (q) params.append('q', q);
                categories.forEach(c => params.append('categories[]', c));
                brands.forEach(b => params.append('brands[]', b));

                try {
                    const res = await fetch('/products/search?' + params.toString());
                    const body = await res.json();
                    renderProducts(body.products);
                } catch (err) {
                    productsContainer.innerHTML = '<div class="text-danger">Error cargando productos</div>';
                }
            }

            search.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchProducts, 300);
            });

            document.querySelectorAll('.category-filter, .brand-filter').forEach(i => i.addEventListener('change', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchProducts, 200);
            }));

            // Initial load
            fetchProducts();

            // Date remaining indicators for start/end
            const startInput = document.querySelector('input[name="startdate"]');
            const endInput = document.querySelector('input[name="enddate"]');
            const startInfo = document.createElement('small');
            const endInfo = document.createElement('small');
            startInput.parentNode.appendChild(startInfo);
            endInput.parentNode.appendChild(endInfo);

            function updateDateInfo() {
                const now = new Date();
                // Start
                if (startInput.value) {
                    const s = new Date(startInput.value);
                    if (s > now) {
                        const diff = Math.ceil((s - now)/(1000*60*60*24));
                        startInfo.textContent = ` — inicia en ${diff} día${diff>1?'s':''}`;
                        startInfo.style.color = diff <=7 ? 'orange' : 'inherit';
                    } else {
                        startInfo.textContent = '';
                    }
                } else startInfo.textContent = '';

                // End
                if (endInput.value) {
                    const e = new Date(endInput.value);
                    const diff = Math.ceil((e - now)/(1000*60*60*24));
                    if (diff < 0) {
                        endInfo.textContent = ` — vencida hace ${Math.abs(diff)} día${Math.abs(diff)>1?'s':''}`;
                        endInfo.style.color = 'red';
                    } else if (diff <= 7) {
                        endInfo.textContent = ` — quedan ${diff} día${diff>1?'s':''}`;
                        endInfo.style.color = 'orange';
                    } else {
                        endInfo.textContent = ` — quedan ${diff} día${diff>1?'s':''}`;
                        endInfo.style.color = 'green';
                    }
                } else endInfo.textContent = '';
            }

            startInput.addEventListener('change', updateDateInfo);
            endInput.addEventListener('change', updateDateInfo);
            updateDateInfo();
        });
    </script>
@endsection
