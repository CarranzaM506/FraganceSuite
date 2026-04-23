@extends('partsAdmin.header')

@section('title', 'Ventas Diarias')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4">Ventas Diarias</h2>

        <form method="GET" class="mb-4 d-flex gap-2 align-items-center">

            <input type="date" name="date" class="form-control w-auto" value="{{ request('date') }}">

            <button class="btn btn-dark">Filtrar</button>

        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" style="border-radius: 0;">

                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Total Ventas</th>
                        <th>Pedidos</th>
                        <th>Promedio</th>
                    </tr>
                </thead>

                <tbody>

                    @if (empty($ventasDiarias) || count($ventasDiarias) === 0)
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <strong>No hay ventas registradas.</strong><br>
                                <small class="text-muted">₡0.</small>
                            </td>
                        </tr>
                    @else
                        @foreach ($ventasDiarias as $venta)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($venta->day)->format('d/m/Y') }}
                                </td>
                                <td>₡{{ number_format($venta->totalSales ?? 0, 2) }}</td>
                                <td>{{ $venta->totalOrders ?? 0 }}</td>
                                <td>₡{{ number_format($venta->avgOrder ?? 0, 2) }}</td>
                            </tr>
                        @endforeach

                    @endif

                </tbody>

            </table>
        </div>

    </div>

@endsection
