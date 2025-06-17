<?php
// api/helpers/cors.php - Configuración de CORS y headers para API

/**
 * Configurar headers CORS para permitir requests desde diferentes orígenes
 * Necesario para testing con Postman y requests desde frontend
 */
function setCorsHeaders() {
    // Permitir requests desde cualquier origen (desarrollo)
    // En producción, cambiar '*' por el dominio específico
    header("Access-Control-Allow-Origin: *");
    
    // Métodos HTTP permitidos
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    // Headers permitidos en requests
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    
    // Permitir credenciales (cookies, sessions)
    header("Access-Control-Allow-Credentials: true");
    
    // Tiempo de cache para preflight requests (24 horas)
    header("Access-Control-Max-Age: 86400");
}

/**
 * Configurar headers básicos para API JSON
 */
function setApiHeaders() {
    // Tipo de contenido JSON
    header("Content-Type: application/json; charset=UTF-8");
    
    // Evitar cache en respuestas de API
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    
    // Headers de seguridad básicos
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
}

/**
 * Manejar requests OPTIONS (preflight)
 * Los navegadores envían OPTIONS antes de requests complejos
 */
function handlePreflightRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        setCorsHeaders();
        setApiHeaders();
        http_response_code(200);
        exit();
    }
}

/**
 * Configuración completa de headers para endpoints API
 * Llamar al inicio de cada endpoint
 */
function initializeApi() {
    // Configurar CORS
    setCorsHeaders();
    
    // Configurar headers de API
    setApiHeaders();
    
    // Manejar preflight requests
    handlePreflightRequest();
}

/**
 * Validar que el request sea del tipo esperado
 * @param string $expectedMethod - Método HTTP esperado (GET, POST, PUT, DELETE)
 * @return bool
 */
function validateRequestMethod($expectedMethod) {
    $currentMethod = $_SERVER['REQUEST_METHOD'];
    
    if ($currentMethod !== $expectedMethod) {
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido',
            'message' => "Este endpoint requiere método $expectedMethod, recibido $currentMethod",
            'allowed_methods' => [$expectedMethod]
        ]);
        exit();
    }
    
    return true;
}

/**
 * Obtener datos JSON del body del request
 * @return array|null
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        return null;
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'error' => 'JSON inválido',
            'message' => 'El cuerpo del request contiene JSON malformado: ' . json_last_error_msg()
        ]);
        exit();
    }
    
    return $data;
}

/**
 * Configuración específica para desarrollo vs producción
 */
function setEnvironmentHeaders() {
    // Detectar si estamos en desarrollo
    $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) || 
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
    
    if ($isLocal) {
        // Headers más permisivos para desarrollo
        header("Access-Control-Allow-Origin: *");
        
        // Mostrar errores PHP en desarrollo
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    } else {
        // Headers más restrictivos para producción
        $allowedOrigins = [
            'https://tudominio.com',
            'https://www.tudominio.com'
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        // Ocultar errores PHP en producción
        ini_set('display_errors', 0);
        error_reporting(0);
    }
}

/**
 * Verificar si el request viene de un origen permitido
 * @return bool
 */
function isOriginAllowed() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // En desarrollo, permitir todos los orígenes
    $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) || 
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
    
    if ($isLocal) {
        return true;
    }
    
    // En producción, verificar lista de orígenes permitidos
    $allowedOrigins = [
        'https://tudominio.com',
        'https://www.tudominio.com',
        'https://app.tudominio.com'
    ];
    
    return in_array($origin, $allowedOrigins);
}

/**
 * Log de requests para debugging (solo en desarrollo)
 */
function logApiRequest() {
    $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) || 
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
    
    if ($isLocal) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
        ];
        
        // En un proyecto real, esto iría a un archivo de log
        // error_log("API Request: " . json_encode($logData));
    }
}

// EJEMPLOS DE USO:
/*
// Al inicio de cualquier endpoint API:
require_once '../helpers/cors.php';
initializeApi();

// Para validar método:
validateRequestMethod('POST');

// Para obtener datos JSON:
$data = getJsonInput();

// Para configurar environment específico:
setEnvironmentHeaders();
*/
?>