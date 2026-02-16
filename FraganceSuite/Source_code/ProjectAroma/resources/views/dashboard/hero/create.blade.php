@extends('partsAdmin.header')

@section('title', 'Agregar Imagen Hero')

@section('content')
<main class="content-wrap">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-plus-circle me-2 text-primary"></i>
                Agregar Imagen Hero
            </h2>
            <a href="{{ route('hero.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('hero.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-4">
                        <!-- Columna izquierda: Información -->
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">
                                    <i class="fas fa-tag me-2 text-muted"></i>Título (opcional)
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       placeholder="Ej: Colección Verano 2026"
                                       value="{{ old('title') }}">
                                <div class="form-text">
                                    Solo para referencia interna, no se muestra en el sitio.
                                </div>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="active" 
                                                   name="active" 
                                                   checked
                                                   style="width: 3em; height: 1.5em;">
                                            <label class="form-check-label fw-semibold ms-2" for="active">
                                                <i class="fas fa-eye me-2"></i>
                                                Activar como Hero Principal
                                            </label>
                                        </div>
                                        <p class="text-info small mb-0 mt-3">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Al activar esta imagen, todas las demás se desactivarán automáticamente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha: Imagen -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label fw-semibold">
                                    <i class="fas fa-image me-2 text-muted"></i>Imagen Hero *
                                </label>
                                
                                <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center bg-light"
                                     id="uploadArea"
                                     style="cursor: pointer; transition: all 0.3s;">
                                    <input type="file" 
                                           class="d-none" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*" 
                                           required>
                                    
                                    <div id="uploadPrompt">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <h6 class="mb-2">Haz clic o arrastra una imagen</h6>
                                        <p class="text-muted small mb-0">1920 x 350 píxeles recomendado</p>
                                        <p class="text-muted small">JPG, PNG o WEBP (Máx. 2MB)</p>
                                    </div>

                                    <div id="imagePreviewContainer" class="d-none">
                                        <img id="imagePreview" 
                                             src="#" 
                                             alt="Preview" 
                                             class="img-fluid rounded"
                                             style="max-height: 200px; width: 100%; object-fit: contain;">
                                    </div>
                                </div>

                                @error('image')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                        <a href="{{ route('hero.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i>Guardar Hero
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
    .border-dashed {
        border-style: dashed !important;
    }
    .upload-area:hover {
        background-color: #e9ecef !important;
        border-color: #927a1b !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImage = document.getElementById('imagePreview');

        // Click en el área de upload
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });

        // Arrastrar y soltar
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('border-primary', 'bg-light');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('border-primary', 'bg-light');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('border-primary', 'bg-light');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                previewImageFile(e.dataTransfer.files[0]);
            }
        });

        // Cambio de archivo
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                previewImageFile(this.files[0]);
            }
        });

        function previewImageFile(file) {
            // Validar tipo de archivo
            if (!file.type.match('image.*')) {
                alert('Por favor selecciona una imagen válida');
                return;
            }

            // Validar tamaño (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar los 2MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                uploadPrompt.classList.add('d-none');
                previewContainer.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection