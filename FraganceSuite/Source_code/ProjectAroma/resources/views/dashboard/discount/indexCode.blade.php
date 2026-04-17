@extends('partsAdmin.header')

@section('title', 'Ver codigos promocionales')

@section('content')
    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Listado de codigos promocionales</h4>
                    <a href="{{ route('promotionCode.create') }}" class="btn btn-dark">Crear codigo</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <table id="promotionCodesTable" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Codigo</th>
                            <th>Descuento (%)</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estado</th>
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
                                <td>{{ $code->code_promotion }}</td>
                                <td>{{ number_format($code->value, 2) }}</td>
                                <td>{{ $startDate ? $startDate->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $endDate ? $endDate->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    @if ($startDate && $startDate->gt($now))
                                        <span class="badge bg-warning text-dark">Proximo</span>
                                    @elseif ($endDate && $endDate->lt($now))
                                        <span class="badge bg-danger">Vencido</span>
                                    @else
                                        <span class="badge bg-success">Activo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay codigos registrados.</td>
                            </tr>
                        @endforelse
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
            $('#promotionCodesTable').DataTable({
                pageLength: 10,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        });
    </script>
@endsection
