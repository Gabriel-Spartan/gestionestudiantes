<?php
// dashboard.php - Panel de servicios del sistema
$pageTitle = "Gestión de Estudiantes";
include 'includes/header.php';
include 'includes/nav.php';

// Si no está logueado, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Incluir la función para mostrar la tabla de estudiantes
require_once __DIR__ . '/config/database.php';

// Mostrar mensajes de éxito si existen
if (isset($_GET['success'])) {
    $successMessage = '';
    switch ($_GET['success']) {
        case 'student_created':
            $studentName = $_GET['name'] ?? 'el estudiante';
            $successMessage = "✅ Estudiante <strong>" . htmlspecialchars($studentName) . "</strong> creado exitosamente.";
            break;
        case 'student_updated':
            $studentName = $_GET['name'] ?? 'el estudiante';
            $cedula = $_GET['cedula'] ?? '';
            $successMessage = "✏️ Estudiante <strong>" . htmlspecialchars($studentName) . "</strong> (Cédula: " . htmlspecialchars($cedula) . ") actualizado exitosamente.";
            break;
        case 'student_deleted':
            $studentName = $_GET['name'] ?? 'el estudiante';
            $cedula = $_GET['cedula'] ?? '';
            $successMessage = "🗑️ Estudiante <strong>" . htmlspecialchars($studentName) . "</strong> (Cédula: " . htmlspecialchars($cedula) . ") eliminado exitosamente.";
            break;
    }
}

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
    
    // Verificar si el usuario es ADMIN
    $isAdmin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'ADMIN';
    
    echo '<div class="d-flex justify-content-between align-items-center mb-3">';
    echo '<h5 class="mb-0">Listado de Estudiantes</h5>';
    
    // Solo mostrar botones si es ADMIN
    if ($isAdmin) {
        echo '<div class="btn-group" role="group">';
        echo '<a href="students/create.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Agregar Estudiante</a>';
        echo '<button id="generateReportBtn" class="btn btn-danger ms-2"><i class="fas fa-file-pdf"></i> Generar Reporte</button>';
        echo '</div>';
    } else {
        // Para usuarios no admin, solo mostrar el botón de reporte
        echo '<div class="btn-group" role="group">';
        echo '<button id="generateReportBtn" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Generar Reporte</button>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-bordered align-middle" id="estudiantesTable">';
    echo '<thead class="table-dark"><tr>';
    
    // Mostrar la columna "Seleccionar" para todos (para reportes individuales)
    echo '<th>Seleccionar</th>';
    echo '<th>Cédula</th><th>Nombre</th><th>Apellido</th><th>Dirección</th><th>Teléfono</th>';
    
    // Solo mostrar la columna "Acciones" si es ADMIN
    if ($isAdmin) {
        echo '<th>Acciones</th>';
    }
    
    echo '</tr></thead><tbody>';
    
    if (empty($estudiantes)) {
        $colspan = $isAdmin ? 7 : 6; // Ajustar colspan: 7 si es admin (con acciones), 6 si no es admin (sin acciones)
        echo '<tr><td colspan="' . $colspan . '" class="text-center">No hay estudiantes registrados.</td></tr>';
    } else {
        foreach ($estudiantes as $est) {
            $cedula = htmlspecialchars($est['cedula']);
            echo '<tr class="student-row" data-cedula="' . $cedula . '">';
            
            // Mostrar checkbox para todos (para reportes individuales)
            echo '<td class="text-center">';
            echo '<input type="radio" name="student_select" value="' . $cedula . '" class="form-check-input student-radio" data-cedula="' . $cedula . '" style="transform: scale(1.2);">';
            echo '</td>';
            
            echo '<td>' . $cedula . '</td>';
            echo '<td>' . htmlspecialchars($est['nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($est['apellido']) . '</td>';
            echo '<td>' . htmlspecialchars($est['direccion']) . '</td>';
            echo '<td>' . htmlspecialchars($est['telefono']) . '</td>';
            
            // Solo mostrar botones de acción si es ADMIN
            if ($isAdmin) {
                echo '<td class="action-buttons">';
                echo '<a href="students/edit.php?cedula=' . $cedula . '" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i> Editar</a>';
                // ELIMINADO EL onclick="return confirm()" - Ahora usa nuestro sistema de confirmación
                echo '<a href="students/delete.php?cedula=' . $cedula . '" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Eliminar</a>';
                echo '</td>';
            }
            
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}
?>

<main class="services-main">
    <div class="container mt-4">
        <!-- Mostrar mensaje de éxito si existe -->
        <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php mostrarTablaEstudiantes(); ?>
    </div>
</main>

<style>
.student-row:hover {
    background-color: #f8f9fa !important;
    transition: all 0.3s ease;
}

.student-row.selected {
    background-color: #e3f2fd !important;
    border-left: 4px solid #2196f3;
}

.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.student-radio {
    cursor: pointer;
}

/* Estilos para los mensajes de éxito */
.alert-success {
    border-left: 4px solid #28a745;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-success strong {
    color: #155724;
}

/* Animación para el mensaje de éxito */
.alert.fade.show {
    animation: slideInFromTop 0.5s ease-out;
}

@keyframes slideInFromTop {
    0% {
        transform: translateY(-100%);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generateReportBtn');
    const radioButtons = document.querySelectorAll('.student-radio');
    const studentRows = document.querySelectorAll('.student-row');
    
    // Solo ejecutar la lógica de selección (ahora todos tienen radio buttons)
    // Manejar cambios en los radio buttons
    radioButtons.forEach(function(radio) {
        radio.addEventListener('click', function(e) {
            const cedula = this.getAttribute('data-cedula');
            
            // Si ya está seleccionado, deseleccionar
            if (this.dataset.selected === 'true') {
                this.checked = false;
                this.dataset.selected = 'false';
                
                // Remover selección visual
                const currentRow = this.closest('.student-row');
                currentRow.classList.remove('selected');
                
                return;
            }
            
            // Deseleccionar todos los otros radio buttons
            radioButtons.forEach(function(r) {
                r.dataset.selected = 'false';
                r.closest('.student-row').classList.remove('selected');
            });
            
            // Seleccionar el actual
            this.dataset.selected = 'true';
            const currentRow = this.closest('.student-row');
            currentRow.classList.add('selected');
        });
    });
    
    // Manejar clic en el botón de generar reporte
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const selectedRadio = document.querySelector('.student-radio[data-selected="true"]');
            
            if (selectedRadio) {
                // Hay un estudiante seleccionado - generar reporte individual
                const cedula = selectedRadio.getAttribute('data-cedula');
                window.open(`reports/estudiantes_fpdf.php?student_id=${cedula}`, '_blank');
            } else {
                // No hay selección - generar reporte completo
                window.open('reports/estudiantes_fpdf.php', '_blank');
            }
        });
    }
    
    // Auto-ocultar mensaje de éxito después de 5 segundos
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(successAlert);
            bsAlert.close();
        }, 5000);
    }
});
</script>

<!-- Scripts de reportes -->
<script src="reports/js/dashboard-reports.js"></script>

<?php include 'includes/footer.php'; ?>