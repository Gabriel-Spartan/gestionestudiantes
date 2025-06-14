<?php
// index.php - Página principal (Home pública)
$pageTitle = "Inicio";
include 'includes/header.php';
include 'includes/nav.php';
?>

<main>
    <section>
        <h1>Sistema de Gestión de Estudiantes</h1>
        <p>Bienvenido al sistema de gestión de estudiantes. Aquí podrás administrar la información de los estudiantes de manera eficiente.</p>
    </section>

    <section>
        <h2>Características del Sistema</h2>
        <ul>
            <li>Registro completo de estudiantes</li>
            <li>Búsqueda y filtrado avanzado</li>
            <li>Gestión de información personal</li>
            <li>Reportes y estadísticas</li>
            <li>Sistema de usuarios seguro</li>
        </ul>
    </section>

    <?php if (!isset($_SESSION['user_id'])): ?>
    <section>
        <h2>Acceso al Sistema</h2>
        <p>Para acceder a las funcionalidades completas del sistema, necesitas iniciar sesión.</p>
        <a href="login.php">Iniciar Sesión</a>
    </section>
    <?php else: ?>
    <section>
        <h2>Panel de Control</h2>
        <p>Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Bienvenido de vuelta.</p>
        <a href="dashboard.php">Ir al Dashboard</a>
        <a href="students/">Ver Estudiantes</a>
    </section>
    <?php endif; ?>

    <section>
        <h2>Información del Sistema</h2>
        <p>Versión: 1.0</p>
        <p>Última actualización: <?php echo date('Y'); ?></p>
    </section>
</main>

<?php include 'includes/footer.php'; ?>