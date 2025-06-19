<?php
// includes/header.php - Header básico para todas las páginas
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variable para título dinámico (cada página puede definir $pageTitle antes de incluir header)
$defaultTitle = "Gestión de Estudiantes";
$finalTitle = isset($pageTitle) ? $pageTitle . " - " . $defaultTitle : $defaultTitle;

// Definir la ruta base
$basePath = '/gestionestudiantes';
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
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Estilos del sistema -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/uta-theme.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/header-style.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/navbar-style.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/footer-style.css"> <?php
       // Agregar estilo específico de la página actual si existe
       $currentPage = basename($_SERVER['PHP_SELF'], '.php');

       // Mapear nombres de archivo a nombres de CSS
       $cssMapping = [
           'nosotros' => 'about',
           'contactanos' => 'contact',
           'index' => 'index',
           'login' => 'login',
           'dashboard' => 'dashboard'
       ];

       $cssName = isset($cssMapping[$currentPage]) ? $cssMapping[$currentPage] : $currentPage;
       $cssFile = $_SERVER['DOCUMENT_ROOT'] . "/gestionestudiantes/assets/css/{$cssName}-style.css";

       if (file_exists($cssFile)) {
           echo "<link rel=\"stylesheet\" href=\"{$basePath}/assets/css/{$cssName}-style.css\">";
           echo "<!-- Cargando CSS específico: {$cssName}-style.css -->";
       }
       ?> <!-- Debug: Mostrar en consola si los CSS se cargan -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('=== DEBUG CSS ===');
            console.log('Base path: <?php echo $basePath; ?>');
            console.log('Current page: <?php echo $currentPage; ?>');
            console.log('CSS name: <?php echo isset($cssName) ? $cssName : "none"; ?>');

            const cssFiles = [
                '<?php echo $basePath; ?>/assets/css/uta-theme.css',
                '<?php echo $basePath; ?>/assets/css/header-style.css',
                '<?php echo $basePath; ?>/assets/css/navbar-style.css',
                '<?php echo $basePath; ?>/assets/css/footer-style.css'
                <?php if (isset($cssName)): ?>
                    , '<?php echo $basePath; ?>/assets/css/<?php echo $cssName; ?>-style.css'
                <?php endif; ?>
            ];

            cssFiles.forEach(function (url) {
                fetch(url)
                    .then(response => {
                        console.log(url + ' - Status: ' + response.status);
                        if (response.status === 200) {
                            console.log('✓ ' + url + ' cargado correctamente');
                        } else {
                            console.log('✗ ' + url + ' error al cargar');
                        }
                    })
                    .catch(error => {
                        console.log('✗ ' + url + ' - Error: ' + error.message);
                    });
            });
        });
    </script>
</head>

<body></body>