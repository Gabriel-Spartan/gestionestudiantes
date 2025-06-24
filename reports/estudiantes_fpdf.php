<?php

require_once "../config/database.php";
ob_start();
session_start();

// Validar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Cargar FPDF primero antes de definir las clases
$fpdf_paths = [
    'C:/xampp/htdocs/gestionestudiantes/fpdf186/fpdf.php',
    '../fpdf186/fpdf.php',
    dirname(__DIR__) . '/fpdf186/fpdf.php',
    $_SERVER['DOCUMENT_ROOT'] . '/gestionestudiantes/fpdf186/fpdf.php'
];

$fpdf_loaded = false;
$found_path = '';
foreach ($fpdf_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $fpdf_loaded = true;
        $found_path = $path;
        break;
    }
}

if (!$fpdf_loaded) {
    ob_end_clean();
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h3><strong>Error: Librería FPDF no encontrada</strong></h3>";
    echo "<p>El sistema no pudo encontrar la librería FPDF en ninguna de estas rutas:</p>";
    echo "<ul>";
    foreach ($fpdf_paths as $path) {
        $exists = file_exists($path) ? '✅ Existe' : '❌ No existe';
        echo "<li><code>" . htmlspecialchars($path) . "</code> - $exists</li>";
    }
    echo "</ul>";    echo "<h4>Solución:</h4>";
    echo "<ol>";
    echo "<li>Verifica que FPDF esté instalado en <code>C:/xampp/htdocs/gestionestudiantes/fpdf186/</code></li>";
    echo "<li>Descarga FPDF desde <a href='http://www.fpdf.org/' target='_blank'>http://www.fpdf.org/</a> si no lo tienes</li>";
    echo "<li>Asegúrate de que el archivo <code>fpdf.php</code> esté en la carpeta correcta</li>";
    echo "</ol>";
    echo "<p><a href='debug_fpdf.php' style='padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🔍 Ejecutar Diagnóstico</a></p>";
    echo "</div>";
    exit();
}

// Verificar que la clase FPDF esté disponible después de cargar
if (!class_exists('FPDF')) {
    ob_end_clean();
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h3><strong>Error: Clase FPDF no disponible</strong></h3>";
    echo "<p>El archivo FPDF se cargó desde: <code>$found_path</code></p>";
    echo "<p>Pero la clase FPDF no está disponible. Esto puede indicar:</p>";
    echo "<ul>";
    echo "<li>El archivo fpdf.php está corrupto</li>";
    echo "<li>Hay un error de sintaxis en fpdf.php</li>";
    echo "<li>Conflicto con otra librería</li>";
    echo "</ul>";
    echo "</div>";
    exit();
}

/**
 * Clase principal para manejo completo de reportes PDF
 */
class StudentReportSystem 
{
    private $connection;
    private $config;

    public function __construct() 
    {
        $this->initializeConfig();
        $this->initializeDatabase();
    }

    /**
     * Configuración completa del sistema
     */
    private function initializeConfig() 
    {
        $this->config = [
            'colors' => [
                'uta_wine' => [139, 0, 0],
                'silver' => [192, 192, 192],
                'white' => [255, 255, 255],
                'black' => [0, 0, 0],
                'light_gray' => [250, 250, 250],
                'border_gray' => [200, 200, 200],
                'text_gray' => [128, 128, 128]
            ],
            'fonts' => [
                'title' => ['Arial', 'B', 20],
                'subtitle' => ['Arial', '', 13],
                'small' => ['Arial', 'I', 10],
                'header' => ['Arial', 'B', 18],
                'table_header' => ['Arial', 'B', 11],
                'table_data' => ['Arial', '', 10],
                'section' => ['Arial', 'B', 14]
            ]
        ];
    }    /**
     * Inicializar conexión a base de datos
     */
    private function initializeDatabase() 
    {
        try {
            // Usar PDO a través de la función getConnection() del archivo database.php
            $this->connection = getConnection();
            
            // Verificar que la conexión es válida
            if (!$this->connection) {
                throw new Exception("No se pudo establecer conexión a la base de datos");
            }
            
            // Test de conectividad con una query simple
            $stmt = $this->connection->query("SELECT 1");
            if (!$stmt) {
                throw new Exception("Error en la prueba de conectividad");
            }
            
        } catch (Exception $e) {
            throw new Exception("Error de base de datos: " . $e->getMessage());
        }
    }

