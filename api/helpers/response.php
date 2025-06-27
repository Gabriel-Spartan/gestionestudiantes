<?php
// api/helpers/response.php - Funciones para respuestas JSON estandarizadas

/**
 * Estructura estándar de respuesta para toda la API
 * Garantiza consistencia en todas las respuestas
 */

/**
 * Respuesta exitosa genérica
 * @param mixed $data - Datos a devolver
 * @param string $message - Mensaje descriptivo
 * @param int $httpCode - Código HTTP (default: 200)
 */
function sendSuccessResponse($data = null, $message = "Operación exitosa", $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Respuesta de error genérica
 * @param string $error - Tipo de error
 * @param string $message - Mensaje descriptivo del error
 * @param int $httpCode - Código HTTP (default: 400)
 * @param mixed $details - Detalles adicionales del error
 */
function sendErrorResponse($error, $message, $httpCode = 400, $details = null) {
    http_response_code($httpCode);
    
    $response = [
        'success' => false,
        'error' => $error,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Agregar detalles solo si se proporcionan
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    // En desarrollo, agregar información adicional de debug
    if (isLocalEnvironment()) {
        $response['debug'] = [
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'server_time' => time()
        ];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Respuesta para login exitoso
 * @param array $user - Datos del usuario
 * @param string $sessionId - ID de la sesión
 */
function sendLoginSuccessResponse($user, $sessionId = null) {
    $data = [
        'user' => [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'correo' => $user['correo'],
            'tipo' => $user['tipo']
        ],
        'session' => [
            'id' => $sessionId,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
        ]
    ];
    
    sendSuccessResponse($data, "Login exitoso", 200);
}

/**
 * Respuesta para errores de autenticación
 * @param string $message - Mensaje específico del error
 */
function sendAuthErrorResponse($message = "Credenciales inválidas") {
    sendErrorResponse(
        "AUTH_ERROR", 
        $message, 
        401,
        ['action_required' => 'login']
    );
}

/**
 * Respuesta para acceso no autorizado
 * @param string $message - Mensaje específico
 */
function sendUnauthorizedResponse($message = "Acceso no autorizado") {
    sendErrorResponse(
        "UNAUTHORIZED", 
        $message, 
        403,
        ['required_permission' => 'valid_session']
    );
}

/**
 * Respuesta para recurso no encontrado
 * @param string $resource - Tipo de recurso no encontrado
 * @param mixed $identifier - Identificador buscado
 */
function sendNotFoundResponse($resource = "Recurso", $identifier = null) {
    $message = "$resource no encontrado";
    if ($identifier) {
        $message .= " (ID: $identifier)";
    }
    
    sendErrorResponse("NOT_FOUND", $message, 404);
}

/**
 * Respuesta para errores de validación
 * @param array $errors - Lista de errores de validación
 */
function sendValidationErrorResponse($errors) {
    sendErrorResponse(
        "VALIDATION_ERROR", 
        "Datos inválidos", 
        422,
        ['validation_errors' => $errors]
    );
}

/**
 * Respuesta para métodos no permitidos
 * @param string $allowedMethods - Métodos permitidos
 */
function sendMethodNotAllowedResponse($allowedMethods) {
    http_response_code(405);
    header("Allow: $allowedMethods");
    
    sendErrorResponse(
        "METHOD_NOT_ALLOWED", 
        "Método no permitido", 
        405,
        ['allowed_methods' => explode(', ', $allowedMethods)]
    );
}

/**
 * Respuesta para lista de recursos con paginación
 * @param array $items - Lista de elementos
 * @param int $total - Total de elementos
 * @param int $page - Página actual
 * @param int $limit - Límite por página
 */
function sendPaginatedResponse($items, $total, $page = 1, $limit = 10) {
    $totalPages = ceil($total / $limit);
    
    $data = [
        'items' => $items,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ]
    ];
    
    sendSuccessResponse($data, "Lista obtenida exitosamente");
}

/**
 * Respuesta para operaciones de creación
 * @param mixed $data - Datos del recurso creado
 * @param string $resource - Tipo de recurso creado
 */
function sendCreatedResponse($data, $resource = "Recurso") {
    sendSuccessResponse($data, "$resource creado exitosamente", 201);
}

/**
 * Respuesta para operaciones de actualización
 * @param mixed $data - Datos del recurso actualizado
 * @param string $resource - Tipo de recurso actualizado
 */
function sendUpdatedResponse($data, $resource = "Recurso") {
    sendSuccessResponse($data, "$resource actualizado exitosamente", 200);
}

/**
 * Respuesta para operaciones de eliminación
 * @param string $resource - Tipo de recurso eliminado
 * @param mixed $identifier - Identificador del recurso eliminado
 */
function sendDeletedResponse($resource = "Recurso", $identifier = null) {
    $message = "$resource eliminado exitosamente";
    if ($identifier) {
        $message .= " (ID: $identifier)";
    }
    
    sendSuccessResponse(null, $message, 200);
}

/**
 * Respuesta para errores del servidor
 * @param string $message - Mensaje del error
 * @param Exception $exception - Excepción capturada (opcional)
 */
function sendServerErrorResponse($message = "Error interno del servidor", $exception = null) {
    $details = null;
    
    // En desarrollo, mostrar detalles del error
    if (isLocalEnvironment() && $exception) {
        $details = [
            'exception_message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
    }
    
    sendErrorResponse("SERVER_ERROR", $message, 500, $details);
}

/**
 * Respuesta para rate limiting
 * @param int $retryAfter - Segundos hasta poder intentar de nuevo
 */
function sendRateLimitResponse($retryAfter = 60) {
    http_response_code(429);
    header("Retry-After: $retryAfter");
    
    sendErrorResponse(
        "RATE_LIMIT_EXCEEDED", 
        "Demasiadas solicitudes. Intente más tarde.", 
        429,
        ['retry_after_seconds' => $retryAfter]
    );
}

/**
 * Función auxiliar para detectar entorno local
 * @return bool
 */
function isLocalEnvironment() {
    $localHosts = ['localhost', '127.0.0.1', '::1'];
    return in_array($_SERVER['HTTP_HOST'], $localHosts) ||
        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'], '.local') !== false;
}

/**
 * Wrapper para manejo de excepciones en endpoints
 * @param callable $callback - Función a ejecutar
 */
function handleApiRequest($callback) {
    try {
        $callback();
    } catch (PDOException $e) {
        sendServerErrorResponse("Error de base de datos", $e);
    } catch (Exception $e) {
        sendServerErrorResponse("Error interno", $e);
    }
}

/**
 * Validar campos requeridos en request
 * @param array $data - Datos recibidos
 * @param array $required - Campos requeridos
 * @return bool - true si todo está válido, false y envía error si falta algo
 */
function validateRequiredFields($data, $required) {
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendValidationErrorResponse([
            'missing_fields' => $missing,
            'message' => 'Los siguientes campos son requeridos: ' . implode(', ', $missing)
        ]);
        return false;
    }
    
    return true;
}

// EJEMPLOS DE USO:
/*
// Respuesta exitosa simple:
sendSuccessResponse(['id' => 1, 'nombre' => 'Juan'], "Usuario obtenido");

// Error de validación:
sendValidationErrorResponse(['email' => 'Formato de email inválido']);

// Lista con paginación:
sendPaginatedResponse($estudiantes, 50, 1, 10);

// Manejo seguro de requests:
handleApiRequest(function() {
    // Tu código aquí
    sendSuccessResponse($data);
});
*/
?>