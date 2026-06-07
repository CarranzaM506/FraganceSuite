@extends('partsAdmin.header')

@section('title', 'Mensajes Informativos')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Mensajes del Carrusel</h4>
                    <p class="text-muted mb-0">Administra los mensajes que se muestran en el carrusel superior.</p>
                </div>
                <a href="{{ route('admin.info-carousel.create') }}" class="btn btn-dark">
                    + Agregar Mensaje
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Desktop: tabla -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px">Orden</th>
                            <th>Mensaje</th>
                            <th>Enlace</th>
                            <th style="width: 80px">Activo</th>
                            <th style="width: 160px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-items">
                        @foreach($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td>
                                <i class="fas fa-grip-vertical text-muted me-2" style="cursor: move;"></i>
                                {{ $item->order_position }}
                            </td>
                            <td>{{ Str::limit($item->message, 80) }}</td>
                            <td>
                                @if($item->link)
                                    <a href="{{ $item->link }}" target="_blank" class="small">{{ $item->link_text ?: 'Enlace' }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-active" type="checkbox" 
                                        data-id="{{ $item->id }}"
                                        {{ $item->active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.info-carousel.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
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

            <!-- Mobile: cards -->
            <div class="d-md-none">
                @foreach($items as $item)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <p class="mb-1"><strong>Orden:</strong> {{ $item->order_position }}</p>
                            <p class="mb-1"><strong>Mensaje:</strong> {{ $item->message }}</p>
                            <p class="mb-1"><strong>Enlace:</strong> 
                                @if($item->link)
                                    <a href="{{ $item->link }}" target="_blank">{{ $item->link_text ?: 'Ver enlace' }}</a>
                                @else
                                    —
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Activo:</strong>
                                <span class="badge {{ $item->active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $item->active ? 'Sí' : 'No' }}
                                </span>
                            </p>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.info-carousel.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-btn-mobile" 
                                    data-id="{{ $item->id }}"
                                    data-message="{{ $item->message }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($items->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay mensajes. ¡Agrega tu primer mensaje!</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sortable para ordenar
        const tbody = document.getElementById('sortable-items');
        if (tbody) {
            new Sortable(tbody, {
                handle: 'td:first-child',
                animation: 150,
                onEnd: function() {
                    const rows = tbody.querySelectorAll('tr');
                    const orders = [];
                    rows.forEach((row, index) => {
                        const id = row.dataset.id;
                        const position = index + 1;
                        orders.push({ id: id, position: position });
                        row.querySelector('td:first-child').innerHTML = 
                            '<i class="fas fa-grip-vertical text-muted me-2" style="cursor: move;"></i> ' + position;
                    });
                    
                    fetch('/admin/info-carousel/update-order', {
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
        
        // Toggle activo
        document.querySelectorAll('.toggle-active').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const id = this.dataset.id;
                fetch('/admin/info-carousel/toggle/' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            });
        });
        
        // Eliminar
        function deleteItem(id, message) {
            openAdminDeleteConfirm(message, function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/info-carousel/' + id;
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"> <input type="hidden" name="_method" value="DELETE">';
                document.body.appendChild(form);
                form.submit();
            });
        }
        
        document.querySelectorAll('.delete-btn, .delete-btn-mobile').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                deleteItem(this.dataset.id, this.dataset.message);
            });
        });
    });
</script>
@endsection