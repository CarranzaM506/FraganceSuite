@extends('partsAdmin.header')

@section('title', 'Editar Logo: ' . $brand->brand_name)

@section('content')
<div class="content-wrap">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">{{ $brand->brand_name }}</h4>
            
            <form action="{{ route('brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Logo de la marca *</label>
                    @if($brand->logo)
                        <div class="mb-2">
                            <img src="{{ Storage::url($brand->logo) }}" class="img-fluid" style="max-height: 80px; width: auto;">
                        </div>
                    @endif
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" 
                           accept="image/*" required>
                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Orden</label>
                    <input type="number" name="order" class="form-control" value="{{ $brand->order }}">
                    <small class="text-muted">Número menor = aparece primero</small>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="active" class="form-check-input" value="1" 
                           {{ $brand->active ? 'checked' : '' }}>
                    <label class="form-check-label">Mostrar en el carrusel</label>
                </div>

                <button type="submit" class="btn btn-dark">Guardar Logo</button>
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection