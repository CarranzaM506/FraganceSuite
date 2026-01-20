@extends('layouts.guest')

@section('content')
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-4">

                <h3 class="text-center mb-4">Restablecer contraseña</h3>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-dark">Cambiar contraseña</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection