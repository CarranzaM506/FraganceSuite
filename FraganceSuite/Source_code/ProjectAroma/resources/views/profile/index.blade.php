@extends('layouts.app')

@section('title', 'Mi Perfil | AROMA')

@section('body-class', 'profile-body')

@section('content')
<section class="profile-container">

    <div class="profile-card">

        <div class="profile-header">
            <div class="profile-avatar">
                <i class="far fa-user"></i>
            </div>
            <h2 class="profile-name">{{ Auth::user()->name }}</h2>
            <p class="profile-email">{{ Auth::user()->email }}</p>
        </div>

        <div class="profile-actions">
            <a href="#" class="profile-action">
                <i class="fas fa-box"></i>
                <span>Mis pedidos</span>
            </a>

            <a href="#" class="profile-action">
                <i class="far fa-heart"></i>
                <span>Favoritos</span>
            </a>

            <a href="#" class="profile-action">
                <i class="fas fa-location-dot"></i>
                <span>Direcciones</span>
            </a>

            <a href="{{route('profile.edit',Auth::user()->id)}}" class="profile-action">
                <i class="fas fa-gear"></i>
                <span>Editar Perfil</span>
            </a>
        </div>

    </div>

</section>
@endsection
