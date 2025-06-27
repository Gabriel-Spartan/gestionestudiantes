# 📋 Sistema de Gestión de Estudiantes

Sistema web desarrollado en PHP puro para la gestión integral de estudiantes con control de usuarios y seguridad.

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Servidor** Local: XAMPP
- **Hosting:** InfinityFree (compatible con hosting gratuito)

## Características
- Sistema de login con usuarios quemados
- CRUD completo de estudiantes
- Interfaz responsive

## 📁 Estructura del Proyecto
```
gestionestudiantes/
├── index.php                  # Página principal
├── login.php                  # Formulario de login
├── dashboard.php              # Panel de control
├── logout.php                 # Cerrar sesión
├── nosotros.php               # Página nosotros
├── servicios.php              # Servicios (solo usuarios logueados)
├── contactanos.php            # Página de contacto
├── config/
│   ├── database.php          # Configuración de BD
│   └── env_local.php         # Archivo de prueba
├── includes/
│   ├── header.php            # Header común
│   ├── footer.php            # Footer común
│   └── nav.php               # Navegación
├── auth/
│   ├── authenticate.php      # Procesar login
│   └── session_check.php     # Verificar sesión activa
├── students/
│   ├── index.php            # Listar estudiantes
│   ├── create.php           # Crear estudiante
│   ├── edit.php             # Editar estudiante
│   ├── view.php             # Ver detalles
│   └── process.php          # Procesar operaciones CRUD
├── api/                      # Endpoints para testing
│   ├── auth/
│   │   └── login.php        # API login (JSON)
│   └── students/
│       └── index.php        # API estudiantes (JSON)
├── assets/
│   ├── css/
│   │   └── style.css        # Estilos principales
│   ├── js/
│   │   └── main.js          # JavaScript principal
│   └── images/
└── sql/
    └── database.sql         # Script de creación de BD
```

## 🚀 Instalación y Configuración

1. Clonar repositorio desde git bash `git clone https://github.com/Gabriel-Spartan/gestionestudiantes.git`.
2. En Xampp en la interfaz en el apartado de SQL ejecutar el script que se encuentra en `sql/database.sql`.
3. Crear `config/database.php` usando el ejemplo de `config/database_example.txt`.
3. Crear `config/env_local.php` usando el ejemplo de `config/env_local_example.txt`.
4. Ejecutar en servidor local
5. Acceder a: `http://localhost/gestionestudiantes/config/env_local.php` Verificar que muestre:
    ✅ Conexión exitosa
    📋 Listado de tablas
    👥 Usuarios en el sistema
    🎓 Estudiantes de ejemplo.

## 🔧 Configuración de Puertos

### Puerto MySQL por Defecto: 3306

Si tu XAMPP usa puerto personalizado (ej: 3307), modificar en `config/database.php`:
```php
'port' => '3307',  // Cambiar según tu configuración
```

