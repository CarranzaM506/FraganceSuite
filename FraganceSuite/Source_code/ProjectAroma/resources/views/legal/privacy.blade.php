@extends('layouts.app')

@section('page-name', 'Política de Privacidad')

@section('title', 'Política de Privacidad | AROMA')

@section('body-class', 'legal-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesLegal.css') }}">
@endsection

@section('content')
<div id="aroma-legal-page">

    <div class="legal-page-header">
        <span class="legal-tag">Documento legal</span>
        <h1>Política de Privacidad</h1>
        <p class="legal-meta">Última actualización: {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    {{-- 1. Datos que recolectamos --}}
    <div class="legal-section">
        <p class="legal-section-number">01</p>
        <h2>Datos que Recolectamos</h2>
        <p>
            Para brindarte nuestros servicios, Aroma Perfumería recopila la siguiente información personal
            cuando te registrás o realizás una compra:
        </p>
        <ul>
            <li><strong>Nombre y apellido</strong></li>
            <li><strong>Dirección de correo electrónico</strong></li>
            <li><strong>Número de teléfono</strong></li>
            <li><strong>Contraseña</strong> (almacenada de forma encriptada, nunca en texto plano)</li>
            <li><strong>Direcciones de envío</strong> (provincia, cantón, distrito, código postal)</li>
            <li><strong>Historial de pedidos y productos favoritos</strong></li>
        </ul>
    </div>

    <hr class="legal-divider">

    {{-- 2. Cómo obtenemos los datos --}}
    <div class="legal-section">
        <p class="legal-section-number">02</p>
        <h2>Cómo Obtenemos tus Datos</h2>
        <p>Recopilamos tu información a través de:</p>
        <ul>
            <li>El formulario de registro de cuenta.</li>
            <li>Los formularios de dirección de envío.</li>
            <li>El proceso de compra y pago.</li>
            <li>Cookies de sesión necesarias para el funcionamiento del sitio.</li>
        </ul>
    </div>

    <hr class="legal-divider">

    {{-- 3. Para qué usamos los datos --}}
    <div class="legal-section">
        <p class="legal-section-number">03</p>
        <h2>Para qué Usamos tus Datos</h2>
        <p>La información recopilada se utiliza exclusivamente para:</p>
        <ul>
            <li>Crear y gestionar tu cuenta de usuario.</li>
            <li>Procesar y entregar tus pedidos.</li>
            <li>Comunicarnos con vos sobre el estado de tus compras.</li>
            <li>Mejorar la experiencia de uso del sitio.</li>
            <li>Cumplir con obligaciones legales o fiscales cuando corresponda.</li>
        </ul>
        <p>
            No utilizamos tus datos para envío de publicidad no solicitada (spam) ni para crear perfiles
            de comportamiento con fines comerciales externos.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 4. Compartición de datos --}}
    <div class="legal-section">
        <p class="legal-section-number">04</p>
        <h2>Compartición de Datos con Terceros</h2>
        <p>
            Aroma Perfumería <strong>no vende ni alquila</strong> tus datos personales a terceros.
            Sin embargo, para procesar los pagos en línea utilizamos <strong>PayPal</strong>, un servicio externo
            que gestiona las transacciones de forma segura bajo sus propias políticas de privacidad.
        </p>
        <p>
            Solo compartimos la información estrictamente necesaria con estos servicios para completar
            tu compra. Te recomendamos revisar la
            <a href="https://www.paypal.com/cr/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener noreferrer" style="color:#000;">
                Política de Privacidad de PayPal
            </a>.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 5. Seguridad --}}
    <div class="legal-section">
        <p class="legal-section-number">05</p>
        <h2>Seguridad de tus Datos</h2>
        <p>
            Aplicamos medidas técnicas para proteger tu información personal:
        </p>
        <ul>
            <li>Las contraseñas se almacenan encriptadas mediante algoritmos de hashing seguros.</li>
            <li>Las comunicaciones entre tu navegador y nuestro servidor utilizan protocolo HTTPS.</li>
            <li>El acceso a los datos está restringido únicamente al personal autorizado.</li>
        </ul>
        <p>
            Aunque hacemos todo lo posible por proteger tu información, ningún sistema es 100% seguro.
            Te recomendamos usar contraseñas únicas y seguras para tu cuenta.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 6. Derechos del usuario --}}
    <div class="legal-section">
        <p class="legal-section-number">06</p>
        <h2>Tus Derechos</h2>
        <p>
            De acuerdo con la Ley de Protección de la Persona frente al Tratamiento de sus Datos Personales
            (Ley N° 8968) de Costa Rica, tenés derecho a:
        </p>
        <ul>
            <li><strong>Acceder</strong> a los datos personales que tenemos sobre vos.</li>
            <li><strong>Rectificar</strong> información incorrecta o desactualizada desde tu perfil.</li>
            <li><strong>Solicitar la eliminación</strong> de tu cuenta y datos asociados.</li>
            <li><strong>Oponerte</strong> al tratamiento de tus datos en casos específicos.</li>
        </ul>
        <p>
            Para ejercer cualquiera de estos derechos, escribinos a
            <a href="mailto:info@aroma.com" style="color:#000;">info@aroma.com</a>.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 7. Cookies --}}
    <div class="legal-section">
        <p class="legal-section-number">07</p>
        <h2>Uso de Cookies</h2>
        <p>
            Utilizamos cookies de sesión para mantener tu inicio de sesión activo y gestionar el carrito
            de compras. Estas cookies son necesarias para el funcionamiento básico del sitio y
            no recopilan información de navegación externa.
        </p>
        <p>
            Podés configurar tu navegador para rechazar cookies, aunque esto puede afectar el funcionamiento
            de ciertas secciones del sitio.
        </p>
    </div>

    <hr class="legal-divider">

    {{-- 8. Cambios a la política --}}
    <div class="legal-section">
        <p class="legal-section-number">08</p>
        <h2>Cambios a esta Política</h2>
        <p>
            Aroma Perfumería se reserva el derecho de actualizar esta Política de Privacidad en cualquier
            momento. Los cambios serán publicados en esta página con la fecha de actualización correspondiente.
        </p>
        <p>
            El uso continuado del sitio tras la publicación de cambios implica la aceptación de la
            política actualizada.
        </p>
    </div>

    <div class="legal-footer-note">
        <p>&copy; {{ now()->year }} Aroma Perfumería — Todos los derechos reservados</p>
        <a href="{{ route('legal.terms') }}">Ver Términos y Condiciones →</a>
    </div>

</div>
@endsection
