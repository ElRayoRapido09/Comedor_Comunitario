<?php
ob_start();
header('Content-Type: application/json');
session_start();

// Verificación de sesión mejorada
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    echo json_encode(['error' => 'Acceso no autorizado. Por favor inicie sesión.']);
    ob_end_flush();
    exit();
}

require_once 'database.php';

// Validación del método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido. Use POST.']);
    ob_end_flush();
    exit();
}

// Obtener y validar datos JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Datos JSON inválidos', 'detalle' => json_last_error_msg()]);
    ob_end_flush();
    exit();
}

// Validar campos requeridos
$required = ['id_menu', 'hora_recogida', 'num_porciones', 'platillos'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['error' => "Falta el campo requerido: $field"]);
        ob_end_flush();
        exit();
    }
}

// Validación adicional
if ($data['num_porciones'] < 1 || $data['num_porciones'] > 3) {
    echo json_encode(['error' => 'Número de porciones inválido (1-3)']);
    ob_end_flush();
    exit();
}

try {
    $db = (new Database())->getConnection();
    $db->beginTransaction();

    // 1. Generar código de reserva único (formato CC-AAMMDD-NN)
    $fecha_actual = new DateTime();
    $codigo_base = 'CC-' . $fecha_actual->format('ymd');
    
    // Obtener el último número de secuencia para hoy
    $stmt_secuencia = $db->prepare("SELECT COUNT(*) as total FROM reservaciones WHERE codigo_reservacion LIKE :codigo_base");
    $stmt_secuencia->bindValue(':codigo_base', $codigo_base . '%');
    $stmt_secuencia->execute();
    $secuencia = $stmt_secuencia->fetch(PDO::FETCH_ASSOC)['total'] + 1;
    
    $codigo_reservacion = $codigo_base . '-' . str_pad($secuencia, 2, '0', STR_PAD_LEFT);

    // 2. Insertar reserva principal
    $stmt_reserva = $db->prepare("INSERT INTO reservaciones (
        codigo_reservacion,
        id_usuario,
        id_menu,
        fecha_reservacion,
        hora_recogida,
        num_porciones,
        estado,
        notas,
        fecha_creacion
    ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?, NOW())");

    $success = $stmt_reserva->execute([
        $codigo_reservacion,
        $_SESSION['usuario']['id'],
        $data['id_menu'],
        $fecha_actual->format('Y-m-d'),
        $data['hora_recogida'],
        $data['num_porciones'],
        $data['notas'] ?? null
    ]);

    if (!$success) {
        throw new Exception('Error al crear la reserva principal');
    }

    $id_reservacion = $db->lastInsertId();

    // 3. Insertar platillos seleccionados
    $stmt_secciones = $db->query("SELECT id_seccion, nombre_seccion FROM secciones_menu");
    $secciones_db = $stmt_secciones->fetchAll(PDO::FETCH_KEY_PAIR);

    // Mapeo de tipos de sección
    $mapeo_tipos = [
        'Entrada' => 'primer_tiempo',
        'Plato Principal' => 'plato_principal',
        'Acompañamiento' => 'acompanamiento',
        'Postre' => 'postre',
        'Bebida' => 'bebida'
    ];

    foreach ($data['platillos'] as $id_seccion => $id_platillo) {
        $nombre_seccion = $secciones_db[$id_seccion] ?? 'Plato Principal';
        $tipo_seccion = $mapeo_tipos[$nombre_seccion] ?? 'plato_principal';

        $stmt_platillo = $db->prepare("INSERT INTO reservacion_platillos (
            id_reservacion,
            id_platillo,
            tipo_seccion
        ) VALUES (?, ?, ?)");

        if (!$stmt_platillo->execute([$id_reservacion, $id_platillo, $tipo_seccion])) {
            throw new Exception("Error al guardar el platillo $id_platillo para la sección $id_seccion");
        }
    }

    $db->commit();

    // Respuesta exitosa
    $response = [
        'success' => true,
        'codigo_reservacion' => $codigo_reservacion,
        'id_reservacion' => $id_reservacion,
        'fecha' => $fecha_actual->format('d/m/Y'),
        'hora' => $data['hora_recogida'],
        'detalle' => 'Reserva registrada correctamente',
        'usuario' => [
            'id' => $_SESSION['usuario']['id'],
            'nombre' => $_SESSION['usuario']['nombre']
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    error_log("Error en base de datos: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error al procesar la reserva',
        'detalle' => $e->getMessage(),
        'codigo_generado' => $codigo_reservacion ?? 'N/A'
    ]);
} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    error_log("Error general: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

ob_end_flush();
?>