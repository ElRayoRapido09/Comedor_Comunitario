<?php
session_start();
require_once 'session_check.php';
require_once 'conexion.php';

// Configurar el tipo de contenido primero
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]));
}

// Verificar datos requeridos
if (empty($_POST['codigo_reservacion'])) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Código de reservación requerido'
    ]));
}

// Asegurar que el ID de usuario existe en sesión
if (!isset($_SESSION['usuario']['id_usuario'])) {
    http_response_code(401);
    die(json_encode([
        'success' => false,
        'message' => 'ID de usuario no encontrado en sesión'
    ]));
}

try {
    // Actualizar reservación
    $stmt = $conn->prepare("
        UPDATE reservaciones 
        SET estado = 'completada', 
            id_usuario_atendio = :id_usuario_atendio, 
            fecha_atencion = NOW() 
        WHERE codigo_reservacion = :codigo_reservacion
        AND estado = 'pendiente'
    ");
    
    $stmt->execute([
        ':codigo_reservacion' => $_POST['codigo_reservacion'],
        ':id_usuario_atendio' => $_SESSION['usuario']['id_usuario']
    ]);
    
    // Verificar si se actualizó alguna fila
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Pedido completado con éxito'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la reservación o ya fue completada'
        ]);
    }
    exit();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
    exit();
}
?>