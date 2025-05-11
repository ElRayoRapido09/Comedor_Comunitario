<?php
header('Content-Type: application/json');
session_start();
require_once 'database.php';

// Verificar método y autenticación
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

if (!isset($_SESSION['usuario']['id_usuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
$required = ['id_menu', 'hora_recogida', 'num_porciones'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['error' => "Falta el campo: $field"]);
        exit();
    }
}

// Validaciones adicionales
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['hora_recogida'])) {
    echo json_encode(['error' => 'Formato de hora inválido']);
    exit();
}

if ($data['num_porciones'] < 1 || $data['num_porciones'] > 3) {
    echo json_encode(['error' => 'Número de porciones inválido (1-3)']);
    exit();
}

try {
    $db = (new Database())->getConnection();

    // Generar código de reserva: CC-dia-mes-numero
    $fecha = new DateTime();
    $codigo = 'CC-' . $fecha->format('d-m-') . rand(100, 999);

    // Insertar reserva
    $stmt = $db->prepare("INSERT INTO reservaciones (
        codigo_reservacion, id_usuario, id_menu, fecha_reservacion, 
        hora_recogida, num_porciones, estado, notas
    ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?)");

    $success = $stmt->execute([
        $codigo,
        $_SESSION['usuario']['id_usuario'],
        $data['id_menu'],
        $fecha->format('Y-m-d'),
        $data['hora_recogida'],
        $data['num_porciones'],
        $data['notas'] ?? null
    ]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'codigo_reservacion' => $codigo,
            'id_reservacion' => $db->lastInsertId()
        ]);
    } else {
        echo json_encode(['error' => 'Error al guardar la reserva']);
    }
} catch (PDOException $e) {
    error_log("Error en reserva: " . $e->getMessage());
    echo json_encode(['error' => 'Error en la base de datos']);
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    echo json_encode(['error' => 'Error inesperado']);
}
?>