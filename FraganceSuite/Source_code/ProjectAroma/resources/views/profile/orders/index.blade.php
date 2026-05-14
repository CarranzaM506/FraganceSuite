@extends('layouts.app')

@section('title', 'Mis pedidos | AROMA')

@section('body-class', 'profile-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesLocation.css') }}">
@endsection

@section('content')
<div id="aroma-location-page">

    <div class="location-page-header">
        <div class="location-page-header-left">
            <a href="{{ route('profile.index') }}" class="location-back-btn">
                ← Volver
            </a>
            <h4>Mis pedidos</h4>
        </div>
    </div>

    @forelse ($orders as $order)
        @if ($loop->first)
        <div class="orders-table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th># Pedido</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>N° Guía</th>
                    </tr>
                </thead>
                <tbody>
        @endif
                    <tr>
                        <td class="order-id">#{{ $order->idorder }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</td>
                        <td>
                            @if ($order->state == 2)
                                <span class="badge-estado enviado">Enviado</span>
                            @else
                                <span class="badge-estado pendiente">Pendiente</span>
                            @endif
                        </td>
                        <td class="order-total">₡{{ number_format($order->total, 2, '.', ',') }}</td>
                        <td class="order-guide">{{ $order->guidenumber ?: 'Sin guía' }}</td>
                    </tr>
        @if ($loop->last)
                </tbody>
            </table>
        </div>
        @endif
    @empty
        <div class="location-empty">
            <i class="fas fa-box-open"></i>
            <p>Aún no tienes pedidos registrados.</p>
        </div>
    @endforelse

</div>

<style>
    .orders-table-wrapper {
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .orders-table thead tr {
        border-bottom: 2px solid #000;
    }

    .orders-table th {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #999;
        padding: 12px 16px;
        text-align: left;
        white-space: nowrap;
    }

    .orders-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s;
    }

    .orders-table tbody tr:hover {
        background: #fafafa;
    }

    .orders-table td {
        padding: 16px;
        color: #333;
        vertical-align: middle;
    }

    .order-id {
        font-weight: 700;
        color: #000;
    }

    .order-total {
        font-weight: 600;
        color: #000;
    }

    .order-guide {
        color: #999;
        font-size: 12px;
    }

    .badge-estado {
        display: inline-block;
        padding: 4px 12px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .badge-estado.pendiente {
        background: #fff3cd;
        color: #856404;
    }

    .badge-estado.enviado {
        background: #d4edda;
        color: #155724;
    }
</style>

@endsection
