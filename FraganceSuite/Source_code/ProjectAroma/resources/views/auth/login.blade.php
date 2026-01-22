@extends('layouts.guest')

@section('content')
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">Iniciar sesión</h3>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label>Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember">
                            <label class="form-check-label">Recordarme</label>
                        </div>

                        <div class="d-grid mb-3">
                            <button class="btn btn-dark">Entrar</button>
                        </div>

                        <hr>
                        <div class="text-center mb-3">
                            <a class="text-decoration-none" data-bs-toggle="collapse" href="#socialLogin">
                                Continuar con redes sociales
                            </a>
                        </div>

                        <div class="collapse" id="socialLogin">
                            <div class="d-grid gap-2 mb-3">
                                <a href="{{ url('/auth/google/redirect') }}" class="btn btn-outline-danger">
                                    <i class="fab fa-google me-2"></i> Continuar con Google
                                </a>

                                <a href="{{ url('/auth/facebook/redirect') }}" class="btn btn-outline-primary">
                                    <i class="fab fa-facebook-f me-2"></i> Continuar con Facebook
                                </a>
                            </div>
                        </div>


                        <hr>

                        <div class="text-center">
                            <p class="mb-2">¿No tienes cuenta?</p>
                            <a href="{{ route('register') }}" class="btn btn-outline-dark w-100">
                                Crear cuenta
                            </a>
                        </div>

                        <hr>

                        <div class="text-center">
                            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
