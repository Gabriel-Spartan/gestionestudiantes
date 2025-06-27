<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/nav.php';

$errores = [];
$exito = false;

// Función para validar nombre y apellido (solo letras y espacios)
function validarNombreApellido($texto) {
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $texto);
}

// Función para validar teléfono ecuatoriano
function validarTelefonoEcuatoriano($telefono) {
    // Debe empezar con 09 y tener exactamente 10 dígitos
    return preg_match('/^09\d{8}$/', $telefono);
}

$cedula = $_GET['cedula'] ?? '';
if ($cedula === '') {
    echo '<div class="alert alert-danger">Cédula no especificada.</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Obtener datos actuales
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM estudiantes WHERE cedula = ?');
    $stmt->execute([$cedula]);
    $estudiante = $stmt->fetch();
    if (!$estudiante) {
        echo '<div class="alert alert-danger">Estudiante no encontrado.</div>';
        include_once __DIR__ . '/../includes/footer.php';
        exit;
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');    if ($nombre === '' || $apellido === '' || $direccion === '' || $telefono === '') {
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
            $stmt->execute([$nombre, $apellido, $direccion, $telefono, $cedula]);
            $exito = true;
            header('Location: student.php');
            exit();
        } catch (PDOException $e) {
            $errores[] = 'Error al actualizar: ' . htmlspecialchars($e->getMessage());
        }
    }
} else {
    // Valores por defecto del estudiante
    $nombre = $estudiante['nombre'];
    $apellido = $estudiante['apellido'];
    $direccion = $estudiante['direccion'];
    $telefono = $estudiante['telefono'];
}
?>
<div class="container mt-5">
    <h3>Editar Estudiante</h3>
    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <?php foreach ($errores as $err) echo '<div>' . $err . '</div>'; ?>
        </div>
    <?php endif; ?>    <form method="post" autocomplete="off" id="formEditarEstudiante">
        <div class="mb-3">
            <label class="form-label">Cédula</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($cedula); ?>" disabled>
            <div class="form-text">La cédula no se puede modificar</div>
        </div>
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" 
                   value="<?php echo htmlspecialchars($nombre); ?>" required
                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                   title="Solo se permiten letras y espacios">
            <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" 
                   value="<?php echo htmlspecialchars($apellido); ?>" required
                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                   title="Solo se permiten letras y espacios">
            <div class="form-text">Solo letras y espacios, sin números ni símbolos</div>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" 
                   value="<?php echo htmlspecialchars($direccion); ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" 
                   value="<?php echo htmlspecialchars($telefono); ?>" required
                   pattern="09\d{8}" maxlength="10"
                   title="Debe empezar con 09 y tener 10 dígitos en total">
            <div class="form-text">Debe empezar con 09 y tener exactamente 10 dígitos</div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="student.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const telefonoInput = document.getElementById('telefono');
    
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
