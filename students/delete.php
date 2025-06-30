<?php
require_once __DIR__ . '/../api/config/database.php';

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar permisos (solo ADMIN puede eliminar)
if ($_SESSION['user_type'] !== 'ADMIN') {
    $errorContent = '
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>❌ Acceso Denegado</h4>
            <p>Solo los administradores pueden eliminar estudiantes.</p>
            <a href="../dashboard.php" class="btn btn-primary mt-3">🏠 Ir al Dashboard</a>
        </div>
    </div>';
    
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/nav.php';
    echo $errorContent;
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Verificar que se proporcionó la cédula
$cedula = $_GET['cedula'] ?? '';
if ($cedula === '') {
    $errorContent = '
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>❌ Error</h4>
            <p>No se especificó la cédula del estudiante a eliminar.</p>
            <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
        </div>
    </div>';
    
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/nav.php';
    echo $errorContent;
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Verificar si se confirmó la eliminación
$confirmado = isset($_GET['confirmado']) && $_GET['confirmado'] === 'si';

if (!$confirmado) {
    // Mostrar página de confirmación
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare('SELECT nombre, apellido, direccion, telefono FROM estudiantes WHERE cedula = ?');
        $stmt->execute([$cedula]);
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$estudiante) {
            $errorContent = '
            <div class="container mt-5">
                <div class="alert alert-warning">
                    <h4>⚠️ Estudiante No Encontrado</h4>
                    <p>No se encontró un estudiante con la cédula: <strong>' . htmlspecialchars($cedula) . '</strong></p>
                    <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
                </div>
            </div>';
            
            include_once __DIR__ . '/../includes/header.php';
            include_once __DIR__ . '/../includes/nav.php';
            echo $errorContent;
            include_once __DIR__ . '/../includes/footer.php';
            exit();
        }
        
        // Mostrar página de confirmación
        include_once __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../includes/nav.php';
        ?>
        
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">🗑️ Confirmar Eliminación</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <strong>⚠️ ¡ATENCIÓN!</strong><br>
                                Esta acción <strong>NO SE PUEDE DESHACER</strong>. Una vez eliminado, toda la información del estudiante se perderá permanentemente.
                            </div>
                            
                            <h5 class="text-danger">¿Estás seguro de que deseas eliminar este estudiante?</h5>
                            
                            <div class="student-info bg-light p-3 rounded mt-3">
                                <h6><strong>Información del estudiante:</strong></h6>
                                <div class="row">
                                    <div class="col-sm-3"><strong>Cédula:</strong></div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($cedula); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"><strong>Nombre:</strong></div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"><strong>Dirección:</strong></div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($estudiante['direccion']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"><strong>Teléfono:</strong></div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($estudiante['telefono']); ?></div>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex justify-content-center gap-3">
                                <a href="delete.php?cedula=<?php echo urlencode($cedula); ?>&confirmado=si" 
                                   class="btn btn-danger btn-lg"
                                   id="confirmarEliminacion">
                                    🗑️ Sí, Eliminar Definitivamente
                                </a>
                                <a href="../dashboard.php" class="btn btn-success btn-lg">
                                    ❌ No, Cancelar
                                </a>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    Eliminado por: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong><br>
                                    Fecha: <?php echo date('d/m/Y H:i:s'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación adicional -->
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmModalLabel">⚠️ Confirmación Final</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-danger">¿Estás absolutamente seguro?</h5>
                        <p class="mb-3">El estudiante <strong><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></strong> será eliminado permanentemente.</p>
                        <p class="text-muted small">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                            ❌ Cancelar
                        </button>
                        <a href="delete.php?cedula=<?php echo urlencode($cedula); ?>&confirmado=si" 
                           class="btn btn-danger">
                            🗑️ Eliminar Definitivamente
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .student-info .row {
            margin-bottom: 0.5rem;
        }
        .card-border-danger {
            border-color: #dc3545 !important;
        }
        .gap-3 {
            gap: 1rem !important;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmarBtn = document.getElementById('confirmarEliminacion');
            
            confirmarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Mostrar modal de confirmación adicional
                const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                modal.show();
            });
            
            // Auto-focus en el botón de cancelar cuando se abre el modal
            document.getElementById('confirmModal').addEventListener('shown.bs.modal', function() {
                document.querySelector('[data-bs-dismiss="modal"]').focus();
            });
        });
        </script>

        <?php
        include_once __DIR__ . '/../includes/footer.php';
        exit();
        
    } catch (Exception $e) {
        $errorContent = '
        <div class="container mt-5">
            <div class="alert alert-danger">
                <h4>❌ Error</h4>
                <p>Error al obtener información del estudiante: ' . htmlspecialchars($e->getMessage()) . '</p>
                <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
            </div>
        </div>';
        
        include_once __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../includes/nav.php';
        echo $errorContent;
        include_once __DIR__ . '/../includes/footer.php';
        exit();
    }
}

// Si llegamos aquí, la eliminación fue confirmada
try {
    $pdo = getConnection();
    
    // Obtener el nombre del estudiante para el mensaje de confirmación
    $stmt = $pdo->prepare('SELECT nombre, apellido FROM estudiantes WHERE cedula = ?');
    $stmt->execute([$cedula]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        $errorContent = '
        <div class="container mt-5">
            <div class="alert alert-warning">
                <h4>⚠️ Estudiante No Encontrado</h4>
                <p>No se encontró un estudiante con la cédula: <strong>' . htmlspecialchars($cedula) . '</strong></p>
                <a href="../dashboard.php" class="btn btn-secondary mt-3">🏠 Ir al Dashboard</a>
            </div>
        </div>';
        
        include_once __DIR__ . '/../includes/header.php';
        include_once __DIR__ . '/../includes/nav.php';
        echo $errorContent;
        include_once __DIR__ . '/../includes/footer.php';
        exit();
    }
    
    // Eliminar el estudiante
    $stmt = $pdo->prepare('DELETE FROM estudiantes WHERE cedula = ?');
    $result = $stmt->execute([$cedula]);
    
    if ($result && $stmt->rowCount() > 0) {
        // Redirigir al dashboard con mensaje de éxito
        $studentName = $estudiante['nombre'] . ' ' . $estudiante['apellido'];
        header('Location: ../dashboard.php?success=student_deleted&name=' . urlencode($studentName) . '&cedula=' . urlencode($cedula));
        exit();
    } else {
        throw new Exception('No se pudo eliminar el estudiante. Intenta nuevamente.');
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    $errorContent = '
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>❌ Error de Base de Datos</h4>
            <p>Ocurrió un error al eliminar el estudiante:</p>
            <div class="bg-light p-3 rounded mt-2">
                <code>' . htmlspecialchars($e->getMessage()) . '</code>
            </div>
            <div class="mt-3">
                <a href="../dashboard.php" class="btn btn-secondary">🏠 Ir al Dashboard</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">↩️ Volver</a>
            </div>
        </div>
    </div>';
    
} catch (Exception $e) {
    // Error general
    $errorContent = '
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>❌ Error al Eliminar</h4>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <div class="mt-3">
                <a href="../dashboard.php" class="btn btn-secondary">🏠 Ir al Dashboard</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">↩️ Volver</a>
            </div>
        </div>
    </div>';
}

// Si llegamos aquí, hubo un error y necesitamos mostrar la página
if (isset($errorContent)) {
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/nav.php';
    echo $errorContent;
    include_once __DIR__ . '/../includes/footer.php';
}
?>