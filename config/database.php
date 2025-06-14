<?php
// config/database.php - Configuración para desarrollo y producción

// Detectar si estamos en desarrollo local o producción
function isLocalEnvironment()
{
    $localHosts = ['localhost', '127.0.0.1', '::1'];
    return in_array($_SERVER['HTTP_HOST'], $localHosts) ||
        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'], '.local') !== false;
}

// Configuración según el entorno
if (isLocalEnvironment()) {
    // CONFIGURACIÓN PARA DESARROLLO LOCAL (XAMPP)
    $config = [
        'host' => 'localhost',
        'port' => '3306',  // Tu puerto personalizado de XAMPP
        'dbname' => 'gestion_estudiantes',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ];
} else {
    // CONFIGURACIÓN PARA PRODUCCIÓN (HOSTING)
    $config = [
        'host' => 'localhost',  // El hosting te dará esta info
        'port' => '3306',       // Puerto estándar en hosting
        'dbname' => 'tu_usuario_gestion_estudiantes',  // El hosting asigna nombres
        'username' => 'tu_usuario_db',  // Usuario de BD del hosting
        'password' => 'tu_password_seguro',  // Password del hosting
        'charset' => 'utf8'
    ];
}

try {
    // Construir DSN
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

    // Crear conexión PDO
    $pdo = new PDO($dsn, $config['username'], $config['password']);

    // Configurar atributos PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $e) {
    // En producción, no mostrar detalles del error
    if (isLocalEnvironment()) {
        die("❌ Error de conexión: " . $e->getMessage());
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("❌ Error de conexión a la base de datos. Contacte al administrador.");
    }
}
?>