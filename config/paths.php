<?php
// config/paths.php - Configuración de rutas para desarrollo y producción

// Detectar automáticamente si estamos en desarrollo local o producción
$localHosts = ['localhost', '127.0.0.1', '::1'];
$isLocal = in_array($_SERVER['HTTP_HOST'], $localHosts) ||
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false;

if ($isLocal) {
    // Configuración local (XAMPP)
    define('BASE_URL', '/gestionestudiantes');
    define('PROJECT_ROOT', __DIR__ . '/..');
} else {
    // Configuración producción (InfinityFree)
    define('BASE_URL', ''); // Sin subdirectorio en el dominio principal
    define('PROJECT_ROOT', __DIR__ . '/..');
}

// Define las rutas de los assets
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');

// Configuración adicional para producción
if (!$isLocal) {
    // Configuraciones específicas de producción
    ini_set('display_errors', 0);
    error_reporting(0);
    
    // Log de errores en archivo
    ini_set('log_errors', 1);
    ini_set('error_log', PROJECT_ROOT . '/logs/error.log');
}
?>