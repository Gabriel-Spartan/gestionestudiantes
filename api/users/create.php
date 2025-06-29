<?php
// api/users/create.php - Endpoint para crear usuarios (secretarias)
// POST /api/users/create.php

// Incluir archivos necesarios
require_once '../helpers/cors.php';
require_once '../helpers/response.php';
require_once '../config/database.php';

// Usar la función initializeApi que ya tienes
initializeApi();

// Validar método manualmente ya que no tienes validateRequestMethod
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido',
        'message' => 'Este endpoint solo acepta POST'
    ]);
    exit();
}

// Verificar autenticación
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ADMIN') {
    sendUnauthorizedResponse("Solo los administradores pueden crear usuarios");
}

// Función principal del endpoint
handleApiRequest(function() {
    global $pdo;
    
    // 1. Obtener datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Si no hay datos JSON, intentar obtener de $_POST
    if (empty($data)) {
        $data = $_POST;
    }
    
    // 2. Validar campos requeridos manualmente
    $requiredFields = ['nombre', 'correo', 'tipo', 'contrasenia'];
    $missing = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendValidationErrorResponse([
            'missing_fields' => $missing,
            'message' => 'Los siguientes campos son requeridos: ' . implode(', ', $missing)
        ]);
        return;
    }
    
    // 3. Limpiar y preparar datos
    $nombre = trim($data['nombre']);
    $correo = trim(strtolower($data['correo']));
    $tipo = strtoupper(trim($data['tipo']));
    $contrasenia = trim($data['contrasenia']);
    $createdBy = $_SESSION['user_id'];
    
    // 4. Validaciones específicas
    $errors = [];
    
    // Validar nombre (solo letras y espacios)
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
        $errors['nombre'] = 'El nombre solo debe contener letras y espacios';
    }
    
    // Validar email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = 'Formato de email inválido';
    }
    
    // Validar tipo de usuario
    $tiposPermitidos = ['ADMIN', 'SECRETARIA'];
    if (!in_array($tipo, $tiposPermitidos)) {
        $errors['tipo'] = 'Tipo de usuario no válido. Permitidos: ' . implode(', ', $tiposPermitidos);
    }
    
    // Validar contraseña
    if (strlen($contrasenia) < 6) {
        $errors['contrasenia'] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (!empty($errors)) {
        sendValidationErrorResponse($errors);
        return;
    }
    
    try {
        // 5. Verificar que el email no esté registrado
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            sendValidationErrorResponse([
                'correo' => 'Este email ya está registrado'
            ]);
            return;
        }
        
        // 6. Usar contraseña personalizada del formulario
        $passwordHash = password_hash($contrasenia, PASSWORD_DEFAULT);
        
        // 7. Crear usuario en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO usuarios 
            (nombre, correo, contrasenia, tipo, estado, fecha_creacion, fecha_actualizacion) 
            VALUES 
            (:nombre, :correo, :contrasenia, :tipo, 'ACTIVO', NOW(), NOW())
        ");
        
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':contrasenia', $passwordHash);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        
        $newUserId = $pdo->lastInsertId();
        
        // 8. Registrar en auditoría
        logUserCreation($newUserId, $correo, $tipo, $createdBy, $pdo);
        
        // 9. Preparar respuesta con datos del usuario creado
        $userData = [
            'id' => $newUserId,
            'nombre' => $nombre,
            'correo' => $correo,
            'tipo' => $tipo,
            'estado' => 'ACTIVO',
            'password_display' => $contrasenia, // Mostrar la contraseña configurada
            'password_type' => 'custom', // Indicar que es personalizada
            'created_by' => $_SESSION['user_name'] ?? 'Admin'
        ];
        
        // 10. DEBUGGING - Agregar logs temporales
        error_log("Usuario creado exitosamente: ID = $newUserId, Email = $correo");
        error_log("Contraseña personalizada configurada");
        
        // 11. Enviar respuesta exitosa
        sendCreatedResponse($userData, "Usuario");
        
    } catch (PDOException $e) {
        error_log("Create user DB Error: " . $e->getMessage());
        sendServerErrorResponse("Error al crear usuario");
    }
});

/**
 * Registrar creación de usuario en auditoría
 */
function logUserCreation($newUserId, $email, $tipo, $createdBy, $pdo) {
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("
            INSERT INTO auditoria_usuarios 
            (usuario_id, correo_intento, accion, descripcion, ip_address, user_agent) 
            VALUES 
            (:user_id, :email, :action, :description, :ip, :user_agent)
        ");
        
        $action = 'USUARIO_CREADO';
        $description = "Usuario tipo $tipo creado por ID: $createdBy";
        
        $stmt->bindParam(':user_id', $newUserId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("User creation audit log error: " . $e->getMessage());
    }
}
?>