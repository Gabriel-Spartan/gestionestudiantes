<?php
require_once __DIR__ . '/../api/config/database.php';

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug de sesión (remover en producción)
error_log("DEBUG - Contenido de sesión: " . print_r($_SESSION, true));
error_log("DEBUG - User ID: " . ($_SESSION['user_id'] ?? 'NO EXISTE'));
error_log("DEBUG - User type: " . ($_SESSION['user_type'] ?? 'NO EXISTE'));

$errores = [];
$exito = false;

// Variables para conservar los valores del formulario
$cedula = '';
$nombre = '';
$apellido = '';
$direccion = '';
$telefono = '';

// Función para validar cédula ecuatoriana
function validarCedulaEcuatoriana($cedula) {
    // Verificar que tenga exactamente 10 dígitos
    if (!preg_match('/^\d{10}$/', $cedula)) {
        return false;
    }
    
    // Obtener los dígitos
    $digitos = array_map('intval', str_split($cedula));
    
    // Los dos primeros dígitos deben corresponder a una provincia válida (01-24)
    $provincia = intval(substr($cedula, 0, 2));
    if ($provincia < 1 || $provincia > 24) {
        return false;
    }
    
    // El tercer dígito debe ser menor a 6 (para personas naturales)
    if ($digitos[2] >= 6) {
        return false;
    }
    
    // Algoritmo de validación del dígito verificador
    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;
    
    for ($i = 0; $i < 9; $i++) {
        $producto = $digitos[$i] * $coeficientes[$i];
        if ($producto > 9) {
            $producto -= 9;
        }
        $suma += $producto;
    }
    
    $digitoVerificador = (10 - ($suma % 10)) % 10;
    
    return $digitoVerificador === $digitos[9];
}

// Función para validar nombre y apellido (solo letras y espacios)
function validarNombreApellido($texto) {
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $texto);
}

