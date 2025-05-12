<?php
session_start();
require_once 'session_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: escaneo_trabajador.php');
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['codigo_reservacion']) || !isset($_SESSION['usuario']['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

require_once 'conexion.php';

try {
    $stmt = $conn->prepare("
        UPDATE reservaciones 
        SET estado = 'completada', 
            id_usuario_atendio = :id_usuario_atendio, 
            fecha_atencion = NOW() 
        WHERE codigo_reservacion = :codigo_reservacion
    ");
    
    $stmt->execute([
        ':codigo_reservacion' => $data['codigo_reservacion'],
        ':id_usuario_atendio' => $_SESSION['usuario']['id_usuario']
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Reservación no encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}