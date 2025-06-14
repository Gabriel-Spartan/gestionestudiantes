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
    
    <!-- CSS - Tu compañero frontend agregará los estilos aquí -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>