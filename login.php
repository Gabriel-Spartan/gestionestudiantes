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
    <link rel="stylesheet" href="assets/css/login-style.css">
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
            
            
        </div>
    </section>
</main>

<!-- Scripts específicos para login -->
<script src="assets/js/api.js"></script>
<script src="assets/js/login.js"></script>

<?php include 'includes/footer.php'; ?>