<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AROMA - Perfumería</title>
    
    <style>
    <!-- Estilos críticos inline para evitar FOUC -->
    <style>
        /* Evita el flash de contenido sin estilos */
        body {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        body.loaded {
            opacity: 1;
        }
        
        body.loaded {
            opacity: 1;
        }
        
        /* Estilos mínimos para que no se vea feo mientras carga */
        .product-grid, .store-section, .videos-section {
            min-height: 200px;
        }
    </style>
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preload" href="{{ asset('css/stylesMain.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('css/stylesMain.css') }}">
    @vite(['resources/js/app.js'])
    
    <script>
        window.isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
        });
        
        // Mostrar el body cuando todo esté listo
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
        });
        
        // Fallback por si algo tarda
        setTimeout(function() {
            document.body.classList.add('loaded');
        }, 500);
    </script>
    
    @yield('styles')
</head>

<body class="@yield('body-class', 'default-body')">

    <!-- HEADER (con el carrusel dentro como primer elemento) -->
    <header>
        <!-- INFO CARROUSEL - PRIMER HIJO DEL HEADER -->
        @if(isset($infoCarouselItems) && $infoCarouselItems->count() > 0)
        <div class="info-carousel">
            <div class="info-carousel-track">
                @foreach($infoCarouselItems as $item)
                <div class="info-carousel-slide">
                    <div class="info-carousel-content">
                        <span class="info-carousel-message">{{ $item->message }}</span>
                        @if($item->link)
                        <a href="{{ $item->link }}" class="info-carousel-link" target="_blank">
                            {{ $item->link_text ?: 'Saber más' }} →
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
                @foreach($infoCarouselItems as $item)
                <div class="info-carousel-slide">
                    <div class="info-carousel-content">
                        <span class="info-carousel-message">{{ $item->message }}</span>
                        @if($item->link)
                        <a href="{{ $item->link }}" class="info-carousel-link" target="_blank">
                            {{ $item->link_text ?: 'Saber más' }} →
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Contenido del header (logo, menú, etc.) -->
        @include('partials.header')
    </header>

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.whatsapp')
    @if(!request()->routeIs('checkout'))
    @include('partials.debug-cart')
    @endif

    @vite(['resources/js/favorites.js'])

    @stack('scripts')
</body>
</html>