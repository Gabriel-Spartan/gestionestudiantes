/**
 * assets/js/login.js - Lógica específica para la página de login
 * 
 * Este archivo maneja toda la interacción del formulario de login,
 * incluyendo validaciones, envío de datos y manejo de respuestas.
 */

// =============================================================================
// Variables globales y configuración
// =============================================================================

let loginFormInstance = null;

// Configuración específica del login
const LOGIN_CONFIG = {
    maxAttempts: 5,
    lockoutTime: 30, // minutos
    passwordMinLength: 6,
    rememberSessionDays: 30,
    redirectDelay: 1500, // ms antes de redirigir
    autoFillDelay: 500 // ms para animación de auto-fill
};

// =============================================================================
// Clase principal para manejo del formulario de login
// =============================================================================

class LoginForm {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.rememberCheckbox = document.getElementById('remember');
        this.loginBtn = document.getElementById('loginBtn');
        this.togglePasswordBtn = document.getElementById('togglePassword');
        this.messageArea = document.getElementById('messageArea');
        
        this.btnText = this.loginBtn.querySelector('.btn-text');
        this.btnLoading = this.loginBtn.querySelector('.btn-loading');
        
        this.isSubmitting = false;
        this.attemptCount = parseInt(localStorage.getItem('login_attempts') || '0');
        
