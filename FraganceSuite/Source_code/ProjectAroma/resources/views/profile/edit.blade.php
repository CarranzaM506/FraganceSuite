@extends('layouts.app')

@section('title', 'Editar perfil | AROMA')

@section('body-class', 'profile-body')

@section('content')
    <section class="profile-container">

        <div class="profile-card">

            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="far fa-user"></i>
                </div>
                <h2 class="profile-name">Editar perfil</h2>
                <p class="profile-email">{{ Auth::user()->email }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('profile.update', Auth::user()->id) }}" class="profile-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="form-input"
                        required>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="lastname" value="{{ old('lastname', Auth::user()->lastname) }}"
                        class="form-input">
                </div>

                <div class="form-group">
                    <label>Teléfono *</label>
                    <input type="tel" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="form-input"
                        required>
                </div>

                <button type="submit" class="profile-save-btn">
                    Guardar cambios
                </button>
            </form>

        </div>

    </section>
@endsection
