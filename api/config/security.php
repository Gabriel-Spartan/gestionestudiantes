<?php
// api/config/security.php - Endpoint para obtener configuración de seguridad
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Solo GET.'
    ]);
    exit();
}

try {
    // Incluir configuración de base de datos
    require_once __DIR__ . '/database.php';
    
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar que el usuario esté logueado
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'No autorizado. Debe iniciar sesión.'
        ]);
        exit();
    }
    
    // Obtener conexión a la base de datos
    $pdo = getConnection();
    
    // Consultar todas las configuraciones de seguridad
    $stmt = $pdo->prepare('SELECT clave, valor, descripcion FROM configuracion_seguridad ORDER BY id');
    $stmt->execute();
    $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($configuraciones)) {
        // Si no hay configuraciones, devolver valores por defecto
        $configuracionDefault = [
            'max_intentos_login' => ['valor' => '5', 'descripcion' => 'Máximo número de intentos de login fallidos'],
            'tiempo_bloqueo_minutos' => ['valor' => '30', 'descripcion' => 'Tiempo de bloqueo en minutos'],
            'timeout_sesion_minutos' => ['valor' => '30', 'descripcion' => 'Timeout de sesión en minutos'],
            'max_sesiones_simultaneas' => ['valor' => '2', 'descripcion' => 'Máximo sesiones simultáneas'],
            'longitud_minima_password' => ['valor' => '6', 'descripcion' => 'Longitud mínima de contraseña']
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Configuración de seguridad obtenida (valores por defecto)',
            'data' => $configuracionDefault
        ]);
        exit();
    }
    
    // Convertir array a formato clave => valor
    $configuracionFormateada = [];
    foreach ($configuraciones as $config) {
        $configuracionFormateada[$config['clave']] = [
            'valor' => $config['valor'],
            'descripcion' => $config['descripcion']
        ];
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Configuración de seguridad obtenida correctamente',
        'data' => $configuracionFormateada
    ]);
    
} catch (PDOException $e) {
    error_log("Error de base de datos en security config: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos al obtener configuración',
        'error' => 'DATABASE_ERROR'
    ]);
    
} catch (Exception $e) {
    error_log("Error general en security config: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => 'INTERNAL_ERROR'
    ]);
}
?>