<?php
// includes/nav.php - Navegación principal con control de acceso
// Obtener la página actual para marcar como activa
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);

// Función para marcar página activa
function isActive($page) {
    global $currentPage;
    return ($currentPage === $page) ? 'active' : '';
}
?>

<header>
    <nav>
        <div class="nav-container">
            <!-- Logo/Nombre del sistema -->
            <div class="nav-brand">
                <a href="index.php">
                    <img src="htttp://ruta_logo.png" alt="Logo" class="logo">
                    <span>Gestión Estudiantes</span>
                </a>
            </div>

            <!-- Menú principal -->
            <ul class="nav-menu">
                <!-- Inicio - Acceso público -->
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo isActive('index.php'); ?>">
                        Inicio
                    </a>
                </li>

                <!-- Nosotros - Acceso público -->
                <li class="nav-item">
                    <a href="nosotros.php" class="nav-link <?php echo isActive('nosotros.php'); ?>">
                        Nosotros
                    </a>
                </li>

                <!-- Servicios - Solo usuarios logueados -->
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a href="servicios.php" class="nav-link <?php echo isActive('servicios.php'); ?>">
                            Servicios
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <span class="nav-link disabled" title="Debes iniciar sesión para acceder">
                            Servicios
                        </span>
                    </li>
                <?php endif; ?>

                <!-- Contacto - Acceso público -->
                <li class="nav-item">
                    <a href="contactanos.php" class="nav-link <?php echo isActive('contactanos.php'); ?>">
                        Contáctanos
                    </a>
                </li>
            </ul>

            <!-- Menú de usuario -->
            <div class="nav-user">
                <?php if ($isLoggedIn): ?>
                    <!-- Usuario logueado -->
                    <div class="user-info">
                        <span class="user-name">
                            Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                        <span class="user-type">
                            (<?php echo htmlspecialchars($_SESSION['user_type']); ?>)
                        </span>
                    </div>
                    
                    <ul class="user-menu">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="students/" class="nav-link">
                                Estudiantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link logout">
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <ul class="auth-menu">
                        <li class="nav-item">
                            <a href="login.php" class="nav-link login-btn <?php echo isActive('login.php'); ?>">
                                Iniciar Sesión
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Botón menú móvil -->
            <button class="nav-toggle" type="button">
                <span class="nav-toggle-icon"></span>
                <span class="nav-toggle-icon"></span>
                <span class="nav-toggle-icon"></span>
            </button>
        </div>
    </nav>
</header>