    /**
     * Método principal para generar reportes
     */
    public function generateReport() 
    {
        try {
            ob_clean();
            
            $student_id = $this->getStudentId();
            $data = $this->getStudentData($student_id);
            $report_type = $student_id ? 'individual' : 'general';
            $report_title = $student_id ? 'Reporte Individual de Estudiante' : 'Reporte General de Estudiantes';
            
            $pdf = new StudentPDF($report_title, $report_type, $this->config);
            $pdf->AddPage();
            
            if (!empty($data)) {
                $this->addReportContent($pdf, $data, $report_type);
            } else {
                $pdf->addNoDataMessage();
            }
            
            $filename = $this->generateFilename($student_id);
            $pdf->Output('I', $filename);
            
        } catch (Exception $e) {
            $this->handleError($e);
        } finally {
            $this->closeConnection();
        }
    }

    /**
     * Obtener ID del estudiante desde parámetros GET
     */
    private function getStudentId() 
    {
        return isset($_GET['student_id']) ? trim($_GET['student_id']) : null;
    }    /**
     * Obtener datos de estudiantes
     */
    private function getStudentData($student_id) 
    {
        if ($student_id) {
            $sql = "SELECT cedula, nombre, apellido, direccion, telefono FROM estudiantes WHERE cedula = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$student_id]);
        } else {
            $sql = "SELECT cedula, nombre, apellido, direccion, telefono FROM estudiantes ORDER BY nombre";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
        }
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Agregar contenido al reporte
     */
    private function addReportContent($pdf, $data, $report_type) 
    {
        // Resumen del reporte
        $pdf->addReportSummary(count($data), $report_type);
          // Tabla de datos
        $headers = ['Cédula', 'Nombre', 'Apellido', 'Dirección', 'Teléfono'];
        $pdf->addStudentTable($headers, $data);
    }

    /**
     * Generar nombre del archivo
     */
    private function generateFilename($student_id) 
    {
        $timestamp = date('Y-m-d_H-i-s');
        return $student_id ? 
            "estudiante_{$student_id}_{$timestamp}.pdf" : 
            "estudiantes_general_{$timestamp}.pdf";
    }

    /**
     * Manejar errores
     */
    private function handleError($exception) 
    {
        ob_clean();
        
        $pdf = new StudentPDF('Error en el Reporte', 'error', $this->config);
        $pdf->AddPage();
        $pdf->addErrorMessage($exception->getMessage());
        $pdf->Output('I', 'error_reporte.pdf');
    }    /**
     * Cerrar conexión
     */
    private function closeConnection() 
    {
        if ($this->connection) {
            $this->connection = null;
        }
    }
}


/**
 * Clase PDF personalizada con diseño UTA
 */
class StudentPDF extends FPDF
{
    private $reportTitle;
    private $reportType;
    private $config;
    
    public function __construct($title, $type, $config)
    {
        parent::__construct();
        $this->reportTitle = $title;
        $this->reportType = $type;
        $this->config = $config;
    }

    /**
     * Encabezado del documento
     */
    function Header()
    {
        $this->drawHeader();
        $this->addTitle();
    }

    /**
     * Dibujar encabezado con colores UTA
     */
    private function drawHeader()
    {
        // Fondo principal UTA
        $this->SetFillColor(...$this->config['colors']['uta_wine']);
        $this->Rect(0, 0, 210, 35, 'F');
        
        // Línea decorativa
        $this->SetFillColor(...$this->config['colors']['silver']);
        $this->Rect(0, 32, 210, 3, 'F');        // Información institucional
        $this->SetTextColor(...$this->config['colors']['white']);
        $this->SetFont(...$this->config['fonts']['title']);
        $this->SetXY(10, 8);
        $this->Cell(0, 8, $this->utf8_to_fpdf('UNIVERSIDAD TÉCNICA DE AMBATO'), 0, 1, 'C');
        
        $this->SetFont(...$this->config['fonts']['subtitle']);
        $this->SetXY(10, 18);
        $this->Cell(0, 6, $this->utf8_to_fpdf('Sistema de Gestión de Estudiantes'), 0, 1, 'C');
        
        $this->SetFont(...$this->config['fonts']['small']);
        $this->SetXY(10, 26);
        $this->Cell(0, 4, $this->utf8_to_fpdf('Generado el: ') . date('d/m/Y H:i:s'), 0, 1, 'C');
    }