// Función para validar teléfono ecuatoriano (solo los 8 dígitos después de 09)
function validarTelefonoEcuatoriano($telefono) {
    // El teléfono completo debe empezar con 09 y tener exactamente 10 dígitos
    $telefonoCompleto = '09' . $telefono;
    return preg_match('/^09\d{8}$/', $telefonoCompleto);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y conservar los valores del formulario
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? ''); // Solo los 8 dígitos
    
    // Debug del usuario creador
    $usuario_creador_id = $_SESSION['user_id'] ?? null;
    error_log("DEBUG - Usuario creador ID capturado: " . ($usuario_creador_id ?? 'NULL'));
    
    // Verificar si el usuario está logueado y tiene permisos
    if (!$usuario_creador_id) {
        error_log("ERROR - No hay user_id en la sesión");
        $errores[] = 'Sesión expirada. Por favor, inicia sesión nuevamente.';
    } else {
        // Verificar que el usuario existe y tiene permisos para crear estudiantes
        try {
            $pdo = getConnection();
            error_log("DEBUG - Conexión PDO obtenida correctamente");
            
            $stmt = $pdo->prepare('SELECT id, nombre, tipo FROM usuarios WHERE id = ? AND estado = 1');
            error_log("DEBUG - Query preparada: SELECT id, nombre, tipo FROM usuarios WHERE id = ? AND estado = 1");
            error_log("DEBUG - Parámetro user_id: " . $usuario_creador_id);
            
            $stmt->execute([$usuario_creador_id]);
            error_log("DEBUG - Query ejecutada");
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("DEBUG - Resultado de fetch: " . print_r($usuario, true));
            
            if (!$usuario) {
                error_log("ERROR - Usuario no encontrado o inactivo: " . $usuario_creador_id);
                $errores[] = 'Usuario no válido o cuenta desactivada.';
                $usuario_creador_id = null;
            } else {
                error_log("DEBUG - Usuario válido encontrado: " . $usuario['nombre'] . " (" . $usuario['tipo'] . ")");
                
                // Verificar permisos de tipo de usuario
                if (!in_array($usuario['tipo'], ['ADMIN', 'SECRETARIA'])) {
                    error_log("ERROR - Usuario sin permisos suficientes: " . $usuario['tipo']);
                    $errores[] = 'No tienes permisos para crear estudiantes. Tu tipo: ' . $usuario['tipo'];
                    $usuario_creador_id = null;
                }
            }
        } catch (PDOException $e) {
            error_log("ERROR PDO - Error al verificar usuario: " . $e->getMessage());
            error_log("ERROR PDO - Código de error: " . $e->getCode());
            error_log("ERROR PDO - Información adicional: " . print_r($e->errorInfo, true));
            $errores[] = 'Error de base de datos: ' . $e->getMessage();
            $usuario_creador_id = null;
        } catch (Exception $e) {
            error_log("ERROR GENERAL - Error al verificar usuario: " . $e->getMessage());
            $errores[] = 'Error general: ' . $e->getMessage();
            $usuario_creador_id = null;
        }
    }
    
    // Validación básica
    if ($cedula === '' || $nombre === '' || $apellido === '' || $direccion === '' || $telefono === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    
    // Validaciones específicas
    if ($cedula !== '' && !validarCedulaEcuatoriana($cedula)) {
        $errores[] = 'La cédula ingresada no es válida. Debe ser una cédula ecuatoriana de 10 dígitos.';
    }
    
    if ($nombre !== '' && !validarNombreApellido($nombre)) {
        $errores[] = 'El nombre solo debe contener letras y espacios, sin números ni símbolos.';
    }
    
    if ($apellido !== '' && !validarNombreApellido($apellido)) {
        $errores[] = 'El apellido solo debe contener letras y espacios, sin números ni símbolos.';
    }
    
    if ($telefono !== '' && !validarTelefonoEcuatoriano($telefono)) {
        $errores[] = 'El teléfono debe tener exactamente 8 dígitos después del 09.';
    }
    
    if ($telefono !== '' && !preg_match('/^\d{8}$/', $telefono)) {
        $errores[] = 'El teléfono debe contener solo 8 dígitos numericos.';
    }
    
    // La validación del usuario ya se hizo arriba, no repetir aquí

    if (empty($errores)) {
        try {
            $pdo = getConnection();
            // Concatenar 09 con los 8 dígitos para guardar en BD
            $telefonoCompleto = '09' . $telefono;
            $stmt = $pdo->prepare('INSERT INTO estudiantes (cedula, nombre, apellido, direccion, telefono, usuario_creador_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$cedula, $nombre, $apellido, $direccion, $telefonoCompleto, $usuario_creador_id]);
            
            // Redirigir al dashboard después de guardar exitosamente
            header('Location: ../dashboard.php?success=student_created&name=' . urlencode($nombre . ' ' . $apellido));
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errores[] = 'La cédula ya está registrada.';
            } else {
                $errores[] = 'Error al guardar: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
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
                <p>No tienes permisos para crear estudiantes.</p>
                <p><strong>Tu tipo de usuario:</strong> ' . ($_SESSION['user_type'] ?? 'No definido') . '</p>
                <a href="../dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
            </div>
          </div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}
?>

<div class="container mt-5">
    <h3>Agregar Estudiante</h3>
    
    <!-- DEBUG INFO (remover en producción) -->
    <?php if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false): ?>
    <div class="alert alert-info">
        <h5>🔍 DEBUG INFO (Solo desarrollo)</h5>
        <strong>Sesión actual:</strong><br>
        - User ID: <?php echo $_SESSION['user_id'] ?? 'NO EXISTE'; ?><br>
        - User Name: <?php echo $_SESSION['user_name'] ?? 'NO EXISTE'; ?><br>
        - User Type: <?php echo $_SESSION['user_type'] ?? 'NO EXISTE'; ?><br>
        - User Email: <?php echo $_SESSION['user_email'] ?? 'NO EXISTE'; ?><br>
        <br>
        <strong>Estado de formulario:</strong><br>
        - Método: <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
        - Usuario creador capturado: <?php echo $usuario_creador_id ?? 'NULL'; ?><br>
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

    <?php if ($exito): ?>
        <div class="alert alert-success">
            ✅ Estudiante guardado exitosamente.
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" id="formEstudiante">
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control <?php echo (in_array('cédula', array_map('strtolower', $errores)) || strpos(implode(' ', $errores), 'cédula') !== false) ? 'is-invalid' : ''; ?>" 
                   id="cedula" 
                   name="cedula" 
                   value="<?php echo htmlspecialchars($cedula); ?>"
                   required 
                   pattern="\d{10}" 
                   maxlength="10" 
                   title="La cédula debe tener exactamente 10 dígitos">
            <div class="form-text">Ingrese una cédula ecuatoriana válida de 10 dígitos</div>
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
            <div class="input-group">
                <span class="input-group-text bg-secondary text-white">🇪🇨 +593 09</span>
                <input type="text" 
                       class="form-control <?php echo (strpos(implode(' ', $errores), 'teléfono') !== false || strpos(implode(' ', $errores), 'telefono') !== false) ? 'is-invalid' : ''; ?>" 
                       id="telefono" 
                       name="telefono" 
                       value="<?php echo htmlspecialchars($telefono); ?>"
                       required
                       pattern="\d{8}" 
                       maxlength="8"
                       placeholder="12345678"
                       title="Ingrese solo los 8 dígitos después del 09">
            </div>
            <div class="form-text">Ingrese solo los 8 dígitos después del 09 (ejemplo: 12345678)</div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
            <button type="submit" class="btn btn-success">
                💾 Guardar Estudiante
            </button>
            <a href="../dashboard.php" class="btn btn-secondary">
                ↩️ Cancelar
            </a>
        </div>
    </form>
</div>

<style>
.is-invalid {
    border-color: #dc3545;
}

.input-group-text {
    font-weight: bold;
    min-width: 120px;
}

.text-danger {
    color: #dc3545 !important;
}

.alert ul {
    padding-left: 1.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEstudiante');
    const cedulaInput = document.getElementById('cedula');
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
    
    // Validación de cédula en tiempo real
    cedulaInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, ''); // Solo números
        if (valor.length > 10) valor = valor.slice(0, 10);
        this.value = valor;
        
        // Validación visual
        if (valor.length === 10) {
            showFieldFeedback(this, true);
        } else if (valor.length > 0) {
            showFieldFeedback(this, false, 'La cédula debe tener 10 dígitos');
        }
    });
    
    // Validación de nombre en tiempo real
    nombreInput.addEventListener('input', function() {
        const valorAnterior = this.value;
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        
        // Si se removieron caracteres, mostrar feedback
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
        
        // Si se removieron caracteres, mostrar feedback
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
    
    // Validación de teléfono en tiempo real (solo 8 dígitos)
    telefonoInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, ''); // Solo números
        if (valor.length > 8) valor = valor.slice(0, 8);
        this.value = valor;
        
        // Validación visual
        if (valor.length === 8) {
            showFieldFeedback(this, true);
        } else if (valor.length > 0) {
            showFieldFeedback(this, false, 'Debe tener exactamente 8 dígitos');
        }
    });
    
    // Validación antes de enviar
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar cédula
        if (cedulaInput.value.length !== 10) {
            showFieldFeedback(cedulaInput, false, 'La cédula debe tener 10 dígitos');
            isValid = false;
        }
        
        // Validar teléfono
        if (telefonoInput.value.length !== 8) {
            showFieldFeedback(telefonoInput, false, 'El teléfono debe tener 8 dígitos');
            isValid = false;
        }
        
        // Validar campos vacíos
        [cedulaInput, nombreInput, apellidoInput, document.getElementById('direccion'), telefonoInput].forEach(input => {
            if (!input.value.trim()) {
                showFieldFeedback(input, false, 'Este campo es obligatorio');
                isValid = false;
            }
        });
        
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
    
    // Auto-focus en primer campo vacío
    const campos = [cedulaInput, nombreInput, apellidoInput, document.getElementById('direccion'), telefonoInput];
    const primerVacio = campos.find(campo => !campo.value.trim());
    if (primerVacio) {
        primerVacio.focus();
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>