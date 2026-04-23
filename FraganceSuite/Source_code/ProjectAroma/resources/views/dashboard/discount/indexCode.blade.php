@extends('partsAdmin.header')

@section('title', 'Ver codigos promocionales')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h4 class="mb-0">Listado de codigos promocionales</h4>
                    <a href="{{ route('promotionCode.create') }}" class="btn btn-dark btn-sm">Crear codigo</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="promotionCodesTable" class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Codigo</th>
                                <th>Descuento</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($codes as $code)
                                @php
                                    $start = $code->startdate ?? $code->start_date;
                                    $end = $code->enddate ?? $code->end_date;
                                    $now = \Carbon\Carbon::now();
                                    $startDate = $start ? \Carbon\Carbon::parse($start) : null;
                                    $endDate = $end ? \Carbon\Carbon::parse($end) : null;
                                @endphp
                                <tr>
                                    <td class="fw-medium">{{ $code->code_promotion }}</td>
                                    <td>{{ number_format($code->value, 2) }}%</td>
                                    <td>{{ $startDate ? $startDate->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $endDate ? $endDate->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if ($startDate && $startDate->gt($now))
                                            <span class="badge bg-warning text-dark">Proximo</span>
                                        @elseif ($endDate && $endDate->lt($now))
                                            <span class="badge bg-danger">Vencido</span>
                                        @else
                                            <span class="badge bg-success">Activo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 flex-wrap justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning">Editar</button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-id="{{ $code->idcode_promotion }}" 
                                                data-code="{{ $code->code_promotion }}">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay codigos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <p class="mb-0" id="deleteModalMessage">¿Estás seguro de que deseas eliminar este código de promoción?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-2 border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger btn-sm px-4" id="confirmDeleteBtn">Sí, eliminar</button>
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
$.fn.dataTable.ext.errMode = 'none';
        function initDataTable() {
            if (!isMobile()) {
                if ($.fn.DataTable.isDataTable('#promotionCodesTable')) {
                    $('#promotionCodesTable').DataTable().destroy();
                }
                $('#promotionCodesTable').DataTable({
                    pageLength: 10,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                        paginate: {
                            previous: "←",
                            next: "→"
                        }
                    },
                    columnDefs: [
                        { orderable: false, targets: [5] }
                    ]
                });
            } else {
                if ($.fn.DataTable.isDataTable('#promotionCodesTable')) {
                    $('#promotionCodesTable').DataTable().destroy();
                }
                $('.dataTables_wrapper').contents().unwrap();
            }
        }

        $(document).ready(function() {
            initDataTable();
            $(window).resize(function() {
                initDataTable();
            });
        });

        // Modal de confirmación para eliminar
        let deleteId = null;

        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            deleteId = button.data('id');
            const code = button.data('code');
            
            const modal = $(this);
            modal.find('#deleteModalMessage').html(`¿Eliminar "${code}" de los códigos promocionales?`);
        });
        
        $('#confirmDeleteBtn').on('click', function() {
            if (deleteId) {
                // Crear formulario y enviar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/promotionCode/${deleteId}`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
@endsection