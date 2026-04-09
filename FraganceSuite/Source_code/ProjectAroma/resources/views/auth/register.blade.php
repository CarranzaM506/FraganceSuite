@extends('layouts.app')

@section('content')
<style>
/* ========== REGISTRO ESTILO AROMA ========== */
.register-wrapper {
    min-height: calc(100vh - 70px - 350px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: #ffffff;
}

.register-card {
    background: white;
    width: 100%;
    max-width: 480px;
    padding: 45px 40px;
    border: none;
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
}

.register-title {
    font-size: 22px;
    font-weight: 400;
    letter-spacing: 3px;
    text-align: center;
    margin-bottom: 35px;
    color: #1a1a1a;
    text-transform: uppercase;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #999;
    margin-bottom: 8px;
}

.register-input {
    width: 100%;
    padding: 10px 0;
    border: none;
    border-bottom: 1px solid #e0e0e0;
    background: transparent;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.register-input:focus {
    outline: none;
    border-bottom-color: #927a1b;
}

.row {
    display: flex;
    gap: 20px;
}

.row .form-group {
    flex: 1;
}

.register-btn {
    width: 100%;
    padding: 14px;
    background: #000;
    color: white;
    border: none;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity 0.3s;
    margin-top: 10px;
    margin-bottom: 25px;
}

.register-btn:hover {
    opacity: 0.85;
}

.register-link {
    text-align: center;
}

.register-link a {
    color: #888;
    text-decoration: none;
    font-size: 12px;
    letter-spacing: 0.8px;
    transition: color 0.3s;
}

.register-link a:hover {
    color: #927a1b;
}

.alert {
    padding: 10px 15px;
    margin-bottom: 20px;
    font-size: 12px;
}

.alert-danger {
    background: #f5f5f5;
    color: #c33;
    border-left: 2px solid #c33;
}

.alert ul {
    margin-bottom: 0;
    padding-left: 20px;
}

@media (max-width: 480px) {
    .register-card {
        padding: 35px 25px;
    }
    
    .register-title {
        font-size: 18px;
        letter-spacing: 2px;
    }
    
    .row {
        flex-direction: column;
        gap: 0;
    }
}
</style>

<div class="register-wrapper">
    <div class="register-card">
        <h3 class="register-title">CREAR CUENTA</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="row">
                <div class="form-group">
                    <label>NOMBRE</label>
                    <input type="text" name="name" class="register-input" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label>APELLIDO</label>
                    <input type="text" name="lastname" class="register-input" value="{{ old('lastname') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>EMAIL</label>
                <input type="email" name="email" class="register-input" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label>CONTRASEÑA</label>
                <input type="password" name="password" class="register-input" required>
            </div>

            <div class="form-group">
                <label>CONFIRMAR CONTRASEÑA</label>
                <input type="password" name="password_confirmation" class="register-input" required>
            </div>

            <button type="submit" class="register-btn">REGISTRARSE</button>

            <div class="register-link">
                <a href="{{ route('login') }}">¿YA TENÉS CUENTA? INICIAR SESIÓN</a>
            </div>
        </form>
    </div>
</div>
@endsection