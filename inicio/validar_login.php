<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'comedor_comunitario';
$username = 'root';
$password = '12345';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $correo = $data['correo'];
    $contrasena = $data['contrasena'];
    $tipoUsuarioEsperado = $data['tipoUsuario'];
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = :correo AND activo = TRUE");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificación para SHA-256
        $hashIngresado = hash('sha256', $contrasena);
        
        if ($hashIngresado === $usuario['contrasena']) {
            if ($usuario['tipo_usuario'] === $tipoUsuarioEsperado || 
                ($tipoUsuarioEsperado === 'empleado' && $usuario['tipo_usuario'] === 'admin')) {
                
                session_start();
                $_SESSION['usuario'] = [
                    'id' => $usuario['id_usuario'],
                    'nombre' => $usuario['nombre'],
                    'apellidos' => $usuario['apellidos'],
                    'correo' => $usuario['correo'],
                    'tipo_usuario' => $usuario['tipo_usuario']
                ];
                
                if ($data['recordar']) {
                    $token = bin2hex(random_bytes(32));
                    $expira = time() + (30 * 24 * 60 * 60);
                    
                    $stmt = $conn->prepare("UPDATE usuarios SET recordar_token = :token WHERE id_usuario = :id");
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':id', $usuario['id_usuario']);
                    $stmt->execute();
                    
                    setcookie('recordar_token', $token, $expira, '/');
                }
                
                echo json_encode([
                    'success' => true,
                    'tipo_usuario' => $usuario['tipo_usuario']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para acceder como ' . $tipoUsuarioEsperado
                ]);
            }
        } else {
            // Añade esto temporalmente para depuración
            error_log("Contraseña ingresada: " . $contrasena);
            error_log("Hash generado: " . $hashIngresado);
            error_log("Hash en BD: " . $usuario['contrasena']);
            
            echo json_encode([
                'success' => false,
                'message' => 'Contraseña incorrecta',
                'debug' => [
                    'input' => $contrasena,
                    'generated_hash' => $hashIngresado,
                    'stored_hash' => $usuario['contrasena']
                ]
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>