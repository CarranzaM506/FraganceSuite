@extends('partsAdmin.header')

@section('title', 'Agregar Mensaje')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">
            <div class="card shadow border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Agregar Mensaje</h3>
                            <p class="text-muted mb-0">Crea un nuevo mensaje para el carrusel superior.</p>
                        </div>
                        <a href="{{ route('admin.info-carousel.index') }}" class="btn btn-outline-secondary">
                            Volver
                        </a>
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

                    <form method="POST" action="{{ route('admin.info-carousel.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Mensaje *</label>
                                <textarea name="message" class="form-control" rows="3" required>{{ old('message') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Enlace (URL)</label>
                                <input type="url" name="link" class="form-control" placeholder="https://..." value="{{ old('link') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Texto del enlace</label>
                                <input type="text" name="link_text" class="form-control" placeholder="Ver más" value="{{ old('link_text') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Orden</label>
                                <input type="number" name="order_position" class="form-control" value="{{ old('order_position', $nextOrder ?? 1) }}">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="active" value="1" id="activeSwitch" checked>
                                    <label class="form-check-label" for="activeSwitch">Activo</label>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.info-carousel.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-dark px-4" id="btnGuardar">
                                <span class="btn-text">Guardar mensaje</span>
                                <span class="spinner-border spinner-border-sm d-none"></span>
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
            text.textContent = 'Guardando...';
            spinner.classList.remove('d-none');
        });
    });
</script>
@endsection