<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "comedor_comunitario";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres
$conn->set_charset("utf8");

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar los datos del formulario
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $apellidos = htmlspecialchars(trim($_POST['apellidos']));
    $direccion = htmlspecialchars(trim($_POST['direccion']));
    $edad = intval($_POST['edad']);
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    
    // Validar los datos
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es requerido";
    }
    
    if (empty($apellidos)) {
        $errores[] = "Los apellidos son requeridos";
    }
    
    if (empty($direccion)) {
        $errores[] = "La dirección es requerida";
    }
    
    if ($edad < 18) {
        $errores[] = "El empleado debe ser mayor de 18 años";
    }
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (strlen($_POST['contrasena']) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    if ($_POST['contrasena'] !== $_POST['confirmar-contrasena']) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    if (!isset($_POST['terminos'])) {
        $errores[] = "Debes aceptar los términos y condiciones";
    }
    
    // Si no hay errores, insertar en la base de datos
    if (empty($errores)) {
        try {
            // Verificar si el correo ya existe
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $errores[] = "El correo electrónico ya está registrado";
            } else {
                // Insertar el nuevo empleado
                $tipo_usuario = "empleado";
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, correo, direccion, edad, contrasena, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssiss", $nombre, $apellidos, $correo, $direccion, $edad, $contrasena, $tipo_usuario);
                
                if ($stmt->execute()) {
                    // Éxito en el registro
                    $response = [
                        'success' => true,
                        'message' => 'Empleado registrado correctamente'
                    ];
                } else {
                    $errores[] = "Error al registrar el empleado: " . $stmt->error;
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $errores[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
    
    // Si hay errores, devolverlos
    if (!empty($errores)) {
        $response = [
            'success' => false,
            'errors' => $errores
        ];
    }
    
    // Devolver respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$conn->close();
?>