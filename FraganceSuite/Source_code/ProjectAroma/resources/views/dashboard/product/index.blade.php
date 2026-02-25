@extends('partsAdmin.header')

@section('title', 'Ver Productos')

@section('content')
    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="mb-4">Listado de Productos</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                <table id="productsTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Decant</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ $product->pathimg }}" alt="Imagen" width="50" class="rounded">
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->brand }}</td>
                                <td>{{ $product->category }}</td>
                                <td>₡{{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    <span class="badge {{ $product->decant ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->decant ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->active ? 'bg-primary' : 'bg-danger' }}">
                                        {{ $product->active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    {{-- Editar --}}
                                    <a href="{{ route('product.edit', $product->idproduct) }}"
                                        class="btn btn-sm btn-warning m-2">Editar</a>

                                    {{-- Eliminar --}}
                                    <form method="POST" action="{{ route('product.destroy', $product->idproduct) }}"
                                        class="delete-form" onsubmit="return confirmDelete(this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span>
                                            <span class="btn-text">Eliminar</span>
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable({
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });
    </script>

    <script>
        function confirmDelete(form) {
            const confirmMsg = confirm("¿Estás seguro de que deseas eliminar este producto?");
            if (!confirmMsg) return false;

            // Buscar botón dentro del formulario
            const button = form.querySelector('.delete-btn');
            const spinner = button.querySelector('.spinner-border');
            const btnText = button.querySelector('.btn-text');

            // Mostrar spinner y desactivar botón
            spinner.classList.remove('d-none');
            btnText.textContent = " Eliminando...";
            button.disabled = true;

            return true; // Permitir envío
        }
    </script>

@endsection