    /**
     * Agregar título del reporte
     */
    private function addTitle()
    {
        $this->Ln(10);
        $this->SetTextColor(...$this->config['colors']['black']);        $this->SetFont(...$this->config['fonts']['header']);
        $this->Cell(0, 10, $this->utf8_to_fpdf($this->reportTitle), 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 12);
        $typeText = ($this->reportType == 'individual') ? 'Reporte Individual' : 'Reporte General';
        $this->Cell(0, 6, $this->utf8_to_fpdf($typeText), 0, 1, 'C');
        
        $this->Ln(5);
    }

    /**
     * Pie de página
     */
    function Footer()
    {
        $this->SetY(-25);
        
        // Línea decorativa
        $this->SetDrawColor(...$this->config['colors']['text_gray']);
        $this->SetLineWidth(0.8);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(3);        // Información de contacto
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$this->config['colors']['black']);
        $this->Cell(0, 4, $this->utf8_to_fpdf('Universidad Técnica de Ambato - Facultad de Ingeniería'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, $this->utf8_to_fpdf('Campus Huachi - Av. Los Chasquis y Río Payamino'), 0, 1, 'C');
        $this->Cell(0, 4, $this->utf8_to_fpdf('Teléfono: (03) 2848487 | Email: info@uta.edu.ec'), 0, 1, 'C');
        
        // Número de página
        $this->SetXY(10, -10);
        $this->SetFont(...$this->config['fonts']['small']);
        $this->Cell(0, 4, $this->utf8_to_fpdf('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    /**
     * Agregar resumen del reporte
     */
    public function addReportSummary($total, $type)
    {        $this->SetFont(...$this->config['fonts']['section']);
        $this->SetTextColor(...$this->config['colors']['black']);
        $this->Cell(0, 8, $this->utf8_to_fpdf('📊 Resumen del Reporte'), 0, 1, 'L');
        
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $this->utf8_to_fpdf('• Total de estudiantes: ') . $total, 0, 1, 'L');
        $this->Cell(0, 6, $this->utf8_to_fpdf('• Tipo: ') . ($type == 'individual' ? 'Individual' : 'General'), 0, 1, 'L');
        $this->Ln(8);
    }

    /**
     * Crear tabla de estudiantes
     */
    public function addStudentTable($headers, $data)
    {
        $widths = [25, 40, 40, 45, 40];
        
        // Encabezado de tabla
        $this->SetFillColor(...$this->config['colors']['uta_wine']);
        $this->SetTextColor(...$this->config['colors']['white']);
        $this->SetDrawColor(...$this->config['colors']['border_gray']);
        $this->SetLineWidth(0.3);
        $this->SetFont(...$this->config['fonts']['table_header']);        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 12, $this->utf8_to_fpdf($headers[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Datos de tabla
        $this->SetFont(...$this->config['fonts']['table_data']);
        $fill = false;
        
        foreach ($data as $row) {
            // Alternar colores
            if ($fill) {
                $this->SetFillColor(...$this->config['colors']['light_gray']);
            } else {
                $this->SetFillColor(...$this->config['colors']['white']);
            }
            
            $this->SetTextColor(...$this->config['colors']['black']);
            $this->SetDrawColor(...$this->config['colors']['border_gray']);            // Agregar fila
            $this->Cell($widths[0], 10, $this->utf8_to_fpdf($row['cedula']), 1, 0, 'C', true);
            $this->Cell($widths[1], 10, $this->utf8_to_fpdf($row['nombre']), 1, 0, 'L', true);
            $this->Cell($widths[2], 10, $this->utf8_to_fpdf($row['apellido']), 1, 0, 'L', true);
            $this->Cell($widths[3], 10, $this->utf8_to_fpdf(substr($row['direccion'] ?? 'N/A', 0, 25)), 1, 0, 'L', true);
            $this->Cell($widths[4], 10, $this->utf8_to_fpdf($row['telefono'] ?? 'N/A'), 1, 0, 'C', true);
            $this->Ln();
            
            $fill = !$fill;
        }
    }

    /**
     * Mensaje cuando no hay datos
     */
    public function addNoDataMessage()
    {
        $this->Ln(20);
        
        // Icono de información
        $this->SetFillColor(...$this->config['colors']['uta_wine']);
        $this->drawCircle(105, $this->GetY() + 15, 12, 'F');
        
        $this->SetTextColor(...$this->config['colors']['white']);
        $this->SetFont('Arial', 'B', 20);
        $this->SetXY(100, $this->GetY() + 10);
        $this->Cell(10, 10, 'i', 0, 0, 'C');
        
        $this->Ln(25);        // Mensaje
        $this->SetTextColor(...$this->config['colors']['black']);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $this->utf8_to_fpdf('No se encontraron estudiantes'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 8, $this->utf8_to_fpdf('No hay datos disponibles para mostrar.'), 0, 1, 'C');
        $this->Cell(0, 6, $this->utf8_to_fpdf('Verifique los criterios de búsqueda.'), 0, 1, 'C');
        
        // Borde decorativo
        $this->SetDrawColor(...$this->config['colors']['text_gray']);
        $this->SetLineWidth(1.5);
        $this->Rect(30, $this->GetY() - 35, 150, 45, 'D');
    }

    /**
     * Mensaje de error
     */
    public function addErrorMessage($message)
    {
        $this->Ln(20);
        
        // Icono de error
        $this->SetFillColor(...$this->config['colors']['uta_wine']);
        $this->drawCircle(105, $this->GetY() + 15, 12, 'F');
        
        $this->SetTextColor(...$this->config['colors']['white']);
        $this->SetFont('Arial', 'B', 20);
        $this->SetXY(100, $this->GetY() + 10);
        $this->Cell(10, 10, '!', 0, 0, 'C');
        
        $this->Ln(25);        // Mensaje de error
        $this->SetTextColor(...$this->config['colors']['black']);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $this->utf8_to_fpdf('Error al generar el reporte'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 8, $this->utf8_to_fpdf('Se produjo un error al procesar la solicitud.'), 0, 1, 'C');
        $this->Cell(0, 6, $this->utf8_to_fpdf('Contacte al administrador del sistema.'), 0, 1, 'C');
        
        $this->Ln(10);
        $this->SetFont(...$this->config['fonts']['small']);
        $this->Cell(0, 6, $this->utf8_to_fpdf('Detalle: ') . $this->utf8_to_fpdf($message), 0, 1, 'C');
        
        // Borde de error
        $this->SetDrawColor(...$this->config['colors']['text_gray']);
        $this->SetLineWidth(1.5);
        $this->Rect(30, $this->GetY() - 45, 150, 55, 'D');
    }

    /**
     * Método para dibujar círculos
     */
    private function drawCircle($x, $y, $r, $style = 'D')
    {
        $this->drawEllipse($x, $y, $r, $r, $style);
    }

    /**
     * Método para dibujar elipses
     */
    private function drawEllipse($x, $y, $rx, $ry, $style = 'D')
    {
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        
        $lx = 4/3 * (M_SQRT2 - 1) * $rx;
        $ly = 4/3 * (M_SQRT2 - 1) * $ry;
        $k = $this->k;
        $h = $this->h;
        
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $rx) * $k, ($h - $y) * $k,
            ($x + $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x + $lx) * $k, ($h - ($y - $ry)) * $k,
            $x * $k, ($h - ($y - $ry)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $lx) * $k, ($h - ($y - $ry)) * $k,
            ($x - $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x - $rx) * $k, ($h - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x - $lx) * $k, ($h - ($y + $ry)) * $k,
            $x * $k, ($h - ($y + $ry)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x + $lx) * $k, ($h - ($y + $ry)) * $k,
            ($x + $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x + $rx) * $k, ($h - $y) * $k, $op));
    }
    
    /**
     * Convertir texto UTF-8 para FPDF
     * Este método maneja la codificación correcta para mostrar tildes y caracteres especiales
     */
    private function utf8_to_fpdf($text) 
    {
        // Primero intentamos con iconv si está disponible
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ISO-8859-1//IGNORE', $text);
            if ($converted !== false) {
                return $converted;
            }
        }
        
        // Si iconv no está disponible o falla, usamos utf8_decode
        return utf8_decode($text);
    }
}

// Ejecutar el sistema
try {
    $reportSystem = new StudentReportSystem();
    $reportSystem->generateReport();
} catch (Exception $e) {
    ob_clean();
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h3><strong>Error en el Sistema de Reportes</strong></h3>";
    echo "<p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h4>Información del sistema:</h4>";
    echo "<ul>";
    echo "<li><strong>FPDF cargado:</strong> " . ($fpdf_loaded ? 'Sí desde: ' . $found_path : 'No') . "</li>";
    echo "<li><strong>Clase FPDF disponible:</strong> " . (class_exists('FPDF') ? 'Sí' : 'No') . "</li>";
    echo "<li><strong>Archivo encontrado:</strong> " . ($found_path ?: 'Ninguno') . "</li>";
    echo "</ul>";
    echo "<p><a href='check_reports.php' style='color: #721c24;'>► Ir a verificador del sistema</a></p>";
    echo "</div>";
}
?>
