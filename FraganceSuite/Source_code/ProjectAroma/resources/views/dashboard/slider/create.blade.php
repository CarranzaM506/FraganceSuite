@extends('partsAdmin.header')

@section('title', 'Agregar Imagen al Slider')

@section('content')
<main class="content-wrap">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agregar Imagen al Slider</h2>
            <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('slider.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título (opcional)</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="Ej: Nueva Colección Verano">
                            </div>

                            <div class="mb-3">
                                <label for="order" class="form-label">Orden de aparición</label>
                                <input type="number" class="form-control" id="order" name="order" value="0" min="0">
                                <div class="form-text">Número más bajo aparece primero</div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                    <label class="form-check-label" for="active">Activo</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Imagen *</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                <div class="form-text">
                                    Tamaño recomendado: 1200x400px o similar.<br>
                                    Formatos: JPG, PNG, GIF, WEBP. Máx: 2MB.
                                </div>
                            </div>

                            <div class="mt-3">
                                <div id="imagePreview" 
                                     style="width: 100%; height: 200px; background: #f8f9fa; border: 2px dashed #dee2e6; 
                                            border-radius: 8px; display: flex; align-items: center; justify-content: center; 
                                            overflow: hidden; margin-bottom: 15px;">
                                    <span class="text-muted">Vista previa de la imagen</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('slider.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Imagen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    // Vista previa de imagen
    document.getElementById('image').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" 
                                          style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">`;
            }
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<span class="text-muted">Vista previa de la imagen</span>';
        }
    });
</script>
@endsection