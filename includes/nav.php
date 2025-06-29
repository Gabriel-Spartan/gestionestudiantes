<?php
// includes/nav.php - Navegación principal con control de acceso
// Obtener la página actual para marcar como activa
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'ADMIN';

// Definir la ruta base
$localHosts = ['localhost', '127.0.0.1', '::1'];
$isLocal = in_array($_SERVER['HTTP_HOST'], $localHosts) ||
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false;

$basePath = $isLocal ? '/gestionestudiantes' : '';

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
                </li> 
                
                <!-- Nosotros - Acceso público -->
                <li class="navbar-item">
                    <a href="<?php echo $basePath; ?>/nosotros.php"
                        class="navbar-link <?php echo isActive('nosotros.php'); ?>">
                        Nosotros
                    </a>
                </li> 
                
                <!-- Servicios (Dashboard) - Redirección según login -->
                <li class="navbar-item">
                    <a href="<?php echo $isLoggedIn ? $basePath . '/dashboard.php' : $basePath . '/login.php'; ?>"
                        class="navbar-link <?php echo isActive('dashboard.php') || isActive('servicios.php') ? 'active' : ''; ?>">
                        Servicios
                    </a>
                </li>

                <!-- Contacto - Acceso público -->
                <li class="navbar-item">
                    <a href="<?php echo $basePath; ?>/contactanos.php"
                        class="navbar-link <?php echo isActive('contactanos.php'); ?>">
                        Contáctanos
                    </a>
                </li>

                <!-- Crear Usuario - Solo para ADMINs -->
                <?php if ($isAdmin): ?>
                <li class="navbar-item">
                    <a href="<?php echo $basePath; ?>/users/create.php"
                        class="navbar-link <?php echo isActive('create.php') && strpos($_SERVER['REQUEST_URI'], '/users/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-user-plus"></i> Crear Usuario
                    </a>
                </li>
                <?php endif; ?>

                <!-- Botón Iniciar/Cerrar Sesión -->
                <li class="navbar-item navbar-item-session">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $basePath; ?>/logout.php" class="navbar-link navbar-logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>/login.php"
                            class="navbar-link navbar-login <?php echo isActive('login.php'); ?>">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                    <?php endif; ?>
                </li>
            </ul>

            <!-- Menú de usuario - Solo mostrar info si está logueado -->
            <?php if ($isLoggedIn): ?>
                <div class="nav-user">
                    <!-- Usuario logueado -->
                    <div class="user-info">
                        <span class="user-name">
                            Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            <?php if ($isAdmin): ?>
                                <span class="badge bg-warning text-dark ms-1">ADMIN</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<style>
/* Estilos adicionales para la nueva opción */
.navbar-item .fas.fa-user-plus {
    margin-right: 5px;
}

.badge {
    font-size: 0.7em;
    padding: 0.2em 0.5em;
    border-radius: 0.375rem;
}

/* Responsive: ocultar texto en pantallas pequeñas */
@media (max-width: 768px) {
    .navbar-item a .fas.fa-user-plus + text {
        display: none;
    }
    
    .navbar-item a .fas.fa-user-plus::after {
        content: "";
    }
}
</style>