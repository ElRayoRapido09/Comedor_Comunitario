<?php
require_once 'session_check.php';

// Configuración de respuesta
header('Content-Type: application/json');

// Limpiar buffer de salida
while (ob_get_level()) ob_end_clean();

// Solo método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

// Validar entrada
$codigo_reservacion = filter_input(INPUT_POST, 'codigo_reservacion', FILTER_SANITIZE_STRING);
if (empty($codigo_reservacion)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Código requerido']));
}

try {
    require_once 'conexion.php';
    
    $stmt = $conn->prepare("
        UPDATE reservaciones 
        SET estado = 'completada', 
            id_usuario_atendio = :id_usuario, 
            fecha_atencion = NOW() 
        WHERE codigo_reservacion = :codigo
        AND estado = 'pendiente'
    ");
    
    $stmt->execute([
        ':id_usuario' => $_SESSION['usuario']['id_usuario'],
        ':codigo' => $codigo_reservacion
    ]);
    
    if ($stmt->rowCount() > 0) {
        die(json_encode([
            'success' => true,
            'message' => 'Reservación completada'
        ]));
    }
    
    http_response_code(404);
    die(json_encode([
        'success' => false,
        'message' => 'Reservación no encontrada'
    ]));
    
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Error en base de datos',
        'error' => $e->getMessage()
    ]));
}
?>