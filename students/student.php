<?php
require_once __DIR__ . '/../config/database.php';

function mostrarTablaEstudiantes()
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->query('SELECT cedula, nombre, apellido, direccion, telefono FROM estudiantes ORDER BY apellido, nombre');
        $estudiantes = $stmt->fetchAll();
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error al obtener estudiantes: ' . htmlspecialchars($e->getMessage()) . '</div>';
        return;
    }
    echo '<div class="d-flex justify-content-between align-items-center mb-3">';
    echo '<h5 class="mb-0">Listado de Estudiantes</h5>';
    echo '<a href="create.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Agregar Estudiante</a>';
    echo '</div>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-bordered align-middle">';
    echo '<thead class="table-dark"><tr>';
    echo '<th>Cédula</th><th>Nombre</th><th>Apellido</th><th>Dirección</th><th>Teléfono</th><th>Acciones</th>';
    echo '</tr></thead><tbody>';
    if (empty($estudiantes)) {
        echo '<tr><td colspan="6" class="text-center">No hay estudiantes registrados.</td></tr>';
    } else {
        foreach ($estudiantes as $est) {
            $cedula = htmlspecialchars($est['cedula']);
            echo '<tr>';
            echo '<td>' . $cedula . '</td>';
            echo '<td>' . htmlspecialchars($est['nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($est['apellido']) . '</td>';
            echo '<td>' . htmlspecialchars($est['direccion']) . '</td>';
            echo '<td>' . htmlspecialchars($est['telefono']) . '</td>';
            echo '<td>';
            echo '<a href="edit.php?cedula=' . $cedula . '" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i> Editar</a>';
            echo '<a href="delete.php?cedula=' . $cedula . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Seguro que deseas eliminar este estudiante?\');"><i class="fas fa-trash"></i> Eliminar</a>';
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}

// Mostrar la tabla directamente al cargar la página
include_once __DIR__ . '/../includes/header.php';
echo '<div class="container mt-4">';
mostrarTablaEstudiantes();
echo '</div>';
include_once __DIR__ . '/../includes/footer.php';
