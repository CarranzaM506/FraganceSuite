@extends('partsAdmin.header')

@section('title', 'Ver Promociones')

@section('content')
    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="mb-4">Listado de Promociones</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <table id="discountsTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Condición</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($discounts as $d)
                            <tr>
                                <td>
                                    {{ $d->startdate }}
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $s = \Carbon\Carbon::parse($d->startdate);
                                    @endphp
                                    @if($s->gt($now))
                                            @php $sdiff = (int) ceil($s->diffInDays($now)); @endphp
                                            <span class="badge bg-warning text-dark">Inicia en {{ $sdiff }} día{{ $sdiff>1?'s':'' }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $d->enddate }}
                                    @php
                                        $e = \Carbon\Carbon::parse($d->enddate);
                                    @endphp
                                    @if($e->lt($now))
                                        @php $ediff = (int) ceil($e->diffInDays($now)); @endphp
                                        <span class="badge bg-danger">Venció hace {{ $ediff }} día{{ $ediff>1?'s':'' }}</span>
                                    @else
                                        @php $ediff = (int) ceil($now->diffInDays($e)); @endphp
                                        @if($ediff <= 7)
                                            <span class="badge bg-warning text-dark">Quedan {{ $ediff }} día{{ $ediff>1?'s':'' }}</span>
                                        @else
                                            <span class="badge bg-success">Quedan {{ $ediff }} día{{ $ediff>1?'s':'' }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $d->condition }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-id="{{ $d->iddiscount }}" onclick="showProducts(this)">Ver productos</button>
                                </td>
                                <td>
                                    <a href="{{ route('discount.edit', $d->iddiscount) }}" class="btn btn-sm btn-warning m-2">Editar</a>

                                    <form method="POST" action="{{ route('discount.destroy', $d->iddiscount) }}" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta promoción?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal productos -->
    <div class="modal fade" id="productsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Productos en la promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="productsModalBody">
                    <!-- Contenido via JS -->
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
        $(document).ready(function() {
            $('#discountsTable').DataTable({
                pageLength: 10,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        });

        function showProducts(btn) {
            const id = btn.getAttribute('data-id');
            fetch(`/discount/${id}/products`)
                .then(r => r.json())
                .then(data => {
                    const body = document.getElementById('productsModalBody');
                    body.innerHTML = '';
                    if (data.products.length === 0) {
                        body.innerHTML = '<p>No hay productos con esta promoción.</p>';
                    } else {
                        let html = '<div class="row">';
                        data.products.forEach(p => {
                            html += `
                                <div class="col-12 col-md-6 d-flex gap-3 mb-3">
                                    <img src="${p.pathimg}" width="80" class="rounded" />
                                    <div>
                                        <div class="fw-bold">${p.name}</div>
                                        <div class="text-muted">${p.brand}</div>
                                        <div><small><del>₡${p.old_price}</del> <span class="fw-bold">₡${p.new_price}</span></small></div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        body.innerHTML = html;
                    }
                    var modal = new bootstrap.Modal(document.getElementById('productsModal'));
                    modal.show();
                })
                .catch(err => alert('Error cargando productos'));
        }
    </script>

@endsection
