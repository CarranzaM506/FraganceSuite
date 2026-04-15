@extends('partsAdmin.header')

@section('title', 'Ver Promociones')

@section('content')
<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Listado de Promociones</h4>
                <a href="{{ route('discount.create') }}" class="btn btn-dark btn-sm">+ Nueva Promoción</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="discountsTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Descuento</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($discounts as $d)
                        <tr>
                            <td>
                                <span class="badge bg-dark">{{ $d->value }}%</span>
                            </td>
                            <td class="text-nowrap">
                                {{ \Carbon\Carbon::parse($d->startdate)->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-nowrap">
                                {{ \Carbon\Carbon::parse($d->enddate)->format('d/m/Y H:i') }}
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $e = \Carbon\Carbon::parse($d->enddate);
                                    $ediff = (int) ceil($now->diffInDays($e, false));
                                @endphp
                                @if($e->lt($now))
                                    <span class="badge bg-danger ms-1">Vencida</span>
                                @elseif($ediff <= 3)
                                    <span class="badge bg-warning ms-1">{{ $ediff }} día(s)</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-outline-secondary btn-sm" data-id="{{ $d->iddiscount }}" onclick="showProducts(this)">
                                    Ver ({{ $d->products->count() ?? 0 }})
                                </button>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('discount.edit', $d->iddiscount) }}" class="btn btn-warning btn-sm">Editar</a>
                                <form method="POST" action="{{ route('discount.destroy', $d->iddiscount) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta promoción?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="productsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Productos en promoción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="productsModalBody"></div>
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
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                paginate: {
                    previous: "←",
                    next: "→"
                }
            },
            columnDefs: [
                { orderable: false, targets: [3, 4] }
            ]
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
                    body.innerHTML = '<p class="text-muted text-center mb-0 py-3">No hay productos con esta promoción.</p>';
                } else {
                    let html = '<div class="row g-3">';
                    data.products.forEach(p => {
                        html += `
                            <div class="col-12 col-md-6 d-flex gap-3 align-items-center border-bottom pb-2">
                                <img src="${p.pathimg}" width="60" style="border: 1px solid #e5e5e5;">
                                <div>
                                    <div class="fw-medium">${p.name}</div>
                                    <div class="text-muted small">${p.brand}</div>
                                    <div class="small">
                                        <del class="text-muted">₡${p.old_price}</del>
                                        <span class="fw-medium ms-1">₡${p.new_price}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    body.innerHTML = html;
                }
                new bootstrap.Modal(document.getElementById('productsModal')).show();
            })
            .catch(() => alert('Error cargando productos'));
    }
</script>
@endsection