<?php
require_once __DIR__ . '/../config/database.php';

$cedula = $_GET['cedula'] ?? '';
if ($cedula === '') {
    echo '<div class="alert alert-danger">Cédula no especificada.</div>';
    echo '<a href="student.php" class="btn btn-secondary mt-3">Regresar</a>';
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM estudiantes WHERE cedula = ?');
    $stmt->execute([$cedula]);
    header('Location: student.php');
    exit();
} catch (Exception $e) {
    include_once __DIR__ . '/../includes/header.php';
    echo '<div class="container mt-5">';
    echo '<div class="alert alert-danger">Error al eliminar: ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<a href="student.php" class="btn btn-secondary mt-3">Regresar</a>';
    echo '</div>';
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}
