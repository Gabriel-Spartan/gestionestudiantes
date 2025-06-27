<?php
// api/auth/login.php - Endpoint para autenticación de usuarios
// POST /api/auth/login.php

// Incluir archivos necesarios
require_once '../helpers/cors.php';
require_once '../helpers/response.php';
require_once '../config/database.php';

// Inicializar API con CORS y headers
initializeApi();

// Validar que sea método POST
validateRequestMethod('POST');

// Función principal del endpoint
handleApiRequest(function() {
    global $pdo;
    
    // 1. Obtener datos del request
    $data = getJsonInput();
    
    // 2. Validar campos requeridos
    validateRequiredFields($data, ['email', 'password']);
    
    // 3. Limpiar y preparar datos
    $email = trim(strtolower($data['email']));
    $password = trim($data['password']);
    
    // 4. Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendValidationErrorResponse([
            'email' => 'Formato de email inválido'
        ]);
    }
    
    // 5. Buscar usuario en la base de datos
    try {
        $stmt = $pdo->prepare("
            SELECT id, nombre, correo, contrasenia, tipo, estado, 
                    intentos_fallidos, cuenta_bloqueada, fecha_bloqueo
            FROM usuarios 
            WHERE correo = :email
        ");
        
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        // 6. Verificar si el usuario existe
        if (!$user) {
            // Log del intento fallido
            logFailedAttempt($email, 'Usuario no encontrado', $pdo);
            
            sendAuthErrorResponse("Credenciales inválidas");
        }
        
        // 7. Verificar si la cuenta está bloqueada
        if ($user['cuenta_bloqueada']) {
            // Obtener tiempo de bloqueo desde configuración
            $stmt = $pdo->prepare("SELECT valor FROM configuracion_seguridad WHERE clave = 'tiempo_bloqueo_minutos'");
            $stmt->execute();
            $bloqueoMinutos = $stmt->fetch()['valor'] ?? 30; // Default 30 si no existe
            
            // Verificar si ya pasó el tiempo de bloqueo (dinámico desde BD)
            if ($user['fecha_bloqueo'] && 
                strtotime($user['fecha_bloqueo']) < strtotime("-{$bloqueoMinutos} minutes")) {
                // Desbloquear cuenta automáticamente
                unlockAccount($user['id'], $pdo);
                $user['cuenta_bloqueada'] = false;
                $user['intentos_fallidos'] = 0;
            } else {
                sendErrorResponse(
                    "ACCOUNT_BLOCKED", 
                    "Cuenta bloqueada por múltiples intentos fallidos. Intente más tarde.", 
                    423, // 423 Locked
                    [
                        'blocked_until' => date('Y-m-d H:i:s', strtotime($user['fecha_bloqueo'] . " +{$bloqueoMinutos} minutes")),
                        'retry_after_minutes' => (int)$bloqueoMinutos
                    ]
                );
            }
        }
        
        // 8. Verificar si la cuenta está activa
        if ($user['estado'] !== 'ACTIVO') {
            sendErrorResponse(
                "ACCOUNT_INACTIVE", 
                "Cuenta desactivada. Contacte al administrador.", 
                403
            );
        }
        
        // 9. Verificar contraseña
        $passwordValid = false;
        
        // Verificar si es hash MD5 (contraseñas temporales)
        if (strlen($user['contrasenia']) === 32 && ctype_xdigit($user['contrasenia'])) {
            // Contraseña MD5 (temporal)
            $passwordValid = (md5($password) === $user['contrasenia']);
        } else {
            // Contraseña bcrypt (segura)
            $passwordValid = password_verify($password, $user['contrasenia']);
        }
        
        if (!$passwordValid) {
            // Incrementar intentos fallidos
            incrementFailedAttempts($user['id'], $email, $pdo);
            
            sendAuthErrorResponse("Credenciales inválidas");
        }
        
        // 10. Login exitoso - Limpiar intentos fallidos
        resetFailedAttempts($user['id'], $pdo);
        
        // 11. Crear sesión
        session_start();
        session_regenerate_id(true); // Regenerar ID por seguridad
        
        $sessionId = session_id();
        
        // 12. Guardar datos en la sesión PHP
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['correo'];
        $_SESSION['user_type'] = $user['tipo'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // 13. Actualizar información del usuario en BD
        updateUserLoginInfo($user['id'], $sessionId, $pdo);
        
        // 14. Registrar en auditoría
        logSuccessfulLogin($user['id'], $user['correo'], $pdo);
        
        // 15. Registrar sesión activa en BD
        createActiveSession($sessionId, $user['id'], $pdo);
        
        // 16. Preparar respuesta con datos del usuario (sin contraseña)
        $userData = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'correo' => $user['correo'],
            'tipo' => $user['tipo']
        ];
        
        // 17. Enviar respuesta exitosa
        sendLoginSuccessResponse($userData, $sessionId);
        
    } catch (PDOException $e) {
        // Error de base de datos
        error_log("Login DB Error: " . $e->getMessage());
        sendServerErrorResponse("Error al procesar login");
    }
});

/**
 * Incrementar contador de intentos fallidos
 */
