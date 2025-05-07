<?php
header('Content-Type: application/json');
include 'conexion.php';

// Debug: Verificar si la conexión funciona
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión: ' . $conn->connect_error
    ]));
}

$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Debug: Registrar la fecha recibida
error_log("Fecha solicitada: " . $fecha);

try {
    $query = "
        SELECT 
            r.id_reservacion AS id,
            r.codigo_reservacion AS codigo_reserva,
            CONCAT(u.nombre, ' ', u.apellidos) AS nombre,
            DATE_FORMAT(r.hora_recogida, '%H:%i') AS hora,
            r.num_porciones AS porciones,
            r.estado
        FROM reservaciones r
        JOIN usuarios u ON r.id_usuario = u.id_usuario
        WHERE r.fecha_reservacion = ?
        ORDER BY r.hora_recogida
    ";
    
    error_log("Consulta SQL: " . $query); // Debug
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en preparación: " . $conn->error);
    }
    
    $stmt->bind_param("s", $fecha);
    if (!$stmt->execute()) {
        throw new Exception("Error en ejecución: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $reservaciones = [];
    
    while ($row = $result->fetch_assoc()) {
        $reservaciones[] = $row;
    }
    
    // Debug: Ver datos obtenidos
    error_log("Datos obtenidos: " . print_r($reservaciones, true));
    
    echo json_encode([
        'success' => true,
        'data' => $reservaciones,
        'debug' => [
            'query' => $query,
            'fecha' => $fecha,
            'num_results' => count($reservaciones)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $conn->error
    ]);
}

$conn->close();
?>