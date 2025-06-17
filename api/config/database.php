<?php
// config/database.php - Configuración para desarrollo y producción
// CORREGIDO: Función isLocalEnvironment() removida para evitar duplicación

// Configuración según el entorno
$localHosts = ['localhost', '127.0.0.1', '::1', '192.168.2.27', '10.79.17.58'];
$isLocal = in_array($_SERVER['HTTP_HOST'], $localHosts) ||
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
            strpos($_SERVER['HTTP_HOST'], '192.168') !== false ||
            strpos($_SERVER['HTTP_HOST'], '10.79') !== false;

if ($isLocal) {
    // CONFIGURACIÓN PARA DESARROLLO LOCAL (XAMPP)
    $config = [
        'host' => 'localhost',
        'port' => '3306',
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
    if ($isLocal) {
        die("❌ Error de conexión: " . $e->getMessage());
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("❌ Error de conexión a la base de datos. Contacte al administrador.");
    }
}
?>