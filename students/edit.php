<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

$errores = [];
$exito = false;

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
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre === '' || $apellido === '' || $direccion === '' || $telefono === '') {
        $errores[] = 'Todos los campos son obligatorios.';
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
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Cédula</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($cedula); ?>" disabled>
        </div>
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido); ?>" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($direccion); ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="student.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
