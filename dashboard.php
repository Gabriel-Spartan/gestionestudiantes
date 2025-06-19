<?php
// dashboard.php - Panel de servicios del sistema
$pageTitle = "Servicios";
include 'includes/header.php';
include 'includes/nav.php';

// Si no está logueado, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener información del usuario desde la sesión
$userName = $_SESSION['user_name'] ?? 'Usuario';
$userEmail = $_SESSION['user_email'] ?? '';
$userType = $_SESSION['user_type'] ?? '';
?>

<main class="services-main">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Servicios Académicos</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 mb-4">
                                <div class="welcome-card">
                                    <h5>Bienvenido, <?php echo htmlspecialchars($userName); ?></h5>
                                    <p class="text-muted">Accede a los servicios disponibles según tu rol:
                                        <strong><?php echo htmlspecialchars($userType); ?></strong></p>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <p>El sistema de <strong>Gestión de Estudiantes</strong> permite administrar de forma
                                    eficiente los datos de los alumnos registrados en la institución.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tarjeta de Gestión de Estudiantes -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate service-icon"></i>
                        <h5 class="card-title">Gestión de Estudiantes</h5>
                        <p class="card-text">Administra los datos de los estudiantes, registra nuevos ingresos y
                            gestiona su información académica.</p>
                        <a href="students/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Informes -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar service-icon"></i>
                        <h5 class="card-title">Informes y Estadísticas</h5>
                        <p class="card-text">Visualiza estadísticas e informes sobre los estudiantes registrados en el
                            sistema.</p>
                        <a href="#" class="btn btn-primary">Próximamente</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Configuración -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <i class="fas fa-cog service-icon"></i>
                        <h5 class="card-title">Configuración</h5>
                        <p class="card-text">Administra tu perfil, preferencias y datos de acceso al sistema.</p>
                        <a href="#" class="btn btn-primary">Próximamente</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>