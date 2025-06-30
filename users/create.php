<?php
// users/create.php - Formulario para crear usuarios con dominio @gestion.com
$pageTitle = "Crear Usuario";
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/nav.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'ADMIN') {
    $_SESSION['error_message'] = 'No tienes permisos para acceder a esta página.';
    header('Location: ../dashboard.php');
    exit();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Alertas -->
                    <div id="alertContainer"></div>
                    
                    <!-- Formulario -->
                    <form id="createUserForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user"></i> Nombre Completo *
                                    </label>
                                    <input type="text" 
                                            class="form-control" 
                                            id="nombre" 
                                            name="nombre" 
                                            required
                                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                            title="Solo se permiten letras y espacios">
                                    <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="correo" class="form-label">
                                        <i class="fas fa-envelope"></i> Correo Electrónico *
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                                class="form-control flex-grow-1" 
                                                id="correoUsuario" 
                                                name="correoUsuario" 
                                                required
                                                pattern="[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]|[a-zA-Z0-9]"
                                                title="Solo letras, números, puntos y guiones. Debe empezar y terminar con letra o número"
                                                placeholder="usuario"
                                                style="min-width: 0; flex: 2;">
                                        <span class="input-group-text bg-primary text-white fw-bold" style="flex: 0 0 auto; white-space: nowrap;">@gestion.com</span>
                                    </div>
                                    <div class="form-text">Solo letras, números, puntos y guiones. NO extensiones como .com, .net, etc.</div>
                                    <input type="hidden" id="correo" name="correo">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">
                                        <i class="fas fa-user-tag"></i> Tipo de Usuario *
                                    </label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="ADMIN">Administrador</option>
                                        <option value="SECRETARIA">Secretaria</option>
                                    </select>
                                    <div class="form-text">Tipo de usuario que determinará los permisos</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contrasenia" class="form-label">
                                        <i class="fas fa-key"></i> Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                                class="form-control" 
                                                id="contrasenia" 
                                                name="contrasenia" 
                                                required
                                                minlength="6"
                                                title="La contraseña debe tener al menos 6 caracteres">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="toggleNewPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Mínimo 6 caracteres. El usuario podrá cambiarla después.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle"></i> Información
                                    </label>
                                    <div class="alert alert-info mb-0">
                                        <small>
                                            <strong>Nota:</strong> Todos los usuarios tendrán correos con dominio @gestion.com.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="../dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar datos del usuario creado -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Usuario Creado Exitosamente
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Importante!</strong> Guarde esta información de forma segura. La contraseña no se volverá a mostrar.
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h6>Datos de acceso para el nuevo usuario:</h6>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Nombre:</strong></div>
                            <div class="col-sm-8" id="modalUserName"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Email:</strong></div>
                            <div class="col-sm-8" id="modalUserEmail"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Tipo:</strong></div>
                            <div class="col-sm-8" id="modalUserType"></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4"><strong>Contraseña:</strong></div>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="password" 
                                            class="form-control" 
                                            id="modalPassword" 
                                            readonly>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword"
                                            title="Mostrar/Ocultar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-primary" 
                                            type="button" 
                                            id="copyPassword"
                                            title="Copiar contraseña">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnVolver">
                    <i class="fas fa-home"></i> Volver al Dashboard
                </button>
                <button type="button" class="btn btn-success" id="btnCrearOtro">
                    <i class="fas fa-plus"></i> Crear Otro Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createUserForm');
    const submitBtn = document.getElementById('submitBtn');
    const alertContainer = document.getElementById('alertContainer');
    const nombreInput = document.getElementById('nombre');
    const contraseniaInput = document.getElementById('contrasenia');
    
    // Validación de nombre en tiempo real
    nombreInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        
        // Validación visual
        this.classList.remove('is-valid', 'is-invalid');
        if (this.value.trim().length >= 2) {
            this.classList.add('is-valid');
            showFieldFeedback(this, true);
        } else if (this.value.length > 0) {
            this.classList.add('is-invalid');
            showFieldFeedback(this, false, 'Mínimo 2 caracteres');
        }
    });
    
    // Validación de correo en tiempo real y construcción automática
    const correoUsuarioInput = document.getElementById('correoUsuario');
    const correoHiddenInput = document.getElementById('correo');
    
    correoUsuarioInput.addEventListener('input', function() {
        // Limpiar caracteres no válidos (solo letras, números, puntos, guiones)
        let valor = this.value.replace(/[^a-zA-Z0-9._-]/g, '');
        
        // Evitar puntos, guiones al inicio
        valor = valor.replace(/^[._-]+/, '');
        
        // Evitar puntos, guiones al final
        valor = valor.replace(/[._-]+$/, '');
        
        // Evitar dobles puntos, guiones consecutivos
        valor = valor.replace(/[._-]{2,}/g, function(match) {
            return match[0];
        });
        
        // Evitar patrones como .com, .net, .org, etc. (extensiones de dominio)
        valor = valor.replace(/\.(com|net|org|edu|gov|mil|int|co|io|ly|me|tv|cc|biz|info|name|pro|aero|museum)$/gi, '');
        
        // Evitar @ y otros caracteres de email
        valor = valor.replace(/[@]/g, '');
        
        // Limitar longitud
        if (valor.length > 20) {
            valor = valor.substring(0, 20);
        }
        
        this.value = valor;
        
        // Construir email completo
        const emailCompleto = valor ? valor + '@gestion.com' : '';
        correoHiddenInput.value = emailCompleto;
        
        // Validación visual más estricta
        this.classList.remove('is-valid', 'is-invalid');
        if (valor.length >= 3 && /^[a-zA-Z0-9]/.test(valor) && /^[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/.test(valor)) {
            this.classList.add('is-valid');
            showFieldFeedback(this, true);
        } else if (valor.length > 0) {
            this.classList.add('is-invalid');
            let mensaje = 'Formato inválido. ';
            if (valor.length < 3) {
                mensaje += 'Mínimo 3 caracteres. ';
            }
            if (!/^[a-zA-Z0-9]/.test(valor)) {
                mensaje += 'Debe empezar con letra o número. ';
            }
            if (valor.length > 1 && !/[a-zA-Z0-9]$/.test(valor)) {
                mensaje += 'Debe terminar con letra o número.';
            }
            showFieldFeedback(this, false, mensaje.trim());
        }
    });
    
    // Validación de contraseña en tiempo real
    contraseniaInput.addEventListener('input', function() {
        const password = this.value;
        const minLength = 6;
        
        // Remover clases previas
        this.classList.remove('is-valid', 'is-invalid');
        
        if (password.length >= minLength) {
            this.classList.add('is-valid');
            showFieldFeedback(this, true);
        } else if (password.length > 0) {
            this.classList.add('is-invalid');
            showFieldFeedback(this, false, 'Mínimo 6 caracteres');
        }
    });
    
    // Función para mostrar feedback visual en campos
    function showFieldFeedback(input, isValid, message = '') {
        // Buscar feedback existente
        let feedbackDiv = input.parentNode.querySelector('.invalid-feedback');
        
        if (isValid) {
            if (feedbackDiv) feedbackDiv.remove();
        } else {
            if (!feedbackDiv && message) {
                feedbackDiv = document.createElement('div');
                feedbackDiv.className = 'invalid-feedback';
                feedbackDiv.textContent = message;
                input.parentNode.appendChild(feedbackDiv);
            } else if (feedbackDiv && message) {
                feedbackDiv.textContent = message;
            }
        }
    }
    
    // Botones de sugerencias de usuario
    document.querySelectorAll('.suggestion-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const usuario = this.getAttribute('data-usuario');
            correoUsuarioInput.value = usuario;
            correoUsuarioInput.dispatchEvent(new Event('input')); // Disparar validación
            correoUsuarioInput.focus();
        });
    });
    
    // Toggle para mostrar/ocultar contraseña nueva
    document.getElementById('toggleNewPassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('contrasenia');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });
    
    // Envío del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validar que el correo esté completo
        if (!correoHiddenInput.value || !correoHiddenInput.value.includes('@gestion.com')) {
            showAlert('danger', 'Por favor, complete el campo de correo electrónico.');
            correoUsuarioInput.focus();
            return;
        }
        
        // Cambiar estado del botón
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
        
        // Limpiar alertas previas
        alertContainer.innerHTML = '';
        
        try {
            // Obtener datos del formulario
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Asegurar que se envía el correo completo
            data.correo = correoHiddenInput.value;
            
            console.log('Datos a enviar:', data);
            
            // Enviar solicitud a la API
            const response = await fetch('../api/users/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Mostrar datos en el modal
                document.getElementById('modalUserName').textContent = result.data.nombre;
                document.getElementById('modalUserEmail').textContent = result.data.correo;
                document.getElementById('modalUserType').textContent = result.data.tipo;
                document.getElementById('modalPassword').value = result.data.password_display || data.contrasenia;
                
                // Mostrar modal
                showModal();
                
                // Limpiar formulario
                form.reset();
                correoHiddenInput.value = '';
                
            } else {
                // Mostrar errores
                showAlert('danger', result.message, result.details);
            }
            
        } catch (error) {
            console.error('Error completo:', error);
            showAlert('danger', 'Error de conexión. Detalles: ' + error.message);
        } finally {
            // Restaurar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Usuario';
        }
    });
    
    // Función para mostrar alertas
    function showAlert(type, message, details = null) {
        let alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <strong>${type === 'danger' ? 'Error!' : 'Éxito!'}</strong> ${message}
        `;
        
        if (details && details.validation_errors) {
            alertHtml += '<ul class="mb-0 mt-2">';
            for (const [field, error] of Object.entries(details.validation_errors)) {
                if (typeof error === 'string') {
                    alertHtml += `<li>${error}</li>`;
                } else if (error.message) {
                    alertHtml += `<li>${error.message}</li>`;
                }
            }
            alertHtml += '</ul>';
        }
        
        alertHtml += `
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHtml;
        alertContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Funciones para manejar el modal manualmente
    function showModal() {
        const modal = document.getElementById('passwordModal');
        modal.style.display = 'block';
        modal.classList.add('show');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        
        // Agregar backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdrop';
        document.body.appendChild(backdrop);
    }
    
    function closeModal() {
        const modal = document.getElementById('passwordModal');
        const backdrop = document.getElementById('modalBackdrop');
        
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        
        if (backdrop) {
            backdrop.remove();
        }
    }
    
    // Event listeners para el modal
    document.getElementById('btnVolver').addEventListener('click', function() {
        closeModal();
        window.location.href = '../dashboard.php';
    });
    
    document.getElementById('btnCrearOtro').addEventListener('click', function() {
        closeModal();
        location.reload();
    });
    
    // Cerrar modal al hacer clic en el backdrop
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'modalBackdrop') {
            closeModal();
        }
    });
    
    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('passwordModal');
            if (modal.style.display === 'block') {
                closeModal();
            }
        }
    });
    
    // Toggle mostrar/ocultar contraseña en modal
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('modalPassword');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });
    
    // Copiar contraseña al portapapeles
    document.getElementById('copyPassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('modalPassword');
        passwordInput.select();
        document.execCommand('copy');
        
        // Cambiar icono temporalmente
        const icon = this.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'fas fa-check';
        
        setTimeout(() => {
            icon.className = originalClass;
        }, 1000);
        
        // Mostrar mensaje temporal
        const button = this;
        const originalTitle = button.title;
        button.title = '¡Copiado!';
        setTimeout(() => {
            button.title = originalTitle;
        }, 2000);
    });
});
</script>

