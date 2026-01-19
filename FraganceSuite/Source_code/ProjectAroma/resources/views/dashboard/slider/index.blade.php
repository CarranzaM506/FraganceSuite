@extends('partsAdmin.header')

@section('title', 'Slider - Dashboard')

@section('content')
<main class="content-wrap">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Slider Principal</h2>
            <a href="{{ route('slider.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Imagen
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
                @if($sliders->isEmpty())
                    <div class="text-center py-5">
                        <p class="text-muted">No hay imágenes en el slider.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Imagen</th>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sliders as $slider)
                                <tr>
                                    <td>{{ $slider->order }}</td>
                                    <td>
                                        @if($slider->image_url)
                                            <img src="{{ asset($slider->image_url) }}" alt="{{ $slider->title }}" 
                                                 style="width: 150px; height: 80px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <span class="text-muted">Sin imagen</span>
                                        @endif
                                    </td>
                                    <td>{{ $slider->title ?? 'Sin título' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $slider->active ? 'success' : 'secondary' }}">
                                            {{ $slider->active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('slider.edit', $slider->idslider) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('slider.destroy', $slider->idslider) }}" method="POST" 
                                                  onsubmit="return confirm('¿Eliminar esta imagen del slider?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection