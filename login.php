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
            <form id="loginForm" class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required placeholder="admin@gestion.com"
                        autocomplete="email">
                </div>

                <div class="form-group password-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required placeholder="Tu contraseña"
                        autocomplete="current-password">
                    <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar/Ocultar contraseña"></button>
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

            <!-- Botones de prueba rápida (solo para desarrollo) -->
            <?php if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false): ?>
            <div class="quick-fill-section" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <h4>Acceso Rápido (Solo Desarrollo)</h4>
                <button type="button" class="quick-fill btn-secondary" data-email="admin@gestion.com" data-password="Admin12345">
                    👨‍💼 Admin
                </button>
                <button type="button" class="quick-fill btn-secondary" data-email="secretaria@gestion.com" data-password="Secretaria123">
                    👩‍💼 Secretaria
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Scripts específicos para login - RUTAS CORREGIDAS -->
<script src="assets/js/api.js"></script>
<script src="assets/js/login.js"></script>

<script>
// Verificar si los scripts se cargaron correctamente
document.addEventListener('DOMContentLoaded', function() {
    // Dar tiempo a que se carguen los scripts
    setTimeout(function() {
        if (typeof AuthAPI === 'undefined' || typeof LoginForm === 'undefined') {
            console.warn('[LOGIN] Scripts externos no cargados, usando fallback');
            
            // Implementación de emergencia inline
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const btn = document.getElementById('loginBtn');
                    const messageArea = document.getElementById('messageArea');
                    
                    // Deshabilitar botón
                    btn.disabled = true;
                    btn.querySelector('.btn-text').style.display = 'none';
                    btn.querySelector('.btn-loading').style.display = 'flex';
                    
                    try {
                        const formData = new FormData(loginForm);
                        const data = {
                            email: formData.get('email'),
                            password: formData.get('password')
                        };
                        
                        console.log('[LOGIN] Enviando a API:', data);
                        
                        const response = await fetch('api/auth/login.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            messageArea.innerHTML = '<div class="message message-success">✅ Login exitoso. Redirigiendo...</div>';
                            messageArea.style.display = 'block';
                            
                            setTimeout(() => {
                                window.location.href = 'dashboard.php';
                            }, 1500);
                        } else {
                            throw new Error(result.message || 'Error de login');
                        }
                        
                    } catch (error) {
                        console.error('[LOGIN] Error:', error);
                        messageArea.innerHTML = `<div class="message message-error">❌ ${error.message}</div>`;
                        messageArea.style.display = 'block';
                        
                        // Reactivar botón
                        btn.disabled = false;
                        btn.querySelector('.btn-text').style.display = 'inline';
                        btn.querySelector('.btn-loading').style.display = 'none';
                    }
                });
            }
        } else {
            console.log('[LOGIN] Scripts cargados correctamente');
        }
    }, 500);
});
</script>

<?php include 'includes/footer.php'; ?>