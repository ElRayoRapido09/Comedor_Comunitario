<?php
include 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_reservacion = $_POST['id'] ?? null;
    $nuevo_estado = $_POST['estado'] ?? null;

    if (!$id_reservacion || !$nuevo_estado) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    try {
        // Validar que la reservación existe
        $stmt = $conn->prepare("SELECT id_reservacion FROM reservaciones WHERE id_reservacion = ?");
        $stmt->bind_param("i", $id_reservacion);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Reservación no encontrada");
        }

        // Actualizar estado
        $stmt = $conn->prepare("UPDATE reservaciones SET estado = ? WHERE id_reservacion = ?");
        $stmt->bind_param("si", $nuevo_estado, $id_reservacion);
        
        if ($stmt->execute()) {
            // Si se completa, registrar asistencia
            if ($nuevo_estado == 'completada') {
                $usuario_atendio = 4; // ID del usuario empleado (debería venir de la sesión)
                $stmt = $conn->prepare("UPDATE reservaciones SET id_usuario_atendio = ?, fecha_atencion = NOW() WHERE id_reservacion = ?");
                $stmt->bind_param("ii", $usuario_atendio, $id_reservacion);
                $stmt->execute();
                
                // Registrar en tabla asistencias
                $stmt = $conn->prepare("INSERT INTO asistencias (id_reservacion, confirmada_por) VALUES (?, ?)");
                $stmt->bind_param("ii", $id_reservacion, $usuario_atendio);
                $stmt->execute();
            }
            
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            throw new Exception("Error al actualizar: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>