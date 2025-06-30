<?php
require_once __DIR__ . '/../api/config/database.php';

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errores = [];
$exito = false;

// Variables para conservar los valores del formulario
$cedula = $_GET['cedula'] ?? '';
$nombre = '';
$apellido = '';
$direccion = '';
$telefono = '';

// Función para validar nombre y apellido (solo letras y espacios)
function validarNombreApellido($texto) {
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $texto);
}

// Función para validar teléfono ecuatoriano (teléfono completo con 09)
function validarTelefonoEcuatoriano($telefono) {
    // Debe empezar con 09 y tener exactamente 10 dígitos
    return preg_match('/^09\d{8}$/', $telefono);
}

// Verificar que se proporcionó la cédula
if ($cedula === '') {
    // Incluir archivos de UI para mostrar error
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/nav.php';
    
    echo '<div class="container mt-5">
            <div class="alert alert-danger">
                <h4>❌ Error</h4>
                <p>No se especificó la cédula del estudiante a editar.</p>
                <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
            </div>
          </div>';
    
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Obtener datos actuales del estudiante
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM estudiantes WHERE cedula = ?');
    $stmt->execute([$cedula]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        // Incluir archivos de UI para mostrar error
        include_once __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../includes/nav.php';
        
        echo '<div class="container mt-5">
                <div class="alert alert-warning">
                    <h4>⚠️ Estudiante No Encontrado</h4>
                    <p>No se encontró un estudiante con la cédula: <strong>' . htmlspecialchars($cedula) . '</strong></p>
                    <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
                </div>
              </div>';
        
        include_once __DIR__ . '/../includes/footer.php';
        exit();
    }
} catch (Exception $e) {
    // Incluir archivos de UI para mostrar error
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/nav.php';
    
    echo '<div class="container mt-5">
            <div class="alert alert-danger">
                <h4>❌ Error de Base de Datos</h4>
                <p>Error al obtener datos del estudiante:</p>
                <div class="bg-light p-3 rounded mt-2">
                    <code>' . htmlspecialchars($e->getMessage()) . '</code>
                </div>
                <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
            </div>
          </div>';
    
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y conservar los valores del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    // Validación básica
    if ($nombre === '' || $apellido === '' || $direccion === '' || $telefono === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    
    // Validaciones específicas
    if ($nombre !== '' && !validarNombreApellido($nombre)) {
        $errores[] = 'El nombre solo debe contener letras y espacios, sin números ni símbolos.';
    }
    
    if ($apellido !== '' && !validarNombreApellido($apellido)) {
        $errores[] = 'El apellido solo debe contener letras y espacios, sin números ni símbolos.';
    }
    
    if ($telefono !== '' && !validarTelefonoEcuatoriano($telefono)) {
        $errores[] = 'El teléfono debe empezar con 09 y tener exactamente 10 dígitos.';
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare('UPDATE estudiantes SET nombre=?, apellido=?, direccion=?, telefono=? WHERE cedula=?');
            $result = $stmt->execute([$nombre, $apellido, $direccion, $telefono, $cedula]);
            
            if ($result) {
                // Redirigir al dashboard con mensaje de éxito
                $studentName = $nombre . ' ' . $apellido;
                header('Location: ../dashboard.php?success=student_updated&name=' . urlencode($studentName) . '&cedula=' . urlencode($cedula));
                exit();
            } else {
                $errores[] = 'No se pudieron guardar los cambios. Intenta nuevamente.';
            }
        } catch (PDOException $e) {
            $errores[] = 'Error al actualizar: ' . htmlspecialchars($e->getMessage());
        }
    }
} else {
    // Cargar valores por defecto del estudiante (primera carga)
    $nombre = $estudiante['nombre'];
    $apellido = $estudiante['apellido'];
    $direccion = $estudiante['direccion'];
    $telefono = $estudiante['telefono'];
}

// Incluir archivos de UI solo después de procesar el formulario
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/nav.php';

// Verificar acceso después de incluir la navegación
if (!isset($_SESSION['user_id'])) {
    echo '<div class="container mt-5">
            <div class="alert alert-danger">
                <h4>Acceso Denegado</h4>
                <p>Debes iniciar sesión para acceder a esta página.</p>
                <a href="../login.php" class="btn btn-primary">Ir al Login</a>
            </div>
          </div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}

if (!in_array($_SESSION['user_type'] ?? '', ['ADMIN', 'SECRETARIA'])) {
    echo '<div class="container mt-5">
            <div class="alert alert-warning">
                <h4>Permisos Insuficientes</h4>
                <p>No tienes permisos para editar estudiantes.</p>
                <a href="../dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
            </div>
          </div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}
?>

