<?php
// Limpiar buffers de salida
while (ob_get_level()) ob_end_clean();

// Configurar cabeceras
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Opcional para desarrollo

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "comedor_comunitario";

// Respuesta JSON
$response = [
    'success' => false,
    'message' => '',
    'redirect' => '../inicio/index.html' // Ruta absoluta recomendada
];

try {
    // 1. Validar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Solo se permiten peticiones POST", 405);
    }

    // 2. Validar campos obligatorios
    $requiredFields = ['nombre', 'apellidos', 'direccion', 'edad', 'sexo', 'contrasena', 'terminos'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo '$field' es obligatorio", 400);
        }
    }

    // 3. Validar términos
    if ($_POST['terminos'] !== 'on') {
        throw new Exception("Debes aceptar los términos y condiciones", 400);
    }

    // 4. Validar edad
    $edad = filter_var($_POST['edad'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 120]
    ]);
    if ($edad === false) {
        throw new Exception("La edad debe ser entre 1 y 120 años", 400);
    }

    // 5. Conectar a la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error, 500);
    }

    // 6. Preparar INSERT (con todos los campos)
    $stmt = $conn->prepare("
        INSERT INTO usuarios (
            nombre, 
            apellidos, 
            direccion, 
            edad, 
            sexo, 
            correo, 
            contrasena, 
            fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, SHA2(?,256) , NOW())
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error, 500);
    }

    $hashedPassword = $_POST['contrasena'];
    $correo = !empty($_POST['correo']) ? $_POST['correo'] : NULL;

    $stmt->bind_param(
        "sssisss", // Tipos: s=string, i=integer
        $_POST['nombre'],
        $_POST['apellidos'],
        $_POST['direccion'],
        $edad,
        $_POST['sexo'],
        $correo,
        $hashedPassword
    );

    // 8. Ejecutar consulta
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "¡Registro exitoso!";
    } else {
        throw new Exception("Error al guardar: " . $stmt->error, 500);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
} finally {
    // 9. Cerrar conexiones
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}

// 10. Enviar respuesta
echo json_encode($response);
exit;
?>