        this.init();
    }

    /**
     * Inicializar el formulario y sus eventos
     */
    init() {
        this.setupEventListeners();
        this.loadRememberedData();
        this.setupQuickFillButtons();
        this.checkLockoutStatus();
        
        // Focus automático en el primer campo vacío
        this.autoFocus();
        
        console.log('[LoginForm] Initialized successfully');
    }

    /**
     * Configurar todos los event listeners
     */
    setupEventListeners() {
        // Envío del formulario
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Toggle password visibility
        this.togglePasswordBtn.addEventListener('click', () => this.togglePassword());
        
        // Validación en tiempo real
        this.emailInput.addEventListener('input', () => this.validateEmail());
        this.passwordInput.addEventListener('input', () => this.validatePassword());
        
        // Enter key en campos
        this.emailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.passwordInput.focus();
        });
        
        this.passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.form.requestSubmit();
        });
        
        // Limpiar mensajes cuando el usuario empiece a escribir
        this.emailInput.addEventListener('focus', () => this.clearMessageAfterDelay());
        this.passwordInput.addEventListener('focus', () => this.clearMessageAfterDelay());
        
        // Prevenir copiar/pegar en campo de contraseña (opcional)
        // this.passwordInput.addEventListener('paste', (e) => e.preventDefault());
    }

    /**
     * Manejar envío del formulario
     * @param {Event} e - Evento del formulario
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        if (this.isSubmitting) return;
        
        // Validaciones previas
        if (!this.validateForm()) return;
        
        // Verificar estado de bloqueo
        if (this.isLockedOut()) {
            this.showLockoutMessage();
            return;
        }
        
        this.setLoadingState(true);
        this.hideMessage();
        
        try {
            const formData = this.getFormData();
            
            // Llamar a la API de login
            const response = await AuthAPI.login(formData.email, formData.password);
            
            if (response.success) {
                this.handleLoginSuccess(response, formData.remember);
            } else {
                this.handleLoginError(new Error(response.message));
            }
            
        } catch (error) {
            this.handleLoginError(error);
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Manejar login exitoso
     * @param {Object} response - Respuesta de la API
     * @param {boolean} remember - Si recordar sesión
     */
    handleLoginSuccess(response, remember) {
        console.log('[LoginForm] Login successful:', response.data.user);
        
        // Limpiar contador de intentos
        this.resetAttemptCount();
        
        // Manejar "recordar sesión"
        if (remember) {
            this.saveRememberData(response.data.user.correo);
        } else {
            this.clearRememberData();
        }
        
        // Mostrar mensaje de éxito
        this.showMessage(
            `¡Bienvenido, ${response.data.user.nombre}!`, 
            'success'
        );
        
        // Redirigir después de un delay
        setTimeout(() => {
            window.location.href = 'dashboard.php';
        }, LOGIN_CONFIG.redirectDelay);
    }

    /**
     * Manejar errores de login
     * @param {Error} error - Error ocurrido
     */
    handleLoginError(error) {
        console.error('[LoginForm] Login failed:', error);
        
        // Incrementar contador de intentos
        this.incrementAttemptCount();
        
        let errorMessage = error.message || 'Error al iniciar sesión';
        
        // Manejar errores específicos
        if (error.data) {
            switch (error.data.error) {
                case 'ACCOUNT_BLOCKED':
                    this.handleAccountBlocked(error.data);
                    return;
                
                case 'ACCOUNT_INACTIVE':
                    errorMessage = 'Tu cuenta está desactivada. Contacta al administrador.';
                    break;
                
                case 'AUTH_ERROR':
                    errorMessage = this.getAuthErrorMessage();
                    break;
                
                case 'VALIDATION_ERROR':
                    errorMessage = 'Por favor, verifica los datos ingresados.';
                    break;
            }
        }
        
        this.showMessage(errorMessage, 'error');
        
        // Enfocar campo de contraseña para reintento
        this.passwordInput.focus();
        this.passwordInput.select();
    }

    /**
     * Manejar cuenta bloqueada
     * @param {Object} errorData - Datos del error
     */
    handleAccountBlocked(errorData) {
        const details = errorData.details || {};
        
        let message = 'Cuenta bloqueada por múltiples intentos fallidos.';
        
        if (details.retry_after_minutes) {
            message += `\n\nPodrás intentar de nuevo en ${details.retry_after_minutes} minutos.`;
        }
        
        if (details.blocked_until) {
            const blockedUntil = new Date(details.blocked_until);
            message += `\n\nBloqueado hasta: ${ApiUtils.formatDateTime(details.blocked_until)}`;
        }
        
        this.showMessage(message, 'error');
        
        // Deshabilitar formulario temporalmente
        this.setFormDisabled(true);
        
        // Programar reactivación
        if (details.retry_after_minutes) {
            setTimeout(() => {
                this.setFormDisabled(false);
                this.showMessage('Ya puedes intentar iniciar sesión nuevamente.', 'info');
            }, details.retry_after_minutes * 60 * 1000);
        }
    }

    /**
     * Obtener mensaje de error de autenticación personalizado
     * @returns {string} - Mensaje de error
     */
    getAuthErrorMessage() {
        const remaining = LOGIN_CONFIG.maxAttempts - this.attemptCount;
        
        if (remaining <= 2) {
            return `Credenciales incorrectas. Te quedan ${remaining} intento(s) antes del bloqueo.`;
        }
        
        return 'Email o contraseña incorrectos. Verifica tus datos.';
    }

    /**
     * Validar formulario completo
     * @returns {boolean} - Si es válido
     */
    validateForm() {
        let isValid = true;
        
        if (!this.validateEmail()) isValid = false;
        if (!this.validatePassword()) isValid = false;
        
        return isValid;
    }

    /**
     * Validar email
     * @returns {boolean} - Si es válido
     */
    validateEmail() {
        const email = this.emailInput.value.trim();
        const isValid = email && ApiUtils.isValidEmail(email);
        
        this.setFieldValidation(this.emailInput, isValid);
        
        if (!isValid && email) {
            this.showFieldError(this.emailInput, 'Ingresa un email válido');
        }
        
        return isValid;
    }

    /**
     * Validar contraseña
     * @returns {boolean} - Si es válida
     */
    validatePassword() {
        const password = this.passwordInput.value;
        const isValid = password && password.length >= LOGIN_CONFIG.passwordMinLength;
        
        this.setFieldValidation(this.passwordInput, isValid);
        
        if (!isValid && password) {
            this.showFieldError(this.passwordInput, `Mínimo ${LOGIN_CONFIG.passwordMinLength} caracteres`);
        }
        
        return isValid;
    }

    /**
     * Establecer estado de validación visual en campo
     * @param {HTMLElement} field - Campo a validar
     * @param {boolean} isValid - Si es válido
     */
    setFieldValidation(field, isValid) {
        field.classList.toggle('is-valid', isValid);
        field.classList.toggle('is-invalid', !isValid);
    }

    /**
     * Mostrar error específico de campo
     * @param {HTMLElement} field - Campo con error
     * @param {string} message - Mensaje de error
     */
    showFieldError(field, message) {
        // Buscar o crear contenedor de error
        let errorDiv = field.parentNode.querySelector('.field-error');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            field.parentNode.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        
        // Auto-ocultar después de 3 segundos
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 3000);
    }

    /**
     * Obtener datos del formulario
     * @returns {Object} - Datos del formulario
     */
    getFormData() {
        return {
            email: this.emailInput.value.trim().toLowerCase(),
            password: this.passwordInput.value,
            remember: this.rememberCheckbox.checked
        };
    }

    /**
     * Establecer estado de carga
     * @param {boolean} loading - Si está cargando
     */
    setLoadingState(loading) {
        this.isSubmitting = loading;
        this.loginBtn.disabled = loading;
        
        if (loading) {
            this.btnText.style.display = 'none';
            this.btnLoading.style.display = 'flex';
        } else {
            this.btnText.style.display = 'inline';
            this.btnLoading.style.display = 'none';
        }
        
        // Deshabilitar campos durante carga
        this.emailInput.disabled = loading;
        this.passwordInput.disabled = loading;
        this.rememberCheckbox.disabled = loading;
    }

    /**
     * Deshabilitar/habilitar formulario completo
     * @param {boolean} disabled - Si deshabilitar
     */
    setFormDisabled(disabled) {
        this.emailInput.disabled = disabled;
        this.passwordInput.disabled = disabled;
        this.rememberCheckbox.disabled = disabled;
        this.loginBtn.disabled = disabled;
        this.togglePasswordBtn.disabled = disabled;
    }

    /**
     * Alternar visibilidad de contraseña
     */
    togglePassword() {
        const isPassword = this.passwordInput.type === 'password';
        const toggleText = this.togglePasswordBtn.querySelector('.toggle-text');
        
        if (isPassword) {
            this.passwordInput.type = 'text';
            this.togglePasswordBtn.innerHTML = '🙈 <span class="toggle-text">Ocultar</span>';
        } else {
            this.passwordInput.type = 'password';
            this.togglePasswordBtn.innerHTML = '👁️ <span class="toggle-text">Mostrar</span>';
        }
        
        // Mantener focus en el campo
        this.passwordInput.focus();
    }

    /**
     * Mostrar mensaje
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo de mensaje (success, error, info, warning)
     */
    showMessage(message, type = 'info') {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        this.messageArea.innerHTML = `
            <div class="message message-${type}">
                <span class="message-icon">${icons[type] || icons.info}</span>
                <span class="message-text">${message.replace(/\n/g, '<br>')}</span>
            </div>
        `;
        
        this.messageArea.style.display = 'block';
        
        // Auto-ocultar mensajes de éxito
        if (type === 'success') {
            setTimeout(() => this.hideMessage(), 3000);
        }
        
        // Scroll al mensaje si está fuera de vista
        this.messageArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Ocultar mensaje
     */
    hideMessage() {
        this.messageArea.style.display = 'none';
    }

    /**
     * Ocultar mensaje después de un delay
     */
    clearMessageAfterDelay() {
        setTimeout(() => this.hideMessage(), 2000);
    }

    /**
     * Cargar datos recordados
     */
    loadRememberedData() {
        const rememberedEmail = localStorage.getItem('rememberedEmail');
        
        if (rememberedEmail) {
            this.emailInput.value = rememberedEmail;
            this.rememberCheckbox.checked = true;
            this.passwordInput.focus(); // Focus en contraseña si email está recordado
        }
    }

    /**
     * Guardar datos para recordar
     * @param {string} email - Email a recordar
     */
    saveRememberData(email) {
        localStorage.setItem('rememberedEmail', email);
        localStorage.setItem('rememberTimestamp', Date.now().toString());
    }

    /**
     * Limpiar datos recordados
     */
    clearRememberData() {
        localStorage.removeItem('rememberedEmail');
        localStorage.removeItem('rememberTimestamp');
    }

    /**
     * Configurar botones de auto-completar
     */
    setupQuickFillButtons() {
        const quickFillBtns = document.querySelectorAll('.quick-fill');
        
        quickFillBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                const email = btn.getAttribute('data-email');
                const password = btn.getAttribute('data-password');
                
                this.animateAutoFill(email, password);
            });
        });
    }

    /**
     * Animar auto-completar campos
     * @param {string} email - Email a completar
     * @param {string} password - Contraseña a completar
     */
    animateAutoFill(email, password) {
        // Limpiar campos primero
        this.emailInput.value = '';
        this.passwordInput.value = '';
        
        // Animar escritura del email
        this.typeText(this.emailInput, email, () => {
            // Después animar la contraseña
            setTimeout(() => {
                this.typeText(this.passwordInput, password);
            }, 200);
        });
    }

    /**
     * Simular escritura de texto
     * @param {HTMLElement} element - Campo donde escribir
     * @param {string} text - Texto a escribir
     * @param {Function} callback - Función a ejecutar al terminar
     */
    typeText(element, text, callback = null) {
        let i = 0;
        element.focus();
        
        const interval = setInterval(() => {
            element.value = text.substring(0, i + 1);
            i++;
            
            if (i >= text.length) {
                clearInterval(interval);
                if (callback) callback();
            }
        }, 50);
    }

    /**
     * Auto-focus en primer campo vacío
     */
    autoFocus() {
        if (!this.emailInput.value) {
            this.emailInput.focus();
        } else if (!this.passwordInput.value) {
            this.passwordInput.focus();
        }
    }

    /**
     * Incrementar contador de intentos
     */
    incrementAttemptCount() {
        this.attemptCount++;
        localStorage.setItem('login_attempts', this.attemptCount.toString());
        localStorage.setItem('last_attempt_time', Date.now().toString());
    }

    /**
     * Resetear contador de intentos
     */
    resetAttemptCount() {
        this.attemptCount = 0;
        localStorage.removeItem('login_attempts');
        localStorage.removeItem('last_attempt_time');
        localStorage.removeItem('lockout_until');
    }

    /**
     * Verificar si está en lockout local
     * @returns {boolean} - Si está bloqueado
     */
    isLockedOut() {
        const lockoutUntil = localStorage.getItem('lockout_until');
        
        if (lockoutUntil && Date.now() < parseInt(lockoutUntil)) {
            return true;
        }
        
        // Si pasó el tiempo, limpiar lockout
        if (lockoutUntil) {
            localStorage.removeItem('lockout_until');
        }
        
        return false;
    }

    /**
     * Verificar estado de lockout al cargar
     */
    checkLockoutStatus() {
        if (this.isLockedOut()) {
            this.showLockoutMessage();
            this.setFormDisabled(true);
        }
    }

    /**
     * Mostrar mensaje de lockout
     */
    showLockoutMessage() {
        const lockoutUntil = localStorage.getItem('lockout_until');
        
        if (lockoutUntil) {
            const remainingMs = parseInt(lockoutUntil) - Date.now();
            const remainingMinutes = Math.ceil(remainingMs / (1000 * 60));
            
            this.showMessage(
                `Demasiados intentos fallidos. Espera ${remainingMinutes} minuto(s) antes de intentar nuevamente.`,
                'warning'
            );
        }
    }
}

// =============================================================================
// Inicialización cuando el DOM esté listo
// =============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Verificar que estamos en la página de login
    if (document.getElementById('loginForm')) {
        loginFormInstance = new LoginForm();
        console.log('[Login] Login form initialized');
    }
});

// =============================================================================
// Funciones globales de utilidad
// =============================================================================

/**
 * Función global para mostrar errores
 * @param {string} message - Mensaje de error
 */
window.showGlobalError = function(message) {
    if (loginFormInstance) {
        loginFormInstance.showMessage(message, 'error');
    }
};

/**
 * Función global para mostrar mensajes
 * @param {string} message - Mensaje
 * @param {string} type - Tipo de mensaje
 */
window.showGlobalMessage = function(message, type = 'info') {
    if (loginFormInstance) {
        loginFormInstance.showMessage(message, type);
    }
};