<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario_creador_id = $_SESSION['user_id'] ?? null;

    // Validación básica
    if ($cedula === '' || $nombre === '' || $apellido === '' || $direccion === '' || $telefono === '') {
        $errores[] = 'Todos los campos son obligatorios.';
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
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula</label>
            <input type="text" class="form-control" id="cedula" name="cedula" required>
        </div>
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="student.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
