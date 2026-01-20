@extends('layouts.guest')

@section('content')
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-4">

                <h3 class="text-center mb-4">Recuperar contraseña</h3>

                <p class="text-muted text-center mb-4">
                    Ingresá tu correo y te enviaremos un enlace para restablecer tu contraseña.
                </p>

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button class="btn btn-dark">Enviar enlace</button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}">Volver al login</a>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
