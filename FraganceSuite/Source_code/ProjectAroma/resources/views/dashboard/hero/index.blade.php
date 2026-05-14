@extends('partsAdmin.header')

@section('title', 'Hero - Dashboard')

@section('content')
<main class="content-wrap">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2 class="mb-0">Hero Principal</h2>
            <a href="{{ route('hero.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Imagen Hero
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                @if($heroes->isEmpty())
                    <div class="text-center py-5">
                        <p class="text-muted mb-3">No hay imágenes en el hero.</p>
                        <a href="{{ route('hero.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Agregar primera imagen
                        </a>
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <i class="fas fa-info-circle fs-5 me-3"></i>
                            <div>
                                <strong class="d-block">¿Cómo funciona el Hero?</strong>
                                <span>Solo la imagen marcada como <span class="badge bg-success">Activo</span> se mostrará en la página principal. Las imágenes inactivas están guardadas pero no visibles.</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($heroes as $hero)
                                <tr class="{{ $hero->active ? 'table-success' : '' }}">
                                    <td>#{{ $hero->idhero }}</td>
                                    <td>
                                        @if($hero->image)
                                            <img src="{{ asset('img-hero/' . $hero->image) }}" 
                                                 alt="{{ $hero->title ?? 'Hero' }}" 
                                                 class="img-fluid"
                                                 style="max-width: 100px; height: auto; max-height: 60px; object-fit: cover; border: 1px solid #dee2e6;">
                                        @else
                                            <span class="text-muted">Sin imagen</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $hero->title ?? '—' }}
                                        @if(!$hero->title)
                                            <small class="text-muted d-block">Sin título</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hero->active)
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-2">
                                                <i class="fas fa-eye-slash me-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="mostrarModalEliminar({{ $hero->idhero }})"
                                                data-bs-toggle="tooltip" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                        
                                        <form id="form-eliminar-{{ $hero->idhero }}" 
                                              action="{{ route('hero.destroy', $hero->idhero) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-3 bg-light">
                        <div class="d-flex flex-wrap gap-2">
                            <i class="fas fa-lightbulb text-warning me-3 mt-1"></i>
                            <div>
                                <strong class="d-block">Recomendación:</strong>
                                <p class="text-muted mb-0">
                                    Mantén solo 1 imagen activa a la vez. Las imágenes inactivas puedes guardarlas como respaldo.
                                    <br>
                                    <span class="small">Medida ideal: <strong>1920x350px</strong> | Formato: PNG, JPG, WEBP | Peso máx: 2MB</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

<!-- Modal -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border: none; border-radius: 0; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-body p-4">
                <p class="text-center mb-4" style="font-size: 0.95rem;">¿Eliminar esta imagen?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal" style="border: 1px solid #dee2e6; border-radius: 0;">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm px-4" id="btnConfirmarEliminar" style="border-radius: 0;">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let idAEliminar = null;
    
    function mostrarModalEliminar(id) {
        idAEliminar = id;
        var modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
        modal.show();
    }
    
    document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
        if (idAEliminar) {
            document.getElementById('form-eliminar-' + idAEliminar).submit();
        }
    });
    
    // Activar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection


