<?php

?>

<footer class="footer-fs">
    <div class="container-fs">
        <div class="grid-fs">
            <!-- Sección Enlaces Rápidos -->
            <div class="section-fs">
                <h3 class="title-fs">Enlaces Rápidos</h3>
                <ul class="list-fs">
                    <li class="list-item-fs">
                        <a href="/index.php" class="link-fs">Inicio</a>
                    </li>
                    <li class="list-item-fs">
                        <a href="/nosotros.php" class="link-fs">Nosotros</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="list-item-fs">
                            <a href="/servicios.php" class="link-fs">Servicios</a>
                        </li>
                        <li class="list-item-fs">
                            <a href="/dashboard.php" class="link-fs">Dashboard</a>
                        </li>
                    <?php endif; ?>
                    <li class="list-item-fs">
                        <a href="/contactanos.php" class="link-fs">Contáctanos</a>
                    </li>
                </ul>
            </div>

            <!-- Sección Contacto -->
            <div class="section-fs">
                <h3 class="title-fs">Contacto</h3>
                <div class="contact-info">
                    <p>Email: info@gestionestudiantes.com</p>
                    <p>Teléfono: (593) 123-4567</p>
                    <p>Dirección: Ambato, Ecuador</p>
                </div>
            </div> <!-- Información del sistema -->
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
        <div class="footer-bottom">
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Estudiantes. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Script JavaScript -->
<script src="/assets/js/nav-menu.js"></script>

</body>

</html>