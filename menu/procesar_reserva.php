<?php
header('Content-Type: application/json');
require_once 'database.php';

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Iniciar sesión para acceder al ID del usuario
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario']['id_usuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// Obtener y validar los datos del formulario
$data = json_decode(file_get_contents('php://input'), true);

$required_fields = ['id_menu', 'hora_recogida', 'num_porciones'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['error' => "Campo requerido faltante: $field"]);
        exit();
    }
}

$id_menu = $data['id_menu'];
$hora_recogida = $data['hora_recogida'];
$num_porciones = intval($data['num_porciones']);
$notas = isset($data['notas']) ? trim($data['notas']) : null;

// Validar número de porciones (máximo 3)
if ($num_porciones < 1 || $num_porciones > 3) {
    echo json_encode(['error' => 'Número de porciones inválido (1-3)']);
    exit();
}

// Validar formato de hora (HH:MM)
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_recogida)) {
    echo json_encode(['error' => 'Formato de hora inválido. Use HH:MM']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Generar código de reserva (CC-dia/mes-numero)
    $fecha_actual = new DateTime();
    $codigo_reservacion = 'CC-' . $fecha_actual->format('d/m') . '-' . rand(100, 999);

    // Insertar la reserva en la base de datos
    $query = "INSERT INTO reservaciones (
                codigo_reservacion, 
                id_usuario, 
                id_menu, 
                fecha_reservacion, 
                hora_recogida, 
                num_porciones, 
                estado, 
                notas
              ) VALUES (
                :codigo_reservacion, 
                :id_usuario, 
                :id_menu, 
                :fecha_reservacion, 
                :hora_recogida, 
                :num_porciones, 
                'pendiente', 
                :notas
              )";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':codigo_reservacion', $codigo_reservacion);
    $stmt->bindParam(':id_usuario', $_SESSION['usuario']['id_usuario']);
    $stmt->bindParam(':id_menu', $id_menu);
    $stmt->bindParam(':fecha_reservacion', $fecha_actual->format('Y-m-d'));
    $stmt->bindParam(':hora_recogida', $hora_recogida);
    $stmt->bindParam(':num_porciones', $num_porciones, PDO::PARAM_INT);
    $stmt->bindParam(':notas', $notas);

    if ($stmt->execute()) {
        // Obtener el ID de la reserva recién creada
        $id_reservacion = $db->lastInsertId();

        // Aquí podrías insertar también los platillos específicos si es necesario
        // en la tabla reservacion_platillos

        echo json_encode([
            'success' => true,
            'codigo_reservacion' => $codigo_reservacion,
            'id_reservacion' => $id_reservacion
        ]);
    } else {
        echo json_encode(['error' => 'Error al crear la reserva']);
    }

} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    echo json_encode(['error' => 'Error al procesar la reserva']);
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    echo json_encode(['error' => 'Error inesperado']);
}
?>