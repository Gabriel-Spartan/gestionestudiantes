<?php
// Definir la ruta base para el footer
$localHosts = ['localhost', '127.0.0.1', '::1'];
$isLocal = in_array($_SERVER['HTTP_HOST'], $localHosts) ||
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false;

$basePath = $isLocal ? '/gestionestudiantes' : '';
?>

<footer class="footer-fs">
    <div class="container-fs">
        <div class="grid-fs">
            <!-- Información del sistema -->
            <div class="section-fs">
                <h3 class="title-fs">Gestión de Estudiantes</h3>
                <p>Sistema integral para la administración y gestión de información estudiantil.</p>
            </div>

            <!-- Enlaces rápidos -->
            <div class="section-fs">
                <h3 class="title-fs">Enlaces Rápidos</h3>
                <ul class="list-fs">
                    <li class="list-item-fs"><a href="<?php echo $basePath; ?>/index.php" class="link-fs">Inicio</a>
                    </li>
                    <li class="list-item-fs"><a href="<?php echo $basePath; ?>/nosotros.php"
                            class="link-fs">Nosotros</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="list-item-fs"><a href="<?php echo $basePath; ?>/dashboard.php"
                                class="link-fs">Servicios</a></li>
                    <?php endif; ?>
                    <li class="list-item-fs"><a href="<?php echo $basePath; ?>/contactanos.php"
                            class="link-fs">Contáctanos</a></li>
                </ul>
            </div> <!-- Información de contacto -->
            <div class="section-fs">
                <h3 class="title-fs">Contacto</h3>
                <div class="contact-info-fs">
                    <p>Email: info@gestionestudiantes.com</p>
                    <p>Teléfono: (593) 123-4567</p>
                    <p>Dirección: Ambato, Ecuador</p>
                </div>
            </div>

            <!-- Información del sistema -->
            <div class="section-fs">
                <h3 class="title-fs">Sistema</h3>
                <div class="system-info-fs">
                    <p>Versión: 1.0.0</p>
                    <p>Última actualización: <?php echo date('Y-m-d'); ?></p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <p>Usuario: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom-fs">
            <div class="copyright-fs">
                <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Estudiantes. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
    </div>
</footer>

<!-- Scripts JavaScript -->
<script src="assets/js/main.js"></script>

<!-- Script para manejo del menú móvil -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Manejo del menú móvil
        const navToggle = document.querySelector('.nav-toggle');
        const navMenu = document.querySelector('.nav-menu');

        if (navToggle && navMenu) {
            navToggle.addEventListener('click', function () {
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