@extends('partsAdmin.header')

@section('title', 'Agregar producto')

@section('content')

    <div class="container-fluid py-3">
        <div class="row justify-content-center">
            {{-- Más ancho para que “calce” mejor en pantalla (más rectangular) --}}
            <div class="col-12 col-xl-10">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">

                        {{-- Header del form + botón de import --}}
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                            <div>
                                <h3 class="mb-1">Agregar producto</h3>
                                <p class="text-muted mb-0">Crea un producto manualmente o importa en lote desde Excel.</p>
                            </div>

                            {{-- Botón (lleva a una pantalla/modal de import o a una ruta directa) --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    Volver
                                </a>

                                {{-- Si ya tenés ruta para import: product.import.form o similar, cámbiala aquí --}}
                                <button class="btn btn-success" type="button" data-bs-toggle="modal"
                                    data-bs-target="#importExcelModal">
                                    Importar Excel
                                </button>
                            </div>
                        </div>


                        {{-- FORM CREAR PRODUCTO --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Cerrar"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Cerrar"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('product.store') }}">
                            @csrf

                            <div class="row g-3">
                                {{-- Nombre --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                {{-- Marca --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Marca</label>
                                    <input type="text" name="brand" class="form-control">
                                </div>

                                {{-- Categoría --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Categoría</label>
                                    <input type="text" name="category" class="form-control">
                                </div>

                                {{-- Ruta imagen --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Ruta de imagen</label>
                                    <input type="text" name="pathimg" class="form-control" placeholder="url">
                                </div>

                                {{-- Precio --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label">Precio</label>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control"
                                        required>
                                </div>

                                {{-- Stock --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" min="0" name="stock" class="form-control" required>
                                </div>

                                {{-- Switches: decant / active (se ven mejor que checkbox normales) --}}
                                <div class="col-12 col-lg-6 d-flex align-items-end">
                                    <div class="d-flex gap-4 flex-wrap">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="decant"
                                                value="1" id="decantSwitch">
                                            <label class="form-check-label" for="decantSwitch">Es decant</label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="active"
                                                value="1" id="activeSwitch" checked>
                                            <label class="form-check-label" for="activeSwitch">Activo</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Descripción --}}
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>

                                {{-- Descripción corta --}}
                                <div class="col-12">
                                    <label class="form-label">Descripción corta</label>
                                    <textarea name="shortDescription" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- Botones --}}
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-dark px-4" id="btnGuardar">
                                    <span class="btn-text">Guardar producto</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                </button>

                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL IMPORT EXCEL --}}
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importación masiva (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="importForm" method="POST" action="{{ route('product.import') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Selecciona un archivo .xlsx/.xls con las columnas esperadas.
                        </p>

                        <label class="form-label">Archivo Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>

                    <!-- Loader (Progress bar) -->
                    <div id="loaderContainer" class="mt-3" style="display:none;">
                        <p class="text-center mb-2">Importando productos, por favor espere...</p>
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 100%">
                                Cargando...
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Importar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@section('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btn = document.getElementById('btnGuardar');
            const text = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');

            form.addEventListener('submit', function() {
                btn.disabled = true;
                text.textContent = 'Guardando...';
                spinner.classList.remove('d-none');
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const importForm = document.getElementById('importForm');
            const loader = document.getElementById('loaderContainer');

            if (importForm && loader) {
                importForm.addEventListener('submit', function() {
                    loader.style.display = 'block';
                });
            }
        });
    </script>

@endsection
