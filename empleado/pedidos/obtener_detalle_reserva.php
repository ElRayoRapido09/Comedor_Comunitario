<?php
include 'conexion.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de reservación no proporcionado']);
    exit;
}

$reservaId = $_GET['id'];

try {
    // Consulta modificada para usar solo columnas existentes
    $stmt = $conn->prepare("
        SELECT 
            r.id_reservacion,
            r.codigo_reservacion,
            CONCAT(u.nombre, ' ', u.apellidos) AS nombre,
            u.correo,  -- Usando correo en lugar de teléfono
            u.direccion,
            r.fecha_reservacion AS fecha,
            DATE_FORMAT(r.hora_recogida, '%H:%i') AS hora,
            r.num_porciones,
            r.estado,
            r.notas,
            r.fecha_creacion AS fecha_registro
        FROM reservaciones r
        JOIN usuarios u ON r.id_usuario = u.id_usuario
        WHERE r.id_reservacion = ?
    ");
    $stmt->bind_param("i", $reservaId);
    $stmt->execute();
    $reserva = $stmt->get_result()->fetch_assoc();
    
    if (!$reserva) {
        throw new Exception("Reservación no encontrada");
    }

    // Obtener platillos (igual que antes)
    $stmt = $conn->prepare("
        SELECT 
            p.nombre_platillo,
            rp.tipo_seccion
        FROM reservacion_platillos rp
        JOIN platillos p ON rp.id_platillo = p.id_platillo
        WHERE rp.id_reservacion = ?
    ");
    $stmt->bind_param("i", $reservaId);
    $stmt->execute();
    $platillosResult = $stmt->get_result();
    
    $platillos = [
        'primer_tiempo' => '',
        'plato_principal' => '',
        'bebida' => ''
    ];
    
    while ($row = $platillosResult->fetch_assoc()) {
        $platillos[$row['tipo_seccion']] = $row['nombre_platillo'];
    }

    echo json_encode([
        'success' => true,
        'reserva' => $reserva,
        'platillos' => $platillos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>