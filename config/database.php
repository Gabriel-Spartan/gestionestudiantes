<?php
// config/database.php - Configuración de conexión reutilizable

function getConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    // Configuración local por defecto
    $config = [
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => 'gestion_estudiantes',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ];
    // Permitir sobreescribir con variables de entorno si existen
    if (file_exists(__DIR__ . '/env_local.php')) {
        // Opcional: cargar configuración personalizada
    }
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión: ' . $e->getMessage());
    }
}