<div class="container mt-5">
    <h3>📝 Editar Estudiante</h3>
    
    <!-- DEBUG INFO (remover en producción) -->
    <?php if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false): ?>
    <div class="alert alert-info">
        <h5>🔍 DEBUG INFO (Solo desarrollo)</h5>
        <strong>Datos del estudiante:</strong><br>
        - Cédula: <?php echo htmlspecialchars($cedula); ?><br>
        - Nombre original: <?php echo htmlspecialchars($estudiante['nombre'] ?? 'N/A'); ?><br>
        - Método: <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
        - Usuario: <?php echo $_SESSION['user_name'] ?? 'NO LOGUEADO'; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errores as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" id="formEditarEstudiante">
        <div class="mb-3">
            <label class="form-label">Cédula <span class="text-muted">(No editable)</span></label>
            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($cedula); ?>" disabled>
            <div class="form-text">La cédula no se puede modificar después de crear el estudiante</div>
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?php echo (strpos(implode(' ', $errores), 'nombre') !== false) ? 'is-invalid' : ''; ?>" 
                   id="nombre" 
                   name="nombre" 
                   value="<?php echo htmlspecialchars($nombre); ?>" 
                   required
                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                   title="Solo se permiten letras y espacios">
            <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
        </div>

        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?php echo (strpos(implode(' ', $errores), 'apellido') !== false) ? 'is-invalid' : ''; ?>" 
                   id="apellido" 
                   name="apellido" 
                   value="<?php echo htmlspecialchars($apellido); ?>" 
                   required
                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                   title="Solo se permiten letras y espacios">
            <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
        </div>

        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="direccion" 
                   name="direccion" 
                   value="<?php echo htmlspecialchars($direccion); ?>" 
                   required>
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?php echo (strpos(implode(' ', $errores), 'teléfono') !== false || strpos(implode(' ', $errores), 'telefono') !== false) ? 'is-invalid' : ''; ?>" 
                   id="telefono" 
                   name="telefono" 
                   value="<?php echo htmlspecialchars($telefono); ?>" 
                   required
                   pattern="09\d{8}" 
                   maxlength="10"
                   title="Debe empezar con 09 y tener 10 dígitos en total">
            <div class="form-text">Debe empezar con 09 y tener exactamente 10 dígitos</div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
            <button type="submit" class="btn btn-success">
                💾 Guardar Cambios
            </button>
            <a href="../dashboard.php" class="btn btn-secondary">
                ↩️ Volver al Dashboard
            </a>
        </div>
    </form>
</div>

<style>
.is-invalid {
    border-color: #dc3545;
}

.text-danger {
    color: #dc3545 !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditarEstudiante');
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const telefonoInput = document.getElementById('telefono');
    
    // Función para mostrar feedback visual
    function showFieldFeedback(input, isValid, message = '') {
        const feedbackDiv = input.parentNode.querySelector('.invalid-feedback');
        
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (feedbackDiv) feedbackDiv.remove();
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            
            if (message && !feedbackDiv) {
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = message;
                input.parentNode.appendChild(div);
            }
        }
    }
    
    // Validación de nombre en tiempo real
    nombreInput.addEventListener('input', function() {
        const valorAnterior = this.value;
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        
        if (valorAnterior !== this.value) {
            showFieldFeedback(this, false, 'Solo se permiten letras y espacios');
            setTimeout(() => {
                if (this.value.length > 0) {
                    showFieldFeedback(this, true);
                }
            }, 1500);
        } else if (this.value.length > 0) {
            showFieldFeedback(this, true);
        }
    });
    
    // Validación de apellido en tiempo real
    apellidoInput.addEventListener('input', function() {
        const valorAnterior = this.value;
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        
        if (valorAnterior !== this.value) {
            showFieldFeedback(this, false, 'Solo se permiten letras y espacios');
            setTimeout(() => {
                if (this.value.length > 0) {
                    showFieldFeedback(this, true);
                }
            }, 1500);
        } else if (this.value.length > 0) {
            showFieldFeedback(this, true);
        }
    });
    
    // Validación de teléfono en tiempo real
    telefonoInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, ''); // Solo números
        if (valor.length > 10) valor = valor.slice(0, 10);
        if (valor.length > 0 && !valor.startsWith('09')) {
            valor = '09' + valor.replace(/^09/, '');
        }
        this.value = valor;
        
        // Validación visual
        if (valor.length === 10 && valor.startsWith('09')) {
            showFieldFeedback(this, true);
        } else if (valor.length > 0) {
            showFieldFeedback(this, false, 'Debe empezar con 09 y tener 10 dígitos');
        }
    });
    
    // Validación antes de enviar
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar campos vacíos
        [nombreInput, apellidoInput, document.getElementById('direccion'), telefonoInput].forEach(input => {
            if (!input.value.trim()) {
                showFieldFeedback(input, false, 'Este campo es obligatorio');
                isValid = false;
            }
        });
        
        // Validar teléfono específicamente
        if (telefonoInput.value.length !== 10 || !telefonoInput.value.startsWith('09')) {
            showFieldFeedback(telefonoInput, false, 'El teléfono debe empezar con 09 y tener 10 dígitos');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll al primer campo con error
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>