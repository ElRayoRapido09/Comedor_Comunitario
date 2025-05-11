<?php
// Iniciar sesión al principio del archivo
header('Content-Type: application/json');

// Iniciar sesión DEBE ser lo primero
session_start();

// Verificar autenticación usando tu estructura de sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

require_once 'database.php';

// Verificar autenticación primero
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    error_log("Intento de reserva sin autenticación. Datos de sesión: " . print_r($_SESSION, true));
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validación de campos requeridos
$required = ['id_menu', 'hora_recogida', 'num_porciones'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['error' => "Falta el campo requerido: $field"]);
        exit();
    }
}

try {
    $db = (new Database())->getConnection();

    // Generar código de reserva
    $fecha = new DateTime();
    $codigo = 'CC-' . $fecha->format('d-m-') . rand(100, 999);

    // Insertar reserva
    $stmt = $db->prepare("INSERT INTO reservaciones (
        codigo_reservacion, 
        id_usuario, 
        id_menu, 
        fecha_reservacion, 
        hora_recogida, 
        num_porciones, 
        estado, 
        notas
    ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?)");

    $success = $stmt->execute([
        $codigo,
        $_SESSION['usuario']['id'], // ID de usuario desde sesión
        $data['id_menu'],
        $fecha->format('Y-m-d'), // Fecha actual
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
}
?>