<?php
// Detectar si estamos en desarrollo local o producción
$isLocal = true; // Cambia a false en producción

if ($isLocal) {
    // Configuración local
    define('BASE_URL', '/gestionestudiantes');
    define('PROJECT_ROOT', __DIR__ . '/..');
} else {
    // Configuración producción
    define('BASE_URL', '');
    define('PROJECT_ROOT', __DIR__ . '/..');
}

// Define las rutas de los assets
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
?>