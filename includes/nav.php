<?php
// includes/nav.php - Navegación principal con control de acceso
// Obtener la página actual para marcar como activa
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);

// Definir la ruta base
$basePath = '/gestionestudiantes';

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
            <a href="<?php echo $basePath; ?>/index.php">
                <img src="https://i.imgur.com/tUOpmqC.png" alt="Banner UTA-FISEI" class="navbar-banner-img">
            </a>
        </div>
    </div>

    <!-- Navegación principal -->
    <nav class="navbar-main">
        <div class="navbar-container">
            <!-- Menú principal centrado -->
            <ul class="navbar-menu">
                <!-- Inicio - Acceso público -->
                <li class="navbar-item">
                    <a href="<?php echo $basePath; ?>/index.php"
                        class="navbar-link <?php echo isActive('index.php'); ?>">
                        Inicio
                    </a>
                </li> <!-- Nosotros - Acceso público -->
                <li class="navbar-item">
                    <a href="<?php echo $basePath; ?>/nosotros.php"
                        class="navbar-link <?php echo isActive('nosotros.php'); ?>">
                        Nosotros
                    </a>
                </li>

                <!-- Servicios - Solo usuarios logueados -->
                <?php if ($isLoggedIn): ?>
                    <li class="navbar-item">
                        <a href="<?php echo $basePath; ?>/servicios.php"
                            class="navbar-link <?php echo isActive('servicios.php'); ?>">
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
                    <a href="<?php echo $basePath; ?>/contactanos.php"
                        class="navbar-link <?php echo isActive('contactanos.php'); ?>">
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
                <?php else: ?> <!-- Usuario no logueado -->
                    <ul class="navbar-menu">
                        <li class="navbar-item">
                            <a href="<?php echo $basePath; ?>/login.php"
                                class="navbar-link <?php echo isActive('login.php'); ?>">
                                Iniciar Sesión
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>