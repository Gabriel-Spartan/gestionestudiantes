<?php
// logout.php - Versión adaptada a tu configuración

// 1. Configuración de seguridad básica
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// 2. Incluir configuración de la base de datos
require_once __DIR__ . '/config/database.php';

// 3. Iniciar sesión con configuración segura
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict'
    ]);
}

// 4. Obtener datos del usuario antes de destruir la sesión
$user_id = $_SESSION['user_id'] ?? null;

// 5. Destruir la sesión de manera segura
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// 6. Registrar el logout en la base de datos (versión simplificada pero segura)
if ($user_id) {
    try {
        // Usar la conexión PDO ya establecida en database.php
        global $pdo;
        
        // Registrar en auditoría
        $stmt = $pdo->prepare("
            INSERT INTO auditoria_usuarios 
            (usuario_id, accion, ip_address, user_agent) 
            VALUES (:user_id, 'LOGOUT', :ip, :ua)
        ");
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':ip' => $_SERVER['REMOTE_ADDR'],
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        ]);
        
        // Actualizar estado del usuario
        $pdo->prepare("UPDATE usuarios SET sesion_activa = NULL WHERE id = :user_id")
           ->execute([':user_id' => $user_id]);
           
    } catch (PDOException $e) {
        error_log('Error BD en logout: ' . $e->getMessage());
        // Continuar el proceso aunque falle el registro en BD
    }
}

// 7. Respuesta JSON con redirección
echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada correctamente',
    'redirect' => 'index.php'
]);

exit;