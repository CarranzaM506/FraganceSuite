@extends('layouts.app')

@section('title', 'Mis Pedidos | AROMA')

@section('body-class', 'profile-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesOrders.css') }}">
@endsection

@section('content')
<div id="aroma-orders-page">

    <div class="orders-page-header">
        <div class="orders-page-header-left">
            <a href="{{ route('profile.index') }}" class="orders-back-btn">
                ← Volver
            </a>
            <h4>Mis pedidos</h4>
        </div>
    </div>

    @forelse ($orders as $order)
        <div class="order-card">
            <div class="order-card-body">
                <div class="order-col order-col-id">
                    <span class="order-label">Pedido</span>
                    <span class="order-value order-number">#{{ $order->idorder }}</span>
                </div>
                <div class="order-col order-col-date">
                    <span class="order-label">Fecha</span>
                    <span class="order-value">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</span>
                </div>
                <div class="order-col order-col-state">
                    <span class="order-label">Estado</span>
                    <span class="order-badge {{ $order->state == 2 ? 'order-badge-sent' : 'order-badge-pending' }}">
                        {{ $order->state == 2 ? 'Enviado' : 'Pendiente' }}
                    </span>
                </div>
                <div class="order-col order-col-total">
                    <span class="order-label">Total</span>
                    <span class="order-value order-total">₡{{ number_format($order->total, 2) }}</span>
                </div>
                <div class="order-col order-col-guide">
                    <span class="order-label">Número de guía</span>
                    <span class="order-value {{ !$order->guidenumber ? 'order-guide-empty' : '' }}">
                        {{ $order->guidenumber ?: 'Sin guía' }}
                    </span>
                </div>
            </div>
        </div>
    @empty
        <div class="orders-empty">
            <i class="fas fa-box-open"></i>
            <p>Aún no tienes pedidos registrados.</p>
        </div>
    @endforelse

</div>
@endsection
