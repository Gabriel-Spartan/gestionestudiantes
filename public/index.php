<?php
// index.php - Página principal (Home pública)
$pageTitle = "Inicio";
include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="main-is">
    <section class="section-is">
        <h1 class="title-is">Sistema de Gestión de Estudiantes</h1>
        <p>Bienvenido al sistema de gestión de estudiantes. Aquí podrás administrar la información de los estudiantes de
            manera eficiente.</p>
    </section>

    <section class="section-is">
        <h2 class="subtitle-is">Características del Sistema</h2>
        <ul>
            <li>Registro completo de estudiantes</li>
            <li>Búsqueda y filtrado avanzado</li>
            <li>Gestión de información personal</li>
            <li>Reportes y estadísticas</li>
            <li>Sistema de usuarios seguro</li>
        </ul>
    </section>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <section class="section-is">
            <h2 class="subtitle-is">Acceso al Sistema</h2>
            <p>Para acceder a las funcionalidades completas del sistema, necesitas iniciar sesión.</p>
            <a href="login.php" class="btn-is">Iniciar Sesión</a>
        </section>
    <?php else: ?>
        <section>
            <h2>Panel de Control</h2>
            <p>Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Bienvenido de vuelta.</p>
            <a href="dashboard.php">Ir al Dashboard</a>
            <a href="students/">Ver Estudiantes</a>
        </section>
    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>