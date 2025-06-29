<?php
// config/database.php - Configuración de conexión reutilizable

function getConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    
    // Detectar entorno automáticamente
    $localHosts = ['localhost', '127.0.0.1', '::1'];
    $isLocal = in_array($_SERVER['HTTP_HOST'], $localHosts) ||
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
                strpos($_SERVER['HTTP_HOST'], '.local') !== false;
    
    if ($isLocal) {
        // Configuración local (XAMPP)
        $config = [
            'host' => 'localhost',
            'port' => '3306',
            'dbname' => 'gestion_estudiantes',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8'
        ];
    } else {
        // Configuración para producción (InfinityFree)
        $config = [
            'host' => 'sql303.infinityfree.com',
            'port' => '3306',
            'dbname' => 'if0_39340414_gestion_estudiantes',
            'username' => 'if0_39340414',
            'password' => 'XxDhdHMyOnHU',
            'charset' => 'utf8'
        ];
    }
    
    // Permitir sobreescribir con variables de entorno si existen
    if (file_exists(__DIR__ . '/env_local.php')) {
        include __DIR__ . '/env_local.php';
    }
    
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        $isLocalEnv = in_array($_SERVER['HTTP_HOST'], $localHosts) ||
                        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
        if ($isLocalEnv) {
            throw new Exception('Error de conexión: ' . $e->getMessage());
        } else {
            error_log('Database connection error: ' . $e->getMessage());
            throw new Exception('Error de conexión a la base de datos.');
        }
    }
}
?>