<?php
// includes/header.php - Header básico para todas las páginas
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variable para título dinámico (cada página puede definir $pageTitle antes de incluir header)
$defaultTitle = "Gestión de Estudiantes";
$finalTitle = isset($pageTitle) ? $pageTitle . " - " . $defaultTitle : $defaultTitle;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gestión de estudiantes">
    <meta name="author" content="Sistema Gestión Estudiantes">
    <title><?php echo htmlspecialchars($finalTitle); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Tema UTA y estilos base -->
    <link rel="stylesheet" href="/assets/css/uta-theme.css"> <!-- Estilos específicos de componentes -->
    <link rel="stylesheet" href="/assets/css/header-style.css">
    <link rel="stylesheet" href="/assets/css/navbar-style.css">
    <link rel="stylesheet" href="/assets/css/footer-style.css">
    <link rel="stylesheet" href="/assets/css/about-style.css">
    <link rel="stylesheet" href="/assets/css/contact-style.css">
    <!-- Estilos específicos de página -->
    <?php
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
    echo '<link rel="stylesheet" href="/assets/css/' . $currentPage . '-style.css">';
    ?>
    <!-- Bootstrap JavaScript y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>