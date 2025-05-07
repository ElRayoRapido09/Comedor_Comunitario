<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

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
                'summary' => [
                    'total_beneficiarios' => getTotalBeneficiarios($conn, $startDate, $endDate),
                    'total_reservaciones' => getTotalReservaciones($conn, $startDate, $endDate),
                    'total_porciones' => getTotalPorciones($conn, $startDate, $endDate),
                    'costo_promedio' => getCostoPromedio($conn, $startDate, $endDate)
                ],
                'reservations_by_day' => getReservationsByDay($conn, $startDate, $endDate),
                'users_distribution' => getUsersDistribution($conn),
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
    
    // Total Beneficiarios
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT id_usuario) as total FROM reservaciones 
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['total_beneficiarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Reservaciones
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reservaciones 
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['total_reservaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Porciones
    $stmt = $conn->prepare("SELECT SUM(num_porciones) as total FROM reservaciones 
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['total_porciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Costo Promedio (simplificado)
    $stmt = $conn->prepare("SELECT AVG(m.precio) as promedio FROM menus_dia m
                           JOIN reservaciones r ON m.id_menu = r.id_menu
                           WHERE r.fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $summary['costo_promedio'] = number_format($stmt->fetch(PDO::FETCH_ASSOC)['promedio'], 2);
    
    return $summary;
}

function getReservationsByDay($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT DATE(fecha_reservacion) as dia, COUNT(*) as cantidad 
                           FROM reservaciones 
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate
                           GROUP BY DATE(fecha_reservacion) 
                           ORDER BY dia");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsersDistribution($conn) {
    $distribution = [];
    
    // Por sexo
    $stmt = $conn->prepare("SELECT sexo, COUNT(*) as cantidad FROM usuarios 
                           WHERE tipo_usuario = 'beneficiario' AND activo = 1
                           GROUP BY sexo");
    $stmt->execute();
    $distribution['sexo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Por edad
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
        WHERE tipo_usuario = 'beneficiario' AND activo = 1
        GROUP BY grupo_edad");
    $stmt->execute();
    $distribution['edad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $distribution;
}

function getRecentActivity($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT 
        DATE(r.fecha_reservacion) as fecha,
        COUNT(*) as reservaciones,
        SUM(r.num_porciones) as porciones,
        SUM(CASE WHEN r.estado = 'completada' THEN 1 ELSE 0 END) as completadas,
        SUM(CASE WHEN r.estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
        ROUND((SUM(CASE WHEN r.estado = 'completada' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as tasa_asistencia
        FROM reservaciones r
        WHERE r.fecha_reservacion BETWEEN :startDate AND :endDate
        GROUP BY DATE(r.fecha_reservacion)
        ORDER BY fecha DESC
        LIMIT 5");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getTotalBeneficiarios($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT r.id_usuario) as total 
                           FROM reservaciones r
                           JOIN usuarios u ON r.id_usuario = u.id_usuario
                           WHERE r.fecha_reservacion BETWEEN :startDate AND :endDate
                           AND u.tipo_usuario = 'beneficiario' AND u.activo = 1");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function getTotalReservaciones($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
                           FROM reservaciones
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function getTotalPorciones($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT SUM(num_porciones) as total 
                           FROM reservaciones
                           WHERE fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function getCostoPromedio($conn, $startDate, $endDate) {
    $stmt = $conn->prepare("SELECT AVG(m.precio) as promedio 
                           FROM menus_dia m
                           JOIN reservaciones r ON m.id_menu = r.id_menu
                           WHERE r.fecha_reservacion BETWEEN :startDate AND :endDate");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    return number_format($stmt->fetch(PDO::FETCH_ASSOC)['promedio'], 2);
}
?>