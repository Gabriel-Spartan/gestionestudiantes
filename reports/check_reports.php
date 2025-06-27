<?php
/**
 * Sistema de Verificación de Reportes FPDF
 * Versión 2.0 - Reorganizado y optimizado
 * Universidad Técnica de Ambato
 */

/**
 * Clase para verificar el sistema de reportes
 */
class ReportSystemChecker 
{
    private $checks = [];
    private $fpdf_paths;

    public function __construct() 
    {
        $this->initializeFPDFPaths();
        $this->runAllChecks();
    }    /**
     * Inicializar rutas posibles para FPDF
     */
    private function initializeFPDFPaths() 
    {
        $this->fpdf_paths = [
            'C:/xampp/htdocs/cuarto2/fpdf186/fpdf.php',
            '../cuarto2/fpdf186/fpdf.php',
            '../../cuarto2/fpdf186/fpdf.php'
        ];
    }

    /**
     * Ejecutar todas las verificaciones
     */
    private function runAllChecks() 
    {
        $this->checkFPDFLibrary();
        $this->checkReportsFolder();
        $this->checkReportFiles();
        $this->checkDatabaseConnection();
    }

    /**
     * Verificar librería FPDF
     */
    private function checkFPDFLibrary() 
    {
        $this->checks['fpdf'] = false;
        foreach ($this->fpdf_paths as $path) {
            if (file_exists($path)) {
                $this->checks['fpdf'] = true;
                break;
            }
        }
    }

    /**
     * Verificar carpeta de reportes
     */
    private function checkReportsFolder() 
    {
        $this->checks['reports_folder'] = is_dir(__DIR__);
    }

    /**
     * Verificar archivos de reportes
     */
    private function checkReportFiles() 
    {
        $this->checks['estudiantes_fpdf'] = file_exists(__DIR__ . '/estudiantes_fpdf.php');
    }

    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabaseConnection() 
    {
        try {
            require_once __DIR__ . '/../config/database.php';
            $pdo = getConnection();
            $this->checks['database'] = true;
        } catch (Exception $e) {
            $this->checks['database'] = false;
        }
    }

    /**
     * Obtener todos los resultados de verificación
     */
    public function getChecks() 
    {
        return $this->checks;
    }

    /**
     * Verificar si todo está configurado correctamente
     */
    public function isSystemReady() 
    {
        return $this->checks['fpdf'] && 
               $this->checks['reports_folder'] && 
               $this->checks['estudiantes_fpdf'] && 
               $this->checks['database'];
    }

    /**
     * Obtener descripción de una verificación
     */
    public function getCheckDescription($check_name) 
    {
        $descriptions = [
            'fpdf' => 'Librería FPDF (C:/xampp/htdocs/cuarto2/fpdf186)',
            'reports_folder' => 'Carpeta de reportes',
            'estudiantes_fpdf' => 'Generador de PDF de estudiantes (FPDF)',
            'database' => 'Conexión a base de datos'
        ];

        return $descriptions[$check_name] ?? $check_name;
    }
}

/**
 * Clase para generar la interfaz HTML
 */
class ReportCheckerHTML 
{
    private $checker;

    public function __construct($checker) 
    {
        $this->checker = $checker;
    }

    /**
     * Generar HTML completo
     */
    public function generateHTML() 
    {
        return $this->getHTMLHeader() . 
               $this->getHTMLBody() . 
               $this->getHTMLFooter();
    }

    /**
     * Generar encabezado HTML
     */
    private function getHTMLHeader() 
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema de Reportes FPDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>';
    }

    /**
     * Generar cuerpo HTML
     */
    private function getHTMLBody() 
    {
        return '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-pdf"></i> Verificación del Sistema de Reportes FPDF</h3>
                        </div>
                        <div class="card-body">' .
                            $this->generateCheckResults() .
                            $this->generateStatusMessage() .
                            $this->generateSystemInfo() .
                            $this->generateActionButtons() .
                        '</div>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * Generar resultados de verificación
     */
    private function generateCheckResults() 
    {
        $html = '';
        foreach ($this->checker->getChecks() as $name => $status) {
            $icon = $status ? 'fa-check text-success' : 'fa-times text-danger';
            $text = $status ? 'OK' : 'ERROR';
            $description = $this->checker->getCheckDescription($name);
            
            $html .= "<div class='mb-2'>";
            $html .= "<i class='fas $icon'></i> ";
            $html .= "<strong>$description:</strong> $text";
            $html .= "</div>";
        }
        return $html;
    }

    /**
     * Generar mensaje de estado
     */
    private function generateStatusMessage() 
    {
        if ($this->checker->isSystemReady()) {
            return '<div class="alert alert-success mt-3">
                <h5><i class="fas fa-thumbs-up"></i> ¡Sistema de Reportes FPDF Configurado!</h5>
                <p>El sistema de reportes FPDF está listo para usar.</p>
                <a href="estudiantes_fpdf.php" class="btn btn-danger" target="_blank">Probar Reporte FPDF</a>
            </div>';
        } else {
            return '<div class="alert alert-warning mt-3">
                <h5><i class="fas fa-exclamation-triangle"></i> Hay problemas en la configuración FPDF</h5>
                <p>Por favor revisa los elementos marcados como ERROR.</p>
            </div>';
        }
    }

    /**
     * Generar información del sistema
     */
    private function generateSystemInfo() 
    {
        return '<hr>
        <h5>Archivos del Sistema de Reportes:</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">📄 <code>C:/xampp/htdocs/cuarto2/fpdf186/fpdf.php</code> - Librería FPDF</li>
            <li class="list-group-item">📁 <code>reports/</code> - Carpeta de reportes</li>
            <li class="list-group-item">📄 <code>reports/estudiantes_fpdf.php</code> - Generador FPDF</li>
        </ul>
        
        <div class="mt-4">
            <h6>Funcionalidades Implementadas:</h6>
            <div class="alert alert-light">
                <ul class="mb-0">
                    <li><strong>Reportes Completos:</strong> Genera reporte de todos los estudiantes</li>
                    <li><strong>Reportes Individuales:</strong> Genera reporte por cédula específica</li>
                    <li><strong>Diseño Profesional:</strong> Formato UTA con colores institucionales</li>
                    <li><strong>Manejo de Errores:</strong> Sistema robusto de manejo de excepciones</li>
                </ul>
            </div>
        </div>';
    }

    /**
     * Generar botones de acción
     */
    private function generateActionButtons() 
    {
        return '<h6>Enlaces de Prueba:</h6>
        <div class="btn-group" role="group">
            <a href="estudiantes_fpdf.php" class="btn btn-outline-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Reporte Completo
            </a>
            <a href="estudiantes_fpdf.php?student_id=1234567890" class="btn btn-outline-info" target="_blank">
                <i class="fas fa-search"></i> Reporte Individual (ejemplo)
            </a>
            <a href="../dashboard.php" class="btn btn-outline-success">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>';
    }

    /**
     * Generar pie HTML
     */
    private function getHTMLFooter() 
    {
        return '</body>
</html>';
    }
}

// Ejecutar el sistema de verificación
try {
    $checker = new ReportSystemChecker();
    $htmlGenerator = new ReportCheckerHTML($checker);
    echo $htmlGenerator->generateHTML();
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
