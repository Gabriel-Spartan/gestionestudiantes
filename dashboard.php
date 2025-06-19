<?php
session_start();
require_once __DIR__ . '/public/includes/utils.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$baseUrl = getBaseUrl();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></h1>
            <a href="<?php echo $baseUrl; ?>logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>

        <!-- Aquí puedes agregar más contenido del dashboard -->

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>

</html>