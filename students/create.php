<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/nav.php';

$errores = [];
$exito = false;

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

// Función para validar teléfono ecuatoriano
function validarTelefonoEcuatoriano($telefono) {
    // Debe empezar con 09 y tener exactamente 10 dígitos
    return preg_match('/^09\d{8}$/', $telefono);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario_creador_id = $_SESSION['user_id'] ?? null;    // Validación básica
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
        $errores[] = 'El teléfono debe empezar con 09 y tener exactamente 10 dígitos.';
    }
    
    if (!$usuario_creador_id) {
        $errores[] = 'No se ha identificado el usuario creador.';
    }

    if (empty($errores)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare('INSERT INTO estudiantes (cedula, nombre, apellido, direccion, telefono, usuario_creador_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$cedula, $nombre, $apellido, $direccion, $telefono, $usuario_creador_id]);
            $exito = true;
            header('Location: student.php');
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
?>
<div class="container mt-5">
    <h3>Agregar Estudiante</h3>
    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <?php foreach ($errores as $err) echo '<div>' . $err . '</div>'; ?>
        </div>
    <?php endif; ?>    <form method="post" autocomplete="off" id="formEstudiante">
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula</label>
            <input type="text" class="form-control" id="cedula" name="cedula" required 
                   pattern="\d{10}" maxlength="10" 
                   title="La cédula debe tener exactamente 10 dígitos">
            <div class="form-text">Ingrese una cédula ecuatoriana válida de 10 dígitos</div>
        </div>
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required
                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                   title="Solo se permiten letras y espacios">
            <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" required
                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                   title="Solo se permiten letras y espacios">
            <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required
                   pattern="09\d{8}" maxlength="10"
                   title="Debe empezar con 09 y tener 10 dígitos en total">
            <div class="form-text">Debe empezar con 09 y tener exactamente 10 dígitos</div>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="student.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEstudiante');
    const cedulaInput = document.getElementById('cedula');
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const telefonoInput = document.getElementById('telefono');
    
    // Validación de cédula en tiempo real
    cedulaInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, ''); // Solo números
        if (valor.length > 10) valor = valor.slice(0, 10);
        this.value = valor;
    });
    
    // Validación de nombre en tiempo real
    nombreInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });
    
    // Validación de apellido en tiempo real
    apellidoInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });
    
    // Validación de teléfono en tiempo real
    telefonoInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, ''); // Solo números
        if (valor.length > 10) valor = valor.slice(0, 10);
        if (valor.length > 0 && !valor.startsWith('09')) {
            valor = '09' + valor.replace(/^09/, '');
        }
        this.value = valor;
    });
});
</script>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
