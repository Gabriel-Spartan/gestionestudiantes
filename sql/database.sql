-- sql/database.sql - Script SQL optimizado para hosting gratuito
-- Compatible con XAMPP (desarrollo) e InfinityFree (producción)
-- Sin eventos automáticos, triggers complejos ni procedimientos almacenados

CREATE DATABASE IF NOT EXISTS gestion_estudiantes CHARACTER SET utf8 COLLATE utf8_spanish_ci;
USE gestion_estudiantes;

-- ============================================================================
-- TABLA USUARIOS
-- ============================================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasenia VARCHAR(255) NOT NULL,
    tipo ENUM('ADMIN', 'SECRETARIA') DEFAULT 'SECRETARIA',
    intentos_fallidos INT DEFAULT 0,
    cuenta_bloqueada TINYINT(1) DEFAULT 0,
    fecha_bloqueo TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    sesion_activa VARCHAR(255) NULL,
    ip_ultimo_acceso VARCHAR(45),
    estado ENUM('ACTIVO', 'INACTIVO', 'BLOQUEADO') DEFAULT 'ACTIVO',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================================
-- TABLA ESTUDIANTES
-- ============================================================================
CREATE TABLE estudiantes (
    cedula VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    usuario_creador_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_creador_id) REFERENCES usuarios(id)
);

-- ============================================================================
-- TABLA AUDITORÍA DE USUARIOS
-- ============================================================================
CREATE TABLE auditoria_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    correo_intento VARCHAR(100),
    accion ENUM('LOGIN_EXITOSO', 'LOGIN_FALLIDO', 'LOGOUT', 'CUENTA_BLOQUEADA', 'CUENTA_DESBLOQUEADA', 'SESION_EXPIRADA', 'ACCESO_DENEGADO') NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================================
