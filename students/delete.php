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
                                <button type="button" class="btn btn-danger btn-lg" id="confirmarEliminacion">
                                    🗑️ Sí, Eliminar Definitivamente
                                </button>
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

        <!-- Modal de confirmación final - HTML puro sin Bootstrap -->
        <div id="confirmModal" class="custom-modal" style="display: none;">
            <div class="modal-overlay">
                <div class="modal-content-custom">
                    <div class="modal-header-custom">
                        <h5>⚠️ Confirmación Final</h5>
                        <button type="button" class="close-modal" onclick="closeConfirmModal()">&times;</button>
                    </div>
                    <div class="modal-body-custom">
                        <div class="text-center">
                            <div class="mb-3">
                                <span style="font-size: 3rem; color: #ffc107;">⚠️</span>
                            </div>
                            <h5 class="text-danger">¿Estás absolutamente seguro?</h5>
                            <p class="mb-3">El estudiante <strong><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></strong> será eliminado permanentemente.</p>
                            <p class="text-muted small">Esta acción no se puede deshacer.</p>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-success" onclick="closeConfirmModal()">
                            ❌ Cancelar
                        </button>
                        <a href="delete.php?cedula=<?php echo urlencode($cedula); ?>&confirmado=si" 
                           class="btn btn-danger"
                           onclick="showLoadingMessage()">
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
        .gap-3 {
            gap: 1rem !important;
        }

        /* Modal personalizado */
        .custom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1050;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 1rem;
        }

        .modal-content-custom {
            background: white;
            border-radius: 8px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border: 2px solid #dc3545;
        }

        .modal-header-custom {
            background: #dc3545;
            color: white;
            padding: 1rem;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header-custom h5 {
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        /* Mensaje de carga */
        .loading-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            z-index: 2000;
            display: none;
        }
        </style>

        <!-- Mensaje de carga -->
        <div id="loadingMessage" class="loading-message">
            <div class="text-center">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div class="mt-2">Eliminando estudiante...</div>
            </div>
        </div>

        <script>
        function showConfirmModal() {
            document.getElementById('confirmModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevenir scroll
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restaurar scroll
        }

        function showLoadingMessage() {
            document.getElementById('loadingMessage').style.display = 'block';
            closeConfirmModal();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const confirmarBtn = document.getElementById('confirmarEliminacion');
            
            confirmarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showConfirmModal();
            });

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeConfirmModal();
                }
            });

            // Cerrar modal haciendo clic fuera
            document.getElementById('confirmModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeConfirmModal();
                }
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