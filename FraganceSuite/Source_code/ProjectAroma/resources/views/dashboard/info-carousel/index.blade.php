@extends('partsAdmin.header')

@section('title', 'Mensajes Informativos')

@section('styles')
<style>
    .sortable-handle {
        cursor: move;
        padding: 8px;
        text-align: center;
        vertical-align: middle;
        width: 40px;
    }
    .sortable-handle i {
        font-size: 18px;
        color: #999;
    }
    .sortable-handle:hover i {
        color: #333;
    }
    .drag-row {
        transition: background 0.2s;
    }
    .drag-row.dragging {
        background: #f5f5f5;
        opacity: 0.6;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Mensajes del Carrusel</h4>
                    <p class="text-muted mb-0">Administra los mensajes que se muestran en el carrusel superior.</p>
                </div>
                <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#createModal">
                    + Agregar Mensaje
                </button>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px"></th>
                            <th>Mensaje</th>
                            <th>Enlace</th>
                            <th>Orden</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-items">
                        @foreach($items as $item)
                        <tr data-id="{{ $item->id }}" data-position="{{ $item->order_position }}" class="drag-row">
                            <td class="sortable-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </td>
                            <td>{{ Str::limit($item->message, 80) }}</td>
                            <td>
                                @if($item->link)
                                    <a href="{{ $item->link }}" target="_blank" class="small">{{ $item->link_text ?: 'Enlace' }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $item->order_position }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-active" type="checkbox" 
                                        data-id="{{ $item->id }}"
                                        {{ $item->active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-btn" 
                                    data-id="{{ $item->id }}"
                                    data-message="{{ $item->message }}"
                                    data-link="{{ $item->link }}"
                                    data-link_text="{{ $item->link_text }}"
                                    data-order="{{ $item->order_position }}"
                                    data-active="{{ $item->active ? 1 : 0 }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn" 
                                    data-id="{{ $item->id }}"
                                    data-message="{{ $item->message }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($items->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay mensajes. ¡Agrega tu primer mensaje!</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.info-carousel.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mensaje *</label>
                        <textarea name="message" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enlace (URL)</label>
                        <input type="url" name="link" class="form-control" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto del enlace</label>
                        <input type="text" name="link_text" class="form-control" placeholder="Ver más">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" name="order_position" class="form-control" value="{{ $items->count() + 1 }}">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="activeCreate" checked>
                        <label class="form-check-label" for="activeCreate">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mensaje *</label>
                        <textarea name="message" id="editMessage" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enlace (URL)</label>
                        <input type="url" name="link" id="editLink" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto del enlace</label>
                        <input type="text" name="link_text" id="editLinkText" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" name="order_position" id="editOrder" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="editActive">
                        <label class="form-check-label" for="editActive">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sortable para ordenar
        const tbody = document.getElementById('sortable-items');
        if (tbody) {
            new Sortable(tbody, {
                handle: '.sortable-handle',
                animation: 150,
                onEnd: function() {
                    const rows = tbody.querySelectorAll('tr');
                    const orders = [];
                    rows.forEach((row, index) => {
                        const id = row.dataset.id;
                        orders.push({
                            id: id,
                            position: index + 1
                        });
                        row.querySelector('td:nth-child(4)').textContent = index + 1;
                    });
                    
                    fetch('{{ route("admin.info-carousel.update-order") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ orders: orders })
                    });
                }
            });
        }
        
        // Toggle activo/inactivo
        document.querySelectorAll('.toggle-active').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const id = this.dataset.id;
                fetch('{{ url("admin/info-carousel/toggle") }}/' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            });
        });
        
        // Editar
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const message = this.dataset.message;
                const link = this.dataset.link;
                const linkText = this.dataset.link_text;
                const order = this.dataset.order;
                const active = this.dataset.active == 1;
                
                document.getElementById('editMessage').value = message;
                document.getElementById('editLink').value = link || '';
                document.getElementById('editLinkText').value = linkText || '';
                document.getElementById('editOrder').value = order;
                document.getElementById('editActive').checked = active;
                
                const form = document.getElementById('editForm');
                form.action = '{{ url("admin/info-carousel") }}/' + id;
                
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
        });
        
        // Eliminar
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const message = this.dataset.message;
                
                Swal.fire({
                    title: '¿Eliminar mensaje?',
                    html: `<strong>${message}</strong><br>Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ url("admin/info-carousel") }}/' + id;
                        form.innerHTML = '@csrf @method("DELETE")';
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection