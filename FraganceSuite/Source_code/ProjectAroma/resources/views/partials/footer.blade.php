<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3 class="footer-title">ACERCA DE</h3>
            <ul class="footer-links">
                <li><a href="{{ route('legal.terms') }}">Términos y condiciones</a></li>
                <li><a href="{{ route('legal.privacy') }}">Política de privacidad</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3 class="footer-title">CATALOGO</h3>
            <ul class="footer-links">
                <li><a href="{{ route('catalog.index') }}">Ver todos los productos</a></li>
                <li><a href="{{ route('catalog.index', ['category' => 'mujer']) }}">Perfumes para mujer</a></li>
                <li><a href="{{ route('catalog.index', ['category' => 'hombre']) }}">Perfumes para hombre</a></li>
                <li><a href="{{ route('favorites.index') }}">Favoritos</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3 class="footer-title">CONTACTO</h3>
            <ul class="footer-links">
                {{-- <li><a href="mailto:info@aroma.com">Email: info@aroma.com</a></li> --}}
                <li><a href="tel:+50671387812">Telefono: +506 7138-7812</a></li>
                <li><a href="https://wa.me/50671387812" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3 class="footer-title">SIGUENOS</h3>
            <ul class="footer-links">
                <li>
                    <a href="https://www.instagram.com/aromaperfumeriacr?igsh=MnY4MnZpdWU4aTVy"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-icon"
                       aria-label="Instagram Aroma Perfumeria"
                       title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </li>
                <li>
                    <a href="https://www.tiktok.com/@aromaperfumeriacr?_r=1&_t=ZS-93oWDxsF3kS"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-icon"
                       aria-label="TikTok Aroma Perfumeria"
                       title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div id="info-legal" class="copyright">
        &copy; {{ now()->year }} Aroma Perfumeria - Todos los derechos reservados
    </div>
</footer>
