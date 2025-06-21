<?php
session_start();
$pageTitle = "Inicio";
include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="main-is">
    <!-- Sección Hero -->
    <section class="hero-is">
        <div class="hero-container-is">
            <h1 class="hero-title-is">Bienvenido al Sistema de Gestión de Estudiantes</h1>
            <p class="hero-subtitle-is">Una plataforma moderna para la gestión académica eficiente</p>
        </div>
    </section>

    <!-- Sección de Características -->
    <section class="features-is">
        <div class="features-container-is">
            <h2 class="title-is">Nuestras Características</h2>
            <div class="cards-is"> <!-- Característica 1 -->
                <div class="card-is">
                    <div class="card-icon-is">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="card-title-is">Gestión de Estudiantes</h3>
                    <p class="card-description-is">
                        Administra fácilmente los registros de estudiantes, información personal y académica.
                    </p>
                </div>

                <!-- Característica 2 -->
                <div class="card-is">
                    <div class="card-icon-is">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title-is">Seguimiento Académico</h3>
                    <p class="card-description-is">
                        Monitorea el progreso académico y mantén un registro detallado del rendimiento.
                    </p>
                </div>

                <!-- Característica 3 -->
                <div class="card-is">
                    <div class="card-icon-is">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="card-title-is">Reportes y Estadísticas</h3>
                    <p class="card-description-is">
                        Genera informes detallados y visualiza estadísticas importantes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección CTA -->
    <section class="cta-is">
        <div class="cta-container-is">
            <h2 class="cta-title-is">¿Listo para empezar?</h2>
            <p class="cta-description-is">Únete a nuestra plataforma y mejora la gestión académica</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="cta-button-is">Iniciar Sesión</a>
            <?php else: ?>
                <a href="dashboard.php" class="cta-button-is">Ir al Dashboard</a>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>