<?php
// reports/form_fpdf.php - Formulario para generar reportes FPDF
$pageTitle = "Generar Reportes FPDF";
include '../includes/header.php';
include '../includes/nav.php';

// Si no está logueado, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>

<style>
/* Estilos personalizados UTA para el formulario */
.border-uta-primary {
    border-color: #8B0000 !important;
    border-width: 2px !important;
}

.border-uta-accent {
    border-color: #D4AF37 !important;
    border-width: 2px !important;
}

.text-uta-primary {
    color: #8B0000 !important;
}

.text-uta-accent {
    color: #D4AF37 !important;
}

.btn-uta-primary {
    background-color: #8B0000;
    border-color: #8B0000;
    color: white;
}

.btn-uta-primary:hover {
    background-color: #4A0404;
    border-color: #4A0404;
    color: white;
}

.btn-uta-accent {
    background-color: #D4AF37;
    border-color: #D4AF37;
    color: white;
}

.btn-uta-accent:hover {
    background-color: #B8941F;
    border-color: #B8941F;
    color: white;
}

.alert-uta-light {
    background-color: rgba(139, 0, 0, 0.05);
    border-color: #8B0000;
    color: #4A0404;
}

.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<main>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-pdf"></i> Generar Reportes FPDF</h3>
                    </div>
                    <div class="card-body">                        <div class="row">
                            <!-- Reporte completo -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-uta-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-3x text-uta-primary mb-3"></i>
                                        <h5 class="card-title">Reporte Completo</h5>
                                        <p class="card-text">Genera un reporte PDF con todos los estudiantes registrados en el sistema.</p>
                                        <a href="estudiantes_fpdf.php" class="btn btn-uta-primary" target="_blank">
                                            <i class="fas fa-download"></i> Generar Reporte Completo
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte por cédula -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-uta-accent">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="fas fa-search fa-3x text-uta-accent mb-3"></i>
                                            <h5 class="card-title">Reporte Individual</h5>
                                            <p class="card-text">Genera un reporte PDF de un estudiante específico por cédula.</p>
                                        </div>
                                          <form method="GET" action="estudiantes_fpdf.php" target="_blank">
                                            <div class="form-group mb-3">
                                                <label for="student_id" class="form-label">Número de Cédula:</label>
                                                <input type="text" 
                                                       id="student_id" 
                                                       name="student_id" 
                                                       class="form-control" 
                                                       placeholder="Ej: 1234567890"
                                                       required>
                                            </div>                                            <button type="submit" class="btn btn-uta-accent w-100">
                                                <i class="fas fa-search"></i> Buscar y Generar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>                        <!-- Instrucciones -->
                        <div class="alert alert-uta-light">
                            <h6><i class="fas fa-info-circle text-uta-primary"></i> <strong>Instrucciones de Uso:</strong></h6>
                            <ul class="mb-0">
                                <li><strong>Reporte Completo:</strong> Descarga PDF con todos los estudiantes registrados</li>
                                <li><strong>Reporte Individual:</strong> Ingresa el número de cédula (10 dígitos) para buscar un estudiante específico</li>
                                <li><strong>Formato de Cédula:</strong> Ejemplo: 1234567890 (sin guiones ni espacios)</li>
                                <li>Los reportes se abren automáticamente en una nueva pestaña del navegador</li>
                                <li>Si no hay datos disponibles, se mostrará un mensaje informativo estilizado</li>
                                <li>Los reportes incluyen información completa: cédula, nombre, apellido, dirección y teléfono</li>
                            </ul>
                        </div>

                        <!-- Información adicional -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> <strong>Nota Importante:</strong></h6>
                            <p class="mb-0">Los reportes utilizan el diseño oficial de la Universidad Técnica de Ambato con colores institucionales (vino y dorado). Asegúrate de tener una conexión estable para la correcta generación del PDF.</p>
                        </div>                        <div class="text-center mt-4">
                            <a href="../dashboard.php" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                            <a href="../test_fpdf.php" class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-vial"></i> Probar FPDF
                            </a>
                        </div>                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Scripts de reportes -->
<script src="js/form-reports.js"></script>

<?php include '../includes/footer.php'; ?>
