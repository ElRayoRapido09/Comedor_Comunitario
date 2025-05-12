<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'session_check.php';

$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "comedor_comunitario";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET['action'] ?? '';
    
    switch($action) {
        case 'get_report_data':
            $startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-1 month'));
            $endDate = $_GET['endDate'] ?? date('Y-m-d');
            
            // Obtener datos del reporte
            $reportData = [
                'summary' => getSummaryData($conn, $startDate, $endDate),
                'reservations_by_day' => getReservationsByDay($conn, $startDate, $endDate),
                'users_distribution' => getUsersDistribution($conn, $startDate, $endDate),
                'recent_activity' => getRecentActivity($conn, $startDate, $endDate)
            ];
            
            echo json_encode(['success' => true, 'data' => $reportData]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}

function getSummaryData($conn, $startDate, $endDate) {
    $summary = [];
    
    // Total Beneficiarios Únicos (registrados en el rango de fechas)
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
                           FROM usuarios 
                           WHERE tipo_usuario = 'beneficiario' 
                           AND activo = 1
                           AND DATE(fecha_registro) BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['total_beneficiarios'] = $stmt->fetchColumn() ?: 0;
    
    // Total Reservaciones
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
                           FROM reservaciones
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['total_reservaciones'] = $stmt->fetchColumn() ?: 0;
    
    // Total Porciones
    $stmt = $conn->prepare("SELECT COALESCE(SUM(num_porciones), 0) as total 
                           FROM reservaciones
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['total_porciones'] = $stmt->fetchColumn() ?: 0;
    
    // Costo Promedio
    $stmt = $conn->prepare("SELECT COALESCE(AVG(m.precio), 0) as promedio 
                           FROM menus_dia m
                           JOIN reservaciones r ON m.id_menu = r.id_menu
                           WHERE r.fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['costo_promedio'] = number_format($stmt->fetchColumn() ?: 0, 2);
    
    return $summary;
}

function getReservationsByDay($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT 
        DATE(fecha_reservacion) as dia, 
        COUNT(*) as cantidad,
        SUM(num_porciones) as porciones,
        GROUP_CONCAT(codigo_reservacion SEPARATOR ', ') as codigos_reservacion
        FROM reservaciones 
        WHERE fecha_reservacion BETWEEN :startDate AND :endDate
        GROUP BY DATE(fecha_reservacion) 
        ORDER BY dia DESC");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    
    // Para depuración - verificar los datos que se están obteniendo
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Datos de reservaciones por día: " . print_r($results, true));
    
    return $results;
}

function getUsersDistribution($conn, $startDate, $endDate) {
    $distribution = [];
    
    // Distribución por sexo (registrados en el rango de fechas)
    $stmt = $conn->prepare("SELECT 
        COALESCE(sexo, 'no especificado') as sexo, 
        COUNT(*) as cantidad 
        FROM usuarios 
        WHERE tipo_usuario = 'beneficiario' 
        AND activo = 1
        AND DATE(fecha_registro) BETWEEN :startDate AND :endDate
        GROUP BY sexo");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $distribution['sexo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Distribución por edad (registrados en el rango de fechas)
    $stmt = $conn->prepare("SELECT 
        CASE 
            WHEN edad < 18 THEN 'Menores de 18'
            WHEN edad BETWEEN 18 AND 30 THEN '18-30 años'
            WHEN edad BETWEEN 31 AND 50 THEN '31-50 años'
            WHEN edad > 50 THEN 'Mayores de 50'
            ELSE 'No especificado'
        END as grupo_edad,
        COUNT(*) as cantidad
        FROM usuarios 
        WHERE tipo_usuario = 'beneficiario' 
        AND activo = 1
        AND DATE(fecha_registro) BETWEEN :startDate AND :endDate
        GROUP BY grupo_edad");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $distribution['edad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $distribution;
}

function getRecentActivity($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT 
        DATE(fecha_reservacion) as fecha,
        COUNT(*) as reservaciones,
        SUM(num_porciones) as porciones,
        SUM(estado = 'completada') as completadas,
        SUM(estado = 'cancelada') as canceladas,
        CASE 
            WHEN COUNT(*) = 0 THEN 0
            ELSE ROUND(SUM(estado = 'completada') * 100 / COUNT(*), 0)
        END as tasa_asistencia
        FROM reservaciones
        WHERE fecha_reservacion BETWEEN :startDate AND :endDate
        GROUP BY DATE(fecha_reservacion)
        HAVING reservaciones > 0
        ORDER BY fecha DESC
        LIMIT 5");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>