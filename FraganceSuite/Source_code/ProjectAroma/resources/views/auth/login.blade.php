@extends('layouts.app')

@section('content')
<style>
/* ========== LOGIN ESTILO AROMA ========== */
.login-wrapper {
    min-height: calc(100vh - 70px - 350px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: #ffffff;
}

.login-card {
    background: white;
    width: 100%;
    max-width: 420px;
    padding: 45px 40px;
    border: none;
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
}

.login-title {
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

.login-input {
    width: 100%;
    padding: 10px 0;
    border: none;
    border-bottom: 1px solid #e0e0e0;
    background: transparent;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.login-input:focus {
    outline: none;
    border-bottom-color: #927a1b;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
}

.checkbox-group input {
    width: 14px;
    height: 14px;
    margin: 0;
    cursor: pointer;
}

.checkbox-group label {
    font-size: 12px;
    color: #888;
    cursor: pointer;
}

.login-btn {
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

.login-btn:hover {
    opacity: 0.85;
}

.login-links {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}

.login-links a {
    color: #888;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 0.8px;
    transition: color 0.3s;
}

.login-links a:hover {
    color: #927a1b;
}

.divider {
    text-align: center;
    margin: 30px 0 25px;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #eee;
}

.divider span {
    background: white;
    padding: 0 15px;
    position: relative;
    font-size: 10px;
    color: #bbb;
    letter-spacing: 1px;
}

.social-buttons {
    display: flex;
    gap: 15px;
}

.social-btn {
    flex: 1;
    text-align: center;
    padding: 12px;
    border: 1px solid #e0e0e0;
    background: white;
    color: #333;
    text-decoration: none;
    font-size: 12px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.social-btn:hover {
    border-color: #927a1b;
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
    .login-card {
        padding: 35px 25px;
    }
    
    .login-title {
        font-size: 18px;
        letter-spacing: 2px;
    }
    
    .login-links {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
    
    .social-buttons {
        flex-direction: column;
    }
}
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h3 class="login-title">INICIAR SESIÓN</h3>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
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

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>EMAIL</label>
                <input type="email" name="email" class="login-input" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label>CONTRASEÑA</label>
                <input type="password" name="password" class="login-input" required>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="login-btn">ENTRAR</button>

            <div class="login-links">
                <a href="{{ route('register') }}">CREAR CUENTA</a>
                <a href="{{ route('password.request') }}">¿OLVIDASTE TU CONTRASEÑA?</a>
            </div>

            <div class="divider">
                <span>O CONTINÚA CON</span>
            </div>

            <div class="social-buttons">
                <a href="{{ url('/auth/google/redirect') }}" class="social-btn">
                    <i class="fab fa-google"></i> Google
                </a>
                <a href="{{ url('/auth/facebook/redirect') }}" class="social-btn">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
            </div>
        </form>
    </div>
</div>
@endsection