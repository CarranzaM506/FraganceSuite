@extends('layouts.app')

@section('page-name', 'Pedido Confirmado')

@section('body-class', 'cart-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endsection

@section('content')

<style>
    .success-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
    }

    .success-card {
        background: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 500px;
        width: 100%;
    }

    .success-icon {
        font-size: 50px;
        color: #28a745;
        margin-bottom: 10px;
    }

    .success-card h1 {
        margin-bottom: 10px;
    }

    .success-card p {
        color: #555;
        margin: 8px 0;
    }

    .highlight {
        font-weight: bold;
        color: #000;
    }

    .btn-custom {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #000;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
    }

    .btn-custom:hover {
        background-color: #0056b3;
    }
</style>

<div class="success-wrapper">

    <div class="success-card">

        <h1>¡Compra realizada con éxito!</h1>

        <p>Gracias por tu compra. Tu pedido ha sido procesado correctamente.</p>

        <p>🧾 Número de orden: <span class="highlight">#{{ $order->idorder }}</span></p>

        <p>💰 Total pagado:
            <span class="highlight">
                ₡{{ number_format($order->total, 2, '.', ',') }}
            </span>
        </p>

        <hr style="margin: 20px 0;">

        <p>
            📦 El número de guía de tu envío será generado una vez que tu pedido sea despachado.
        </p>

        <p>
            Podrás consultar el estado de tu pedido y el número de guía en la sección
            <span class="highlight">"Mis pedidos"</span> dentro de tu perfil.
        </p>

        <a href="/" class="btn-custom">Seguir comprando</a>

    </div>

</div>

@endsection

