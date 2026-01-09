@extends('partsAdmin.header')

@section('title', 'Editar producto')

@section('content')

    <div class="container-fluid py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">

                        {{-- Header --}}
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                            <div>
                                <h3 class="mb-1">Editar producto</h3>
                                <p class="text-muted mb-0">Modifica la información del producto.</p>
                            </div>

                            <a href="{{ route('product.index') }}" class="btn btn-outline-secondary">
                                Volver
                            </a>
                        </div>

                        {{-- ERRORES --}}
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

                        {{-- FORM EDITAR --}}
                        <form method="POST" action="{{ route('product.update', $product->idproduct) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">

                                {{-- Nombre --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $product->name) }}" required>
                                </div>

                                {{-- Marca --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Marca</label>
                                    <input type="text" name="brand" class="form-control"
                                        value="{{ old('brand', $product->brand) }}">
                                </div>

                                {{-- Categoría --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Categoría</label>
                                    <input type="text" name="category" class="form-control"
                                        value="{{ old('category', $product->category) }}">
                                </div>

                                {{-- Imagen --}}
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Ruta de imagen</label>
                                    <input type="text" name="pathimg" class="form-control"
                                        value="{{ old('pathimg', $product->pathimg) }}">
                                </div>

                                {{-- Precio --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label">Precio</label>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control"
                                        value="{{ old('price', $product->price) }}" required>
                                </div>

                                {{-- Stock --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" min="0" name="stock" class="form-control"
                                        value="{{ old('stock', $product->stock) }}" required>
                                </div>

                                {{-- Switches --}}
                                <div class="col-12 col-lg-6 d-flex align-items-end">
                                    <div class="d-flex gap-4 flex-wrap">

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="decant" value="1"
                                                id="decantSwitch" {{ old('decant', $product->decant) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="decantSwitch">Es decant</label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="active" value="1"
                                                id="activeSwitch" {{ old('active', $product->active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="activeSwitch">Activo</label>
                                        </div>

                                    </div>
                                </div>

                                {{-- Descripción --}}
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                                </div>

                                {{-- Descripción corta --}}
                                <div class="col-12">
                                    <label class="form-label">Descripción corta</label>
                                    <textarea name="shortDescription" class="form-control" rows="2">{{ old('shortDescription', $product->shortDescription) }}</textarea>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- BOTONES --}}
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('product.index') }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>

                                <button type="submit" class="btn btn-dark px-4" id="btnGuardar">
                                    <span class="btn-text">Actualizar producto</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
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
            const form = document.querySelector('form');
            const btn = document.getElementById('btnGuardar');
            const text = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');

            form.addEventListener('submit', function() {
                btn.disabled = true;
                text.textContent = 'Actualizando...';
                spinner.classList.remove('d-none');
            });
        });
    </script>
@endsection
