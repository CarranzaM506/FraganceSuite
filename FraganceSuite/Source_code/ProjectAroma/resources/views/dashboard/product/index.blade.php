@extends('partsAdmin.header')

@section('title', 'Ver Productos')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="mb-4">Listado de Productos</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <!-- Contenedor con scroll horizontal (como la tabla de marcas) -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" id="productsTable">
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
                                        <img src="{{ $product->pathimg }}" alt="Imagen" width="45" class="rounded">
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->brand }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>₡{{ number_format($product->price, 0) }}</td>
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
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('product.edit', $product->idproduct) }}" class="btn btn-sm btn-warning">Editar</a>
                                            <form method="POST" action="{{ route('product.destroy', $product->idproduct) }}" class="delete-form d-inline-block" onsubmit="return confirmDelete(this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                    <span class="btn-text">Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
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

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    function isMobile() {
        return window.innerWidth < 768;
    }

    function initDataTable() {
        if (!isMobile()) {
            // Solo en desktop: activar DataTables
            if ($.fn.DataTable.isDataTable('#productsTable')) {
                $('#productsTable').DataTable().destroy();
            }
            $('#productsTable').DataTable({
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    paginate: {
                        previous: "←",
                        next: "→"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [0, 8] }
                ]
            });
        } else {
            // En móvil: destruir DataTables completamente
            if ($.fn.DataTable.isDataTable('#productsTable')) {
                $('#productsTable').DataTable().destroy();
            }
            // Eliminar cualquier wrapper que DataTables haya dejado
            $('.dataTables_wrapper').contents().unwrap();
        }
    }

    $(document).ready(function() {
        initDataTable();
        $(window).resize(function() {
            initDataTable();
        });
    });

    function confirmDelete(form) {
        const confirmMsg = confirm("¿Estás seguro de que deseas eliminar este producto?");
        if (!confirmMsg) return false;

        const button = form.querySelector('.delete-btn');
        const spinner = button.querySelector('.spinner-border');
        const btnText = button.querySelector('.btn-text');

        spinner.classList.remove('d-none');
        btnText.textContent = " Eliminando...";
        button.disabled = true;

        return true;
    }
</script>
@endsection