-- TABLA SESIONES ACTIVAS (SIMPLIFICADA - COMPATIBLE CON MySQL 5.5+)
-- ============================================================================
CREATE TABLE sesiones_activas (
    id VARCHAR(255) PRIMARY KEY,
    usuario_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad TIMESTAMP NULL,
    fecha_expiracion TIMESTAMP NULL,
    estado ENUM('ACTIVA', 'EXPIRADA') DEFAULT 'ACTIVA',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================================
-- TABLA CONFIGURACIÓN DE SEGURIDAD (SIMPLIFICADA)
-- ============================================================================
CREATE TABLE configuracion_seguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor VARCHAR(500) NOT NULL,
    descripcion TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================================
-- INSERTAR CONFIGURACIONES DE SEGURIDAD POR DEFECTO
-- ============================================================================
INSERT INTO configuracion_seguridad (clave, valor, descripcion) VALUES 
('max_intentos_login', '5', 'Máximo número de intentos de login fallidos antes de bloquear cuenta'),
('tiempo_bloqueo_minutos', '30', 'Tiempo en minutos que permanece bloqueada una cuenta'),
('timeout_sesion_minutos', '30', 'Tiempo en minutos antes de que expire una sesión por inactividad'),
('max_sesiones_simultaneas', '3', 'Máximo número de sesiones simultáneas por usuario'),
('longitud_minima_password', '8', 'Longitud mínima requerida para contraseñas');

-- ============================================================================
-- INSERTAR USUARIOS INICIALES
-- ============================================================================
-- NOTA: Las contraseñas serán hasheadas por PHP con password_hash()
-- Por ahora insertamos hashes temporales que se actualizarán

INSERT INTO usuarios (nombre, correo, contrasenia, tipo, estado) VALUES 
('Administrador General', 'admin@gestion.com', 'temp_admin_hash', 'ADMIN', 'ACTIVO'),
('Secretaria Principal', 'secretaria@gestion.com', 'temp_secretaria_hash', 'SECRETARIA', 'ACTIVO');

-- ============================================================================
-- INSERTAR ESTUDIANTES DE EJEMPLO
-- ============================================================================
INSERT INTO estudiantes (cedula, nombre, apellido, direccion, telefono, usuario_creador_id) VALUES 
('1234567890', 'Juan Carlos', 'Pérez López', 'Av. Principal 123, Ambato', '0987654321', 1),
('0987654321', 'María Elena', 'González Morales', 'Calle Secundaria 456, Ambato', '0912345678', 2),
('1357924680', 'Carlos Alberto', 'Rodríguez Silva', 'Barrio Central 789, Ambato', '0923456789', 1),
('2468013579', 'Ana Sofía', 'Martínez Torres', 'Sector Norte 321, Ambato', '0934567890', 2);

-- ============================================================================
-- ACTUALIZAR CONTRASEÑAS A BCRYPT (EJECUTAR DESPUÉS DE CREAR ARCHIVOS PHP)
-- ============================================================================
-- Estas consultas se ejecutarán desde PHP una vez que tengas password_hash()

-- UPDATE usuarios SET contrasenia = '$2y$10$hash_real_admin' WHERE correo = 'admin@gestion.com';
-- UPDATE usuarios SET contrasenia = '$2y$10$hash_real_secretaria' WHERE correo = 'secretaria@gestion.com';

-- ============================================================================
-- COMENTARIOS SOBRE LIMITACIONES DE HOSTING GRATUITO
-- ============================================================================

/*
LIMITACIONES DE INFINITYFREE Y HOSTING GRATUITO:
1. ❌ NO eventos automáticos (EVENT SCHEDULER)
2. ❌ NO procedimientos almacenados complejos
3. ❌ NO triggers complejos
4. ❌ NO permisos avanzados de usuario
5. ❌ NO configuración de variables globales
6. ❌ NO jobs o tareas programadas en MySQL
7. ❌ NO múltiples bases de datos
8. ❌ NO índices fulltext complejos

SOLUCIONES IMPLEMENTADAS EN PHP:
✅ Limpieza de sesiones expiradas -> Cron job en PHP
✅ Desbloqueo automático de cuentas -> Función PHP en login
✅ Validación de políticas de seguridad -> Clases PHP
✅ Auditoría completa -> Funciones PHP de logging
✅ Manejo de timeouts -> JavaScript + AJAX
✅ Notificaciones -> Email con PHPMailer
*/

-- ============================================================================
-- ÍNDICES BÁSICOS PARA MEJOR RENDIMIENTO
-- ============================================================================
ALTER TABLE usuarios ADD INDEX idx_correo (correo);
ALTER TABLE usuarios ADD INDEX idx_sesion_activa (sesion_activa);
ALTER TABLE usuarios ADD INDEX idx_estado (estado);

ALTER TABLE estudiantes ADD INDEX idx_nombre (nombre);
ALTER TABLE estudiantes ADD INDEX idx_apellido (apellido);
ALTER TABLE estudiantes ADD INDEX idx_usuario_creador (usuario_creador_id);

ALTER TABLE auditoria_usuarios ADD INDEX idx_usuario_id (usuario_id);
ALTER TABLE auditoria_usuarios ADD INDEX idx_fecha_hora (fecha_hora);
ALTER TABLE auditoria_usuarios ADD INDEX idx_accion (accion);

ALTER TABLE sesiones_activas ADD INDEX idx_usuario_id (usuario_id);
ALTER TABLE sesiones_activas ADD INDEX idx_estado (estado);
ALTER TABLE sesiones_activas ADD INDEX idx_fecha_expiracion (fecha_expiracion);

-- ============================================================================
-- SCRIPT FINALIZADO
-- ============================================================================

/*
CONTRASEÑAS INICIALES:
- admin@gestion.com: admin123
- secretaria@gestion.com: secretaria123

PRÓXIMOS PASOS:
1. Ejecutar este script en phpMyAdmin
2. Crear archivos PHP de seguridad
3. Ejecutar script PHP para actualizar contraseñas a bcrypt
4. Implementar funciones de limpieza automática en PHP
5. Configurar sistema de auditoría en PHP
*/