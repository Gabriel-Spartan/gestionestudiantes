<?php
// login.php - Página de login (solo HTML y PHP)
$pageTitle = "Iniciar Sesión";
include 'includes/header.php';
include 'includes/nav.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<main>
    <link rel="stylesheet" href="assets/css/login.css">
    <section class="login-container">
        <div class="login-form-wrapper">
            <h1>Iniciar Sesión</h1>
            <p>Accede a tu cuenta para gestionar estudiantes</p>
            
            <!-- Formulario de login -->
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="admin@gestion.com"
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group password-group">
                    <label for="password">Contraseña:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Tu contraseña"
                        autocomplete="current-password"
                    >
                    <button type="button" id="togglePassword" class="toggle-password">
                        👁️ <span class="toggle-text">Mostrar</span>
                    </button>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Recordar sesión
                    </label>
                </div>
                
                <button type="submit" id="loginBtn" class="login-btn">
                    <span class="btn-text">Iniciar Sesión</span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        Iniciando...
                    </span>
                </button>
            </form>
            
            <!-- Área de mensajes -->
            <div id="messageArea" class="message-area" style="display: none;">
                <!-- Los mensajes se insertarán aquí dinámicamente -->
            </div>
            
            <!-- Información de ayuda -->
            <div class="login-help">
                <h3>Cuentas de prueba:</h3>
                <div class="test-accounts">
                    <div class="account">
                        <strong>Administrador:</strong><br>
                        <span class="email">admin@gestion.com</span><br>
                        <span class="password">Admin12345</span>
                        <button class="quick-fill" data-email="admin@gestion.com" data-password="Admin12345">
                            ⚡ Autocompletar
                        </button>
                    </div>
                    <div class="account">
                        <strong>Secretaria:</strong><br>
                        <span class="email">secretaria@gestion.com</span><br>
                        <span class="password">Admin12345</span>
                        <button class="quick-fill" data-email="secretaria@gestion.com" data-password="Admin12345">
                            ⚡ Autocompletar
                        </button>
                    </div>
                </div>
                
                <div class="security-info">
                    <h4>🛡️ Información de seguridad:</h4>
                    <ul>
                        <li>Máximo 5 intentos fallidos antes de bloqueo</li>
                        <li>Bloqueo automático por tiempo configurable</li>
                        <li>Sesión expira en 30 minutos de inactividad</li>
                        <li>Máximo de sesiones simultáneas limitado</li>
                        <li>Auditoría completa de todos los accesos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Scripts específicos para login -->
<script src="assets/js/api.js"></script>
<script src="assets/js/login.js"></script>

<?php include 'includes/footer.php'; ?>