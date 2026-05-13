@extends('layouts.app')

@section('title', 'Mi Perfil | AROMA')

@section('body-class', 'profile-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesProfile.css') }}">
@endsection

@section('content')
<div id="aroma-profile-page">
    <!-- Tarjeta grande de edición -->
    <div class="edit-card">
        <div class="edit-header">
            <div>
                <h1>Hola, {{ Auth::user()->name }}</h1>
                <p>{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <button class="edit-btn" onclick="toggleEditMode()">
                <i class="fas fa-pen"></i> EDITAR
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <div class="form-group">
                    <label>NOMBRE</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" id="nameInput" disabled>
                </div>

                <div class="form-group">
                    <label>APELLIDO</label>
                    <input type="text" name="lastname" value="{{ old('lastname', Auth::user()->lastname) }}" id="lastnameInput" disabled>
                </div>

                <div class="form-group">
                    <label>CORREO ELECTRÓNICO</label>
                    <input type="email" value="{{ Auth::user()->email }}" disabled>
                </div>

                <div class="form-group">
                    <label>TELÉFONO</label>
                    <input type="tel" name="phone" value="{{ old('phone', Auth::user()->phone) }}" id="phoneInput" disabled>
                </div>
            </div>

            <button type="submit" class="save-btn" id="saveBtn" style="display: none;">GUARDAR CAMBIOS</button>
        </form>
    </div>

    <!-- Cards cuadradas -->
    <div class="cards-grid">
        <a href="#" class="card-link">
            <i class="fas fa-box"></i>
            <h3>MIS PEDIDOS</h3>
            <p>Historial de compras</p>
        </a>

        <a href="{{ route('favorites.index') }}" class="card-link">
            <i class="far fa-heart"></i>
            <h3>FAVORITOS</h3>
            <p>Tus productos guardados</p>
        </a>

        <a href="{{ route('location.index') }}" class="card-link">
            <i class="fas fa-location-dot"></i>
            <h3>DIRECCIONES</h3>
            <p>Gestiona tus envíos</p>
        </a>
    </div>
</div>

<script>
    function toggleEditMode() {
        const nameInput = document.getElementById('nameInput');
        const lastnameInput = document.getElementById('lastnameInput');
        const phoneInput = document.getElementById('phoneInput');
        const saveBtn = document.getElementById('saveBtn');
        
        const isDisabled = nameInput.disabled;
        
        nameInput.disabled = !isDisabled;
        lastnameInput.disabled = !isDisabled;
        phoneInput.disabled = !isDisabled;
        
        saveBtn.style.display = isDisabled ? 'block' : 'none';
        
        if (!isDisabled) {
            nameInput.value = '{{ Auth::user()->name }}';
            lastnameInput.value = '{{ Auth::user()->lastname }}';
            phoneInput.value = '{{ Auth::user()->phone }}';
        }
    }
</script>

<script>
    // Desaparecer mensajes después de 3 segundos
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }, 3000);
        });
    });
    
    // Tu función toggleEditMode existente
    function toggleEditMode() {
        const nameInput = document.getElementById('nameInput');
        const lastnameInput = document.getElementById('lastnameInput');
        const phoneInput = document.getElementById('phoneInput');
        const saveBtn = document.getElementById('saveBtn');
        
        const isDisabled = nameInput.disabled;
        
        nameInput.disabled = !isDisabled;
        lastnameInput.disabled = !isDisabled;
        phoneInput.disabled = !isDisabled;
        
        saveBtn.style.display = isDisabled ? 'block' : 'none';
        
        if (!isDisabled) {
            nameInput.value = '{{ Auth::user()->name }}';
            lastnameInput.value = '{{ Auth::user()->lastname }}';
            phoneInput.value = '{{ Auth::user()->phone }}';
        }
    }
</script>
@endsection