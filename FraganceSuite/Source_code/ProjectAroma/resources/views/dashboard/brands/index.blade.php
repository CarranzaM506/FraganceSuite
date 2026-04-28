@extends('partsAdmin.header')

@section('title', 'Gestionar Logos de Marcas')

@section('content')
<div class="content-wrap">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2>Logos de Marcas</h2>
        <a href="{{ route('brands.sync') }}" class="btn btn-secondary">Sincronizar Marcas</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="brandsTable">
                    <thead>
                        <tr>
                            <th>Logo Actual</th>
                            <th>Marca</th>
                            <th>Productos</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                        @php
                            $productCount = \App\Models\Product::where('brand', $brand->brand_name)->count();
                        @endphp
                        <tr>
                            <td>
                                @if($brand->logo)
                                    <img src="{{ Storage::url($brand->logo) }}" style="height: 50px; width: auto;">
                                @else
                                    <span class="text-muted">Sin logo</span>
                                @endif
                            </td>
                            <td><strong>{{ $brand->brand_name }}</strong></td>
                            <td>{{ $productCount }} productos</td>
                            <td>{{ $brand->order }}</td>
                            <td>
                                <span class="badge bg-{{ $brand->active ? 'success' : 'danger' }}">
                                    {{ $brand->active ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('brands.edit', $brand->id) }}" class="btn btn-sm btn-warning">
                                    {{ $brand->logo ? 'Cambiar Logo' : 'Subir Logo' }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection