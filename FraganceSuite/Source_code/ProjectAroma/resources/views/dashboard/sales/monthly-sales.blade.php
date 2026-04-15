@extends('partsAdmin.header')

@section('title', 'Agregar producto')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4">Ventas Mensuales</h2>

        <form method="GET" class="mb-4 d-flex gap-2">
            <select name="year" class="form-select w-auto">
                <option value="">Todos los años</option>
                @foreach ($years ?? [] as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-dark">Filtrar</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Mes</th>
                        <th>Año</th>
                        <th>Total Ventas</th>
                        <th>Pedidos</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>

                    @if (empty($ventasMensuales) || count($ventasMensuales) === 0)
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <strong>No hay ventas registradas.</strong><br>
                                <small class="text-muted">
                                    ₡0.
                                </small>
                            </td>
                        </tr>
                    @else
                        @foreach ($ventasMensuales as $venta)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::create()->month($venta->month)->locale('es')->monthName }}
                                </td>
                                <td>{{ $venta->year }}</td>
                                <td>₡{{ number_format($venta->total_sales ?? 0, 2) }}</td>
                                <td>{{ $venta->total_orders ?? 0 }}</td>
                                <td>₡{{ number_format($venta->avg_order ?? 0, 2) }}</td>
                            </tr>
                        @endforeach

                    @endif

                </tbody>
            </table>
        </div>

    </div>

@endsection
