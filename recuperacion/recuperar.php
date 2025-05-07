<?php
require 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $conn = conectarDB();
    
    // Paso 1: Solicitud de recuperación (email)
    if (isset($_POST['email']) && !isset($_POST['codigo']) && !isset($_POST['nueva-contrasena'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Por favor, ingrese un correo electrónico válido';
            echo json_encode($response);
            exit;
        }
        
        // Verificar si el email existe en la base de datos
        $stmt = $conn->prepare("SELECT id_usuario, nombre FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = 'No existe una cuenta con este correo electrónico';
            echo json_encode($response);
            exit;
        }
        
        $user = $result->fetch_assoc();
        $nombre_usuario = $user['nombre'];
        
        // Generar código de verificación de 6 dígitos
        $codigo_verificacion = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Guardar código en la base de datos
        $stmt = $conn->prepare("UPDATE usuarios SET token_recuperacion = ?, token_expiracion = ? WHERE correo = ?");
        if (!$stmt) {
            throw new Exception("Error en preparación de consulta: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $codigo_verificacion, $expira, $email);
        
        if ($stmt->execute()) {
            // Enviar correo electrónico con el código
            $asunto = "Codigo de verificacion - Comedor Comunitario";
            $mensaje = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #9e1c3f;'>Hola $nombre_usuario 😃</h2>
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en el Comedor Comunitario.</p>
                    
                    <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #9e1c3f; margin: 20px 0;'>
                        <p style='margin: 0; font-weight: bold;'>Tu código de verificación es:</p>
                        <p style='font-size: 24px; font-weight: bold; letter-spacing: 3px; margin: 10px 0; color: #9e1c3f;'>$codigo_verificacion</p>
                        <p style='margin: 0; font-size: 12px;'>Este código expirará en 1 hora.</p>
                    </div>
                    
                    <p><strong>Instrucciones:</strong></p>
                    <ol>
                        <li>Ingresa el código de verificación en la página de recuperación de contraseña.</li>
                        <li>Crea una nueva contraseña segura.</li>
                        <li>Confirma la nueva contraseña.</li>
                    </ol>
                    
                    <p>Si no solicitaste este cambio, por favor ignora este mensaje o contacta con nuestro equipo de soporte.</p>
                    
                    <p style='margin-top: 30px;'>Atentamente:<br> ❤ El equipo del Comedor Comunitario ❤</p>
                    
                    <div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>
                        <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                    </div>
                </div>
            ";
            
            if (enviarCorreo($email, $asunto, $mensaje)) {
                $response['success'] = true;
                $response['message'] = 'Se ha enviado un código de verificación a tu correo electrónico';
                $response['email'] = $email;
            } else {
                $response['message'] = 'Error al enviar el correo electrónico';
            }
        } else {
            $response['message'] = 'Error al generar el código de verificación: ' . $stmt->error;
        }
    }
    // Paso 2: Verificación del código
    elseif (isset($_POST['codigo']) && isset($_POST['email'])) {
        $codigo = $_POST['codigo'];
        $email = $_POST['email'];
        
        // Verificar código y fecha de expiración
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND token_recuperacion = ? AND token_expiracion > NOW()");
        $stmt->bind_param("ss", $email, $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $response['success'] = true;
            $response['email'] = $email;
        } else {
            $response['message'] = 'El código es inválido o ha expirado';
        }
    }
    // Paso 3: Actualización de contraseña
    elseif (isset($_POST['nueva-contrasena']) && isset($_POST['confirmar-contrasena']) && isset($_POST['email'])) {
        $email = $_POST['email'];
        $nuevaContrasena = $_POST['nueva-contrasena'];
        $confirmarContrasena = $_POST['confirmar-contrasena'];
        
        // Validar contraseñas
        if (strlen($nuevaContrasena) < 8) {
            $response['message'] = 'La contraseña debe tener al menos 8 caracteres';
            echo json_encode($response);
            exit;
        }
        
        if ($nuevaContrasena !== $confirmarContrasena) {
            $response['message'] = 'Las contraseñas no coinciden';
            echo json_encode($response);
            exit;
        }
        
        
        // Iniciar transacción para asegurar la operación
        $conn->begin_transaction();
        
        try {
            // Actualizar contraseña y limpiar token
            $stmt = $conn->prepare("UPDATE usuarios SET contrasena = SHA2(?,256), token_recuperacion = NULL, token_expiracion = NULL WHERE correo = ?");
            if (!$stmt) {
                throw new Exception("Error preparando statement: " . $conn->error);
            }
            
            $stmt->bind_param("ss", $nuevaContrasena, $email);
            
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando statement: " . $stmt->error);
            }
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("No se encontró el usuario o no se realizaron cambios");
            }
            
            // Obtener nombre del usuario para el correo
            $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE correo = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $nombre_usuario = $user['nombre'];
            
            $conn->commit();
            
            $response['success'] = true;
            $response['message'] = 'Contraseña actualizada correctamente';
            
            // Enviar confirmación por correo
            $asunto = "Contraseña actualizada - Comedor Comunitario";
            $mensaje = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #9e1c3f;'>Hola $nombre_usuario 😃</h2>
                    <p>La contraseña de tu cuenta en el Comedor Comunitario ha sido actualizada exitosamente.</p>
                    
                    <p><strong>Detalles del cambio:</strong></p>
                    <ul>
                        <li>EL cambio se realizo en la cuenta de: " . $nombre_usuario . "</li>
                        <li>Con el correro: " . $email . "</li>
                    </ul>
                    
                    <p>Si no realizaste este cambio, por favor contacta inmediatamente con nuestro equipo de soporte.</p>
                    
                    <p style='margin-top: 30px;'>Atentamente:<br>❤ El equipo del Comedor Comunitario ❤</p>
                    
                    <div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>
                        <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                    </div>
                </div>
            ";
            
            enviarCorreo($email, $asunto, $mensaje);
            
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error al actualizar la contraseña: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Solicitud no válida';
    }
    
    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>