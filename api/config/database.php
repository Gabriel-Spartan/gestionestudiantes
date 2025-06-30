<?php
// api/config/database.php - Configuración centralizada para desarrollo y producción
// ARCHIVO PRINCIPAL DE CONEXIÓN - Todos los demás archivos deben usar este

// Función para obtener conexión (singleton pattern)
function getConnection() {
    static $pdo = null;
    
    // Si ya existe la conexión, devolverla
    if ($pdo !== null) {
        return $pdo;
    }
    
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
            'host' => 'sql303.infinityfree.com',
            'port' => '3306',
            'dbname' => 'if0_39340414_gestion_estudiantes',
            'username' => 'if0_39340414',
            'password' => 'XxDhdHMyOnHU',
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
        
        return $pdo;
        
    } catch (PDOException $e) {
        // En producción, no mostrar detalles del error
        if ($isLocal) {
            throw new Exception("❌ Error de conexión: " . $e->getMessage());
        } else {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("❌ Error de conexión a la base de datos. Contacte al administrador.");
        }
    }
}

// Para compatibilidad con archivos que esperan la variable $pdo directamente
try {
    $pdo = getConnection();
} catch (Exception $e) {
    // Si falla la conexión, mostrar error y terminar
    die($e->getMessage());
}

// Información de debug (solo en local)
$localHosts = ['localhost', '127.0.0.1', '::1'];
$isLocalDebug = in_array($_SERVER['HTTP_HOST'], $localHosts);

if ($isLocalDebug) {
    error_log("[DATABASE] Conexión establecida exitosamente");
    error_log("[DATABASE] Host: " . $_SERVER['HTTP_HOST']);
    error_log("[DATABASE] Entorno: " . ($isLocalDebug ? 'LOCAL' : 'PRODUCCIÓN'));
}
?>