@extends('layouts.app')

@section('page-name', 'Términos y Condiciones')

@section('title', 'Términos y Condiciones | AROMA')

@section('body-class', 'legal-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesLegal.css') }}">
@endsection

@section('content')
<div id="aroma-legal-page">

    <div class="legal-page-header">
        <span class="legal-tag">Documento legal</span>
        <h1>Términos y Condiciones</h1>
        <p class="legal-meta">Última actualización: {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    {{-- 1. Introducción --}}
    <div class="legal-section">
        <p class="legal-section-number">01</p>
        <h2>Introducción</h2>
        <p>
            Bienvenido a <strong>Aroma Perfumería</strong>, una tienda en línea dedicada a la venta de perfumes y fragancias,
            operada desde Costa Rica. Al acceder y utilizar este sitio web, aceptás de forma voluntaria los presentes
            Términos y Condiciones.
        </p>
        <p>
            Si no estás de acuerdo con alguna de estas condiciones, te pedimos que no utilices nuestros servicios.
            Cualquier duda podés escribirnos a <a href="mailto:info@aroma.com" style="color:#000;">info@aroma.com</a>.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 2. Uso del servicio --}}
    <div class="legal-section">
        <p class="legal-section-number">02</p>
        <h2>Uso del Servicio</h2>
        <p>Al usar esta plataforma, te comprometés a:</p>
        <ul>
            <li>Utilizar el sitio únicamente con fines legales y personales.</li>
            <li>Proporcionar información veraz y actualizada al crear tu cuenta o realizar un pedido.</li>
            <li>No intentar acceder a sistemas, cuentas o datos de otros usuarios sin autorización.</li>
            <li>No reproducir, copiar ni redistribuir el contenido del sitio sin nuestro consentimiento.</li>
            <li>No utilizar herramientas automatizadas para extraer información del sitio (scraping).</li>
        </ul>
        <p>
            Nos reservamos el derecho de suspender el acceso a cualquier usuario que incumpla estas condiciones,
            sin previo aviso y sin responsabilidad de nuestra parte.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 3. Cuentas de usuario --}}
    <div class="legal-section">
        <p class="legal-section-number">03</p>
        <h2>Cuentas de Usuario</h2>
        <p>
            Para realizar compras en Aroma Perfumería es necesario crear una cuenta. Al registrarte,
            sos responsable de:
        </p>
        <ul>
            <li>Mantener la confidencialidad de tu contraseña.</li>
            <li>Todas las actividades realizadas desde tu cuenta.</li>
            <li>Notificarnos de inmediato si sospechás de acceso no autorizado a tu cuenta.</li>
        </ul>
        <p>
            Aroma Perfumería no se hace responsable por daños derivados del uso no autorizado de tu cuenta
            causados por no mantener tu contraseña segura.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 4. Propiedad intelectual --}}
    <div class="legal-section">
        <p class="legal-section-number">04</p>
        <h2>Propiedad Intelectual</h2>
        <p>
            Todo el contenido de este sitio —incluyendo textos, imágenes, diseño, logotipos, código fuente
            y estructura— es propiedad de Aroma Perfumería o de sus respectivos titulares, y está protegido
            por las leyes de propiedad intelectual de Costa Rica.
        </p>
        <p>
            Queda estrictamente prohibida su reproducción, distribución o modificación sin autorización previa
            y por escrito de nuestra parte.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 5. Limitación de responsabilidad --}}
    <div class="legal-section">
        <p class="legal-section-number">05</p>
        <h2>Limitación de Responsabilidad</h2>
        <p>
            Aroma Perfumería hace su mejor esfuerzo para mantener el sitio operativo y con información precisa.
            Sin embargo, no garantizamos que el servicio esté disponible de forma ininterrumpida, libre de errores
            o completamente seguro.
        </p>
        <p>No nos hacemos responsables por:</p>
        <ul>
            <li>Pérdidas económicas derivadas del uso o imposibilidad de uso del sitio.</li>
            <li>Errores tipográficos en precios o descripciones de productos.</li>
            <li>Daños causados por interrupciones temporales del servicio.</li>
            <li>Contenido de sitios externos a los que enlacemos.</li>
        </ul>
    </div>

    <hr class="legal-divider">

    {{-- 6. Terminación de cuentas --}}
    <div class="legal-section">
        <p class="legal-section-number">06</p>
        <h2>Terminación de Cuentas</h2>
        <p>
            Nos reservamos el derecho de suspender o eliminar cuentas de usuario en cualquier momento,
            sin previo aviso, si consideramos que se han infringido estos Términos y Condiciones o si
            detectamos actividad sospechosa o fraudulenta.
        </p>
        <p>
            El usuario también puede solicitar la eliminación de su cuenta en cualquier momento
            escribiendo a <a href="mailto:info@aroma.com" style="color:#000;">info@aroma.com</a>.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 7. Cambios en los términos --}}
    <div class="legal-section">
        <p class="legal-section-number">07</p>
        <h2>Cambios en los Términos</h2>
        <p>
            Aroma Perfumería se reserva el derecho de modificar estos Términos y Condiciones en cualquier
            momento. Los cambios entrarán en vigor desde su publicación en este sitio.
        </p>
        <p>
            Te recomendamos revisar esta página periódicamente. El uso continuado del sitio tras la
            publicación de cambios constituye la aceptación de los nuevos términos.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 8. Ley aplicable --}}
    <div class="legal-section">
        <p class="legal-section-number">08</p>
        <h2>Ley Aplicable</h2>
        <p>
            Estos Términos y Condiciones se rigen por las leyes de la República de Costa Rica.
            Cualquier disputa relacionada con el uso de este sitio estará sujeta a la jurisdicción
            de los tribunales competentes de Costa Rica.
        </p>
    </div>

    <div class="legal-footer-note">
        <p>&copy; {{ now()->year }} Aroma Perfumería — Todos los derechos reservados</p>
        <a href="{{ route('legal.privacy') }}">Ver Política de Privacidad →</a>
    </div>

</div>
@endsection