<style>
/* Estilos para el campo de correo con dominio fijo */
.input-group {
    display: flex;
    width: 100%;
}

.input-group-text {
    font-weight: bold;
    font-size: 0.9rem;
    padding: 0.375rem 0.5rem;
    white-space: nowrap;
    flex-shrink: 0;
}

#correoUsuario {
    flex: 2 1 auto;
    min-width: 120px;
    font-size: 1rem;
    padding: 0.375rem 0.75rem;
}

#correoUsuario:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    z-index: 3;
}

#correoUsuario:focus + .input-group-text {
    border-color: #86b7fe;
}

/* Responsive: En pantallas pequeñas, ajustar aún más */
@media (max-width: 576px) {
    .input-group-text {
        font-size: 0.8rem;
        padding: 0.375rem 0.4rem;
    }
    
    #correoUsuario {
        min-width: 100px;
        font-size: 0.9rem;
    }
}

/* Estilos para botones de sugerencias */
.suggestion-btn {
    border-radius: 20px;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.suggestion-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Estilos para el modal manual */
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    outline: 0;
}

.modal.show {
    display: block !important;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
    opacity: 0.5;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 1.75rem;
    pointer-events: none;
}

@media (min-width: 576px) {
    .modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
    }
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: 0.3rem;
    outline: 0;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn {
    border-radius: 0.375rem;
}

.alert {
    border-radius: 0.5rem;
}

/* Validación visual mejorada */
.is-valid {
    border-color: #28a745;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Responsivo para móviles */
@media (max-width: 768px) {
    .d-flex.flex-wrap.gap-2 {
        flex-direction: column;
    }
    
    .suggestion-btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>