@extends('layouts.app')

@section('body-class', 'auth-body')

@section('content')
<style>
/* ========== RECUPERAR CONTRASEÑA ESTILO AROMA ========== */
.forgot-wrapper {
    min-height: calc(100vh - 70px - 350px);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 8px 20px 40px;
    background: #ffffff;
}

.forgot-card {
    background: white;
    width: 100%;
    max-width: 420px;
    padding: 45px 40px;
    border: none;
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
}

.forgot-title {
    font-size: 22px;
    font-weight: 400;
    letter-spacing: 3px;
    text-align: center;
    margin-bottom: 20px;
    color: #1a1a1a;
    text-transform: uppercase;
}

.forgot-description {
    text-align: center;
    font-size: 13px;
    color: #555;
    margin-bottom: 30px;
    line-height: 1.5;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #666;
    margin-bottom: 8px;
}

.forgot-input {
    width: 100%;
    padding: 10px 0;
    border: none;
    border-bottom: 1px solid #e0e0e0;
    background: transparent;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.forgot-input:focus {
    outline: none;
    border-bottom-color: #927a1b;
}

.forgot-btn {
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
    margin-bottom: 25px;
}

.forgot-btn:hover {
    opacity: 0.85;
}

.forgot-link {
    text-align: center;
}

.forgot-link a {
    color: #666;
    text-decoration: none;
    font-size: 12px;
    letter-spacing: 0.8px;
    transition: color 0.3s;
}

.forgot-link a:hover {
    color: #927a1b;
}

.alert {
    padding: 10px 15px;
    margin-bottom: 20px;
    font-size: 12px;
}

.alert-success {
    background: #f5f5f5;
    color: #2c5f2d;
    border-left: 2px solid #2c5f2d;
}

.alert-danger {
    background: #f5f5f5;
    color: #c33;
    border-left: 2px solid #c33;
}

@media (max-width: 480px) {
    .forgot-card {
        padding: 35px 25px;
    }
    
    .forgot-title {
        font-size: 18px;
        letter-spacing: 2px;
    }
}
</style>

<div class="forgot-wrapper">
    <div class="forgot-card">
        <h3 class="forgot-title">RECUPERAR CONTRASEÑA</h3>
        
        <p class="forgot-description">
            Ingresá tu correo y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0" style="padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label>EMAIL</label>
                <input type="email" name="email" class="forgot-input" value="{{ old('email') }}" required autofocus>
            </div>

            <button type="submit" class="forgot-btn">ENVIAR ENLACE</button>

            <div class="forgot-link">
                <a href="{{ route('login') }}">VOLVER AL LOGIN</a>
            </div>
        </form>
    </div>
</div>
@endsection