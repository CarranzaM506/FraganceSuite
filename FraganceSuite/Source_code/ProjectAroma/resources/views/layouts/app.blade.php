<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AROMA - Perfumería</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/stylesMain.css') }}">
    @vite(['resources/js/app.js'])
    
    <!-- Script para variable de autenticación -->
    <script>
        window.isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
    </script>
    
    @yield('styles')
</head>

<body class="@yield('body-class', 'default-body')">
    @include('partials.header')

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