function incrementFailedAttempts($userId, $email, $pdo) {
    try {
        // Obtener intentos actuales
        $stmt = $pdo->prepare("SELECT intentos_fallidos FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $current = $stmt->fetch();
        
        $newAttempts = $current['intentos_fallidos'] + 1;
        $blockAccount = $newAttempts >= 5; // Bloquear después de 5 intentos
        
        // Crear variables para bindParam (requiere referencias)
        $blockDate = $blockAccount ? date('Y-m-d H:i:s') : null;
        
        // Actualizar intentos y bloquear si es necesario
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET intentos_fallidos = :attempts,
                cuenta_bloqueada = :blocked,
                fecha_bloqueo = :block_date
            WHERE id = :id
        ");
        
        $stmt->bindParam(':attempts', $newAttempts);
        $stmt->bindParam(':blocked', $blockAccount, PDO::PARAM_BOOL);
        $stmt->bindParam(':block_date', $blockDate);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        // Log de auditoría
        $action = $blockAccount ? 'CUENTA_BLOQUEADA' : 'LOGIN_FALLIDO';
        $description = "Intento fallido #$newAttempts";
        logAuditEvent($userId, $email, $action, $description, $pdo);
        
    } catch (PDOException $e) {
        error_log("Failed attempts increment error: " . $e->getMessage());
    }
}

/**
 * Resetear intentos fallidos después de login exitoso
 */
function resetFailedAttempts($userId, $pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET intentos_fallidos = 0,
                cuenta_bloqueada = 0,
                fecha_bloqueo = NULL
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Reset failed attempts error: " . $e->getMessage());
    }
}

/**
 * Desbloquear cuenta automáticamente
 */
function unlockAccount($userId, $pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET cuenta_bloqueada = 0,
                intentos_fallidos = 0,
                fecha_bloqueo = NULL
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        logAuditEvent($userId, '', 'CUENTA_DESBLOQUEADA', 'Desbloqueo automático por tiempo', $pdo);
        
    } catch (PDOException $e) {
        error_log("Unlock account error: " . $e->getMessage());
    }
}

/**
 * Actualizar información de login del usuario
 */
function updateUserLoginInfo($userId, $sessionId, $pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET ultimo_acceso = NOW(),
                sesion_activa = :session_id,
                ip_ultimo_acceso = :ip
            WHERE id = :id
        ");
        
        // Crear variables para bindParam (requiere referencias)
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->bindParam(':ip', $ipAddress);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Update login info error: " . $e->getMessage());
    }
}

/**
 * Crear registro de sesión activa
 */
function createActiveSession($sessionId, $userId, $pdo) {
    try {
        // Obtener límite de sesiones simultáneas desde configuración
        $stmt = $pdo->prepare("SELECT valor FROM configuracion_seguridad WHERE clave = 'max_sesiones_simultaneas'");
        $stmt->execute();
        $maxSesiones = (int)($stmt->fetch()['valor'] ?? 3); // Default 3 si no existe
        
        // Contar sesiones activas actuales del usuario
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sesiones_activas WHERE usuario_id = :user_id AND estado = 'ACTIVA'");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $sesionesActivas = $stmt->fetch()['total'];
        
        // Si ya alcanzó el límite, eliminar la sesión más antigua
        if ($sesionesActivas >= $maxSesiones) {
            $stmt = $pdo->prepare("
                DELETE FROM sesiones_activas 
                WHERE usuario_id = :user_id 
                AND estado = 'ACTIVA' 
                ORDER BY ultima_actividad ASC 
                LIMIT 1
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            // Log de auditoría
            logAuditEvent($userId, '', 'SESION_EXPIRADA', 'Sesión cerrada por límite de sesiones simultáneas', $pdo);
        }
        
        // Crear nuevas variables para bindParam
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Crear nueva sesión
        $stmt = $pdo->prepare("
            INSERT INTO sesiones_activas 
            (id, usuario_id, ip_address, user_agent, ultima_actividad, fecha_expiracion, estado) 
            VALUES 
            (:id, :user_id, :ip, :user_agent, NOW(), DATE_ADD(NOW(), INTERVAL 30 MINUTE), 'ACTIVA')
        ");
        
        $stmt->bindParam(':id', $sessionId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':ip', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Create active session error: " . $e->getMessage());
    }
}

/**
 * Log de intento fallido (usuario no encontrado)
 */
function logFailedAttempt($email, $reason, $pdo) {
    logAuditEvent(null, $email, 'LOGIN_FALLIDO', $reason, $pdo);
}

/**
 * Log de login exitoso
 */
function logSuccessfulLogin($userId, $email, $pdo) {
    logAuditEvent($userId, $email, 'LOGIN_EXITOSO', 'Login exitoso', $pdo);
}

/**
 * Función general para log de auditoría
 */
function logAuditEvent($userId, $email, $action, $description, $pdo) {
    try {
        // Crear variables para bindParam
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("
            INSERT INTO auditoria_usuarios 
            (usuario_id, correo_intento, accion, descripcion, ip_address, user_agent) 
            VALUES 
            (:user_id, :email, :action, :description, :ip, :user_agent)
        ");
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}
?>