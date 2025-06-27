<?php
// api/helpers/cors.php - Configuración de CORS y headers para API

/**
 * Inicializar API con headers CORS y configuración básica
 */
function initializeApi() {
    // Headers CORS
    setCorsHeaders();
    
    // Headers de contenido
    header('Content-Type: application/json; charset=utf-8');
    
    // Headers de seguridad
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    // Manejar preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Configurar headers CORS
 */
function setCorsHeaders() {
    // Permitir origen local para desarrollo
    $allowedOrigins = [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:3000',
        'http://localhost:8000'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // En desarrollo local, permitir cualquier localhost
    if (isLocalDevelopment()) {
        if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: *");
        }
    } else {
        // En producción, verificar orígenes permitidos
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
    }
    
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400'); // 24 horas
}

/**
 * Detectar si estamos en desarrollo local
 */
function isLocalDevelopment() {
    $localHosts = ['localhost', '127.0.0.1', '::1'];
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    
    return in_array($currentHost, $localHosts) ||
           strpos($currentHost, 'localhost') !== false ||
           strpos($currentHost, '.local') !== false ||
           strpos($currentHost, '192.168') !== false ||
           strpos($currentHost, '10.') === 0;
}

/**
 * Validar token CSRF (si se implementa)
 */
function validateCsrfToken() {
    // Implementar si se necesita protección CSRF
    // Por ahora, validamos que venga de una sesión válida
    return true;
}

/**
 * Rate limiting básico por IP
 */
function checkRateLimit($maxRequests = 100, $timeWindow = 3600) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cacheFile = sys_get_temp_dir() . '/api_rate_limit_' . md5($ip);
    
    $requests = [];
    $currentTime = time();
    
    // Leer requests previos si existen
    if (file_exists($cacheFile)) {
        $data = file_get_contents($cacheFile);
        $requests = json_decode($data, true) ?: [];
    }
    
    // Filtrar requests dentro de la ventana de tiempo
    $requests = array_filter($requests, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });
    
    // Verificar si excede el límite
    if (count($requests) >= $maxRequests) {
        require_once __DIR__ . '/response.php';
        sendRateLimitResponse($timeWindow);
    }
    
    // Agregar request actual
    $requests[] = $currentTime;
    
    // Guardar en cache
    file_put_contents($cacheFile, json_encode($requests));
    
    return true;
}

/**
 * Limpiar archivos de rate limit antiguos
 */
function cleanupRateLimitFiles() {
    $tempDir = sys_get_temp_dir();
    $pattern = $tempDir . '/api_rate_limit_*';
    $files = glob($pattern);
    $currentTime = time();
    
    foreach ($files as $file) {
        if (($currentTime - filemtime($file)) > 7200) { // 2 horas
            unlink($file);
        }
    }
}

/**
 * Log de requests de API para debugging
 */
function logApiRequest() {
    if (!isLocalDevelopment()) {
        return;
    }
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $logFile = sys_get_temp_dir() . '/api_requests.log';
    file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Validar que el request venga de la misma aplicación
 */
function validateReferrer() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    // Si no hay referer, permitir (para requests directos de la API)
    if (empty($referer)) {
        return true;
    }
    
    // Verificar que el referer sea del mismo host
    $refererHost = parse_url($referer, PHP_URL_HOST);
    
    return $refererHost === $host;
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
 * Validar que el request sea del tipo esperado
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
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        return [];
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

?>