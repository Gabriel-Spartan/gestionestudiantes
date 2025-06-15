<?php
// includes/nav.php - Navegación principal con control de acceso
// Obtener la página actual para marcar como activa
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);

// Función para marcar página activa
function isActive($page)
{
    global $currentPage;
    return ($currentPage === $page) ? 'active' : '';
}
?>

<header class="navbar-header">
    <!-- Banner UTA -->
    <div class="navbar-banner">
        <div class="navbar-banner-container">
            <a href="/index.php">
                <img src="https://imgur.com/Khb2lxs.jpg" alt="UTA - FISEI" class="navbar-banner-img">
            </a>
        </div>
    </div><!-- Navegación principal -->
    <nav class="navbar-main">
        <div class="navbar-container">
            <!-- Menú principal centrado -->
            <ul class="navbar-menu">
                <!-- Inicio - Acceso público -->
                <li class="navbar-item"> <a href="/index.php" class="navbar-link <?php echo isActive('index.php'); ?>">
                        Inicio
                    </a>
                </li> <!-- Nosotros - Acceso público -->
                <li class="navbar-item">
                    <a href="/nosotros.php" class="navbar-link <?php echo isActive('nosotros.php'); ?>">
                        Nosotros
                    </a>
                </li>

                <!-- Servicios - Solo usuarios logueados -->
                <?php if ($isLoggedIn): ?>
                    <li class="navbar-item">
                        <a href="/servicios.php" class="navbar-link <?php echo isActive('servicios.php'); ?>">
                            Servicios
                        </a>
                    </li>
                <?php else: ?>
                    <li class="navbar-item">
                        <span class="navbar-link disabled" title="Debes iniciar sesión para acceder">
                            Servicios
                        </span>
                    </li>
                <?php endif; ?>

                <!-- Contacto - Acceso público -->
                <li class="navbar-item">
                    <a href="/contactanos.php" class="navbar-link <?php echo isActive('contactanos.php'); ?>">
                        Contáctanos
                    </a>
                </li>
            </ul> <!-- Menú de usuario (derecha) -->
            <div class="navbar-user">
                <?php if ($isLoggedIn): ?>
                    <!-- Usuario logueado -->
                    <div class="navbar-user-info">
                        <span class="navbar-user-name">
                            Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                        <span class="navbar-user-type">
                            (<?php echo htmlspecialchars($_SESSION['user_type']); ?>)
                        </span>
                    </div>

                    <ul class="navbar-auth">
                        <li class="navbar-item">
                            <a href="dashboard.php" class="navbar-link <?php echo isActive('dashboard.php'); ?>">
                                Dashboard
                            </a>
                        </li>
                        <li class="navbar-item">
                            <a href="students/" class="navbar-link">
                                Estudiantes
                            </a>
                        </li>
                        <li class="navbar-item">
                            <a href="logout.php" class="navbar-link navbar-logout-btn">
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <ul class="navbar-auth">
                        <li class="navbar-item">
                            <a href="login.php" class="navbar-login-btn">
                                Iniciar Sesión
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div> <!-- Botón menú móvil -->
        <button class="navbar-toggle" type="button">
            <span class="navbar-toggle-icon"></span>
            <span class="navbar-toggle-icon"></span>
            <span class="navbar-toggle-icon"></span>
        </button>
        </div>
    </nav>
</header>