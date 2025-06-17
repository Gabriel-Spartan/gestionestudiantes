/**
 * assets/js/api.js - Funciones centralizadas para llamadas a la API
 * 
 * Este archivo contiene todas las funciones para interactuar con los endpoints
 * de la API, proporcionando una interfaz limpia y reutilizable.
 */

// =============================================================================
// Configuración y constantes
// =============================================================================

const API_CONFIG = {
    baseUrl: window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, ''),
    timeout: 10000, // 10 segundos
    retries: 3,
    endpoints: {
        auth: {
            login: '/api/auth/login.php',
            logout: '/api/auth/logout.php',
            validate: '/api/auth/validate.php'
        },
        students: {
            list: '/api/students/index.php',
            create: '/api/students/create.php',
            show: '/api/students/show.php',
            update: '/api/students/update.php',
            delete: '/api/students/delete.php'
        }
    }
};

// =============================================================================
// Clase principal para manejo de API
// =============================================================================

class ApiClient {
    constructor(config = API_CONFIG) {
        this.config = config;
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    /**
     * Realizar petición HTTP genérica
     * @param {string} url - URL del endpoint
     * @param {Object} options - Opciones de la petición
     * @returns {Promise<Object>} - Respuesta de la API
     */
    async request(url, options = {}) {
        const fullUrl = url.startsWith('http') ? url : this.config.baseUrl + url;
        
        const config = {
            method: 'GET',
            headers: { ...this.defaultHeaders },
            ...options
        };

        // Agregar body para métodos que lo requieren
        if (options.data && ['POST', 'PUT', 'PATCH'].includes(config.method)) {
            config.body = JSON.stringify(options.data);
        }

        let lastError;
        
        // Implementar reintentos
        for (let attempt = 1; attempt <= this.config.retries; attempt++) {
            try {
                console.log(`[API] ${config.method} ${fullUrl} (intento ${attempt})`);
                
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);
                
                const response = await fetch(fullUrl, {
                    ...config,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                // Procesar respuesta
                const result = await this.processResponse(response);
                
                if (attempt > 1) {
                    console.log(`[API] Éxito después de ${attempt} intentos`);
                }
                
                return result;
                
            } catch (error) {
                lastError = error;
                console.warn(`[API] Intento ${attempt} falló:`, error.message);
                
                // Si es el último intento o no es un error de red, lanzar error
                if (attempt === this.config.retries || !this.isRetryableError(error)) {
                    break;
                }
                
                // Esperar antes del siguiente intento (backoff exponencial)
                await this.delay(Math.pow(2, attempt - 1) * 1000);
            }
        }
        
        throw lastError;
    }

    /**
     * Procesar respuesta HTTP
     * @param {Response} response - Respuesta de fetch
     * @returns {Promise<Object>} - Datos procesados
     */
    async processResponse(response) {
        let data;
        
        try {
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(`Respuesta no JSON recibida: ${text.substring(0, 200)}`);
            }
        } catch (error) {
            throw new Error(`Error parsing JSON: ${error.message}`);
        }

        // Log de respuesta para debugging
        console.log(`[API] Response:`, {
            status: response.status,
            success: data.success,
            message: data.message
        });

        // Manejar errores de la API
        if (!response.ok) {
            const error = new Error(data.message || `HTTP ${response.status}`);
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    }

    /**
     * Determinar si un error es reintentable
     * @param {Error} error - Error a evaluar
     * @returns {boolean} - Si debe reintentar
     */
    isRetryableError(error) {
        if (error.name === 'AbortError') return false; // Timeout
        if (error.status && error.status >= 400 && error.status < 500) return false; // Errores del cliente
        return true; // Errores de red o servidor
    }

    /**
     * Esperar un tiempo determinado
     * @param {number} ms - Milisegundos a esperar
     * @returns {Promise<void>}
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // =========================================================================
    // Métodos HTTP convenientes
    // =========================================================================

    async get(url, params = {}) {
        const queryString = Object.keys(params).length > 0 
            ? '?' + new URLSearchParams(params).toString()
            : '';
        
        return this.request(url + queryString, { method: 'GET' });
    }

    async post(url, data = {}) {
        return this.request(url, { method: 'POST', data });
    }

    async put(url, data = {}) {
        return this.request(url, { method: 'PUT', data });
    }

    async delete(url) {
        return this.request(url, { method: 'DELETE' });
    }
}

// =============================================================================
// Instancia global de cliente API
// =============================================================================

const api = new ApiClient();

// =============================================================================
// Funciones específicas de autenticación
// =============================================================================

const AuthAPI = {
    /**
     * Iniciar sesión
     * @param {string} email - Correo electrónico
     * @param {string} password - Contraseña
     * @returns {Promise<Object>} - Datos del usuario y sesión
     */
    async login(email, password) {
        try {
            const response = await api.post(API_CONFIG.endpoints.auth.login, {
                email: email.trim().toLowerCase(),
                password: password
            });

            if (response.success && response.data) {
                // Guardar información de sesión si es necesario
                this.saveSessionInfo(response.data);
                
                return response;
            } else {
                throw new Error(response.message || 'Error en login');
            }
        } catch (error) {
            console.error('[AuthAPI] Login failed:', error);
            throw this.formatAuthError(error);
        }
    },

    /**
     * Cerrar sesión
     * @returns {Promise<Object>} - Confirmación de logout
     */
    async logout() {
        try {
            const response = await api.post(API_CONFIG.endpoints.auth.logout);
            
            // Limpiar información local
            this.clearSessionInfo();
            
            return response;
        } catch (error) {
            console.error('[AuthAPI] Logout failed:', error);
            // Limpiar información local incluso si falla
            this.clearSessionInfo();
            throw error;
        }
    },

    /**
     * Validar sesión actual
     * @returns {Promise<Object>} - Estado de la sesión
     */
    async validateSession() {
        try {
            return await api.get(API_CONFIG.endpoints.auth.validate);
        } catch (error) {
            console.error('[AuthAPI] Session validation failed:', error);
            throw error;
        }
    },

    /**
     * Guardar información de sesión
     * @param {Object} sessionData - Datos de la sesión
     */
    saveSessionInfo(sessionData) {
        if (sessionData.user) {
            sessionStorage.setItem('user_info', JSON.stringify(sessionData.user));
        }
        if (sessionData.session) {
            sessionStorage.setItem('session_info', JSON.stringify(sessionData.session));
        }
    },

    /**
     * Limpiar información de sesión
     */
    clearSessionInfo() {
        sessionStorage.removeItem('user_info');
        sessionStorage.removeItem('session_info');
        localStorage.removeItem('rememberedEmail'); // Solo si el usuario no marcó "recordar"
    },

    /**
     * Obtener información del usuario actual
     * @returns {Object|null} - Datos del usuario
     */
    getCurrentUser() {
        try {
            const userInfo = sessionStorage.getItem('user_info');
            return userInfo ? JSON.parse(userInfo) : null;
        } catch (error) {
            console.error('[AuthAPI] Error getting current user:', error);
            return null;
        }
    },

    /**
     * Formatear errores de autenticación
     * @param {Error} error - Error original
     * @returns {Error} - Error formateado
     */
    formatAuthError(error) {
        if (error.data) {
            const data = error.data;
            
            // Manejar errores específicos
            switch (data.error) {
                case 'ACCOUNT_BLOCKED':
                    error.message = this.formatBlockedAccountMessage(data);
                    break;
                case 'AUTH_ERROR':
                    error.message = data.message || 'Credenciales inválidas';
                    break;
                case 'VALIDATION_ERROR':
                    error.message = this.formatValidationErrors(data.details);
                    break;
                default:
                    error.message = data.message || 'Error de autenticación';
            }
        }
        
        return error;
    },

    /**
     * Formatear mensaje de cuenta bloqueada
     * @param {Object} data - Datos del error
     * @returns {string} - Mensaje formateado
     */
    formatBlockedAccountMessage(data) {
        const details = data.details || {};
        let message = data.message || 'Cuenta bloqueada por múltiples intentos fallidos.';
        
        if (details.retry_after_minutes) {
            message += `\n\nIntenta de nuevo en ${details.retry_after_minutes} minutos.`;
        }
        
        if (details.blocked_until) {
            const blockedUntil = new Date(details.blocked_until);
            message += `\nBloqueado hasta: ${blockedUntil.toLocaleString('es-ES')}`;
        }
        
        return message;
    },

    /**
     * Formatear errores de validación
     * @param {Object} details - Detalles del error
     * @returns {string} - Mensaje formateado
     */
    formatValidationErrors(details) {
        if (details && details.validation_errors) {
            const errors = details.validation_errors;
            if (errors.missing_fields) {
                return `Campos requeridos: ${errors.missing_fields.join(', ')}`;
            }
            if (typeof errors === 'object') {
                return Object.entries(errors)
                    .map(([field, message]) => `${field}: ${message}`)
                    .join('\n');
            }
            return errors.message || 'Error de validación';
        }
        return 'Error de validación';
    }
};

// =============================================================================
// Funciones específicas de estudiantes
// =============================================================================

const StudentsAPI = {
    /**
     * Obtener lista de estudiantes
     * @param {Object} params - Parámetros de consulta (page, limit, search, etc.)
     * @returns {Promise<Object>} - Lista de estudiantes con paginación
     */
    async getList(params = {}) {
        try {
            return await api.get(API_CONFIG.endpoints.students.list, params);
        } catch (error) {
            console.error('[StudentsAPI] Get list failed:', error);
            throw error;
        }
    },

    /**
     * Obtener un estudiante específico
     * @param {string} cedula - Cédula del estudiante
     * @returns {Promise<Object>} - Datos del estudiante
     */
    async getById(cedula) {
        try {
            return await api.get(API_CONFIG.endpoints.students.show, { id: cedula });
        } catch (error) {
            console.error('[StudentsAPI] Get by ID failed:', error);
            throw error;
        }
    },

    /**
     * Crear nuevo estudiante
     * @param {Object} studentData - Datos del estudiante
     * @returns {Promise<Object>} - Estudiante creado
     */
    async create(studentData) {
        try {
            return await api.post(API_CONFIG.endpoints.students.create, studentData);
        } catch (error) {
            console.error('[StudentsAPI] Create failed:', error);
            throw error;
        }
    },

    /**
     * Actualizar estudiante existente
     * @param {string} cedula - Cédula del estudiante
     * @param {Object} studentData - Datos actualizados
     * @returns {Promise<Object>} - Estudiante actualizado
     */
    async update(cedula, studentData) {
        try {
            return await api.put(API_CONFIG.endpoints.students.update, {
                cedula,
                ...studentData
            });
        } catch (error) {
            console.error('[StudentsAPI] Update failed:', error);
            throw error;
        }
    },

    /**
     * Eliminar estudiante
     * @param {string} cedula - Cédula del estudiante
     * @returns {Promise<Object>} - Confirmación de eliminación
     */
    async delete(cedula) {
        try {
            return await api.delete(`${API_CONFIG.endpoints.students.delete}?id=${cedula}`);
        } catch (error) {
            console.error('[StudentsAPI] Delete failed:', error);
            throw error;
        }
    }
};

// =============================================================================
// Utilidades de la API
// =============================================================================

const ApiUtils = {
    /**
     * Verificar si hay conexión a internet
     * @returns {boolean} - Estado de la conexión
     */
    isOnline() {
        return navigator.onLine;
    },

    /**
     * Formatear fecha/hora para mostrar
     * @param {string} dateString - Fecha en formato string
     * @returns {string} - Fecha formateada
     */
    formatDateTime(dateString) {
        try {
            const date = new Date(dateString);
            return date.toLocaleString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('[ApiUtils] Date format error:', error);
            return dateString;
        }
    },

    /**
     * Validar formato de email
     * @param {string} email - Email a validar
     * @returns {boolean} - Si es válido
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    /**
     * Validar cédula ecuatoriana
     * @param {string} cedula - Cédula a validar
     * @returns {boolean} - Si es válida
     */
    isValidCedula(cedula) {
        if (!cedula || cedula.length !== 10) return false;
        
        // Algoritmo de validación de cédula ecuatoriana
        const digits = cedula.split('').map(Number);
        const province = parseInt(cedula.substring(0, 2));
        
        if (province < 1 || province > 24) return false;
        
        const coefficients = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let sum = 0;
        
        for (let i = 0; i < 9; i++) {
            let product = digits[i] * coefficients[i];
            if (product > 9) product -= 9;
            sum += product;
        }
        
        const checkDigit = sum % 10 === 0 ? 0 : 10 - (sum % 10);
        return checkDigit === digits[9];
    },

    /**
     * Debounce function para optimizar búsquedas
     * @param {Function} func - Función a debounce
     * @param {number} wait - Tiempo de espera en ms
     * @returns {Function} - Función debounced
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Escapar HTML para prevenir XSS
     * @param {string} text - Texto a escapar
     * @returns {string} - Texto escapado
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// =============================================================================
// Manejo de errores global
// =============================================================================

window.addEventListener('unhandledrejection', function(event) {
    console.error('[API] Unhandled promise rejection:', event.reason);
    
    // Mostrar mensaje de error si hay una función global para ello
    if (typeof window.showGlobalError === 'function') {
        window.showGlobalError('Error de conexión. Por favor, intenta de nuevo.');
    }
});

// Detectar cambios en la conexión
window.addEventListener('online', function() {
    console.log('[API] Connection restored');
    if (typeof window.showGlobalMessage === 'function') {
        window.showGlobalMessage('Conexión restaurada', 'success');
    }
});

window.addEventListener('offline', function() {
    console.log('[API] Connection lost');
    if (typeof window.showGlobalError === 'function') {
        window.showGlobalError('Sin conexión a internet');
    }
});

// =============================================================================
// Exportar funciones para uso global
// =============================================================================

// Hacer disponibles globalmente
window.api = api;
window.AuthAPI = AuthAPI;
window.StudentsAPI = StudentsAPI;
window.ApiUtils = ApiUtils;

// Para compatibilidad con módulos ES6 (si se usa en el futuro)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        api,
        AuthAPI,
        StudentsAPI,
        ApiUtils,
        API_CONFIG
    };
}

console.log('[API] API client initialized successfully');