<?php
require_once 'conexion.php';

// Iniciar sesión
session_start();

// Configurar cabeceras para respuesta JSON
header('Content-Type: application/json');

// Obtener datos del POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validar datos
if (!isset($_SESSION['usuario']['id'])) {
    $id_usuario = $_SESSION['usuario']['id'];
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

// Validar campos requeridos
if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Nombre, apellidos y correo son requeridos']);
    exit();
}

// Sanitizar datos
$nombre = trim($data['nombre']);
$apellidos = trim($data['apellidos']);
$correo = filter_var(trim($data['correo']), FILTER_SANITIZE_EMAIL);
$edad = isset($data['edad']) ? intval($data['edad']) : null;
$sexo = in_array($data['sexo'], ['masculino', 'femenino', 'otro']) ? $data['sexo'] : null;
$direccion = isset($data['direccion']) ? trim($data['direccion']) : null;

// Validar correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido']);
    exit();
}

// Actualizar en la base de datos (usando los nombres correctos de los campos)
try {
    $stmt = $conn->prepare("UPDATE usuarios SET 
        nombre = ?, 
        apellidos = ?, 
        correo = ?, 
        edad = ?, 
        sexo = ?, 
        direccion = ?
        WHERE id_usuario = ?");
    
    $stmt->bind_param("sssissi", 
        $nombre,
        $apellidos,
        $correo,
        $edad,
        $sexo,
        $direccion,
        $_SESSION['id_usuario']
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Perfil actualizado correctamente',
            'redirect' => ''
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al actualizar el perfil: ' . $conn->error
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>