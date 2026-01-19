@extends('partsAdmin.header')

@section('title', 'Editar Imagen del Slider')

@section('content')
<main class="content-wrap">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Editar Imagen del Slider</h2>
            <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('slider.update', $slider->idslider) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="{{ old('title', $slider->title) }}" placeholder="Título de la imagen">
                            </div>

                            <div class="mb-3">
                                <label for="order" class="form-label">Orden</label>
                                <input type="number" class="form-control" id="order" name="order" 
                                       value="{{ old('order', $slider->order) }}" min="0">
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" 
                                           {{ $slider->active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">Activo</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Imagen</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Dejar vacío para conservar la imagen actual.</div>
                            </div>

                            <div class="mt-3">
                                <p class="mb-2">Imagen actual:</p>
                                @if($slider->image_url)
                                    <div id="imagePreview" 
                                         style="width: 100%; height: 200px; background: #f8f9fa; border: 2px solid #dee2e6; 
                                                border-radius: 8px; display: flex; align-items: center; justify-content: center; 
                                                overflow: hidden; margin-bottom: 15px;">
                                        <img src="{{ asset($slider->image_url) }}" 
                                             style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">
                                    </div>
                                @else
                                    <div class="text-muted p-3 border rounded">No hay imagen</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('slider.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    // Vista previa de nueva imagen
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
        }
    });
</script>
@endsection