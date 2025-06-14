<?php

?>

<footer>
    <div class="footer-container">
        <div class="footer-content">
            <!-- Información de la empresa/sistema -->
            <div class="footer-section">
                <h3>Gestión de Estudiantes</h3>
                <p>Sistema integral para la administración y gestión de información estudiantil.</p>
            </div>

            <!-- Enlaces rápidos -->
            <div class="footer-section">
                <h4>Enlaces Rápidos</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="nosotros.php">Nosotros</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="servicios.php">Servicios</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="contactanos.php">Contacto</a></li>
                </ul>
            </div>

            <!-- Información de contacto -->
            <div class="footer-section">
                <h4>Contacto</h4>
                <div class="contact-info">
                    <p>Email: info@gestionestudiantes.com</p>
                    <p>Teléfono: (593) 123-4567</p>
                    <p>Dirección: Ambato, Ecuador</p>
                </div>
            </div>

            <!-- Información del sistema -->
            <div class="footer-section">
                <h4>Sistema</h4>
                <div class="system-info">
                    <p>Versión: 1.0.0</p>
                    <p>Última actualización: <?php echo date('Y-m-d'); ?></p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <p>Usuario: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Estudiantes. Todos los derechos reservados.</p>
            </div>
            <div class="footer-legal">
                <a href="privacidad.php">Política de Privacidad</a>
                <a href="terminos.php">Términos de Uso</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts JavaScript -->
<script src="assets/js/main.js"></script>

<!-- Script para manejo del menú móvil -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejo del menú móvil
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    // Cerrar menú móvil al hacer click en un enlace
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            navToggle.classList.remove('active');
        });
    });
});
</script>

</body>
</html>